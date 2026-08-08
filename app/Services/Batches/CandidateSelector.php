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
     * @param  array<string,array{tier_1?:int,tier_2?:int,tier_3?:int}>  $tierDistribution  Per-band target
     *         split across the band's 3 equal-width price tiers (config('banding.tier_distribution')).
     *         A band missing here (or whose counts don't sum to that band's total) falls back to an
     *         even auto-split — see selectForBand().
     * @return array{
     *     best: array{selected_ids:int[],total_cost:int,total_market:int,total_value:int,margin_cost:float}|null,
     *     debug: array{tried:int,rejected_lo:int,duplicate_failures:int,sample:array},
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
        array $tierDistribution = [],
        int $attempts = 150,
    ): array {
        $bucketed = [];
        foreach ($pool as $card) {
            $bucketed[$card['rarity_band']][] = $card;
        }

        // Profitability is a floor, not a target: $targetMargin is judged
        // against COST — what we actually paid, not market/value — and a
        // candidate only needs to clear it, with no upper rejection. Making
        // MORE than target is fine (expected, even); we just never accept
        // less. $targetValue plays no part in this at all — it only steers
        // the scoring below toward a consistently-sized batch.
        $best = null;
        $debug = [
            'tried' => 0,
            'rejected_lo' => 0,
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

                $selected = [...$selected, ...$this->selectForBand($dedupedPool, $needed, $band, $thresholds, $tierDistribution[$band] ?? null, $rng)];
            }

            if (count($selected) !== $packCount) {
                continue;
            }

            $totalValue = array_sum(array_column($selected, 'value_pence'));
            $totalCost = array_sum(array_column($selected, 'cost_pence'));
            $totalMarket = array_sum(array_column($selected, 'market_value_pence'));

            if ($totalValue <= 0 || $totalCost <= 0) {
                continue;
            }

            $marginVsCost = ($targetSale - $totalCost) / $totalCost;
            $marginVsValue = ($targetSale - $totalValue) / $totalValue; // informational only — see below
            $debug['tried']++;

            if ($i < 5) {
                $debug['sample'][] = [
                    'value' => round($totalValue / 100, 2),
                    'cost' => round($totalCost / 100, 2),
                    'market' => round($totalMarket / 100, 2),
                    'margin_on_cost' => round($marginVsCost, 4),
                    'margin_on_value' => round($marginVsValue, 4),
                ];
            }

            // Profitability is judged entirely against cost — margin_on_value is
            // captured above for visibility only, it never gates or scores a
            // candidate. Floor only: no upper rejection, and scoring below
            // never pulls toward $targetMargin either — a candidate at 43%
            // is just as valid a "best" as one at 25%, so this can't quietly
            // turn back into "try to match" via the score instead of the gate.
            if ($marginVsCost < $targetMargin) {
                $debug['rejected_lo']++;

                continue;
            }

            // The only thing scored is closeness to $targetValue — a pure
            // sizing/generosity reference (see BatchDesign::targetValue) for
            // picking a consistently-sized batch among everything that already
            // cleared the profit floor above.
            $score = abs(($totalValue - $targetValue) / max(1, $targetValue));

            if (! $best || $score < $best['score']) {
                $best = [
                    'selected_ids' => array_column($selected, 'id'),
                    'total_cost' => $totalCost,
                    'total_market' => $totalMarket,
                    'total_value' => $totalValue,
                    'margin_cost' => $marginVsCost,
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
     * Splits $pool into this band's 3 equal-width price tiers and draws $needed
     * cards from them. Two recipes:
     *
     * - Configured (a valid tier_distribution entry for $band, i.e. one whose
     *   tier_1+tier_2+tier_3 actually sums to $needed): each tier is drawn via
     *   selectBalancedForTier — cards chosen so the tier's realised average price
     *   lands as close as possible to that tier's own midpoint, for predictable
     *   per-tier economics. A tier that comes up short first tries to borrow from
     *   the next tier up's cheaper half (see the escalation loop below) before
     *   falling back to an unconstrained top-up from anywhere left in the band.
     * - Unconfigured (band missing from tier_distribution, or its counts don't
     *   add up — e.g. a stale config edit) — an even auto-split with plain random
     *   picking per tier, same as the top-up fallback. mythic has no config
     *   entry by default, so it lands here — the auto-split for a 1-card band
     *   happens to land it in tier_2, and for a 2-card band splits it tier_1 +
     *   tier_3 — but a band+type can now pin mythic to specific tiers just
     *   like any other rarity (e.g. Sapphire's single mythic pinned to tier_2,
     *   to keep it away from the top of a shared £150–£400 mythic range that a
     *   cheap batch's economics can't absorb).
     *
     * @param  array<string,array{min:int,max:int}>  $thresholds
     * @param  array{tier_1?:int,tier_2?:int,tier_3?:int}|null  $configuredTiers
     */
    private function selectForBand(array $pool, int $needed, string $band, array $thresholds, ?array $configuredTiers, SeededRandom $rng): array
    {
        $range = $thresholds[$band] ?? null;
        if (! $range) {
            return array_slice($rng->shuffle($pool), 0, $needed);
        }

        $tierWidth = max(1, ($range['max'] - $range['min']) / 3);
        $bounds = [
            'tier_1' => ['min' => $range['min'], 'max' => $range['min'] + $tierWidth],
            'tier_2' => ['min' => $range['min'] + $tierWidth, 'max' => $range['min'] + (2 * $tierWidth)],
            'tier_3' => ['min' => $range['min'] + (2 * $tierWidth), 'max' => $range['max']],
        ];

        $tiers = ['tier_1' => [], 'tier_2' => [], 'tier_3' => []];
        foreach ($pool as $card) {
            $price = (int) ($card['market_value_pence'] ?? 0);
            if ($price < $bounds['tier_1']['max']) {
                $tiers['tier_1'][] = $card;
            } elseif ($price < $bounds['tier_2']['max']) {
                $tiers['tier_2'][] = $card;
            } else {
                $tiers['tier_3'][] = $card;
            }
        }

        $isConfigured = $configuredTiers !== null && array_sum($configuredTiers) === $needed;

        if (! $isConfigured) {
            $selected = [];
            $selectedIds = [];

            foreach ($this->autoSplitTierTargets($needed) as $tier => $count) {
                $take = array_slice($rng->shuffle($tiers[$tier]), 0, $count);
                $selected = [...$selected, ...$take];
                $selectedIds = [...$selectedIds, ...array_column($take, 'id')];
            }

            return $this->topUpToNeeded($selected, $selectedIds, $pool, $needed, $rng);
        }

        $selected = [];
        $selectedIds = [];
        $shortfall = ['tier_1' => 0, 'tier_2' => 0, 'tier_3' => 0];

        foreach (['tier_1', 'tier_2', 'tier_3'] as $tier) {
            $count = $configuredTiers[$tier] ?? 0;
            if ($count <= 0) {
                continue;
            }

            $take = $this->selectBalancedForTier($tiers[$tier], $count, $this->tierMidpoint($bounds[$tier]), $rng);
            $selected = [...$selected, ...$take];
            $selectedIds = [...$selectedIds, ...array_column($take, 'id')];
            $shortfall[$tier] = $count - count($take);
        }

        // Escalation: a tier short of stock first tries the tier above it,
        // restricted to that tier's cheaper half; if that's not enough (or
        // doesn't exist — tier_3 has no tier above), it then tries the tier
        // below, restricted to ITS pricier half. Either way the substitute
        // stays as close as possible to the shortfall tier's own boundary,
        // rather than an unconstrained pick from anywhere in the band — that
        // unconstrained pick is still the final fallback (topUpToNeeded
        // below), just now the last resort instead of the first one. Cards
        // already taken (including by another tier's own primary pass) are
        // excluded throughout, so this only ever spends a tier's genuine
        // leftover capacity.
        $neighbors = [
            'tier_1' => ['up' => 'tier_2'],
            'tier_2' => ['up' => 'tier_3', 'down' => 'tier_1'],
            'tier_3' => ['down' => 'tier_2'],
        ];

        foreach (['tier_1', 'tier_2', 'tier_3'] as $tier) {
            foreach ($neighbors[$tier] as $direction => $neighbor) {
                if ($shortfall[$tier] <= 0) {
                    break;
                }

                $midpoint = $this->tierMidpoint($bounds[$neighbor]);
                $candidates = array_values(array_filter($tiers[$neighbor], function ($c) use ($selectedIds, $midpoint, $direction) {
                    if (in_array($c['id'], $selectedIds, true)) {
                        return false;
                    }
                    $price = (int) ($c['market_value_pence'] ?? 0);

                    // Borrowing "up" stays in the neighbour's cheaper half (closest
                    // to the tier below it); borrowing "down" stays in its pricier
                    // half (closest to the tier above it) — either way, closest to
                    // the shortfall tier's own boundary with that neighbour.
                    return $direction === 'up' ? $price < $midpoint : $price >= $midpoint;
                }));

                $take = array_slice($rng->shuffle($candidates), 0, $shortfall[$tier]);
                $selected = [...$selected, ...$take];
                $selectedIds = [...$selectedIds, ...array_column($take, 'id')];
                $shortfall[$tier] -= count($take);
            }
        }

        return $this->topUpToNeeded($selected, $selectedIds, $pool, $needed, $rng);
    }

    /**
     * Last-resort fallback shared by both recipes: an unconstrained random pick
     * from whatever's left anywhere in the band, so a struggling card pool still
     * produces a batch rather than failing generation outright.
     */
    private function topUpToNeeded(array $selected, array $selectedIds, array $pool, int $needed, SeededRandom $rng): array
    {
        if (count($selected) >= $needed) {
            return $selected;
        }

        $remaining = array_values(array_filter($pool, fn ($c) => ! in_array($c['id'], $selectedIds, true)));
        $extra = array_slice($rng->shuffle($remaining), 0, $needed - count($selected));

        return [...$selected, ...$extra];
    }

    /**
     * Greedily keeps the running average as close to $midpoint as possible: at
     * each step, picks whichever remaining card brings the group's average
     * value nearest to it. This is what makes a tier's realised average price
     * predictable/consistent batch to batch (directly drives real margin),
     * rather than swinging with whatever random cards happen to be in stock.
     * Ties are broken by $rng's shuffle order, so the pick stays fair and
     * deterministic without a second source of randomness.
     */
    private function selectBalancedForTier(array $pool, int $needed, int $midpoint, SeededRandom $rng): array
    {
        if ($needed <= 0 || empty($pool)) {
            return [];
        }

        $remaining = array_values($rng->shuffle($pool));
        $selected = [];
        $runningTotal = 0;

        for ($n = 1; $n <= $needed && ! empty($remaining); $n++) {
            $bestIndex = 0;
            $bestDistance = null;

            foreach ($remaining as $index => $card) {
                $price = (int) ($card['market_value_pence'] ?? 0);
                $averageIfPicked = ($runningTotal + $price) / $n;
                $distance = abs($averageIfPicked - $midpoint);

                if ($bestDistance === null || $distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestIndex = $index;
                }
            }

            $chosen = $remaining[$bestIndex];
            $selected[] = $chosen;
            $runningTotal += (int) ($chosen['market_value_pence'] ?? 0);
            unset($remaining[$bestIndex]);
            $remaining = array_values($remaining);
        }

        return $selected;
    }

    private function tierMidpoint(array $bound): int
    {
        return (int) round(($bound['min'] + $bound['max']) / 2);
    }

    /**
     * The even, no-config-needed split — used for any band without a valid
     * tier_distribution entry (currently: mythic never gets one by design, plus
     * a safety net for a stale/typo'd config elsewhere).
     *
     * @return array{tier_1: int, tier_2: int, tier_3: int}
     */
    private function autoSplitTierTargets(int $needed): array
    {
        if ($needed <= 0) {
            return ['tier_1' => 0, 'tier_2' => 0, 'tier_3' => 0];
        }
        if ($needed === 1) {
            return ['tier_1' => 0, 'tier_2' => 1, 'tier_3' => 0];
        }
        if ($needed === 2) {
            return ['tier_1' => 1, 'tier_2' => 0, 'tier_3' => 1];
        }

        $base = intdiv($needed, 3);
        $remainder = $needed % 3;
        $targets = ['tier_1' => $base, 'tier_2' => $base, 'tier_3' => $base];

        if ($remainder === 1) {
            $targets['tier_2']++;
        } elseif ($remainder === 2) {
            $targets['tier_1']++;
            $targets['tier_3']++;
        }

        return $targets;
    }
}
