<?php

namespace App\Services\Verification;

/**
 * Deterministic, portable randomness for provably-fair batch generation. Plain
 * SHA-256 (not HMAC) so anyone can reproduce a draw with nothing more than a
 * hash calculator and the published seed — no PHP-specific behaviour to trust.
 */
class SeededRandom
{
    private int $counter = 0;

    public function __construct(private readonly string $seed) {}

    /** Deterministic float in [0, 1). */
    public function nextFloat(): float
    {
        $hash = hash('sha256', $this->seed.':'.$this->counter++);

        return hexdec(substr($hash, 0, 8)) / 0xFFFFFFFF;
    }

    /** Fisher-Yates shuffle, deterministic given the seed and draw order. */
    public function shuffle(array $items): array
    {
        $items = array_values($items);

        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = (int) floor($this->nextFloat() * ($i + 1));
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return $items;
    }
}
