<?php

namespace App\Services\Api;

use App\Enums\Game;
use App\Models\Batch;
use App\Models\CardInventory;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

/**
 * Fixture batch used by stores in ApiMode::Test — lets a seller build and test
 * their integration against realistic data without touching real inventory.
 * It runs through the exact same controllers/services as a real batch
 * (PackRedeemer, BatchPacksController, ...) so what they test is what
 * production actually does; only the data itself is fake.
 */
class SandboxBatchProvisioner
{
    private const REFERENCE_SUFFIX = '-SANDBOX';

    /** [name, set, number, rarity] — a small, deliberately varied spread. */
    private const FIXTURE_CARDS = [
        ['Pikachu', 'sv3pt5', '025/165', 'Common'],
        ['Bulbasaur', 'sv3pt5', '001/165', 'Common'],
        ['Charmander', 'sv3pt5', '004/165', 'Common'],
        ['Squirtle', 'sv3pt5', '007/165', 'Common'],
        ['Vulpix', 'sv3pt5', '037/165', 'Uncommon'],
        ['Gengar', 'sv3pt5', '094/165', 'Rare'],
        ['Dragonite', 'sv3pt5', '149/165', 'Double Rare'],
        ['Mewtwo ex', 'sv3pt5', '150/165', 'Ultra Rare'],
        ['Charizard ex', 'sv3pt5', '006/165', 'Special Illustration Rare'],
        ['Pikachu VMAX', 'swsh045', '044/072', 'Secret Rare'],
    ];

    /** Creates this store's sandbox batch if it doesn't already have one. */
    public function ensure(Store $store): Batch
    {
        return $store->batches()->withoutGlobalScope('excludeTestBatches')
            ->where('is_test', true)
            ->first() ?? $this->create($store);
    }

    /** Resets every sandbox pack back to sealed/unsold, ready to be sold again. */
    public function reset(Store $store): Batch
    {
        $batch = $this->ensure($store);

        DB::transaction(function () use ($batch) {
            $batch->packs()->update(['status' => 'sealed', 'sold_at' => null]);
            CardInventory::whereIn('pack_id', $batch->packs()->pluck('id'))->update([
                'status' => 'allocated',
                'delisted_at' => null,
                'delisted_by_user_id' => null,
            ]);
        });

        return $batch;
    }

    private function create(Store $store): Batch
    {
        return DB::transaction(function () use ($store) {
            $batch = Batch::create([
                'reference' => "{$store->id}".self::REFERENCE_SUFFIX,
                'store_id' => $store->id,
                'status' => 'committed',
                'pack_count' => count(self::FIXTURE_CARDS),
                'total_cost_pence' => 0,
                'total_market_value_pence' => 0,
                'sale_price_pence' => 0,
                'margin_pence' => 0,
                'margin_scheme_vat_pence' => 0,
                'type' => 'ruby',
                'game' => Game::Pokemon,
                'is_test' => true,
                'committed_at' => now(),
            ]);

            foreach (self::FIXTURE_CARDS as $i => [$name, $set, $number, $rarity]) {
                $pack = $batch->packs()->create([
                    'sequence_no' => $i + 1,
                    'status' => 'sealed',
                ]);

                CardInventory::create([
                    'pack_id' => $pack->id,
                    'qr_token' => CardInventory::generateQrToken(),
                    'status' => 'allocated',
                    'game' => Game::Pokemon->value,
                    'condition' => 'NM',
                    'cost_pence' => 100,
                    'acquired_at' => now(),
                    'acquired_from' => 'Sandbox fixture',
                    'market_value_pence' => 300 + ($i * 450),
                    'market_value_updated_at' => now(),
                    'rarity_band' => 'common',
                    'card_name' => $name,
                    'card_number' => $number,
                    'set_id' => $set,
                    'set_name' => 'Scarlet & Violet 151 (Sandbox)',
                    'rarity' => $rarity,
                    'language' => 'English',
                    'image_url' => asset('seed-cards/placeholder.svg'),
                    'synced_at' => now(),
                ]);
            }

            return $batch;
        });
    }
}
