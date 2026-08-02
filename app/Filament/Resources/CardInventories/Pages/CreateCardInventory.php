<?php

namespace App\Filament\Resources\CardInventories\Pages;

use App\Filament\Resources\CardInventories\CardInventoryResource;
use App\Services\Banding\RarityBander;
use Filament\Resources\Pages\CreateRecord;
use App\Services\PulseApi\PulseApiCardMapper;
use App\Services\PulseApi\PulseApiClient;
use App\Support\Money;

class CreateCardInventory extends CreateRecord
{
    protected static string $resource = CardInventoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $priceOverridden = isset($data['market_value_pounds']) && $data['market_value_pounds'] !== '';

        if (isset($data['cost_pounds'])) {
            $data['cost_pence'] = Money::toPence($data['cost_pounds']);
            unset($data['cost_pounds']);
        }
        if ($priceOverridden) {
            $data['market_value_pence'] = Money::toPence($data['market_value_pounds']);
            unset($data['market_value_pounds']);
        }

        // Pull the rest of the card's data (name/set/image/rarity/price/...) from PulseAPI,
        // reusing a recent inventory row for the same product_id when one is fresh enough.
        // A manually-typed market value still wins — merged in afterwards, below.
        if (! empty($data['product_id'])) {
            $attributes = PulseApiCardMapper::cachedAttributes($data['product_id']);
            if (! $attributes) {
                $card = app(PulseApiClient::class)->getCard($data['product_id']);
                $attributes = $card ? PulseApiCardMapper::toInventoryAttributes($card) : [];
            }
            $data = [...$attributes, ...$data];
        }

        if ($priceOverridden) {
            // Update OUR sync timestamp, not PulseAPI's — see EditCardInventory for why.
            $data['synced_at'] = now();
            $data['rarity_band'] = (new RarityBander())->bandFor($data['market_value_pence']);
        }

        return $data;
    }
}
