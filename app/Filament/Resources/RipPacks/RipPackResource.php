<?php

namespace App\Filament\Resources\RipPacks;

use App\Enums\Game;
use App\Enums\RipGradedPolicy;
use App\Filament\Resources\RipPacks\Pages\CreateRipPack;
use App\Filament\Resources\RipPacks\Pages\EditRipPack;
use App\Filament\Resources\RipPacks\Pages\ListRipPacks;
use App\Models\CardInventory;
use App\Models\RipPack;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Digital Rips' admin-configured product — the only place band_odds (drawn
 * against by RipDrawer) and buy_back_percentage are set. See the five
 * *_pct fields below: they don't exist on the model, they're assembled into
 * the single band_odds JSON column in Pages\CreateRipPack/EditRipPack.
 */
class RipPackResource extends Resource
{
    protected static ?string $model = RipPack::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|UnitEnum|null $navigationGroup = 'Digital Rips';

    protected static ?int $navigationSort = 10;

    public const BANDS = ['common', 'rare', 'super', 'legendary', 'mythic'];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pack')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Set $set, string $operation) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug($state));
                            }
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Pack art')
                        ->image()
                        ->directory('rip-packs')
                        ->visibility('public')
                        ->helperText('Leave blank to use the default Arcane bag art.')
                        // RipPack::getImagePathAttribute() rewrites the stored
                        // value into a full display URL — FileUpload needs the
                        // raw disk path back to recognise the existing file,
                        // same fix as StoreResource's logo field.
                        ->afterStateHydrated(function (Forms\Components\FileUpload $component, ?RipPack $record) {
                            $component->state($record?->getRawOriginal('image_path'));
                        }),
                    Forms\Components\Select::make('status')
                        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                        ->required()
                        ->default('active'),
                ])
                ->columns(2),

            Section::make('Price & games')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('price_pounds')
                        ->label('Price (£)')
                        ->numeric()
                        ->step(0.01)
                        ->prefix('£')
                        ->required()
                        ->afterStateHydrated(fn ($component, $record) => $record && $component->state($record->price_pence / 100)),
                    Forms\Components\Select::make('games')
                        ->label('Games')
                        ->multiple()
                        ->live()
                        ->options(collect(Game::cases())->mapWithKeys(fn (Game $g) => [$g->value => $g->label()]))
                        ->required()
                        ->helperText('Multiple games pool together — the pack draws by rarity band across all of them, not per-game odds.'),
                    Forms\Components\TextInput::make('buy_back_pct')
                        ->label('Buy-back %')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->required()
                        ->helperText('What we pay a customer, as a % of market value, if they sell the card back to us.')
                        ->afterStateHydrated(fn ($component, $record) => $record && $component->state($record->buy_back_percentage * 100)),
                ])
                ->columns(3),

            Section::make('Card pool')
                ->description('Rips always draw batch-quality stock: anything marked "not for batches" is excluded whatever this is set to. Graded slabs are the one exception, because they can never go in a sealed pack but can go in a rip.')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Select::make('graded_policy')
                        ->label('Graded cards')
                        ->options(RipGradedPolicy::options())
                        ->default(RipGradedPolicy::Exclude->value)
                        ->required()
                        ->live()
                        ->helperText(fn (Get $get) => (RipGradedPolicy::tryFrom((string) $get('graded_policy'))
                            ?? RipGradedPolicy::Exclude)->description()),

                    // A pack whose pool is empty takes money at checkout and
                    // then fails to draw, so the count that actually matters
                    // — eligible, in this pack's games, and banded — is shown
                    // before it can be saved. Unbanded cards are invisible to
                    // RipDrawer, which picks a band first.
                    Forms\Components\Placeholder::make('eligible_stock')
                        ->label('Drawable stock right now')
                        ->content(function (Get $get) {
                            $games = $get('games') ?: [];

                            if (empty($games)) {
                                return 'Pick at least one game to see the pool.';
                            }

                            $count = CardInventory::available()
                                ->ripEligible($get('graded_policy'))
                                ->whereIn('game', $games)
                                ->whereNotNull('rarity_band')
                                ->count();

                            return $count === 0
                                ? 'Nothing matches — this pack could not be drawn today.'
                                : number_format($count).' cards available to draw.';
                        }),
                ])
                ->columns(2),

            Section::make('Rarity band odds')
                ->description('Must add up to exactly 100%. This is the live probability distribution RipDrawer draws against.')
                ->columnSpanFull()
                ->schema([
                    static::percentageField('common_pct', 'Common'),
                    static::percentageField('rare_pct', 'Rare'),
                    static::percentageField('super_pct', 'Super'),
                    static::percentageField('legendary_pct', 'Legendary'),
                    static::percentageField('mythic_pct', 'Mythic')
                        ->rule(function (Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $total = array_sum([
                                    (float) $get('common_pct'),
                                    (float) $get('rare_pct'),
                                    (float) $get('super_pct'),
                                    (float) $get('legendary_pct'),
                                    (float) $value,
                                ]);

                                if (abs($total - 100) > 0.01) {
                                    $fail("The five rarity odds must add up to exactly 100% — currently {$total}%.");
                                }
                            };
                        }),
                    Forms\Components\Placeholder::make('pct_total')
                        ->label('Running total')
                        ->content(fn (Get $get) => number_format(array_sum([
                            (float) $get('common_pct'),
                            (float) $get('rare_pct'),
                            (float) $get('super_pct'),
                            (float) $get('legendary_pct'),
                            (float) $get('mythic_pct'),
                        ]), 2).'%')
                        ->columnSpanFull(),
                ])
                ->columns(5),
        ]);
    }

    /**
     * Renames/transforms every display-only form field (none of them real
     * model attributes) into what RipPack actually stores — called from
     * CreateRipPack/EditRipPack's mutateFormDataBeforeCreate/Save. Same
     * "£ input, pence column" convention as EditCardInventory's cost_pounds
     * handling, plus assembling the five *_pct fields into one band_odds
     * JSON column (not something a single field's dehydrateStateUsing() can do).
     */
    public static function transformFormData(array $data): array
    {
        if (isset($data['price_pounds'])) {
            $data['price_pence'] = (int) round(((float) $data['price_pounds']) * 100);
            unset($data['price_pounds']);
        }

        if (isset($data['buy_back_pct'])) {
            $data['buy_back_percentage'] = ((float) $data['buy_back_pct']) / 100;
            unset($data['buy_back_pct']);
        }

        $data['band_odds'] = collect(self::BANDS)->mapWithKeys(
            fn (string $band) => [$band => ((float) ($data["{$band}_pct"] ?? 0)) / 100]
        )->all();

        foreach (self::BANDS as $band) {
            unset($data["{$band}_pct"]);
        }

        return $data;
    }

    private static function percentageField(string $name, string $label): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make($name)
            ->label($label)
            ->numeric()
            ->minValue(0)
            ->maxValue(100)
            ->suffix('%')
            ->required()
            ->live()
            ->afterStateHydrated(function ($component, $record) use ($name) {
                if (! $record) {
                    return;
                }

                $band = str($name)->before('_pct')->toString();
                $component->state((float) ($record->band_odds[$band] ?? 0) * 100);
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('')
                    ->height(50)
                    ->defaultImageUrl(asset('build/assets/Arcane_pack-fGKIuDkr.webp')),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (RipPack $record) => $record->description),

                Tables\Columns\TextColumn::make('games')
                    ->badge()
                    ->formatStateUsing(fn (RipPack $record) => collect($record->games)->map(fn (string $g) => Game::from($g)->label())->implode(', ')),

                Tables\Columns\TextColumn::make('price_pence')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('band_odds.mythic')
                    ->label('Mythic odds')
                    ->formatStateUsing(fn ($state) => number_format(((float) $state) * 100, 2).'%')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('graded_policy')
                    ->label('Graded')
                    ->badge()
                    ->formatStateUsing(fn (RipGradedPolicy $state) => $state->label())
                    ->color(fn (RipGradedPolicy $state) => match ($state) {
                        RipGradedPolicy::Exclude => 'gray',
                        RipGradedPolicy::Allow => 'info',
                        RipGradedPolicy::Only => 'warning',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('buy_back_percentage')
                    ->label('Buy-back')
                    ->formatStateUsing(fn ($state) => number_format(((float) $state) * 100, 0).'%')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('rips_count')
                    ->label('Sold')
                    ->counts('rips')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'active' ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive']),
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ListRipPacks::route('/'),
            'create' => CreateRipPack::route('/create'),
            'edit' => EditRipPack::route('/{record}/edit'),
        ];
    }
}
