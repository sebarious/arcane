<?php

namespace App\Filament\Resources\NonBatchInventory\Pages;

use App\Filament\Resources\CardInventories\Concerns\StoresCapturedCardPhoto;
use App\Filament\Resources\NonBatchInventory\NonBatchInventoryResource;
use App\Services\Banding\RarityBander;
use App\Support\Money;
use Filament\Resources\Pages\CreateRecord;

/**
 * Manual, one-at-a-time card entry — the route in for graded slabs, which
 * don't come through Rapid Intake: the grade, the grading company and our own
 * photo of the slab are all things only a person can supply.
 *
 * Lives on the non-batch Inventory list because that's what's being added
 * here — graded cards can never be batched at all, and the form defaults
 * "not for batches" on, so a card created from this list belongs in it.
 */
class CreateNonBatchInventory extends CreateRecord
{
    use StoresCapturedCardPhoto;

    protected static string $resource = NonBatchInventoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Mirrors EditCardInventory::mutateFormDataBeforeSave() — the form
        // works in pounds, the table stores pence.
        if (isset($data['cost_pounds'])) {
            $data['cost_pence'] = Money::toPence($data['cost_pounds']);
            unset($data['cost_pounds']);
        }

        if (isset($data['market_value_pounds'])) {
            $data['market_value_pence'] = Money::toPence($data['market_value_pounds']);
            unset($data['market_value_pounds']);
            $data['synced_at'] = now();
        }

        // Nothing has claimed a brand-new card yet, so the band always
        // follows its market value (no isBandLocked() guard needed here).
        $data['rarity_band'] = (new RarityBander)->bandFor($data['market_value_pence'] ?? null);

        return $data;
    }
}
