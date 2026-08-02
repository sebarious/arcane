<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSellSubmissionItem extends Model
{
    protected $fillable = [
        'customer_sell_submission_id',
        'product_id', 'card_name', 'card_number', 'set_name', 'rarity', 'image_url',
        'market_value_pence', 'quantity', 'band', 'offer_percentage',
        'unit_offer_pence', 'total_offer_pence',
    ];

    protected $casts = [
        'offer_percentage' => 'float',
    ];

    public function submission()
    {
        return $this->belongsTo(CustomerSellSubmission::class, 'customer_sell_submission_id');
    }
}
