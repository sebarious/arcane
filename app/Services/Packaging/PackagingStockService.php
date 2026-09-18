<?php

namespace App\Services\Packaging;

use App\Models\Batch;
use App\Models\PackagingStock;
use App\Models\PackagingStockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PackagingStockService
{
    /**
     * Add stock — e.g. a delivery of new bags/inserts arriving. Locks the row
     * so this can't race with a concurrent addition or a batch-generation
     * deduction.
     */
    public function addStock(string $key, int $quantity, string $reason, ?User $addedBy = null): PackagingStockMovement
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($key, $quantity, $reason, $addedBy) {
            $stock = PackagingStock::query()->where('key', $key)->lockForUpdate()->firstOrFail();

            $newBalance = $stock->quantity_on_hand + $quantity;
            $stock->update(['quantity_on_hand' => $newBalance]);

            return PackagingStockMovement::create([
                'packaging_stock_id' => $stock->id,
                'delta' => $quantity,
                'balance_after' => $newBalance,
                'reason' => $reason,
                'created_by_user_id' => $addedBy?->id,
            ]);
        });
    }

    /**
     * Deduct stock — e.g. bags/inserts used sealing a batch's packs.
     *
     * @throws \RuntimeException if there isn't enough on hand.
     */
    public function deduct(string $key, int $quantity, string $reason, ?Batch $batch = null, ?User $performedBy = null): PackagingStockMovement
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($key, $quantity, $reason, $batch, $performedBy) {
            $stock = PackagingStock::query()->where('key', $key)->lockForUpdate()->firstOrFail();

            if ($stock->quantity_on_hand < $quantity) {
                throw new \RuntimeException(
                    "Not enough {$stock->label} in stock: need {$quantity}, have {$stock->quantity_on_hand}."
                );
            }

            $newBalance = $stock->quantity_on_hand - $quantity;
            $stock->update(['quantity_on_hand' => $newBalance]);

            return PackagingStockMovement::create([
                'packaging_stock_id' => $stock->id,
                'delta' => -$quantity,
                'balance_after' => $newBalance,
                'reason' => $reason,
                'batch_id' => $batch?->id,
                'created_by_user_id' => $performedBy?->id,
            ]);
        });
    }

    /**
     * Fails fast — before BatchGenerator does any of its expensive card
     * selection work — if there isn't enough physical bag/insert stock for
     * the batch it's about to build: one bag per pack, one insert per card
     * matching its rarity band's count in $bandDistribution.
     *
     * @param  array<string, int>  $bandDistribution  rarity_band => count, e.g. from config/banding.php.
     *
     * @throws \RuntimeException on the first shortfall found.
     */
    public function assertAvailable(array $bandDistribution, int $packCount): void
    {
        $required = ['bag' => $packCount];
        foreach ($bandDistribution as $band => $count) {
            $required[PackagingStock::insertKeyForBand($band)] = $count;
        }

        $onHand = PackagingStock::query()
            ->whereIn('key', array_keys($required))
            ->pluck('quantity_on_hand', 'key');

        foreach ($required as $key => $needed) {
            $available = (int) ($onHand[$key] ?? 0);
            if ($available < $needed) {
                $label = PackagingStock::LABELS[$key] ?? $key;
                throw new \RuntimeException("Not enough {$label} in stock: need {$needed}, have {$available}.");
            }
        }
    }

    /**
     * The actual deduction once a batch's packs have been created — one bag
     * per pack, plus one insert per card in each rarity band. Called from
     * inside BatchGenerator's own DB transaction, so a shortfall here (e.g. a
     * race against another batch generating concurrently) rolls the whole
     * batch back rather than leaving sealed packs with no packaging recorded
     * as used.
     *
     * @param  array<string, int>  $bandDistribution
     */
    public function deductForBatch(Batch $batch, array $bandDistribution, int $packCount): void
    {
        $this->deduct('bag', $packCount, "Batch {$batch->reference} generated ({$packCount} packs)", $batch);

        foreach ($bandDistribution as $band => $count) {
            if ($count <= 0) {
                continue;
            }

            $this->deduct(
                PackagingStock::insertKeyForBand($band),
                $count,
                "Batch {$batch->reference} generated ({$count} {$band})",
                $batch,
            );
        }
    }
}
