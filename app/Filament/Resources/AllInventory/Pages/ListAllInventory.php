<?php

namespace App\Filament\Resources\AllInventory\Pages;

use App\Filament\Resources\AllInventory\AllInventoryResource;
use App\Filament\Resources\CardInventories\Pages\ListCardInventories;
use Filament\Actions\CreateAction;
use Filament\Support\Icons\Heroicon;

class ListAllInventory extends ListCardInventories
{
    protected static string $resource = AllInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Manual entry for graded slabs and anything else Rapid Intake
            // can't describe on its own.
            CreateAction::make()
                ->label('Add card')
                ->icon(Heroicon::OutlinedPlus),
            ...parent::getHeaderActions(),
        ];
    }
}
