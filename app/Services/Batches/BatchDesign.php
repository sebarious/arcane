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
   * Target margin as a fraction (0.30 = 30%) against cost — what we actually
   * paid for the cards, not their market value. This is what CandidateSelector
   * treats as the batch's real profitability target/window.
   */
  public static function targetMargin(Game $game, BatchType $type): float
  {
    return (float) (self::config($game, $type)['target_margin_on_cost'] ?? 0.2);
  }

  /**
   * Target total card VALUE for the batch — a sizing/generosity reference for
   * card selection (config('batches.target_market_multiple'), e.g. 0.90 =
   * 10% under sale price), deliberately independent of targetMargin(). This
   * only feeds CandidateSelector's "how close to this size" scoring; it plays
   * no part in judging profitability — see targetMargin() for that.
   */
  public static function targetValue(Game $game, BatchType $type): int
  {
    $sale = self::targetSalePrice($game, $type);
    $multiple = (float) config('batches.target_market_multiple', 1);

    return (int) round($sale * $multiple);
  }
}
