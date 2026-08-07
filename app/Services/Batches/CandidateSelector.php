<?php

namespace App\Services\Batches;

use App\Services\Verification\SeededRandom;

/**
 * The random candidate search lifted out of BatchGenerator, as a pure function:
 * plain arrays in, plain arrays out, all randomness routed through a SeededRandom.
 * This is deliberately the *only* place this search logic lives — BatchGenerator
 * (the real run) and BatchVerifier (a later replay against a frozen snapshot) both
 * call this same method, so a verification can never silently drift from what
 * generation actually does.
 */
class CandidateSelector
{
    /**
     * @param  array<int, array{id:int,product_id:int|string|null,rarity_band:string,market_value_pence:?int,cost_pence:?int,value_pence:int}>  $pool
     * @param  array<string,int>  $bandDistribution
     * @param  array<string,int>  $duplicateLimits
     * @param  array<string,array{min:int,max:int}>  $thresholds
     * @return array{
     *     best: array{selected_ids:int[],total_cost:int,total_market:int,total_value:int,margin_value:float}|null,
     *     debug: array{tried:int,rejected_lo:int,rejected_hi:int,duplicate_failures:int,sample:array},
     * }
     */
    public function select(
        array $pool,
        array $bandDistribution,
        array $duplicateLimits,
        array $thresholds,
        int $targetSale,
        float $targetMargin,
        int $targetValue,
        int $packCount,
        SeededRandom $rng,
        int $attempts = 150,
    ): array {
        $bucketed = [];
        foreach ($pool as $card) {
            $bucketed[$card['rarity_band']][] = $card;
        }

        $minMargin = max(0.0, $targetMargin - 0.10);
        $maxMargin = $targetMargin + 0.10;

        $best = null;
        $debug = [
            'tried' => 0,
            'rejected_lo' => 0,
            'rejected_hi' => 0,
            'duplicate_failures' => 0,
            'sample' => [],
        ];

        for ($i = 0; $i < $attempts; $i++) {
            $selected = [];

            foreach ($bandDistribution as $band => $needed) {
                $bandPool = $bucketed[$band] ?? [];
                $limitPerCard = (int) ($duplicateLimits[$band] ?? 1);
                $dedupedPool = $this->poolWithDuplicateLimit($bandPool, $limitPerCard, $rng);

                if (count($dedupedPool) < $needed) {
                    $debug['duplicate_failures']++;

                    continue 2;
                }

                $selected = [...$selected, ...$this->selectForBand($dedupedPool, $needed, $band, $thresholds, $rng)];
            }

            if (count($selected) !== $packCount) {
                continue;
            }

            $totalValue = array_sum(array_column($selected, 'value_pence'));
            $totalCost = array_sum(array_column($selected, 'cost_pence'));
            $totalMarket = array_sum(array_column($selected, 'market_value_pence'));

            if ($totalValue <= 0) {
                continue;
            }

            $marginVsValue = ($targetSale - $totalValue) / $totalValue;
            $debug['tried']++;

            if ($i < 5) {
                $debug['sample'][] = [
                    'value' => round($totalValue / 100, 2),
                    'cost' => round($totalCost / 100, 2),
                    'market' => round($totalMarket / 100, 2),
                    'margin' => round($marginVsValue, 4),
                ];
            }

            if ($marginVsValue < $minMargin) {
                $debug['rejected_lo']++;

                continue;
            }
            if ($marginVsValue > $maxMargin) {
                $debug['rejected_hi']++;

                continue;
            }

            $score = abs($marginVsValue - $targetMargin)
                + abs(($totalValue - $targetValue) / max(1, $targetValue));

            if (! $best || $score < $best['score']) {
                $best = [
                    'selected_ids' => array_column($selected, 'id'),
                    'total_cost' => $totalCost,
                    'total_market' => $totalMarket,
                    'total_value' => $totalValue,
                    'margin_value' => $marginVsValue,
                    'score' => $score,
                ];
            }
        }

        if ($best) {
            unset($best['score']);
        }

        return ['best' => $best, 'debug' => $debug];
    }

    private function poolWithDuplicateLimit(array $cards, int $limitPerCard, SeededRandom $rng): array
    {
        $groups = [];
        foreach ($cards as $card) {
            $groups[$card['product_id']][] = $card;
        }

        $result = [];
        foreach ($groups as $group) {
            $result = [...$result, ...array_slice($rng->shuffle($group), 0, $limitPerCard)];
        }

        return $rng->shuffle($result);
    }

    /**
     * @param  array<string,array{min:int,max:int}>  $thresholds
     */
    private function selectForBand(array $pool, int $needed, string $band, array $thresholds, SeededRandom $rng): array
    {
        if ($band === 'mythic') {
            return array_slice($rng->shuffle($pool), 0, $needed);
        }

        $range = $thresholds[$band] ?? null;
        if (! $range) {
            return array_slice($rng->shuffle($pool), 0, $needed);
        }

        $tierWidth = max(1, ($range['max'] - $range['min']) / 3);
        $lowMax = $range['min'] + $tierWidth;
        $midMax = $range['min'] + (2 * $tierWidth);

        $tiers = ['low' => [], 'mid' => [], 'high' => []];
        foreach ($pool as $card) {
            if ($card['market_value_pence'] < $lowMax) {
                $tiers['low'][] = $card;
            } elseif ($card['market_value_pence'] < $midMax) {
                $tiers['mid'][] = $card;
            } else {
                $tiers['high'][] = $card;
            }
        }

        $selected = [];
        $selectedIds = [];

        foreach ($this->tierTargets($needed) as $tier => $count) {
            $take = array_slice($rng->shuffle($tiers[$tier]), 0, $count);
            $selected = [...$selected, ...$take];
            $selectedIds = [...$selectedIds, ...array_column($take, 'id')];
        }

        // A tier came up short (not enough stock at that price point) — top up from
        // whatever's left in the band so we still hit $needed, just less evenly spread.
        if (count($selected) < $needed) {
            $remaining = array_values(array_filter($pool, fn ($c) => ! in_array($c['id'], $selectedIds, true)));
            $extra = array_slice($rng->shuffle($remaining), 0, $needed - count($selected));
            $selected = [...$selected, ...$extra];
        }

        return $selected;
    }

    /**
     * @return array{low: int, mid: int, high: int}
     */
    private function tierTargets(int $needed): array
    {
        if ($needed <= 0) {
            return ['low' => 0, 'mid' => 0, 'high' => 0];
        }
        if ($needed === 1) {
            return ['low' => 0, 'mid' => 1, 'high' => 0];
        }
        if ($needed === 2) {
            return ['low' => 1, 'mid' => 0, 'high' => 1];
        }

        $base = intdiv($needed, 3);
        $remainder = $needed % 3;
        $targets = ['low' => $base, 'mid' => $base, 'high' => $base];

        if ($remainder === 1) {
            $targets['mid']++;
        } elseif ($remainder === 2) {
            $targets['low']++;
            $targets['high']++;
        }

        return $targets;
    }
}
