<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RipOrder extends Model
{
    protected $fillable = [
        'user_id',
        'reference',
        'status',
        'payment_method',
        'total_pence',
        'stripe_payment_intent_id',
        'paid_at',
    ];

    protected $casts = [
        'total_pence' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rips(): HasMany
    {
        return $this->hasMany(Rip::class);
    }

    /**
     * Year-scoped, zero-padded sequential reference — same generator pattern
     * as Batch/Invoice/CustomerSellSubmission::nextReference().
     */
    public static function nextReference(): string
    {
        $year = now()->format('Y');

        $last = static::whereYear('created_at', $year)
            ->where('reference', 'like', "RIP-{$year}-%")
            ->orderByDesc('reference')
            ->value('reference');

        $nextNumber = 1;

        if ($last) {
            $parts = explode('-', $last);
            $suffix = end($parts);
            if (is_numeric($suffix)) {
                $nextNumber = (int) $suffix + 1;
            }
        }

        do {
            $ref = sprintf('RIP-%s-%04d', $year, $nextNumber);
            $exists = static::where('reference', $ref)->exists();
            $nextNumber++;
        } while ($exists);

        return $ref;
    }
}
