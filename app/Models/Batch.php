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

    ];

    protected $casts = [
        'committed_at'  => 'datetime',
        'dispatched_at' => 'datetime',
        'failed_at'     => 'datetime',
        'type'          => BatchType::class,
        'game'          => Game::class,
    ];

    public function store()    { return $this->belongsTo(Store::class); }
    public function packs()    { return $this->hasMany(Pack::class); }
    public function invoice()  { return $this->belongsTo(Invoice::class); }

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
