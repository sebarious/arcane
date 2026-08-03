<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCreditTransaction extends Model
{
    protected $fillable = [
        'store_id',
        'type',
        'amount_pence',
        'balance_after_pence',
        'reason',
        'customer_sell_submission_id',
        'invoice_id',
        'created_by_user_id',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customerSellSubmission(): BelongsTo
    {
        return $this->belongsTo(CustomerSellSubmission::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
