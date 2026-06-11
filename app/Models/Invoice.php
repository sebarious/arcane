<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'number', 'store_id', 'batch_id', 'total_pence',
        'internal_cost_pence', 'internal_margin_pence', 'internal_margin_vat_pence',
        'status', 'issued_on', 'due_on', 'paid_at',
        'stripe_invoice_id', 'stripe_payment_intent_id', 'pdf_path',
    ];

    protected $casts = [
        'issued_on' => 'date',
        'due_on'    => 'date',
        'paid_at'   => 'datetime',
    ];

    public function store() { return $this->belongsTo(Store::class); }
    public function batch() { return $this->belongsTo(Batch::class); }

    public static function nextNumber(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return sprintf('INV-%s-%04d', $year, $count);
    }

}
