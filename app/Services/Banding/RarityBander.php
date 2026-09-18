<?php

namespace App\Services\Banding;

class RarityBander
{
    /**
     * Default thresholds in pence. The upper bound of each band is exclusive
     * of the next band's lower bound.
     *
     *   common:    £0.01 – £4.99
     *   rare:      £5.00 – £10.49
     *   super:     £10.50 – £49.99
     *   legendary: £50.00 – £149.99
     *   mythic:    £150.00 – £349.99
     *   chase:     £425.00 – £750.00
     *
     * Chase (Premium batches only — see config/banding.php) deliberately does
     * NOT start where mythic stops: £350.00–£424.99 belongs to no band, and a
     * card priced there is left unbanded on purpose rather than being widened
     * into either one. Mythic's ceiling is load-bearing for Sapphire/Ruby/
     * Diamond economics, and £425 is the floor the chase slot is sold on, so
     * the gap is the price of keeping both honest — don't "fix" it by
     * stretching one of the two to meet the other. Anything above chase's
     * £750 ceiling is likewise unbanded: too rich for any slot we sell.
     *
     * Unbanded cards can't be picked up by batch generation at all (its pool
     * query requires rarity_band IS NOT NULL), so they need pricing by hand.
     *
     * Key order is load-bearing, not cosmetic: bandFor() returns the first
     * range that matches, and SellOfferCalculator reuses this list with
     * mythic's ceiling raised to the sell-offer cap (~£1,000) — an override
     * that swallows chase's whole range. Mythic sitting above chase here is
     * what keeps a £500 sell quote resolving to mythic. Moving chase first
     * would silently reprice those quotes and start writing 'chase' into
     * customer_sell_submission_items.band, which is still a five-band enum.
     */
    public const DEFAULT_THRESHOLDS = [
        'common'    => ['min' => 1,    'max' => 499],
        'rare'      => ['min' => 500,   'max' => 1049],
        'super'     => ['min' => 1050,  'max' => 4999],
        'legendary' => ['min' => 5000,  'max' => 14999],
        'mythic'    => ['min' => 15000,  'max' => 34999],
        'chase'     => ['min' => 42500,  'max' => 75000],
    ];

    /** @param array<string, array{min:int,max:int}>|null $thresholds */
    public function __construct(protected ?array $thresholds = null)
    {
        $this->thresholds ??= self::DEFAULT_THRESHOLDS;
    }

    /**
     * Return the band a market value falls into, or null if below the floor.
     */
    public function bandFor(?int $marketValuePence): ?string
    {
        if ($marketValuePence === null) return null;

        foreach ($this->thresholds as $band => ['min' => $min, 'max' => $max]) {
            if ($marketValuePence >= $min && $marketValuePence <= $max) {
                return $band;
            }
        }
        return null;
    }

    /** @return array<string, array{min:int,max:int}> */
    public function thresholds(): array
    {
        return $this->thresholds;
    }
}
