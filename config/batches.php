<?php

use App\Enums\BatchType;
use App\Enums\Game;

return [

  /*
  |--------------------------------------------------------------------------
  | Batch type definitions
  |--------------------------------------------------------------------------
  */
  'types' => [
    Game::Pokemon->value => [
      BatchType::Sapphire->value => [
        'label'                => 'Sapphire',
        'packs'                => 100,
        'price_per_pack_pence' => 800,
      ],
      BatchType::Ruby->value => [
        'label'                => 'Ruby',
        'packs'                => 250,
        'price_per_pack_pence' => 700,
      ],
      BatchType::Diamond->value => [
        'label'                => 'Diamond',
        'packs'                => 500,
        'price_per_pack_pence' => 600,
      ],
    ],
  ],

  /*
  |--------------------------------------------------------------------------
  | Global margin settings
  |--------------------------------------------------------------------------
  |
  | target_markup_on_cost:   0.20  => sale = cost * 1.20  (~20% markup on cost)
  | or equivalently: margin = sale * (markup / (1 + markup))
  |
  */
  'target_markup_on_cost' => 0.20,

  'target_market_multiple' => 1.20, // target market value = sale * 1.20 (~20% over sale price)
];
