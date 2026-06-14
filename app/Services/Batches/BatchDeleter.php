<?php

namespace App\Services\Batches;

use App\Models\Batch;
use App\Models\CardInventory;
use App\Models\Invoice;
use App\Models\Pack;
use Illuminate\Support\Facades\DB;

class BatchDeleter
{
  /**
   * Delete a batch.
   *
   * @param  Batch $batch
   * @param  bool  $reallocateInventory  If true, return cards to in_stock; else delete them.
   * @param  bool  $deleteInvoice        If true, also delete the linked invoice.
   */
  public function delete(
    Batch $batch,
    bool $reallocateInventory = true,
    bool $deleteInvoice = false,
  ): void {
    DB::transaction(function () use ($batch, $reallocateInventory, $deleteInvoice) {
      $cardIds = CardInventory::query()
        ->whereIn('pack_id', $batch->packs()->pluck('id'))
        ->pluck('id');

      if ($reallocateInventory) {
        // Reset cards back to in_stock
        CardInventory::whereIn('id', $cardIds)->update([
          'pack_id'                    => null,
          'status'                     => 'in_stock',
          'qr_token'                   => null,
          'allocated_sale_price_pence' => null,
          'margin_pence'               => null,
          'delisted_at'                => null,
          'delisted_by_user_id'        => null,
        ]);
      } else {
        // Hard delete the inventory rows tied to this batch
        CardInventory::whereIn('id', $cardIds)->delete();
      }

      // Drop the packs
      Pack::where('batch_id', $batch->id)->delete();

      // Optionally drop the invoice
      if ($deleteInvoice && $batch->invoice_id) {
        Invoice::where('id', $batch->invoice_id)->delete();
      } else {
        // Just unlink the invoice so we don't FK-fail
        Invoice::where('batch_id', $batch->id)->update(['batch_id' => null]);
      }

      // Finally, the batch itself
      $batch->delete();
    });
  }
}
