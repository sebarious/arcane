<?php

namespace App\Services\Banding;

class RarityBander
{
    /**
     * Default thresholds in pence. The upper bound of each band is exclusive
     * of the next band's lower bound.
     *
     *   common:    £0.75 – £3.99
     *   rare:      £4.00 – £10.99
     *   super:     £11.00 – £49.99
     *   legendary: £50.00 – £199.99
     *   mythic:    £200.00 – £399.99
     */
    public const DEFAULT_THRESHOLDS = [
        'common'    => ['min' => 75,    'max' => 399], // 2.37
        'rare'      => ['min' => 400,   'max' => 1099], // 7.495
        'super'     => ['min' => 1100,  'max' => 4999], // 30.495
        'legendary' => ['min' => 5000,  'max' => 19999], // 199.99
        'mythic'    => ['min' => 20000,  'max' => 39999], // 399.99
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
