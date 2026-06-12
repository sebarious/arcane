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
  | target_markup_on_cost:   0.30  => sale = cost * 1.30  (~30% markup on cost)
  | or equivalently: margin = sale * (markup / (1 + markup))
  |
  */
  'target_markup_on_cost' => 0.30,
];
