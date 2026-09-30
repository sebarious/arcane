<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RipWithdrawal extends Model
{
    protected $fillable = [
        'rip_wallet_id',
        'amount_pence',
        'fee_pence',
        'status',
        'bank_account_name',
        'bank_sort_code',
        'bank_account_number',
        'paid_at',
        'paid_by_user_id',
        'admin_notes',
    ];

    protected $casts = [
        'amount_pence' => 'integer',
        'fee_pence' => 'integer',
        'paid_at' => 'datetime',
    ];

    /** What's actually debited from the wallet for this withdrawal — payout + fee. */
    public function getTotalDebitedPenceAttribute(): int
    {
        return $this->amount_pence + $this->fee_pence;
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(RipWallet::class, 'rip_wallet_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }
}
