<?php

return [

    // Percentage of live market price we offer customers selling cards to us,
    // by the same rarity tiers used elsewhere (see App\Services\Selling\SellOfferCalculator).
    'offer_percentages' => [
        'mythic'    => 0.80,
        'legendary' => 0.75,
        'super'     => 0.70,
        'rare'      => 0.65,
        'common'    => 0.60,
    ],

    // A card's market value must be no more than this to be eligible for an automatic
    // offer — above this, it needs manual arrangement rather than the standard flow.
    'max_offer_price_pence' => (int) env('SELL_MAX_OFFER_PRICE_PENCE', 100000),

    'max_items_per_submission' => 100,

    // Extra uplift applied to the offer when a valid store affiliate code is quoted
    // on a submission — e.g. 0.05 means the customer's offer is 5% higher.
    'affiliate_bonus_percentage' => (float) env('SELL_AFFILIATE_BONUS_PERCENTAGE', 0.05),

    // Card languages we currently buy — English only for now, extend this array
    // (e.g. add 'Japanese') once we're ready to expand.
    'allowed_languages' => ['English'],

    // Where customers post their cards to after a submission is accepted.
    'shipping_address' => env('SELL_SHIPPING_ADDRESS'),

];
