<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketPriceSnapshot extends Model
{
    protected $fillable = [
        'card_id', 'condition', 'source', 'currency',
        'median_pence', 'mean_pence', 'low_pence', 'high_pence',
        'sample_size', 'raw_payload', 'fetched_at',
    ];
    protected $casts = [
        'raw_payload' => 'array',
        'fetched_at'  => 'datetime',
    ];
    public function card() { return $this->belongsTo(Card::class); }
}
