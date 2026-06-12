<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\CardInventory;
use App\Models\MarketPriceSnapshot;
use App\Services\Banding\RarityBander;
use Illuminate\Database\Seeder;

class CardInventorySeeder extends Seeder
{
    public function run(): void
    {
        $bander = new RarityBander();
        $today  = now()->toDateString();

        $cards = Card::with(['priceSnapshots' => function ($q) {
            $q->latest('fetched_at');
        }])->get();

        $created = 0;

        foreach ($cards as $card) {
            // Use the latest snapshot (seed or real Scrydex later)
            $price = $card->priceSnapshots->first();
            $marketPence = $price?->median_pence ?? 0;

            if ($marketPence <= 0) {
                continue;
            }

            $band = $bander->bandFor($marketPence);
            if (! $band) {
                continue;
            }

            // For dev: 5 copies of each common, 3 of each rare, 2 of each super+,
            // tweak as needed.
            $quantity = match ($band) {
                'common'    => 10,
                'rare'      => 6,
                'super'     => 4,
                'legendary' => 3,
                'mythic'    => 2,
                default     => 2,
            };

            // Cost at ~70% of market in seed, so batches have room for margin
            $costPence = (int) round($marketPence * 0.7);

            for ($i = 0; $i < $quantity; $i++) {
                CardInventory::create([
                    'card_id'               => $card->id,
                    'condition'             => 'NM',
                    'cost_pence'            => $costPence,
                    'acquired_at'           => $today,
                    'acquired_from'         => 'Seeded dev inventory',
                    'acquisition_lot'       => 'SEED-LOT-001',
                    'market_value_pence'    => $marketPence,
                    'market_value_updated_at' => now(),
                    'rarity_band'           => $band,
                    'status'                => 'in_stock',
                ]);
                $created++;
            }
        }

        $this->command?->info("Seeded {$created} physical cards into inventory.");
    }
}
