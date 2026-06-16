<?php

namespace App\Services\Banding;

class RarityBander
{
    /**
     * Default thresholds in pence. The upper bound of each band is exclusive
     * of the next band's lower bound.
     *
     *   common:    £0.10 – £1.99
     *   rare:      £2.00 – £9.99
     *   super:     £10.00 – £39.99
     *   legendary: £40.00 – £149.99
     *   mythic:    £150.00+
     */
    public const DEFAULT_THRESHOLDS = [
        'common'    => ['min' => 10,    'max' => 299],
        'rare'      => ['min' => 300,   'max' => 1099],
        'super'     => ['min' => 1100,  'max' => 3099],
        'legendary' => ['min' => 3100,  'max' => 5499],
        'mythic'    => ['min' => 5500,  'max' => PHP_INT_MAX],
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
