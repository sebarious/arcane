<?php

namespace App\Jobs;

use App\Models\Batch;
use App\Services\Batches\BatchGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $batchId,
    ) {}

    public function handle(BatchGenerator $generator): void
    {
        $batch = Batch::findOrFail($this->batchId);

        try {
            $generator->generate($batch);
        } catch (Throwable $e) {
            // You might want to set a failed status / store error message on the batch.
            $batch->update([
                'status' => 'cancelled',
                'failure_reason' => $e->getMessage(),
                'failed_at'      => now(),
            ]);

            throw $e;
        }
    }
}
