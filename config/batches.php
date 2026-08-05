<?php

use App\Enums\Game;
use App\Enums\BatchType;

return [

  'types' => [
    Game::Pokemon->value => [
      BatchType::Sapphire->value => [
        'label'                  => 'Sapphire',
        'packs'                  => 125,
        'price_per_pack_pence'   => 800,  // £8.00
        'target_margin_on_value' => 0.20, // 20% margin vs market value (fallback: cost)
      ],
      BatchType::Ruby->value => [
        'label'                  => 'Ruby',
        'packs'                  => 250,
        'price_per_pack_pence'   => 750,  // £7.50
        'target_margin_on_value' => 0.16, // 16%
      ],
      BatchType::Diamond->value => [
        'label'                  => 'Diamond',
        'packs'                  => 500,
        'price_per_pack_pence'   => 700,  // £7.00
        'target_margin_on_value' => 0.12, // 12%
      ],
    ],

    Game::Magic->value    => [],
    Game::Lorcana->value  => [],
    Game::OnePiece->value => [],
  ],

  // Optional: how generous packs are vs sale (1.0 = market equals sale)
  'target_market_multiple' => 1,

  // Flip to false to temporarily turn off "Request batch" in the seller
  // dashboard — disables the button and blocks the route itself, not just the
  // link (see SellerLayout.vue and Seller\BatchRequestController).
  'requests_enabled' => (bool) env('BATCH_REQUESTS_ENABLED', true),
];
