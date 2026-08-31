<?php

namespace App\Filament\Resources\RipPacks\Pages;

use App\Filament\Resources\RipPacks\RipPackResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRipPack extends EditRecord
{
    protected static string $resource = RipPackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return RipPackResource::transformFormData($data);
    }
}
