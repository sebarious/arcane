<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Filament\Resources\Batches\BatchResource;
use Filament\Resources\Pages\EditRecord;

class EditBatch extends EditRecord
{
    protected static string $resource = BatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            BatchResource::generateAction(),
            BatchResource::qrSheetAction(),
            BatchResource::mergeIntoAction(),
            BatchResource::deleteBatchAction(),
        ];
    }
}
