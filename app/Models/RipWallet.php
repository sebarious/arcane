<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RipWallet extends Model
{
    protected $fillable = [
        'user_id',
        'credit_balance_pence',
        'bank_account_name',
        'bank_sort_code',
        'bank_account_number',
    ];

    protected $casts = [
        'credit_balance_pence' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(RipWalletTransaction::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(RipWithdrawal::class);
    }

    public function hasBankDetails(): bool
    {
        return filled($this->bank_account_name) && filled($this->bank_sort_code) && filled($this->bank_account_number);
    }
}
