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

  /** @return array<string, array{tier_1?:int, tier_2?:int, tier_3?:int}> */
  public static function tiersForGameAndType(Game $game, BatchType $type): array
  {
    return config("banding.tier_distribution.{$game->value}.{$type->value}", []);
  }
}
