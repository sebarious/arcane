<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RipWalletTransaction extends Model
{
    protected $fillable = [
        'rip_wallet_id',
        'type',
        'amount_pence',
        'balance_after_pence',
        'reason',
        'rip_id',
        'rip_withdrawal_id',
        'rip_wallet_topup_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'amount_pence' => 'integer',
        'balance_after_pence' => 'integer',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(RipWallet::class, 'rip_wallet_id');
    }

    public function rip(): BelongsTo
    {
        return $this->belongsTo(Rip::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(RipWithdrawal::class, 'rip_withdrawal_id');
    }

    public function topup(): BelongsTo
    {
        return $this->belongsTo(RipWalletTopup::class, 'rip_wallet_topup_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
