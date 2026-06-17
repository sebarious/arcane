<?php

use App\Enums\Game;
use App\Enums\BatchType;

return [
  'distribution' => [
    Game::Pokemon->value => [
      BatchType::Sapphire->value => [
        'common'    => 113,
        'rare'      => 6,
        'super'     => 3,
        'legendary' => 2,
        'mythic'    => 1,
      ],
      BatchType::Ruby->value => [
        'common'    => 200,
        'rare'      => 36,
        'super'     => 8,
        'legendary' => 5,
        'mythic'    => 1,
      ],
      BatchType::Diamond->value => [
        'common'    => 400,
        'rare'      => 73,
        'super'     => 15,
        'legendary' => 10,
        'mythic'    => 2,
      ],
    ],

    // Game::Magic->value, etc. can be customised later
  ],
];
