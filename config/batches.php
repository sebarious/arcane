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
        'target_margin_on_value' => 0.16, // 16% margin vs market value (fallback: cost)
      ],
      BatchType::Ruby->value => [
        'label'                  => 'Ruby',
        'packs'                  => 250,
        'price_per_pack_pence'   => 750,  // £7.50
        'target_margin_on_value' => 0.12, // 12%
      ],
      BatchType::Diamond->value => [
        'label'                  => 'Diamond',
        'packs'                  => 500,
        'price_per_pack_pence'   => 700,  // £7.00
        'target_margin_on_value' => 0.08, // 8%
      ],
    ],

    Game::Magic->value    => [],
    Game::Lorcana->value  => [],
    Game::OnePiece->value => [],
  ],

  // Optional: how generous packs are vs sale (1.0 = market equals sale)
  'target_market_multiple' => 1,
];
