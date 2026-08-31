<?php

namespace App\Filament\Resources\RipPacks\Pages;

use App\Filament\Resources\RipPacks\RipPackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRipPacks extends ListRecords
{
    protected static string $resource = RipPackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
