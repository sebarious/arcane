<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\BatchType;
use App\Enums\Game;

class Batch extends Model
{
    protected $fillable = [
        'reference', 'store_id', 'status', 'pack_count',
        'total_cost_pence', 'total_market_value_pence',
        'sale_price_pence', 'margin_pence', 'margin_scheme_vat_pence',
        'invoice_id', 'qr_sheet_pdf_path',
        'committed_at', 'dispatched_at',
        'type', 'game',
        'failure_reason',
        'failed_at',
        'admin_notes',
        'merged_into_batch_id', 'merged_at',
        'merge_request_batch_id',

    ];

    protected $casts = [
        'committed_at'  => 'datetime',
        'dispatched_at' => 'datetime',
        'failed_at'     => 'datetime',
        'merged_at'     => 'datetime',
        'type'          => BatchType::class,
        'game'          => Game::class,
    ];

    public function store()    { return $this->belongsTo(Store::class); }
    public function packs()    { return $this->hasMany(Pack::class); }
    public function invoice()  { return $this->belongsTo(Invoice::class); }

    // The batch this one was merged into, if any.
    public function mergedInto() { return $this->belongsTo(Batch::class, 'merged_into_batch_id'); }

    // Other batches that were merged into this one.
    public function mergedFrom() { return $this->hasMany(Batch::class, 'merged_into_batch_id'); }

    // The existing batch the seller asked to have merged into this one, once generated.
    public function mergeRequestBatch() { return $this->belongsTo(Batch::class, 'merge_request_batch_id'); }

    public function isMerged(): bool
    {
        return ! is_null($this->merged_into_batch_id);
    }

    public function cards()
    {
        return $this->hasManyThrough(CardInventory::class, Pack::class, 'batch_id', 'pack_id');
    }

    public static function nextReference(): string
    {
        $year = now()->format('Y');
        // Find the last reference for this year
        $last = static::whereYear('created_at', $year)
            ->where('reference', 'like', "ARC-{$year}-%")
            ->orderByDesc('reference')
            ->value('reference');
        $nextNumber = 1;
        if ($last) {
            // last looks like "ARC-2026-0002"
            $parts = explode('-', $last);
            $suffix = end($parts);
            if (is_numeric($suffix)) {
                $nextNumber = (int) $suffix + 1;
            }
        }
        // Ensure we don't collide in odd edge cases
        do {
            $ref = sprintf('ARC-%s-%04d', $year, $nextNumber);
            $exists = static::where('reference', $ref)->exists();
            $nextNumber++;
        } while ($exists);
        return $ref;
    }

}
