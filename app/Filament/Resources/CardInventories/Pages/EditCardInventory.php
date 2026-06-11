<?php

namespace App\Filament\Resources\CardInventories\Pages;

use App\Filament\Resources\CardInventories\CardInventoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCardInventory extends EditRecord
{
    protected static string $resource = CardInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
