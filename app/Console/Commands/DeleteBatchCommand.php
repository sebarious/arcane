<?php

namespace App\Console\Commands;

use App\Models\Batch;
use App\Services\Batches\BatchDeleter;
use Illuminate\Console\Command;

class DeleteBatchCommand extends Command
{
    protected $signature = 'arcane:delete-batch
                            {batch                : Batch ID or reference (e.g. ARC-2026-0001)}
                            {--keep-cards         : Delete the cards instead of returning them to stock}
                            {--delete-invoice     : Also delete the linked invoice}
                            {--force              : Skip confirmation}';

    protected $description = 'Delete a batch, optionally returning its cards to stock';

    public function handle(BatchDeleter $deleter): int
    {
        $key = $this->argument('batch');

        $batch = is_numeric($key)
            ? Batch::find($key)
            : Batch::where('reference', $key)->first();

        if (! $batch) {
            $this->error("Batch '{$key}' not found.");
            return self::FAILURE;
        }

        $reallocate    = ! $this->option('keep-cards');
        $deleteInvoice = (bool) $this->option('delete-invoice');

        $action = $reallocate ? 'returned to stock' : 'permanently deleted';

        if (
            ! $this->option('force') &&
            ! $this->confirm("Delete batch {$batch->reference}? Cards will be {$action}.")
        ) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        $deleter->delete(
            $batch,
            reallocateInventory: $reallocate,
            deleteInvoice: $deleteInvoice,
        );

        $this->info("Batch {$batch->reference} deleted; cards {$action}.");

        return self::SUCCESS;
    }
}
