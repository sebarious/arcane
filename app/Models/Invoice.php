<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'number',
        'store_id',
        'batch_id',
        'total_pence',
        'credit_applied_pence',
        'internal_cost_pence',
        'internal_margin_pence',
        'internal_margin_vat_pence',
        'status',
        'issued_on',
        'due_on',
        'paid_at',
        'last_emailed_at',
        'stripe_invoice_id',
        'pdf_path',
    ];

    protected $casts = [
        'issued_on'       => 'date',
        'due_on'          => 'date',
        'paid_at'         => 'datetime',
        'last_emailed_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(StoreCreditTransaction::class);
    }

    /** total_pence stays the true invoice value — this is what's actually still owed. */
    public function getAmountDuePenceAttribute(): int
    {
        return $this->total_pence - $this->credit_applied_pence;
    }

    public static function nextNumber(): string
    {
        $year = now()->format('Y');

        $last = static::whereYear('created_at', $year)
            ->where('number', 'like', "INV-{$year}-%")
            ->orderByDesc('number')
            ->value('number');

        $nextNumber = 1;

        if ($last) {
            $parts  = explode('-', $last);
            $suffix = end($parts);

            if (is_numeric($suffix)) {
                $nextNumber = (int) $suffix + 1;
            }
        }

        do {
            $num    = sprintf('INV-%s-%04d', $year, $nextNumber);
            $exists = static::where('number', $num)->exists();
            $nextNumber++;
        } while ($exists);

        return $num;
    }
}
