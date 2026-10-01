<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KioskOrder extends Model
{
    protected $fillable = [
        'reference', 'status', 'total_pence', 'stripe_payment_intent_id', 'paid_at', 'fulfilled_at',
        'subtotal_pence', 'discount_type', 'discount_value', 'discount_pence',
        'customer_email', 'receipt_sent_at',
    ];

    protected $casts = [
        'total_pence' => 'integer',
        'subtotal_pence' => 'integer',
        'discount_value' => 'integer',
        'discount_pence' => 'integer',
        'paid_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'receipt_sent_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(KioskOrderItem::class);
    }

    /**
     * The trailing sequence on its own ("0002") — what a customer is asked to
     * quote at the counter. The full KIOSK-2026-0002 stays the canonical
     * reference everywhere it's stored, on receipts and in the admin; this is
     * only ever for display, since the year and prefix are noise to someone
     * reading a number off a screen and saying it out loud.
     */
    public function shortReference(): string
    {
        $parts = explode('-', $this->reference ?? '');
        $last = end($parts);

        return $last !== false && $last !== '' ? $last : (string) $this->reference;
    }

    public static function nextReference(): string
    {
        $year = now()->format('Y');
        $last = static::whereYear('created_at', $year)
            ->where('reference', 'like', "KIOSK-{$year}-%")
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
            $ref = sprintf('KIOSK-%s-%04d', $year, $nextNumber);
            $exists = static::where('reference', $ref)->exists();
            $nextNumber++;
        } while ($exists);

        return $ref;
    }
}
