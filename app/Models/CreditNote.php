<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    protected $fillable = [
        'number',
        'store_id',
        'invoice_id',
        'amount_pence',
        'reason',
        'status',
        'issued_by_user_id',
        'paid_by_user_id',
        'applied_at',
        'paid_at',
        'last_emailed_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'paid_at' => 'datetime',
        'last_emailed_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public static function nextNumber(): string
    {
        $year = now()->format('Y');

        $last = static::whereYear('created_at', $year)
            ->where('number', 'like', "CN-{$year}-%")
            ->orderByDesc('number')
            ->value('number');

        $nextNumber = 1;

        if ($last) {
            $parts = explode('-', $last);
            $suffix = end($parts);

            if (is_numeric($suffix)) {
                $nextNumber = (int) $suffix + 1;
            }
        }

        do {
            $num = sprintf('CN-%s-%04d', $year, $nextNumber);
            $exists = static::where('number', $num)->exists();
            $nextNumber++;
        } while ($exists);

        return $num;
    }
}
