<?php

namespace App\Filament\Resources\RipPacks\Pages;

use App\Filament\Resources\RipPacks\RipPackResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRipPack extends CreateRecord
{
    protected static string $resource = RipPackResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return RipPackResource::transformFormData($data);
    }
}
