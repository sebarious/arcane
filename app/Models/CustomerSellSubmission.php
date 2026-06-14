<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSellSubmission extends Model
{
    protected $fillable = [
        'reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_postcode',
        'images',
        'description',
        'status',
        'estimated_value_pence',
        'offered_value_pence',
        'offer_expires_on',
        'admin_notes',
        'decline_reason',
        'reviewed_by_user_id',
        'reviewed_at',
        'offered_at',
        'responded_at',
    ];

    protected $casts = [
        'images'           => 'array',
        'offer_expires_on' => 'date',
        'reviewed_at'      => 'datetime',
        'offered_at'       => 'datetime',
        'responded_at'     => 'datetime',
    ];

    public static function nextReference(): string
    {
        $year = now()->format('Y');

        $last = static::whereYear('created_at', $year)
            ->where('reference', 'like', "SELL-{$year}-%")
            ->orderByDesc('reference')
            ->value('reference');

        $nextNumber = 1;

        if ($last) {
            $parts  = explode('-', $last);
            $suffix = end($parts);
            if (is_numeric($suffix)) {
                $nextNumber = (int) $suffix + 1;
            }
        }

        do {
            $ref    = sprintf('SELL-%s-%04d', $year, $nextNumber);
            $exists = static::where('reference', $ref)->exists();
            $nextNumber++;
        } while ($exists);

        return $ref;
    }
}
