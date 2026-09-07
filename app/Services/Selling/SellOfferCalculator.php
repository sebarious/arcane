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

    /**
     * Human-facing summary of the buy percentages by market value, merging
     * adjacent rarity bands that happen to share the same percentage (so a
     * flat rate across several bands reads as one range) — feeds the /sell
     * page's "what we pay" table rather than any offer calculation.
     *
     * @return list<array{min_pence: int, max_pence: int, percentage: float}>
     */
    public function bandSummary(): array
    {
        $thresholds = RarityBander::DEFAULT_THRESHOLDS;
        $thresholds['mythic']['max'] = (int) config('selling.max_offer_price_pence', 100000);

        $rows = [];

        foreach ($thresholds as $band => ['min' => $min, 'max' => $max]) {
            $percentage = (float) (config("selling.offer_percentages.{$band}") ?? 0);
            if ($percentage <= 0) {
                continue;
            }

            $lastIndex = array_key_last($rows);
            if ($lastIndex !== null && $rows[$lastIndex]['percentage'] === $percentage) {
                $rows[$lastIndex]['max_pence'] = $max;

                continue;
            }

            $rows[] = ['min_pence' => $min, 'max_pence' => $max, 'percentage' => $percentage];
        }

        return $rows;
    }
}
