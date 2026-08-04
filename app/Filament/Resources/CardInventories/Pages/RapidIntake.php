<?php

namespace App\Filament\Resources\CardInventories\Pages;

use App\Enums\Game;
use App\Filament\Resources\CardInventories\CardInventoryResource;
use App\Models\CardInventory;
use App\Services\Banding\RarityBander;
use App\Services\PulseApi\PulseApiCardMapper;
use App\Services\PulseApi\PulseApiClient;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RapidIntake extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = CardInventoryResource::class;

    protected string $view = 'filament.resources.card-inventories.pages.rapid-intake';

    // Matches PulseAPI's batch endpoint limit (POST /cards/batch accepts up to 50 product_ids).
    public const MAX_ITEMS = 50;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'acquired_at'     => now()->toDateString(),
            'acquired_from'   => '',
            'acquisition_lot' => 'LOT-'.now()->format('Y-md').'-'.strtoupper(Str::random(4)),
            'rows'            => [$this->emptyRow()],
            'bulk_add_count'  => '10',
            'buy_percentage'  => (string) self::defaultBuyPercentage(),
        ]);
    }

    protected static function defaultBuyPercentage(): int
    {
        return (int) round(((float) config('services.pulseapi.default_cost_ratio', 0.9)) * 100);
    }

    protected function emptyRow(): array
    {
        return [
            'product_id'          => null,
            'card_name'           => null,
            'search_number'       => null,
            'resolved'            => null,
            // Set when a search matches more than one plausible print (Pokémon Center
            // exclusives, stamped/staff variants, etc.) — JSON-encoded candidate list
            // for the "Choose variant" action, same reasoning as `resolved` below.
            'candidates'          => null,
            'market_value_pounds' => null,
            'cost_pounds'         => null,
            'quantity'            => 1,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Acquisition details')
                    ->description('These apply to every card you add below.')
                    ->columns(4)
                    ->schema([
                        DatePicker::make('acquired_at')->required(),
                        TextInput::make('acquired_from')
                            ->label('Source')
                            ->placeholder('e.g. Cardiff Card Show 2026-06'),
                        TextInput::make('acquisition_lot')
                            ->label('Lot reference')
                            ->required(),
                        Select::make('buy_percentage')
                            ->label('Buy %')
                            ->options(
                                collect(range(0, 150, 5))
                                    ->mapWithKeys(fn (int $percent) => [(string) $percent => "{$percent}%"])
                            )
                            ->default((string) self::defaultBuyPercentage())
                            ->selectablePlaceholder(false)
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(fn () => $this->recalculateAllCosts())
                            ->helperText('Drives the Cost (£) for every resolved card below — changing it recalculates them all.'),
                    ]),

                Section::make('Cards')
                    ->description(
                        'Up to '.self::MAX_ITEMS.' cards per intake. Type each card\'s name '.
                        '(and number, if you have it) below, then click "Fetch card data" to '.
                        'look them all up on PulseAPI at once — review or override the market '.
                        'price before saving.'
                    )
                    ->schema([
                        Flex::make([
                            Select::make('bulk_add_count')
                                ->label('')
                                ->options([
                                    '1'  => '1 card',
                                    '10' => '10 cards',
                                    '20' => '20 cards',
                                    '30' => '30 cards',
                                    '40' => '40 cards',
                                    '50' => '50 cards',
                                ])
                                ->default('10')
                                ->selectablePlaceholder(false)
                                ->dehydrated(false),
                            Actions::make([
                                Action::make('bulkAdd')
                                    ->label('Add cards')
                                    ->icon(Heroicon::OutlinedPlusCircle)
                                    ->color('gray')
                                    ->action(function () {
                                        $count = (int) ($this->data['bulk_add_count'] ?? 1);
                                        $rows  = $this->data['rows'] ?? [];

                                        $available = self::MAX_ITEMS - count($rows);
                                        $toAdd     = max(0, min($count, $available));

                                        if ($toAdd <= 0) {
                                            Notification::make()
                                                ->title('Already at the limit')
                                                ->body('You can have up to '.self::MAX_ITEMS.' cards per intake.')
                                                ->warning()
                                                ->send();
                                            return;
                                        }

                                        for ($i = 0; $i < $toAdd; $i++) {
                                            $rows[] = $this->emptyRow();
                                        }

                                        $this->data['rows'] = $rows;
                                        $this->form->fill($this->data);

                                        Notification::make()
                                            ->title($toAdd < $count
                                                ? "Added {$toAdd} row(s) — capped at ".self::MAX_ITEMS
                                                : "Added {$toAdd} row(s)")
                                            ->success()
                                            ->send();
                                    }),
                            ]),
                        ]),

                        Repeater::make('rows')
                            ->label('')
                            ->addActionLabel('+ Add another card')
                            ->defaultItems(1)
                            ->maxItems(self::MAX_ITEMS)
                            ->columns(12)
                            ->extraItemActions([$this->chooseVariantAction(), $this->retryFetchAction(), $this->manualEntryAction()])
                            ->schema([
                                TextInput::make('search_number')
                                    ->label('Number')
                                    ->columnSpan(2)
                                    ->placeholder('e.g. 199/165'),

                                TextInput::make('card_name')
                                    ->label('Card name')
                                    ->columnSpan(5)
                                    ->placeholder('e.g. Charizard ex')
                                    // Not required — if the fetch can't find a match, leave this
                                    // and use the row's "Fill in manually" (✎) action instead.
                                    ->helperText('Looked up when you click "Fetch card data" above.'),

                                TextInput::make('market_value_pounds')
                                    ->label('Market (£)')
                                    ->columnSpan(2)
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('£')
                                    ->helperText('Auto-filled by fetch — edit to override.'),

                                TextInput::make('cost_pounds')
                                    ->label('Cost (£)')
                                    ->columnSpan(2)
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('£')
                                    ->required()
                                    ->helperText('Leave blank — auto-filled by fetch.'),

                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->columnSpan(1)
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),

                                ImageEntry::make('preview_image')
                                    ->label('')
                                    ->columnSpan(1)
                                    ->imageSize(48)
                                    ->extraImgAttributes(['class' => 'rounded object-cover cursor-zoom-in'])
                                    ->state(fn (callable $get) => self::resolvedImageUrl($get))
                                    ->url(fn (callable $get) => self::resolvedImageUrl($get))
                                    ->openUrlInNewTab()
                                    ->visible(fn (callable $get) => filled(self::resolvedImageUrl($get))),

                                TextEntry::make('preview')
                                    ->label('')
                                    ->columnSpan(11)
                                    ->state(function (callable $get) {
                                        $resolved = self::decodeResolved($get('resolved'));

                                        if (! $resolved) {
                                            $candidates = self::decodeResolved($get('candidates'));
                                            if ($candidates) {
                                                return count($candidates).' matches found — use the list icon above to choose the right one.';
                                            }

                                            return 'Not fetched yet — click "Fetch card data" above.';
                                        }

                                        return sprintf(
                                            '%s · %s · %s · %s',
                                            $resolved['card_name'] ?? '?',
                                            $resolved['set_name'] ?? '?',
                                            $resolved['card_number'] ?? '?',
                                            $resolved['rarity'] ?? '?',
                                        );
                                    }),

                                // Carries the fetched card data (name/set/image/rarity/etc.) through
                                // to save() — JSON-encoded, since a plain hidden <input> can only
                                // round-trip a string via wire:model, not a raw PHP array.
                                Hidden::make('resolved'),
                                Hidden::make('candidates'),
                            ])
                            ->itemLabel(function (array $state) {
                                $resolved = self::decodeResolved($state['resolved'] ?? null);
                                if ($resolved) {
                                    return $resolved['card_name'] ?? null;
                                }

                                return trim(($state['card_name'] ?? '').' '.($state['search_number'] ?? '')) ?: null;
                            }),

                        // Mirrors the "Fetch card data" header action — after adding 40-50 rows,
                        // scrolling all the way back up just to trigger a fetch is a real pain.
                        Actions::make([
                            Action::make('fetchCardDataBottom')
                                ->label('Fetch card data')
                                ->icon(Heroicon::OutlinedArrowPath)
                                ->color('gray')
                                ->action(fn () => $this->fetchCardData()),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function fetchCardData(): void
    {
        // getRawState(), not getState() — Cost (£) is required for Save, but Fetch should
        // work before it's filled in (we auto-fill it from the resolved price below), so
        // this deliberately skips form validation rather than blocking on it.
        $rows = $this->form->getRawState()['rows'] ?? [];

        if (collect($rows)->every(fn ($row) => blank($row['card_name'] ?? null) && blank($row['search_number'] ?? null))) {
            Notification::make()->title('Nothing to fetch')->body('Type a card name or number first.')->warning()->send();
            return;
        }

        // Rows that already resolved once (e.g. re-clicking Fetch) go through the
        // cache-first + batch path. Everything else needs an individual search —
        // PulseAPI's batch endpoint takes known product_ids, not free-text queries,
        // so there's no way to combine N different name/number searches into one call.
        $knownIds = collect($rows)->pluck('product_id')->filter()->unique()->values();
        $resolvedById = $knownIds->isNotEmpty() ? $this->resolveProductIds($knownIds)[0] : [];

        $resolvedCount   = 0;
        $ambiguousCount  = 0;
        $missingCount    = 0;

        foreach ($rows as $i => $row) {
            $productId  = $row['product_id'] ?? null;
            $attributes = $productId ? ($resolvedById[$productId] ?? null) : null;

            if ($attributes) {
                $this->applyResolvedAttributes($rows, $i, $attributes);
                $resolvedCount++;
                continue;
            }

            if (blank($row['card_name'] ?? null) && blank($row['search_number'] ?? null)) {
                continue;
            }

            match ($this->applySearchResolution($rows, $i)) {
                'resolved'  => $resolvedCount++,
                'ambiguous' => $ambiguousCount++,
                'not_found' => $missingCount++,
            };
        }

        $this->data['rows'] = $rows;
        $this->form->fill($this->data);

        $notes = [];
        if ($ambiguousCount > 0) $notes[] = "{$ambiguousCount} row(s) have multiple matches — use the list icon to choose";
        if ($missingCount > 0)   $notes[] = "{$missingCount} row(s) couldn't be found — use the ✎ button";

        Notification::make()
            ->title('Card data fetched')
            ->body($notes
                ? "{$resolvedCount} card(s) resolved. ".implode('. ', $notes).'.'
                : 'All cards resolved — review the prices below, then save.')
            ->success()
            ->send();
    }

    /**
     * Apply a single, unambiguous PulseAPI match to a row: sets product_id/resolved,
     * pre-fills the market price, and auto-fills Cost (£) when it's still blank.
     * Clears any pending disambiguation candidates.
     */
    protected function applyResolvedAttributes(array &$rows, int|string $key, array $attributes): void
    {
        $costPounds = $rows[$key]['cost_pounds'] ?? null;

        $rows[$key]['product_id'] = $attributes['product_id'];
        $rows[$key]['resolved']   = json_encode($attributes);
        $rows[$key]['candidates'] = null;
        $rows[$key]['market_value_pounds'] = $attributes['market_value_pence'] !== null
            ? round($attributes['market_value_pence'] / 100, 2)
            : null;

        // Auto-fill Cost (£) when left blank, as a percentage of market value — driven
        // by the "Buy %" dropdown at the top of the page (still freely editable per
        // row before saving; once set, a row's cost is never overwritten by this).
        if (blank($costPounds) && $attributes['market_value_pence'] !== null) {
            $buyPercentage = (float) ($this->data['buy_percentage'] ?? self::defaultBuyPercentage());
            $rows[$key]['cost_pounds'] = round(($attributes['market_value_pence'] * $buyPercentage / 100) / 100, 2);
        }
    }

    /**
     * Unlike applyResolvedAttributes()'s fetch-time fill (which only ever fills a
     * blank Cost), this is a deliberate bulk recalculation — triggered by changing
     * the "Buy %" dropdown itself, so it recomputes every already-resolved row's
     * Cost (£) from its current Market (£), overwriting whatever was there before.
     */
    public function recalculateAllCosts(): void
    {
        $rows          = $this->data['rows'] ?? [];
        $buyPercentage = (float) ($this->data['buy_percentage'] ?? self::defaultBuyPercentage());

        $updated = 0;

        foreach ($rows as $key => $row) {
            $marketPounds = $row['market_value_pounds'] ?? null;
            if (blank($marketPounds)) {
                continue;
            }

            $rows[$key]['cost_pounds'] = round(((float) $marketPounds) * $buyPercentage / 100, 2);
            $updated++;
        }

        if ($updated === 0) {
            return;
        }

        $this->data['rows'] = $rows;
        $this->form->fill($this->data);

        Notification::make()
            ->title('Cost prices updated')
            ->body("{$updated} row(s) recalculated at {$buyPercentage}% of market value.")
            ->success()
            ->send();
    }

    /**
     * Search a row's name/number and apply the result: a single match auto-resolves
     * (via applyResolvedAttributes), several matches are stashed as candidates for the
     * "Choose variant" action rather than silently guessing which print the row means
     * (Pokémon Center exclusives, stamped/staff variants, etc. can share a name+number),
     * and no matches leaves the row untouched for "Fill in manually".
     *
     * @return 'resolved'|'ambiguous'|'not_found'
     */
    protected function applySearchResolution(array &$rows, int|string $key): string
    {
        $row = $rows[$key];

        $candidates = PulseApiCardMapper::searchCandidates(
            (string) ($row['card_name'] ?? ''),
            (string) ($row['search_number'] ?? ''),
        );

        if (empty($candidates)) {
            return 'not_found';
        }

        if (count($candidates) === 1) {
            $this->applyResolvedAttributes($rows, $key, $candidates[0]);
            return 'resolved';
        }

        $rows[$key]['candidates'] = json_encode($candidates);
        $rows[$key]['resolved']   = null;

        return 'ambiguous';
    }

    /**
     * Cache-first resolution: reuse recent card_inventory data per product_id
     * (see PulseApiCardMapper::cachedAttributes) and only batch-fetch what's missing/stale.
     *
     * @param  Collection<int, string>  $productIds
     * @return array{0: array<string, array<string, mixed>>, 1: int} [resolved map, missing count]
     */
    protected function resolveProductIds(Collection $productIds): array
    {
        $resolved   = [];
        $needsFetch = [];

        foreach ($productIds as $productId) {
            $cached = PulseApiCardMapper::cachedAttributes($productId);
            if ($cached) {
                $resolved[$productId] = $cached;
            } else {
                $needsFetch[] = $productId;
            }
        }

        if (! empty($needsFetch)) {
            $fetched = app(PulseApiClient::class)->batchGetCards($needsFetch);
            foreach ($fetched as $productId => $card) {
                if ($card) {
                    $resolved[$productId] = PulseApiCardMapper::toInventoryAttributes($card);
                }
            }
        }

        $missingCount = $productIds->reject(fn ($id) => isset($resolved[$id]))->count();

        return [$resolved, $missingCount];
    }

    protected static function decodeResolved(mixed $resolved): ?array
    {
        if (is_array($resolved)) return $resolved;
        if (is_string($resolved) && $resolved !== '') return json_decode($resolved, true);
        return null;
    }

    protected static function resolvedImageUrl(callable $get): ?string
    {
        $resolved = self::decodeResolved($get('resolved')) ?? [];

        return $resolved['image_url'] ?? null;
    }

    // Per-row "try again" — re-resolves just this one row (product_id cache/live
    // lookup, or a fresh name/number search) instead of re-fetching everything via
    // the header button. Only shown while the row is still unresolved.
    protected function retryFetchAction(): Action
    {
        return Action::make('retryFetch')
            ->label('Retry fetch')
            ->icon(Heroicon::OutlinedArrowPath)
            ->tooltip('Try fetching this card again.')
            ->visible(function (array $arguments) {
                $row = $this->data['rows'][$arguments['item']] ?? [];
                return blank(self::decodeResolved($row['resolved'] ?? null));
            })
            ->action(function (array $arguments) {
                $itemKey = $arguments['item'];
                $rows    = $this->data['rows'];
                if (! isset($rows[$itemKey])) return;

                $row       = $rows[$itemKey];
                $productId = $row['product_id'] ?? null;

                if ($productId) {
                    $attributes = PulseApiCardMapper::cachedAttributes($productId);
                    if (! $attributes) {
                        $card = app(PulseApiClient::class)->getCard($productId);
                        $attributes = $card ? PulseApiCardMapper::toInventoryAttributes($card) : null;
                    }
                    if ($attributes) {
                        $this->applyResolvedAttributes($rows, $itemKey, $attributes);
                        $this->data['rows'] = $rows;
                        $this->form->fill($this->data);
                        Notification::make()->title('Card resolved')->success()->send();
                        return;
                    }
                }

                $outcome = $this->applySearchResolution($rows, $itemKey);
                $this->data['rows'] = $rows;
                $this->form->fill($this->data);

                match ($outcome) {
                    'resolved'  => Notification::make()->title('Card resolved')->success()->send(),
                    'ambiguous' => Notification::make()
                        ->title('Multiple matches found')
                        ->body('Use the list icon on this row to choose the right one.')
                        ->warning()
                        ->send(),
                    'not_found' => Notification::make()
                        ->title('Still not found')
                        ->body('No match for this card — try adjusting the name/number, or use the ✎ button to enter it manually.')
                        ->warning()
                        ->send(),
                };
            });
    }

    // Per-row disambiguation — shown when a name/number search matched several
    // plausible prints (Pokémon Center exclusives, stamped/staff variants, etc. can
    // share a name+number) rather than silently guessing which one the row means.
    protected function chooseVariantAction(): Action
    {
        return Action::make('chooseVariant')
            ->label('Choose variant')
            ->icon(Heroicon::OutlinedQueueList)
            ->color('warning')
            ->tooltip('Multiple matches found — pick the right one.')
            ->visible(function (array $arguments) {
                $row = $this->data['rows'][$arguments['item']] ?? [];
                return blank(self::decodeResolved($row['resolved'] ?? null))
                    && filled(self::decodeResolved($row['candidates'] ?? null));
            })
            ->schema(function (array $arguments) {
                $row        = $this->data['rows'][$arguments['item']] ?? [];
                $candidates = self::decodeResolved($row['candidates'] ?? null) ?? [];

                return [
                    Radio::make('product_id')
                        ->label('Which one do you have?')
                        ->options(collect($candidates)->mapWithKeys(fn (array $c) => [
                            $c['product_id'] => PulseApiCardMapper::candidateLabel($c),
                        ]))
                        ->required(),
                ];
            })
            ->modalHeading('Choose the matching card')
            ->modalDescription('This name/number matched more than one print — pick the one you actually have.')
            ->action(function (array $data, array $arguments) {
                $itemKey = $arguments['item'];
                $rows    = $this->data['rows'];
                if (! isset($rows[$itemKey])) return;

                $candidates = self::decodeResolved($rows[$itemKey]['candidates'] ?? null) ?? [];
                $chosen     = collect($candidates)->firstWhere('product_id', $data['product_id']);
                if (! $chosen) return;

                $this->applyResolvedAttributes($rows, $itemKey, $chosen);

                $this->data['rows'] = $rows;
                $this->form->fill($this->data);

                Notification::make()->title('Variant selected')->success()->send();
            });
    }

    // Per-row fallback for when PulseAPI can't resolve a card (outage, indexing gap,
    // not in their catalogue, etc.) — only shown while that row is still unresolved.
    protected function manualEntryAction(): Action
    {
        return Action::make('manualEntry')
            ->label('Fill in manually')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->tooltip('PulseAPI lookup failed for this card — enter the details by hand.')
            ->visible(function (array $arguments) {
                $row = $this->data['rows'][$arguments['item']] ?? [];
                return blank(self::decodeResolved($row['resolved'] ?? null));
            })
            ->fillForm(function (array $arguments) {
                $row = $this->data['rows'][$arguments['item']] ?? [];
                return [
                    'card_name'           => $row['card_name'] ?? null,
                    'card_number'         => $row['search_number'] ?? null,
                    'market_value_pounds' => $row['market_value_pounds'] ?? null,
                ];
            })
            ->schema([
                TextInput::make('card_name')->label('Card name')->required(),
                TextInput::make('set_name')->label('Set name'),
                TextInput::make('card_number')->label('Number')->placeholder('e.g. 199/165'),
                TextInput::make('rarity')->label('Rarity'),
                TextInput::make('image_url')->label('Image URL')->url(),
                TextInput::make('market_value_pounds')
                    ->label('Market price (£)')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('£'),
            ])
            ->modalHeading('Enter card details manually')
            ->modalDescription('PulseAPI couldn\'t resolve this card — fill in what you can from the physical card, then save as usual.')
            ->action(function (array $data, array $arguments) {
                $itemKey = $arguments['item'];
                $rows = $this->data['rows'];
                if (! isset($rows[$itemKey])) return;

                $marketPence = filled($data['market_value_pounds'] ?? null)
                    ? Money::toPence($data['market_value_pounds'])
                    : null;

                $resolved = [
                    'product_id'              => $rows[$itemKey]['product_id'] ?? null,
                    'card_name'               => $data['card_name'],
                    'set_name'                => $data['set_name'] ?? null,
                    'card_number'             => $data['card_number'] ?? null,
                    'rarity'                  => $data['rarity'] ?? null,
                    'image_url'               => $data['image_url'] ?? null,
                    'market_value_pence'      => $marketPence,
                    // Neither field is PulseAPI's — this was hand-typed, never synced from them.
                    'market_value_updated_at' => null,
                    'synced_at'               => null,
                ];

                $rows[$itemKey]['resolved'] = json_encode($resolved);
                $rows[$itemKey]['market_value_pounds'] = $data['market_value_pounds'] ?? null;

                $this->data['rows'] = $rows;
                $this->form->fill($this->data);

                Notification::make()->title('Card data filled in manually')->success()->send();
            });
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $rows  = $state['rows'] ?? [];

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $state, &$created, &$skipped) {
            foreach ($rows as $row) {
                // No product_id requirement here — a manually-entered row (PulseAPI had
                // no match at all) never gets one, only $resolved from the popup.
                $resolved = self::decodeResolved($row['resolved'] ?? null);
                if (! $resolved) {
                    $skipped++;
                    continue;
                }

                $quantity  = (int) ($row['quantity'] ?? 1);
                $costPence = Money::toPence($row['cost_pounds']);

                // Honor a manual override of the fetched market price, if the user edited it.
                $override    = $row['market_value_pounds'] ?? null;
                $marketPence = ($override !== null && $override !== '')
                    ? Money::toPence($override)
                    : $resolved['market_value_pence'];

                $attributes = [
                    ...$resolved,
                    'market_value_pence' => $marketPence,
                    'rarity_band'        => (new RarityBander())->bandFor($marketPence),
                ];

                // If the price actually changed from what was fetched, that's us setting it —
                // bump our own sync timestamp. PulseAPI's own timestamp is left untouched.
                if ($marketPence !== $resolved['market_value_pence']) {
                    $attributes['synced_at'] = now();
                }

                for ($i = 0; $i < $quantity; $i++) {
                    CardInventory::create([
                        ...$attributes,
                        'condition'       => 'NM',
                        'cost_pence'      => $costPence,
                        'acquired_at'     => $state['acquired_at'],
                        'acquired_from'   => $state['acquired_from'] ?: null,
                        'acquisition_lot' => $state['acquisition_lot'] ?: null,
                        'status'          => 'in_stock',
                        'game'            => Game::Pokemon->value,
                    ]);
                    $created++;
                }
            }
        });

        if ($created === 0) {
            Notification::make()
                ->title('Nothing saved')
                ->body('Click "Fetch card data" first, then save.')
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title('Intake complete')
            ->body($skipped > 0
                ? "{$created} cards added to inventory. {$skipped} row(s) skipped — not fetched or missing."
                : "{$created} cards added to inventory.")
            ->success()
            ->send();

        $this->redirect(CardInventoryResource::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fetchCardData')
                ->label('Fetch card data')
                ->action('fetchCardData')
                ->color('gray'),
            Action::make('save')
                ->label('Save intake')
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
