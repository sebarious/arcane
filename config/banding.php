<?php

use App\Enums\Game;
use App\Enums\BatchType;

return [
  'distribution' => [
    Game::Pokemon->value => [
      BatchType::Sapphire->value => [
        'common'    => 60,
        'rare'      => 25,
        'super'     => 10,
        'legendary' => 4,
        'mythic'    => 1,
      ],
      BatchType::Ruby->value => [
        'common'    => 150,
        'rare'      => 63,
        'super'     => 25,
        'legendary' => 10,
        'mythic'    => 2,
      ],
      BatchType::Diamond->value => [
        'common'    => 300,
        'rare'      => 125,
        'super'     => 50,
        'legendary' => 20,
        'mythic'    => 5,
      ],
    ],

    // Game::Magic->value, etc. can be customised later
  ],
];
