<?php

namespace App\Services\Banding;

use App\Enums\BatchType;
use App\Enums\Game;

class Distribution
{
  /** @return array<string,int> */
  public static function forGameAndType(Game $game, BatchType $type): array
  {
    return config("banding.distribution.{$game->value}.{$type->value}", []);
  }
}
