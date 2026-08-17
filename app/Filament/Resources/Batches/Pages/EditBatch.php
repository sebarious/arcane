<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Filament\Resources\Batches\BatchResource;
use Filament\Resources\Pages\EditRecord;

class EditBatch extends EditRecord
{
    protected static string $resource = BatchResource::class;

    protected function getHeaderActions(): array
    {
        // Icon-only — with generate/invoice/publish/warehouse/QR/picking/verify/
        // merge/delete all potentially visible at once, labelled buttons no
        // longer fit on screen. Each keeps a tooltip so it's still discoverable.
        return [
            BatchResource::generateAction()->iconButton()->tooltip('Generate cards'),
            BatchResource::sendInvoiceAction()->iconButton()->tooltip('Send invoice'),
            BatchResource::publishAction()->iconButton()->tooltip('Put it live'),
            BatchResource::markShippedAction()->iconButton()->tooltip('Mark as shipped'),
            BatchResource::sendToWarehouseAction()->iconButton()->tooltip('Send to warehouse'),
            BatchResource::qrSheetAction()->iconButton()->tooltip('QR sheet'),
            BatchResource::regenerateQrSheetAction()->iconButton()->tooltip('Regenerate QR sheet'),
            BatchResource::pickingSheetAction()->iconButton()->tooltip('Picking sheet'),
            BatchResource::verifyAction()->iconButton()->tooltip('Verification page'),
            BatchResource::mergeIntoAction()->iconButton()->tooltip('Merge into…'),
            BatchResource::swapCardAction()->iconButton()->tooltip('Swap a card…'),
            // Redirect after delete — this is the record's own edit page, so it 404s
            // once the batch is gone. The list-page row action (same deleteBatchAction())
            // doesn't need this since it just refreshes the table in place.
            BatchResource::deleteBatchAction()->iconButton()->tooltip('Delete batch')->successRedirectUrl(BatchResource::getUrl('index')),
        ];
    }
}
