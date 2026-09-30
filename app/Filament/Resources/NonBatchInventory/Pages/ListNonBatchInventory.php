<?php

namespace App\Filament\Resources\NonBatchInventory\Pages;

use App\Filament\Resources\CardInventories\Pages\ListCardInventories;
use App\Filament\Resources\NonBatchInventory\NonBatchInventoryResource;
use Filament\Actions\CreateAction;
use Filament\Support\Icons\Heroicon;

/**
 * Same listing behaviour as the main inventory page — only the resource it
 * belongs to (and so the rows it shows) differs.
 */
class ListNonBatchInventory extends ListCardInventories
{
    protected static string $resource = NonBatchInventoryResource::class;

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
