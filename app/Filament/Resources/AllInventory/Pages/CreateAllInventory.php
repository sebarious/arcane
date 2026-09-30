<?php

namespace App\Filament\Resources\AllInventory\Pages;

use App\Filament\Resources\AllInventory\AllInventoryResource;
use App\Filament\Resources\CardInventories\Concerns\StoresCapturedCardPhoto;
use App\Services\Banding\RarityBander;
use App\Support\Money;
use Filament\Resources\Pages\CreateRecord;

/**
 * Manual, one-at-a-time card entry — the route in for graded slabs, which
 * don't come through Rapid Intake: the grade, the grading company and our own
 * photo of the slab are all things only a person can supply.
 *
 * Deliberately lives on All Inventory rather than one of the two filtered
 * views. A new card may or may not be batch-eligible, and creating it from a
 * list it then doesn't belong in would look like the save had failed.
 */
class CreateAllInventory extends CreateRecord
{
    use StoresCapturedCardPhoto;

    protected static string $resource = AllInventoryResource::class;

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
