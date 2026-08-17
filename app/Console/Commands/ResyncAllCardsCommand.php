<?php

namespace App\Console\Commands;

use App\Models\CardInventory;
use App\Services\PulseApi\PulseApiCardMapper;
use App\Services\PulseApi\PulseApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

/**
 * A one-off, broader sibling to arcane:refresh-prices — that command only
 * touches rows whose price has aged past the TTL, which is right for
 * day-to-day upkeep but wrong after a RarityBander/config('banding')
 * change: every row's rarity_band was computed under the old thresholds,
 * regardless of sync recency, so this deliberately ignores the TTL and
 * walks every in-stock card_inventory row with a product_id. Sold/allocated
 * rows are left alone since their band/price is frozen at time of sale.
 */
class ResyncAllCardsCommand extends Command
{
    protected $signature = 'arcane:resync-all-cards {--chunk=50 : How many distinct product_ids to fetch from PulseAPI per request} {--dry-run : Report what would change without writing}';

    protected $description = 'Resync price and rarity_band for every in-stock card_inventory row from PulseAPI — run after a rarity banding change so all in-stock cards reflect the new thresholds';

    public function handle(PulseApiClient $client): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $productIds = CardInventory::query()
            ->inStock()
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id');

        if ($productIds->isEmpty()) {
            $this->info('No cards with a product_id to resync.');

            return self::SUCCESS;
        }

        $totalRows = CardInventory::query()->inStock()->whereIn('product_id', $productIds)->count();
        $this->info(sprintf(
            '%s%d distinct card(s) across %d inventory row(s), in batches of %d…',
            $dryRun ? '[DRY RUN] ' : '',
            $productIds->count(),
            $totalRows,
            $chunkSize,
        ));

        $updatedRows = 0;
        $changedBand = 0;
        $notFound = 0;
        $chunks = $productIds->chunk($chunkSize)->values();

        foreach ($chunks as $i => $chunk) {
            $this->info(sprintf('Batch %d/%d (%d cards)…', $i + 1, $chunks->count(), $chunk->count()));

            $cards = $client->batchGetCards($chunk->all());

            $rows = CardInventory::query()->inStock()->whereIn('product_id', $chunk->all())->get(['id', 'product_id', 'rarity_band']);

            foreach ($rows->groupBy('product_id') as $productId => $productRows) {
                $card = $cards[$productId] ?? null;

                if (! $card) {
                    $notFound += $productRows->count();

                    continue;
                }

                $attributes = Arr::only(
                    PulseApiCardMapper::toInventoryAttributes($card),
                    ['market_value_pence', 'market_value_updated_at', 'rarity_band', 'rarity', 'rarity_rank', 'image_url', 'synced_at'],
                );

                $changedBand += $productRows->where('rarity_band', '!=', $attributes['rarity_band'])->count();

                if (! $dryRun) {
                    CardInventory::whereIn('id', $productRows->pluck('id'))->update($attributes);
                }

                $updatedRows += $productRows->count();
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s%d row(s) %s, %d moved to a different rarity_band, %d product_id(s) not found on PulseAPI.',
            $dryRun ? '[DRY RUN] ' : '',
            $updatedRows,
            $dryRun ? 'would be updated' : 'updated',
            $changedBand,
            $notFound,
        ));

        return self::SUCCESS;
    }
}
