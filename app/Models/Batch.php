<?php

namespace App\Models;

use App\Enums\ApiMode;
use App\Enums\BatchType;
use App\Enums\Game;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
        'top_card_1_id', 'top_card_2_id',
        // Not verification_seed/verification_hash/verification_committed_at — those are
        // only ever set directly in booted()'s creating hook below, never mass-assigned.
        'verification_revealed_at', 'verification_snapshot_path',
        'is_test',
    ];

    protected $casts = [
        'committed_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'failed_at' => 'datetime',
        'merged_at' => 'datetime',
        'verification_committed_at' => 'datetime',
        'verification_revealed_at' => 'datetime',
        'type' => BatchType::class,
        'game' => Game::class,
        'is_test' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Batch $batch) {
            $seed = bin2hex(random_bytes(32));

            $batch->verification_seed = $seed;
            $batch->verification_hash = hash('sha256', $seed);
            $batch->verification_committed_at = now();
        });

        // Sandbox fixture batches (see App\Services\Api\SandboxBatchProvisioner)
        // stay invisible everywhere else in the app by default — reporting,
        // exports, the storefront, admin listings — without every one of those
        // call sites needing to remember to filter them out. Use
        // withoutGlobalScope('excludeTestBatches') to reach them explicitly.
        static::addGlobalScope('excludeTestBatches', function (Builder $query) {
            $query->where('is_test', false);
        });
    }

    /**
     * The batches a store's partner API should see for a given request — real,
     * committed batches while live, or just its one sandbox batch while in
     * test mode. Centralised here so BatchListController, BatchPacksController,
     * and PackSaleController can't drift apart on the visibility rule.
     */
    public function scopeVisibleToStoreApi(Builder $query, Store $store): Builder
    {
        if ($store->api_mode === ApiMode::Live) {
            return $query->where('store_id', $store->id);
        }

        return $query->withoutGlobalScope('excludeTestBatches')
            ->where('store_id', $store->id)
            ->where('is_test', true);
    }

    public function isVerificationRevealed(): bool
    {
        return $this->verification_revealed_at !== null;
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function packs()
    {
        return $this->hasMany(Pack::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // The batch this one was merged into, if any.
    public function mergedInto()
    {
        return $this->belongsTo(Batch::class, 'merged_into_batch_id');
    }

    // Other batches that were merged into this one.
    public function mergedFrom()
    {
        return $this->hasMany(Batch::class, 'merged_into_batch_id');
    }

    // The existing batch the seller asked to have merged into this one, once generated.
    public function mergeRequestBatch()
    {
        return $this->belongsTo(Batch::class, 'merge_request_batch_id');
    }

    public function isMerged(): bool
    {
        return ! is_null($this->merged_into_batch_id);
    }

    public function cards()
    {
        return $this->hasManyThrough(CardInventory::class, Pack::class, 'batch_id', 'pack_id');
    }

    // Frozen at generation time — see the migration for why these never get
    // recomputed after the fact (Card Lists storefront thumbnail).
    public function topCard1()
    {
        return $this->belongsTo(CardInventory::class, 'top_card_1_id');
    }

    public function topCard2()
    {
        return $this->belongsTo(CardInventory::class, 'top_card_2_id');
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
