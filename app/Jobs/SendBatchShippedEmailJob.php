<?php

namespace App\Jobs;

use App\Mail\BatchShippedMail;
use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBatchShippedEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $batchId,
    ) {}

    public function handle(): void
    {
        $batch = Batch::find($this->batchId);
        if (! $batch) {
            return;
        }

        $batch->loadMissing('store');

        Mail::to($batch->store->contact_email)->send(new BatchShippedMail($batch));
    }
}
