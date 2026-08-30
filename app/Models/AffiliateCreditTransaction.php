<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCreditTransaction extends Model
{
    protected $fillable = [
        'affiliate_id',
        'type',
        'amount_pence',
        'balance_after_pence',
        'reason',
        'customer_sell_submission_id',
        'affiliate_withdrawal_id',
        'created_by_user_id',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function customerSellSubmission(): BelongsTo
    {
        return $this->belongsTo(CustomerSellSubmission::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(AffiliateWithdrawal::class, 'affiliate_withdrawal_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
