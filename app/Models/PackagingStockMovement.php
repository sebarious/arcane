<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingStockMovement extends Model
{
    protected $fillable = [
        'packaging_stock_id',
        'delta',
        'balance_after',
        'reason',
        'batch_id',
        'created_by_user_id',
    ];

    public function packagingStock(): BelongsTo
    {
        return $this->belongsTo(PackagingStock::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
