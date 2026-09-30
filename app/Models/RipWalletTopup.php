<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A customer topping up their own wallet balance with a card payment (as
 * distinct from earning credit via a sell-back, see Rip::decision). Same
 * pending_payment/paid/failed shape as RipOrder, minus anything pack-related.
 */
class RipWalletTopup extends Model
{
    protected $fillable = [
        'user_id',
        'amount_pence',
        'status',
        'stripe_payment_intent_id',
        'paid_at',
    ];

    protected $casts = [
        'amount_pence' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
