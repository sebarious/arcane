<?php

namespace App\Services\Batches;

use App\Enums\BatchType;
use App\Enums\Game;

class BatchDesign
{
  public static function config(Game $game, BatchType $type): array
  {
    return config("batches.types.{$game->value}.{$type->value}", []);
  }

  public static function packCount(Game $game, BatchType $type): int
  {
    $cfg = self::config($game, $type);
    return (int) ($cfg['packs'] ?? 0);
  }

  public static function pricePerPack(Game $game, BatchType $type): int
  {
    $cfg = self::config($game, $type);
    return (int) ($cfg['price_per_pack_pence'] ?? 0);
  }

  public static function targetSalePrice(Game $game, BatchType $type): int
  {
    return self::packCount($game, $type) * self::pricePerPack($game, $type);
  }

  public static function targetCost(Game $game, BatchType $type): int
  {
    $sale   = self::targetSalePrice($game, $type);
    $markup = (float) config('batches.target_markup_on_cost', 0.30);
    return (int) round($sale / (1 + $markup));
  }
}
