<?php

namespace App\Filament\Widgets;

use App\Enums\BatchType;
use App\Enums\Game;
use App\Filament\Widgets\Concerns\HasGameFilter;
use App\Models\CardInventory;
use App\Services\Batches\BatchDesign;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * How many more batches of each type could be generated right now, purely
 * from current available() stock — same rarity-band quotas
 * (config('banding.distribution')) and per-card duplicate cap
 * (config('banding.duplicate_limits')) that BatchGenerator itself enforces
 * as its fail-fast feasibility check, just run here for every band up front
 * instead of stopping at the first shortfall. Doesn't attempt to simulate
 * CandidateSelector's margin-target search, so this is an upper bound —
 * the real number a generation run finds could be lower if the stock that
 * clears the band quotas can't also hit the target margin.
 */
class TheoreticalBatchesWidget extends BaseWidget
{
    use HasGameFilter;

    protected ?string $heading = 'Theoretical batches remaining';

    protected function getStats(): array
    {
        $game = Game::from($this->gameFilter ?? Game::Pokemon->value);

        $duplicateLimits = config('banding.duplicate_limits', []);

        $stockByBand = CardInventory::query()
            ->available()
            ->where('game', $game->value)
            ->whereNotNull('rarity_band')
            ->selectRaw('rarity_band, product_id, COUNT(*) as qty')
            ->groupBy('rarity_band', 'product_id')
            ->get()
            ->groupBy('rarity_band');

        $stats = [];

        foreach (BatchType::cases() as $type) {
            $distribution = config("banding.distribution.{$game->value}.{$type->value}", []);

            if (empty($distribution)) {
                continue;
            }

            $theoreticalBatches = null;

            foreach ($distribution as $band => $needed) {
                if ($needed <= 0) {
                    continue;
                }

                $limitPerCard = (int) ($duplicateLimits[$band] ?? 1);
                $cappedAvailable = $stockByBand->get($band, collect())
                    ->sum(fn ($row) => min($row->qty, $limitPerCard));

                $possible = intdiv((int) $cappedAvailable, $needed);

                $theoreticalBatches = $theoreticalBatches === null
                    ? $possible
                    : min($theoreticalBatches, $possible);
            }

            $theoreticalBatches ??= 0;
            $packCount = BatchDesign::packCount($game, $type);

            $stats[] = Stat::make($type->label(), number_format($theoreticalBatches))
                ->description($theoreticalBatches > 0
                    ? "Enough stock for {$theoreticalBatches} more ({$packCount} packs each)"
                    : 'Not enough stock for another batch')
                ->descriptionIcon('heroicon-m-cube')
                ->color($theoreticalBatches > 0 ? 'success' : 'danger');
        }

        return $stats;
    }
}
