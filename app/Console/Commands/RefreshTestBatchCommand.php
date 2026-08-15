<?php

namespace App\Console\Commands;

use App\Services\Batches\TestBatchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Regenerates the /test-batch public preview from current live stock — see
 * TestBatchService::refresh(). Read-only against CardInventory (no
 * allocation, no price sync); runs weekly so the preview doesn't drift too
 * far from what's actually in stock.
 */
class RefreshTestBatchCommand extends Command
{
    protected $signature = 'arcane:refresh-test-batch';

    protected $description = 'Regenerate the public /test-batch demo Diamond batch from current live stock';

    public function handle(TestBatchService $service): int
    {
        try {
            $batch = $service->refresh();
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            Log::error('arcane:refresh-test-batch failed', ['exception' => $e]);

            return self::FAILURE;
        }

        $count = count($batch->demo_snapshot['cards'] ?? []);
        $this->info("Test batch refreshed: {$count} cards.");

        return self::SUCCESS;
    }
}
