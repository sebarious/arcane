<?php

use App\Enums\Game;
use App\Enums\BatchType;

return [

  'types' => [
    Game::Pokemon->value => [
      BatchType::Sapphire->value => [
        'label'                 => 'Sapphire',
        'packs'                 => 125,
        'price_per_pack_pence'  => 800,  // £8.00
        'target_margin_on_cost' => 0.25, // 25% margin against what we actually paid for the cards
      ],
      BatchType::Ruby->value => [
        'label'                 => 'Ruby',
        'packs'                 => 250,
        'price_per_pack_pence'  => 750,  // £7.50
        'target_margin_on_cost' => 0.20, // 20%
      ],
      BatchType::Diamond->value => [
        'label'                 => 'Diamond',
        'packs'                 => 500,
        'price_per_pack_pence'  => 700,  // £7.00
        'target_margin_on_cost' => 0.15, // 15%
      ],
    ],

    Game::Magic->value    => [],
    Game::Lorcana->value  => [],
    Game::OnePiece->value => [],
  ],

  // How big a batch's target card VALUE should be vs its sale price — purely a
  // sizing/generosity reference for card selection (BatchDesign::targetValue,
  // fed into CandidateSelector's "how close to this size" scoring), not a
  // profitability check. 0.90 = target value is 10% under sale price. Profit
  // itself is judged separately, against cost — see target_margin_on_cost above.
  'target_market_multiple' => 0.90,

  // Flip to false to temporarily turn off "Request batch" in the seller
  // dashboard — disables the button and blocks the route itself, not just the
  // link (see SellerLayout.vue and Seller\BatchRequestController).
  'requests_enabled' => (bool) env('BATCH_REQUESTS_ENABLED', true),
];
