<?php

namespace App\Filament\Resources\NonBatchInventory\Pages;

use App\Filament\Resources\CardInventories\Pages\EditCardInventory;
use App\Filament\Resources\NonBatchInventory\NonBatchInventoryResource;

class EditNonBatchInventory extends EditCardInventory
{
    protected static string $resource = NonBatchInventoryResource::class;
}
