<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\CardInventory;
use App\Models\MarketPriceSnapshot;
use App\Services\Banding\RarityBander;
use Illuminate\Database\Seeder;

class CardInventorySeeder extends Seeder
{
    /**
     * Target per-band economics — designed so a 60/25/10/4/1 batch
     * lands around £6.50/card market value (matching Sapphire 40% / Ruby 30% / Diamond 20%).
     *
     * Each band has:
     *   - count_to_create: how many physical cards to create across the catalogue
     *   - price_pence_range: [low, high] market value range
     *   - cost_ratio: cost as a fraction of market (we buy below market)
     */
    protected array $bandConfig = [
        'common' => [
            'count'      => 2000,
            'price_min'  => 50,
            'price_max'  => 210,    // £0.50 – £2.10, avg ~£1.30
            'cost_ratio' => 0.70,
        ],
        'rare' => [
            'count'      => 800,
            'price_min'  => 350,
            'price_max'  => 900,    // £3.50 – £9.00, avg ~£6.25
            'cost_ratio' => 0.70,
        ],
        'super' => [
            'count'      => 300,
            'price_min'  => 1200,
            'price_max'  => 2500,   // £12 – £25, avg ~£18.50
            'cost_ratio' => 0.65,
        ],
        'legendary' => [
            'count'      => 120,
            'price_min'  => 3200,
            'price_max'  => 5500,   // £32 – £55, avg ~£43
            'cost_ratio' => 0.60,
        ],
        'mythic' => [
            'count'      => 60,
            'price_min'  => 5500,
            'price_max'  => 9000,   // £55 – £90 for the bulk
            'cost_ratio' => 0.55,
            'chase_chance'    => 0.20,
            'chase_price_min' => 15000,
            'chase_price_max' => 30000,
        ],
    ];


    public function run(): void
    {
        // Wipe existing dev inventory so we get a clean slate
        CardInventory::query()->delete();

        $bander = new RarityBander();
        $today  = now()->toDateString();

        // We need cards in the library to attach inventory to.
        // Group library cards however you like; here we just pick at random.
        $libraryCards = Card::query()->get();

        if ($libraryCards->isEmpty()) {
            $this->command?->error('No cards in library. Run CardLibrarySeeder first.');
            return;
        }

        $createdTotal = 0;

        foreach ($this->bandConfig as $band => $cfg) {
            $count = $cfg['count'];
            $this->command?->info("Seeding {$count} {$band} cards…");

            for ($i = 0; $i < $count; $i++) {
                // Pick a random library card
                $card = $libraryCards->random();

                // Determine market value
                if ($band === 'mythic' && (mt_rand() / mt_getrandmax()) < $cfg['chase_chance']) {
                    $marketPence = mt_rand($cfg['chase_price_min'], $cfg['chase_price_max']);
                } else {
                    $marketPence = mt_rand($cfg['price_min'], $cfg['price_max']);
                }

                $costPence = (int) round($marketPence * $cfg['cost_ratio']);

                // Update / create a snapshot so the card has a market price
                MarketPriceSnapshot::updateOrCreate(
                    ['card_id' => $card->id, 'source' => 'seed'],
                    [
                        'condition'    => 'NM',
                        'currency'     => 'GBP',
                        'median_pence' => $marketPence,
                        'low_pence'    => (int) round($marketPence * 0.85),
                        'high_pence'   => (int) round($marketPence * 1.15),
                        'sample_size'  => 25,
                        'raw_payload'  => ['source' => 'seed'],
                        'fetched_at'   => now(),
                    ],
                );

                // Confirm the band the bander would assign
                $computedBand = $bander->bandFor($marketPence);

                CardInventory::create([
                    'card_id'                  => $card->id,
                    'game'                     => $card->game?->value ?? 'pokemon',
                    'condition'                => 'NM',
                    'cost_pence'               => $costPence,
                    'acquired_at'              => $today,
                    'acquired_from'            => 'Dev seed',
                    'acquisition_lot'          => 'SEED-' . strtoupper($band),
                    'market_value_pence'       => $marketPence,
                    'market_value_updated_at'  => now(),
                    'rarity_band'              => $computedBand ?? $band,
                    'status'                   => 'in_stock',
                ]);

                $createdTotal++;
            }
        }

        $this->command?->info("Seeded {$createdTotal} physical inventory rows.");

        // Summary
        $this->command?->newLine();
        $this->command?->info('Inventory shape:');
        foreach (['common', 'rare', 'super', 'legendary', 'mythic'] as $band) {
            $stats = CardInventory::query()
                ->where('rarity_band', $band)
                ->selectRaw('COUNT(*) as n,
                             ROUND(AVG(cost_pence)::numeric / 100, 2) as avg_cost,
                             ROUND(AVG(market_value_pence)::numeric / 100, 2) as avg_market')
                ->first();

            $this->command?->line(sprintf(
                '  %-10s n=%-5d avg_cost=£%-6s avg_market=£%s',
                $band,
                $stats->n,
                $stats->avg_cost,
                $stats->avg_market,
            ));
        }
    }
}
