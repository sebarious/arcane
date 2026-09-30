<?php

namespace App\Filament\Resources\AllInventory\Pages;

use App\Filament\Resources\AllInventory\AllInventoryResource;
use App\Filament\Resources\CardInventories\Pages\ListCardInventories;

class ListAllInventory extends ListCardInventories
{
    protected static string $resource = AllInventoryResource::class;
}
