<?php

namespace App\Jobs;

use App\Mail\BatchWarehousePacketMail;
use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Final step of the "Send to warehouse" chain (see
 * BatchResource::sendToWarehouseAction()) — runs after GenerateBatchQrSheetJob
 * and GeneratePickingSheetJob, so both PDFs are already on the batch by the
 * time this reads them.
 */
class SendBatchWarehousePacketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public int $batchId, public string $recipientEmail) {}

    public function handle(): void
    {
        $batch = Batch::findOrFail($this->batchId);

        Mail::to($this->recipientEmail)->send(new BatchWarehousePacketMail($batch));
    }
}
