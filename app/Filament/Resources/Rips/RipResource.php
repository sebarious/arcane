<?php

namespace App\Filament\Resources\Rips;

use App\Filament\Resources\Rips\Pages\ListRips;
use App\Models\Rip;
use App\Support\Money;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * Read-mostly report of every individual Digital Rip sold — customer,
 * pack, card drawn, decision, and the numbers that make it a profitability
 * report: cost (the card's acquisition cost), sale (price_pence, what the
 * customer paid), payout (sold_back_pence, if sold back) and profit.
 * Doubles as the "over the last X date range" view requested — the date
 * filter below mirrors CardInventoryResource's sold_between filter exactly,
 * scoped to rip_orders.paid_at (i.e. when the pack was sold).
 */
class RipResource extends Resource
{
    protected static ?string $model = Rip::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Digital Rips';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Ripped packs';

    protected static ?string $modelLabel = 'Ripped pack';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        // Only ever report on packs that were actually paid for — a
        // pending/failed order never drew a card, so it has nothing to show.
        return parent::getEloquentQuery()
            ->whereHas('order', fn (Builder $q) => $q->where('status', 'paid'))
            ->with(['order.user', 'pack', 'card']);
    }

    /**
     * By the time a Summarizer's ->using() closure runs, Filament has
     * already flattened $query down to a plain query-builder clone of the
     * filtered result set (see Summarizer::getState()) — not an Eloquent
     * builder, so relations like ->card aren't available on it directly.
     * Re-hydrate real Rip models (with card eager-loaded) from its filtered
     * ids instead, so cost/profit math below can use real casts/relations.
     */
    private static function filteredRips($query): Collection
    {
        return Rip::with('card')->whereIn('id', $query->pluck('id'))->get();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.reference')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.user.name')
                    ->label('Customer')
                    ->description(fn (Rip $record) => $record->order->user->email)
                    ->searchable(),

                Tables\Columns\TextColumn::make('pack_name')
                    ->label('Pack')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('card.image_url')
                    ->label('')
                    ->height(40),

                Tables\Columns\TextColumn::make('card.card_name')
                    ->label('Card drawn')
                    ->description(fn (Rip $record) => $record->card?->set_name)
                    ->placeholder('Not drawn'),

                Tables\Columns\TextColumn::make('card.rarity_band')
                    ->label('Band')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'common' => 'gray',
                        'rare' => 'info',
                        'super' => 'primary',
                        'legendary' => 'warning',
                        'mythic' => 'danger',
                        default => 'gray',
                    }),

                // Not TextColumn::make('card.cost_pence') — a dot-notation
                // column forces Filament's summarizer to auto-guess an
                // inverse relationship (CardInventory has none back to Rip
                // as a HasMany, only a single belongsTo), which throws
                // before ->using() is even consulted. A plain ->state()
                // column with a non-relationship name sidesteps that.
                Tables\Columns\TextColumn::make('cost')
                    ->label('Cost')
                    ->state(fn (Rip $record) => $record->card?->cost_pence)
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd()
                    ->summarize(
                        Summarizer::make('cost_total')
                            ->label('Total cost')
                            ->using(fn ($query) => self::filteredRips($query)->sum(fn (Rip $r) => $r->card?->cost_pence ?? 0))
                            ->formatStateUsing(fn ($state) => Money::format((int) $state))
                    ),

                Tables\Columns\TextColumn::make('price_pence')
                    ->label('Sale')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total sale')->formatStateUsing(fn ($state) => Money::format($state))),

                Tables\Columns\TextColumn::make('decision')
                    ->badge()
                    ->placeholder('Unopened')
                    ->color(fn (?string $state) => match ($state) {
                        'kept' => 'success',
                        'sold_back' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'kept' => 'Kept',
                        'sold_back' => 'Sold back',
                        default => 'Unopened',
                    }),

                Tables\Columns\TextColumn::make('sold_back_pence')
                    ->label('Payout')
                    ->formatStateUsing(fn ($state) => $state !== null ? Money::format($state) : '—')
                    ->alignEnd()
                    ->summarize(Sum::make()->label('Total payout')->formatStateUsing(fn ($state) => Money::format($state))),

                Tables\Columns\TextColumn::make('profit')
                    ->label('Profit')
                    ->state(fn (Rip $record) => $record->price_pence - ($record->card?->cost_pence ?? 0) - ($record->sold_back_pence ?? 0))
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->alignEnd()
                    ->summarize([
                        Summarizer::make('profit_total')
                            ->label('Total profit')
                            ->using(fn ($query) => self::filteredRips($query)->sum(
                                fn (Rip $r) => $r->price_pence - ($r->card?->cost_pence ?? 0) - ($r->sold_back_pence ?? 0)
                            ))
                            ->formatStateUsing(fn ($state) => Money::format((int) $state)),
                        // Profit as a % of total sale value for the filtered
                        // window — the other half of the "profit amount and %"
                        // report asked for, alongside the totals above.
                        Summarizer::make('profit_pct')
                            ->label('Profit %')
                            ->using(function ($query) {
                                $rows = self::filteredRips($query);
                                $sale = $rows->sum('price_pence');
                                $profit = $rows->sum(fn (Rip $r) => $r->price_pence - ($r->card?->cost_pence ?? 0) - ($r->sold_back_pence ?? 0));

                                return $sale > 0 ? round(($profit / $sale) * 100, 1) : 0;
                            })
                            ->formatStateUsing(fn ($state) => $state.'%'),
                    ]),

                Tables\Columns\TextColumn::make('order.paid_at')
                    ->label('Sold')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                // Mirrors CardInventoryResource's sold_between filter exactly,
                // scoped to rip_orders.paid_at (when the pack was sold) rather
                // than delisted_at.
                Tables\Filters\Filter::make('sold_between')
                    ->label('Sold between')
                    ->schema([
                        Forms\Components\DatePicker::make('sold_from'),
                        Forms\Components\DatePicker::make('sold_until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['sold_from'] ?? null,
                                fn ($q, $date) => $q->whereHas('order', fn ($oq) => $oq->whereDate('paid_at', '>=', $date))
                            )
                            ->when(
                                $data['sold_until'] ?? null,
                                fn ($q, $date) => $q->whereHas('order', fn ($oq) => $oq->whereDate('paid_at', '<=', $date))
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRips::route('/'),
        ];
    }
}
