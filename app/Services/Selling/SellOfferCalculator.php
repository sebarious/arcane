<?php

namespace App\Services\Selling;

use App\Services\Banding\RarityBander;

class SellOfferCalculator
{
    /**
     * Quote what we'd offer for a card at the given market value, or null if it's
     * not currently eligible (too cheap to bother with, or above our automatic-offer
     * ceiling and needs manual arrangement instead).
     *
     * Reuses RarityBander's band definitions, but with the top (mythic) band extended
     * up to config('selling.max_offer_price_pence') instead of its own hardcoded ceiling —
     * that ceiling is for inventory batch generation, a different business context.
     *
     * @return array{band: string, percentage: float, unit_offer_pence: int, total_offer_pence: int}|null
     */
    public function quote(?int $marketValuePence, int $quantity = 1): ?array
    {
        if ($marketValuePence === null || $quantity < 1) {
            return null;
        }

        $floor   = RarityBander::DEFAULT_THRESHOLDS['common']['min'];
        $ceiling = (int) config('selling.max_offer_price_pence', 100000);

        if ($marketValuePence < $floor || $marketValuePence > $ceiling) {
            return null;
        }

        $thresholds = RarityBander::DEFAULT_THRESHOLDS;
        $thresholds['mythic']['max'] = $ceiling;

        $band = (new RarityBander($thresholds))->bandFor($marketValuePence);
        if (! $band) {
            return null;
        }

        $percentage = (float) (config("selling.offer_percentages.{$band}") ?? 0);
        if ($percentage <= 0) {
            return null;
        }

        $unitOfferPence = (int) round($marketValuePence * $percentage);

        return [
            'band'              => $band,
            'percentage'        => $percentage,
            'unit_offer_pence'  => $unitOfferPence,
            'total_offer_pence' => $unitOfferPence * $quantity,
        ];
    }

    public function isEligible(?int $marketValuePence): bool
    {
        return $this->quote($marketValuePence) !== null;
    }
}
