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
     */
    public const DEFAULT_THRESHOLDS = [
        'common'    => ['min' => 1,    'max' => 499],
        'rare'      => ['min' => 500,   'max' => 1049],
        'super'     => ['min' => 1050,  'max' => 4999],
        'legendary' => ['min' => 5000,  'max' => 14999],
        'mythic'    => ['min' => 15000,  'max' => 34999],
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
