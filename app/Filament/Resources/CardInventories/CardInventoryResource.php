<?php

namespace App\Filament\Resources\CardInventories;

use App\Filament\Resources\CardInventories\Pages;
use App\Models\Card;
use App\Models\CardInventory;
use App\Services\Banding\RarityBander;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use App\Enums\Game;

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
                    Forms\Components\Select::make('card_id')
                        ->label('Card')
                        ->relationship('card', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Card $c) =>
                            "{$c->name} · {$c->set_name} · {$c->card_number}".
                            ($c->variant && $c->variant !== 'standard' ? " ({$c->variant})" : ''))
                        ->searchable(['name', 'set_name', 'card_number'])
                        ->preload()
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
                        ->dehydrated(false)
                        ->afterStateHydrated(fn ($component, $record) =>
                            $record && $component->state($record->cost_pence / 100)),

                    Forms\Components\DatePicker::make('acquired_at')
                        ->required()
                        ->default(now()),

                    Forms\Components\TextInput::make('acquired_from')
                        ->label('Source')
                        ->placeholder('e.g. Cardiff Card Show 2026-06')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('acquisition_lot')
                        ->label('Lot reference')
                        ->placeholder('e.g. LOT-2026-014')
                        ->helperText('Group multi-card purchases together'),
                ]),

            Section::make('Valuation & status')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('market_value_pounds')
                        ->label('Market value (£)')
                        ->numeric()
                        ->step(0.01)
                        ->prefix('£')
                        ->dehydrated(false)
                        ->afterStateHydrated(fn ($component, $record) =>
                            $record?->market_value_pence !== null
                                && $component->state($record->market_value_pence / 100)),

                    Forms\Components\Select::make('rarity_band')
                        ->options([
                            'common'    => 'Common',
                            'rare'      => 'Rare',
                            'super'     => 'Super',
                            'legendary' => 'Legendary',
                            'mythic'    => 'Mythic',
                        ]),

                    Forms\Components\Select::make('status')
                        ->options([
                            'in_stock'    => 'In stock',
                            'allocated'   => 'Allocated',
                            'dispatched'  => 'Dispatched',
                            'sold'        => 'Sold',
                            'returned'    => 'Returned',
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
                Tables\Columns\ImageColumn::make('card.image_front')
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

                Tables\Columns\TextColumn::make('card.name')
                    ->label('Card')
                    ->searchable()
                    ->sortable()
                    ->description(fn (CardInventory $r) =>
                        "{$r->card?->set_name} · {$r->card?->card_number}"),

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

                Tables\Columns\TextColumn::make('rarity_band')
                    ->label('Rarity')
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'common'    => 'Common',
                        'rare'      => 'Rare',
                        'super'     => 'Super',
                        'legendary' => 'Legendary',
                        'mythic'    => 'Mythic',
                        default     => 'Unknown',
                    })
                    ->color(fn(?string $state) => match ($state) {
                        'common'    => 'gray',
                        'rare'      => 'info',
                        'super'     => 'primary',
                        'legendary' => 'warning',
                        'mythic'    => 'danger',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'in_stock'   => 'In stock',
                        'allocated'  => 'In batch',
                        'dispatched' => 'With store',
                        'sold'       => 'Sold',
                        'returned'   => 'Returned',
                        'written_off' => 'Written off',
                        default      => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn(string $state) => match ($state) {
                        'in_stock'   => 'success',
                        'allocated'  => 'info',
                        'dispatched' => 'warning',
                        'sold'       => 'gray',
                        'returned'   => 'warning',
                        'written_off' => 'danger',
                        default      => 'gray',
                    }),

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
                        'common'    => 'Common',
                        'rare'      => 'Rare',
                        'super'     => 'Super',
                        'legendary' => 'Legendary',
                        'mythic'    => 'Mythic',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'in_stock'    => 'In stock',
                        'allocated'   => 'Allocated',
                        'dispatched'  => 'Dispatched',
                        'sold'        => 'Sold',
                        'returned'    => 'Returned',
                        'written_off' => 'Written off',
                    ])
                    ->default('in_stock'),
                Tables\Filters\SelectFilter::make('acquisition_lot')
                    ->options(fn () => CardInventory::query()
                        ->whereNotNull('acquisition_lot')
                        ->distinct()
                        ->pluck('acquisition_lot', 'acquisition_lot')
                        ->all()),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (isset($data['cost_pounds'])) {
                            $data['cost_pence'] = Money::toPence($data['cost_pounds']);
                            unset($data['cost_pounds']);
                        }
                        if (isset($data['market_value_pounds'])) {
                            $data['market_value_pence'] = Money::toPence($data['market_value_pounds']);
                            unset($data['market_value_pounds']);
                        }
                        return $data;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCardInventories::route('/'),
            'create' => Pages\CreateCardInventory::route('/create'),
            'edit'   => Pages\EditCardInventory::route('/{record}/edit'),
            'rapid'  => Pages\RapidIntake::route('/rapid'),
        ];
    }
}
