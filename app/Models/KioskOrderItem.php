<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KioskOrderItem extends Model
{
    protected $fillable = [
        'kiosk_order_id', 'card_inventory_id', 'card_name', 'set_name', 'card_number',
        'rarity', 'market_value_pence', 'unit_price_pence', 'lot', 'position',
    ];

    protected $casts = [
        'market_value_pence' => 'integer',
        'unit_price_pence' => 'integer',
        'position' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(KioskOrder::class, 'kiosk_order_id');
    }

    public function card()
    {
        return $this->belongsTo(CardInventory::class, 'card_inventory_id');
    }
}
