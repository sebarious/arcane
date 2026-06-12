<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Filament\Resources\Batches\BatchResource;
use Filament\Resources\Pages\CreateRecord;
use App\Enums\BatchType;
use App\Models\Batch;
use App\Services\Batches\BatchDesign;
use App\Enums\Game;

class CreateBatch extends CreateRecord
{
    protected static string $resource = BatchResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure sensible defaults when the form first loads
        $gameValue = $data['game'] ?? Game::Pokemon->value;
        $typeValue = $data['type'] ?? BatchType::Ruby->value;
        $game = Game::from($gameValue);
        $type = BatchType::from($typeValue);
        $data['game'] = $game->value;
        $data['type'] = $type->value;
        // If pack_count/price are missing (e.g. new record), derive them
        $data['pack_count']       ??= BatchDesign::packCount($game, $type);
        $data['sale_price_pence'] ??= BatchDesign::targetSalePrice($game, $type);
        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $game = Game::from($data['game']);
        $type = BatchType::from($data['type']);
        $data['reference'] = Batch::nextReference();
        $data['status']    = 'draft';
        $data['pack_count']       ??= BatchDesign::packCount($game, $type);
        $data['sale_price_pence'] ??= BatchDesign::targetSalePrice($game, $type);
        // Initialise cost/margin fields to 0; generator fills later
        $data['total_cost_pence']           ??= 0;
        $data['total_market_value_pence']   ??= 0;
        $data['margin_pence']               ??= 0;
        $data['margin_scheme_vat_pence']    ??= 0;
        return $data;
    }
}
