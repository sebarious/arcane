<?php

namespace App\Filament\Resources\KioskOrders\Pages;

use App\Filament\Resources\KioskOrders\KioskOrderResource;
use Filament\Resources\Pages\ViewRecord;

class ViewKioskOrder extends ViewRecord
{
    protected static string $resource = KioskOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            KioskOrderResource::markFulfilledAction(),
        ];
    }
}
