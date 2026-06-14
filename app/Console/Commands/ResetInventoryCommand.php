<?php

namespace App\Console\Commands;

use App\Models\Batch;
use App\Models\CardInventory;
use App\Models\Invoice;
use App\Models\Pack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetInventoryCommand extends Command
{
    protected $signature = 'arcane:reset-inventory
                            {--danger : Also delete all card inventory (physical cards), not just batches}
                            {--force  : Skip confirmation}';

    protected $description = 'Reset batches, packs, invoices, and optionally all card inventory (dev only)';

    public function handle(): int
    {
        $danger = $this->option('danger');
        $force  = $this->option('force');

        $what = $danger
            ? 'ALL batches, packs, invoices, and ALL card inventory'
            : 'ALL batches, packs, invoices, and allocations (card_inventory links to packs)';

        if (! $force && ! $this->confirm("This will delete {$what}. Are you sure?")) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($danger) {
            $this->info('Clearing batches, packs, invoices…');

            // Null out FK links first to avoid constraint issues
            Batch::query()->update(['invoice_id' => null]);
            CardInventory::query()->update(['pack_id' => null, 'status' => 'in_stock', 'qr_token' => null]);

            // Delete batches, packs, invoices
            Pack::query()->delete();
            Batch::query()->delete();
            Invoice::query()->delete();

            if ($danger) {
                $this->info('Deleting ALL card inventory (physical cards)…');
                CardInventory::query()->delete();
            }
        });

        $this->info('Inventory reset complete.');

        return self::SUCCESS;
    }
}
