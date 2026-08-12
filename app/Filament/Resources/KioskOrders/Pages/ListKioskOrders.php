<?php

namespace App\Filament\Resources\KioskOrders\Pages;

use App\Filament\Resources\KioskOrders\KioskOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListKioskOrders extends ListRecords
{
    protected static string $resource = KioskOrderResource::class;
}
