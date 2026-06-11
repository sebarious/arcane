<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = [
        'reference', 'store_id', 'status', 'pack_count',
        'total_cost_pence', 'total_market_value_pence',
        'sale_price_pence', 'margin_pence', 'margin_scheme_vat_pence',
        'invoice_id', 'qr_sheet_pdf_path',
        'committed_at', 'dispatched_at',
    ];

    protected $casts = [
        'committed_at' => 'datetime',
        'dispatched_at' => 'datetime',
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
        $count = static::whereYear('created_at', $year)->count() + 1;
        return sprintf('ARC-%s-%04d', $year, $count);
    }

}
