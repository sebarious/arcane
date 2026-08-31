<?php

namespace App\Filament\Resources\CardInventories;

use App\Enums\Game;
use App\Filament\Exports\SoldCardExporter;
use App\Models\CardInventory;
use App\Services\Pricing\PulseApiPriceProvider;
use App\Services\PulseApi\PulseApiCardMapper;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use UnitEnum;

class CardInventoryResource extends Resource
{
    protected static ?string $model = CardInventory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Inventory';

    protected static ?string $pluralModelLabel = 'Inventory';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Card')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Select::make('game')
                        ->label('Game')
                        ->options(collect(Game::cases())->mapWithKeys(
                            fn (Game $g) => [$g->value => $g->label()]
                        ))
                        ->default(Game::Pokemon->value)
                        ->required(),
                    Forms\Components\Select::make('product_id')
                        ->label('Card')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => PulseApiCardMapper::searchOptions($search))
                        ->getOptionLabelUsing(fn ($value) => PulseApiCardMapper::labelForProductId($value))
                        ->required(),
                ]),

            Section::make('Acquisition')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('cost_pounds')
                        ->label('Cost (£)')
                        ->numeric()
                        ->step(0.01)
                        ->prefix('£')
                        ->required()
                        ->afterStateHydrated(fn ($component, $record) => $record && $component->state($record->cost_pence / 100)),

                    Forms\Components\DatePicker::make('acquired_at')
                        ->required()
                        ->default(now()),

                    Forms\Components\TextInput::make('acquired_from')
                        ->label('Source')
                        ->placeholder('e.g. Cardiff Card Show 2026-06')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('acquisition_lot')
                        ->label('LOT number')
                        ->placeholder('e.g. LOT-2026-08-10')
                        ->helperText('Which box this card physically lives in — only change this if you\'re correcting a mis-scanned lot from Rapid Intake.'),
                ]),

            Section::make('Valuation & status')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('market_value_pounds')
                        ->label('Market value (£)')
                        ->numeric()
                        ->step(0.01)
                        ->prefix('£')
                        ->helperText('Leave as-is to keep the current value, or type a new one to override it.')
                        ->afterStateHydrated(fn ($component, $record) => $record?->market_value_pence !== null
                                && $component->state($record->market_value_pence / 100)),

                    Forms\Components\Toggle::make('price_locked')
                        ->label('Lock price')
                        ->helperText('While on, PulseAPI can never overwrite this card\'s market value — not on resync, batch generation, or the scheduled price refresh. It\'s still picked for batches as normal; only the price stays fixed until you turn this off.'),

                    TextEntry::make('synced_at')
                        ->label('Price last synced')
                        ->state(fn (?CardInventory $record) => self::timestampDisplay($record?->synced_at)),

                    TextEntry::make('market_value_updated_at')
                        ->label('PulseAPI price as of')
                        ->helperText('When PulseAPI itself last calculated this price — not the same as when we synced it.')
                        ->state(fn (?CardInventory $record) => self::timestampDisplay($record?->market_value_updated_at)),

                    Forms\Components\Select::make('rarity_band')
                        ->options([
                            'common' => 'Common',
                            'rare' => 'Rare',
                            'super' => 'Super',
                            'legendary' => 'Legendary',
                            'mythic' => 'Mythic',
                        ]),

                    Forms\Components\Select::make('status')
                        ->options([
                            'in_stock' => 'In stock',
                            'allocated' => 'Allocated',
                            'dispatched' => 'Dispatched',
                            'sold' => 'Sold',
                            'returned' => 'Returned',
                            'written_off' => 'Written off',
                        ])
                        ->required()
                        ->default('in_stock'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('')
                    ->height(50)
                    ->extraImgAttributes(['class' => 'rounded']),

                Tables\Columns\TextColumn::make('game')
                    ->label('Game')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof Game) {
                            return $state->label();
                        }
                        if (is_string($state)) {
                            return Game::from($state)->label();
                        }

                        return (string) $state;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('card_name')
                    ->label('Card')
                    ->searchable()
                    ->sortable()
                    ->description(fn (CardInventory $r) => "{$r->set_name} · {$r->card_number}"),

                Tables\Columns\TextColumn::make('product_badges')
                    ->label('Variant')
                    ->badge()
                    ->color('warning')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('cost_pence')
                    ->label('Cost')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('market_value_pence')
                    ->label('Market')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('synced_at')
                    ->label('Price synced')
                    ->since()
                    ->dateTimeTooltip()
                    ->placeholder('Never')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('market_value_updated_at')
                    ->label('PulseAPI price as of')
                    ->since()
                    ->dateTimeTooltip()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('rarity_band')
                    ->label('Rarity')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'common' => 'Common',
                        'rare' => 'Rare',
                        'super' => 'Super',
                        'legendary' => 'Legendary',
                        'mythic' => 'Mythic',
                        default => 'Unbanded',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'common' => 'gray',
                        'rare' => 'info',
                        'super' => 'primary',
                        'legendary' => 'warning',
                        'mythic' => 'danger',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'in_stock' => 'In stock',
                        'allocated' => 'In batch',
                        'dispatched' => 'With store',
                        'sold' => 'Sold',
                        'returned' => 'Returned',
                        'written_off' => 'Written off',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state) => match ($state) {
                        'in_stock' => 'success',
                        'allocated' => 'info',
                        'dispatched' => 'warning',
                        'sold' => 'gray',
                        'returned' => 'warning',
                        'written_off' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('price_locked')
                    ->label('Price locked')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedLockClosed)
                    ->falseIcon(Heroicon::OutlinedLockOpen)
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('acquisition_lot')
                    ->label('Lot')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('acquired_at')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rarity_band')
                    ->options([
                        'common' => 'Common',
                        'rare' => 'Rare',
                        'super' => 'Super',
                        'legendary' => 'Legendary',
                        'mythic' => 'Mythic',
                        'unbanded' => 'Unbanded',
                    ])
                    // The 'unbanded' option has no literal column value to match —
                    // it means rarity_band IS NULL — so this needs an explicit
                    // query() override rather than SelectFilter's default
                    // where(column, value) behaviour.
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value'] ?? null) {
                            null => $query,
                            'unbanded' => $query->whereNull('rarity_band'),
                            default => $query->where('rarity_band', $data['value']),
                        };
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'in_stock' => 'In stock',
                        'allocated' => 'Allocated',
                        'dispatched' => 'Dispatched',
                        'sold' => 'Sold',
                        'returned' => 'Returned',
                        'written_off' => 'Written off',
                    ])
                    ->default('in_stock'),
                Tables\Filters\SelectFilter::make('acquisition_lot')
                    ->options(fn () => CardInventory::query()
                        ->whereNotNull('acquisition_lot')
                        ->distinct()
                        ->pluck('acquisition_lot', 'acquisition_lot')
                        ->all()),
                Tables\Filters\Filter::make('sold_between')
                    ->label('Sold between')
                    ->schema([
                        Forms\Components\DatePicker::make('sold_from'),
                        Forms\Components\DatePicker::make('sold_until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['sold_from'] ?? null, fn ($q, $date) => $q->whereDate('delisted_at', '>=', $date))
                            ->when($data['sold_until'] ?? null, fn ($q, $date) => $q->whereDate('delisted_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('resyncPrice')
                    ->label('Resync price')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->visible(fn (CardInventory $record) => filled($record->product_id))
                    ->disabled(fn (CardInventory $record) => $record->price_locked)
                    ->tooltip(fn (CardInventory $record) => $record->price_locked ? 'Price is locked — unlock it to resync' : null)
                    ->action(function (CardInventory $record) {
                        app(PulseApiPriceProvider::class)->refreshPrice($record);

                        Notification::make()
                            ->title('Price resynced')
                            ->body('New market value: '.Money::format($record->market_value_pence))
                            ->success()
                            ->send();
                    }),
                static::markSoldAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    static::markSoldBulkAction(),
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()->exporter(SoldCardExporter::class),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function timestampDisplay(?Carbon $timestamp): string
    {
        return $timestamp
            ? $timestamp->diffForHumans().' ('.$timestamp->format('d M Y, H:i').')'
            : 'Never';
    }

    /**
     * For a card sold in person (over the counter, at a show, etc.) rather
     * than through a pack redemption — only ever an in_stock row, since an
     * allocated card is committed to a specific pack/batch already; use
     * BatchResource's "Swap a card" (CardSwapper) for that case instead, so
     * the pack gets a replacement rather than being left pointing at a card
     * that's no longer there.
     */
    public static function markSoldAction(): Action
    {
        return Action::make('markSold')
            ->label('Mark as sold')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('gray')
            ->visible(fn (CardInventory $record) => $record->status === 'in_stock')
            ->requiresConfirmation()
            ->modalHeading('Mark as sold')
            ->modalDescription('Use this when a card was sold in person rather than through a pack — removes it from available stock immediately.')
            ->action(function (CardInventory $record) {
                $record->update([
                    'status' => 'sold',
                    'delisted_at' => now(),
                    'delisted_by_user_id' => auth()->id(),
                ]);

                Notification::make()
                    ->title('Marked as sold')
                    ->success()
                    ->send();
            });
    }

    public static function markSoldBulkAction(): BulkAction
    {
        return BulkAction::make('markSold')
            ->label('Mark as sold')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Mark as sold')
            ->modalDescription('Use this when these cards were sold in person rather than through a pack. Any selected card that isn\'t currently in stock (e.g. already allocated to a batch) is left untouched.')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) {
                $inStock = $records->filter(fn (CardInventory $record) => $record->status === 'in_stock');

                CardInventory::whereIn('id', $inStock->pluck('id'))->update([
                    'status' => 'sold',
                    'delisted_at' => now(),
                    'delisted_by_user_id' => auth()->id(),
                ]);

                $skipped = $records->count() - $inStock->count();

                Notification::make()
                    ->title("{$inStock->count()} card(s) marked as sold")
                    ->body($skipped > 0 ? "{$skipped} skipped — not currently in stock." : null)
                    ->success()
                    ->send();
            });
    }

    // Rapid Intake is the only way cards enter inventory — no 'create' page/route.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCardInventories::route('/'),
            'edit' => Pages\EditCardInventory::route('/{record}/edit'),
            'rapid' => Pages\RapidIntake::route('/rapid'),
        ];
    }
}
