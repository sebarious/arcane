<?php

use App\Enums\Game;
use App\Enums\BatchType;

return [

  'types' => [
    Game::Pokemon->value => [
      BatchType::Sapphire->value => [
        'label'                  => 'Sapphire',
        'packs'                  => 100,
        'price_per_pack_pence'   => 900,  // £9.00
        'target_margin_on_value' => 0.40, // 40% margin vs market value (fallback: cost)
      ],
      BatchType::Ruby->value => [
        'label'                  => 'Ruby',
        'packs'                  => 250,
        'price_per_pack_pence'   => 850,  // £8.50
        'target_margin_on_value' => 0.30, // 30%
      ],
      BatchType::Diamond->value => [
        'label'                  => 'Diamond',
        'packs'                  => 500,
        'price_per_pack_pence'   => 800,  // £8.00
        'target_margin_on_value' => 0.20, // 20%
      ],
    ],

    Game::Magic->value    => [],
    Game::Lorcana->value  => [],
    Game::OnePiece->value => [],
  ],

  // Optional: how generous packs are vs sale (1.0 = market equals sale)
  'target_market_multiple' => 1.10,
];
