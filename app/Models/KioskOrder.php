<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KioskOrder extends Model
{
    protected $fillable = [
        'reference', 'status', 'total_pence', 'stripe_payment_intent_id', 'paid_at', 'fulfilled_at',
    ];

    protected $casts = [
        'total_pence' => 'integer',
        'paid_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(KioskOrderItem::class);
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
