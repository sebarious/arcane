<?php

namespace App\Filament\Resources\NonBatchInventory\Pages;

use App\Filament\Resources\CardInventories\Pages\ListCardInventories;
use App\Filament\Resources\NonBatchInventory\NonBatchInventoryResource;

/**
 * Same listing behaviour as the main inventory page — only the resource it
 * belongs to (and so the rows it shows) differs.
 */
class ListNonBatchInventory extends ListCardInventories
{
    protected static string $resource = NonBatchInventoryResource::class;
}
