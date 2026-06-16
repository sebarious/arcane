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
    return (int) (self::config($game, $type)['packs'] ?? 0);
  }

  public static function pricePerPack(Game $game, BatchType $type): int
  {
    return (int) (self::config($game, $type)['price_per_pack_pence'] ?? 0);
  }

  public static function targetSalePrice(Game $game, BatchType $type): int
  {
    return self::packCount($game, $type) * self::pricePerPack($game, $type);
  }

  /**
   * Target margin as a fraction (0.30 = 30%) of the cards' "value".
   */
  public static function targetMargin(Game $game, BatchType $type): float
  {
    return (float) (self::config($game, $type)['target_margin_on_value'] ?? 0.30);
  }

  /**
   * Target total "value" of the cards in the batch:
   *   sale = value * (1 + margin)
   *   value = sale / (1 + margin)
   */
  public static function targetValue(Game $game, BatchType $type): int
  {
    $sale   = self::targetSalePrice($game, $type);
    $margin = self::targetMargin($game, $type);

    return (int) round($sale / (1 + $margin));
  }
}
