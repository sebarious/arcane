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
use Illuminate\Support\Str;
use UnitEnum;

class CardInventoryResource extends Resource
{
    protected static ?string $model = CardInventory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Card';

    protected static ?string $pluralModelLabel = 'Batch Inventory';

    /**
     * Which slice of stock this resource lists. Overridden by the sibling
     * resources that show the other two views of the same table — see
     * NonBatchInventoryResource and AllInventoryResource, which extend this
     * one so the form, table, filters and actions are only defined once.
     */
    protected static function inventoryScope(Builder $query): Builder
    {
        return $query->batchable();
    }

    public static function getEloquentQuery(): Builder
    {
        return static::inventoryScope(parent::getEloquentQuery());
    }

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
                    // Not a column: a card is manual exactly when it has no
                    // PulseAPI product behind it, which is already what every
                    // sync path keys off (CardPriceSyncer::syncStale and
                    // PulseApiPriceProvider both skip a blank product_id).
                    // A flag as well would just be a second thing to disagree.
                    Forms\Components\Toggle::make('is_manual')
                        ->label('Enter this card manually')
                        ->dehydrated(false)
                        ->live()
                        ->afterStateHydrated(fn (Forms\Components\Toggle $component, ?CardInventory $record) => $component->state($record ? blank($record->product_id) : false))
                        ->helperText('For anything PulseAPI doesn\'t list — sealed product, oddities, a slab it doesn\'t carry. You fill in the details and set the price yourself, and nothing will ever overwrite them.')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('product_id')
                        ->label('Card')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => PulseApiCardMapper::searchOptions($search))
                        ->getOptionLabelUsing(fn ($value) => PulseApiCardMapper::labelForProductId($value))
                        ->visible(fn ($get) => ! $get('is_manual'))
                        ->required(fn ($get) => ! $get('is_manual')),

                    // Normally filled from PulseAPI and left alone. On a manual
                    // card they're the only source for the name shown in the
                    // admin table and for what the kiosk and catalogue search
                    // against, so they're editable and the name is required.
                    Forms\Components\TextInput::make('card_name')
                        ->label('Card name')
                        ->maxLength(255)
                        ->visible(fn ($get) => (bool) $get('is_manual'))
                        ->required(fn ($get) => (bool) $get('is_manual'))
                        ->helperText('What staff and customers search for.'),

                    Forms\Components\TextInput::make('set_name')
                        ->label('Set')
                        ->maxLength(255)
                        ->visible(fn ($get) => (bool) $get('is_manual'))
                        ->helperText('Also searchable, and used by the set filter.'),

                    Forms\Components\TextInput::make('card_number')
                        ->label('Number')
                        ->maxLength(50)
                        ->visible(fn ($get) => (bool) $get('is_manual'))
                        ->placeholder('e.g. 199/165'),

                    Forms\Components\TextInput::make('rarity')
                        ->label('Rarity (as printed)')
                        ->maxLength(100)
                        ->visible(fn ($get) => (bool) $get('is_manual'))
                        ->placeholder('e.g. Illustration Rare')
                        ->helperText('Cosmetic only — the price band is worked out from the market value below.'),
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
                            'chase' => 'Chase',
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

                    Forms\Components\Toggle::make('on_ebay')
                        ->label('On eBay')
                        ->helperText('This physical card is currently listed on eBay — flagged on any picking sheet it appears on so staff know to check/delist it before shipping.'),

                    Forms\Components\Toggle::make('in_card_wall')
                        ->label('In card wall')
                        ->helperText('This physical card is currently on display in the card wall rather than boxed — flagged on any picking sheet it appears on so staff know to pull it from there instead.'),

                    Forms\Components\Toggle::make('not_for_batches')
                        ->label('Not for batches')
                        // On by default for hand-added cards: the only create
                        // route is the non-batch Inventory list, so a card
                        // added there should stay in the list it came from.
                        ->default(true)
                        ->helperText('Holds this card back from batch generation — for anything below the condition we\'ll seal into a mystery pack. It stays fully sellable at the kiosk, on the card wall and on eBay, where the buyer can see what they\'re getting.'),
                ]),

            Section::make('Grading & photo')
                ->columnSpanFull()
                ->description('For slabbed cards, and anything else where our own photo beats the stock artwork.')
                ->schema([
                    // Not a column of its own: a card is graded exactly when
                    // it has both a grader and a grade (see
                    // CardInventory::isGraded()). This only drives the form.
                    Forms\Components\Toggle::make('is_graded')
                        ->label('This card is graded')
                        ->dehydrated(false)
                        ->live()
                        ->afterStateHydrated(fn (Forms\Components\Toggle $component, ?CardInventory $record) => $component->state((bool) $record?->isGraded()))
                        ->helperText('Slabbed by PSA, Beckett, CGC and the like.'),

                    Forms\Components\Select::make('graded_by')
                        ->label('Grading company')
                        ->options(config('grading.companies'))
                        ->native(false)
                        ->visible(fn ($get) => (bool) $get('is_graded'))
                        ->required(fn ($get) => (bool) $get('is_graded'))
                        // PulseAPI writes this column too and isn't limited to
                        // our list, so keep whatever's already on the record
                        // selectable rather than silently blanking it.
                        ->getOptionLabelUsing(fn ($value) => config("grading.companies.{$value}", $value)),

                    Forms\Components\TextInput::make('grade')
                        ->label('Grade')
                        ->numeric()
                        ->step(0.5)
                        ->minValue(1)
                        ->maxValue(10)
                        ->visible(fn ($get) => (bool) $get('is_graded'))
                        ->required(fn ($get) => (bool) $get('is_graded'))
                        ->helperText('e.g. 10, or 9.5 for a half grade.'),

                    Forms\Components\TextInput::make('grade_serial')
                        ->label('Certification / serial number')
                        ->maxLength(40)
                        ->visible(fn ($get) => (bool) $get('is_graded'))
                        // Not required: PulseAPI fills graded_by and grade
                        // without one, and demanding it here would make those
                        // records impossible to save.
                        ->helperText('The number on the slab label — lets a buyer verify it against the grader\'s own register.'),

                    Forms\Components\FileUpload::make('custom_image_path')
                        ->label('Our photo of this card')
                        ->image()
                        ->maxSize(8192)
                        ->directory('card-photos')
                        ->visibility('public')
                        ->imageEditor()
                        ->helperText('Replaces the stock artwork everywhere this card appears — kiosk, catalogue and card lists. Leave empty to keep using the artwork from PulseAPI.')
                        // getImageUrlAttribute() rewrites image_url for display;
                        // this field needs the raw stored path to recognise the
                        // existing file rather than showing empty and wiping it.
                        //
                        // Given as [fileKey => path], never the bare path: a
                        // FileUpload's state is always an array, and a string
                        // reaches Livewire's serialiser and Filament's own
                        // validation and save loop as the wrong type.
                        ->afterStateHydrated(function (Forms\Components\FileUpload $component, ?CardInventory $record): void {
                            $path = $record?->getRawOriginal('custom_image_path');

                            $component->state(filled($path) ? [(string) Str::uuid() => $path] : []);
                        }),

                    Forms\Components\ViewField::make('photo_capture')
                        ->label('')
                        ->view('filament.forms.components.card-photo-capture')
                        ->dehydrated(false),
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
                        'chase' => 'Chase',
                        default => 'Unbanded',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'common' => 'gray',
                        'rare' => 'info',
                        'super' => 'primary',
                        'legendary' => 'warning',
                        'mythic' => 'danger',
                        'chase' => 'chase',
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

                Tables\Columns\ToggleColumn::make('on_ebay')
                    ->label('On eBay')
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('in_card_wall')
                    ->label('Card wall')
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('not_for_batches')
                    ->label('No batches')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('grade_serial')
                    ->label('Cert no.')
                    ->placeholder('—')
                    // Searchable because a slab in hand is most easily found
                    // by the number on its label.
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'chase' => 'Chase',
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
                Tables\Filters\TernaryFilter::make('on_ebay')
                    ->label('On eBay'),
                Tables\Filters\TernaryFilter::make('in_card_wall')
                    ->label('In card wall'),
                Tables\Filters\TernaryFilter::make('not_for_batches')
                    ->label('Not for batches'),
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
