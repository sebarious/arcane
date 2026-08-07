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
            BatchResource::verifyAction(),
            BatchResource::mergeIntoAction(),
            // Redirect after delete — this is the record's own edit page, so it 404s
            // once the batch is gone. The list-page row action (same deleteBatchAction())
            // doesn't need this since it just refreshes the table in place.
            BatchResource::deleteBatchAction()->successRedirectUrl(BatchResource::getUrl('index')),
        ];
    }
}
