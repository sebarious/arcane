<?php

namespace App\Filament\Resources\CardInventories\Pages;

use App\Filament\Resources\CardInventories\CardInventoryResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Card;
use App\Services\Banding\RarityBander;
use App\Support\Money;

class CreateCardInventory extends CreateRecord
{
    protected static string $resource = CardInventoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['cost_pounds'])) {
            $data['cost_pence'] = Money::toPence($data['cost_pounds']);
            unset($data['cost_pounds']);
        }
        if (isset($data['market_value_pounds'])) {
            $data['market_value_pence'] = Money::toPence($data['market_value_pounds']);
            unset($data['market_value_pounds']);
        }
        // Auto-pull market value from the card library if not set
        if (empty($data['market_value_pence']) && ! empty($data['card_id'])) {
            $card = Card::find($data['card_id']);
            $data['market_value_pence']      = $card?->current_market_pence;
            $data['market_value_updated_at'] = now();
        }
        // Auto-band if not set
        if (empty($data['rarity_band']) && ! empty($data['market_value_pence'])) {
            $data['rarity_band'] = (new RarityBander())->bandFor($data['market_value_pence']);
        }
        return $data;
    }
}
