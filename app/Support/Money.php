<?php

namespace App\Support;

class Money
{
    /** Convert pence to a £ string. £8.95 from 895. */
    public static function format(?int $pence, string $symbol = '£'): string
    {
        if ($pence === null) return '—';
        return $symbol . number_format($pence / 100, 2, '.', ',');
    }

    /** £8.95 -> 895 */
    public static function toPence(float|string $pounds): int
    {
        return (int) round(((float) $pounds) * 100);
    }

    /**
     * VAT margin scheme: VAT due is 1/6 of the margin (because margin is VAT-inclusive at 20%).
     */
    public static function marginSchemeVat(int $marginPence): int
    {
        return (int) round($marginPence / 6);
    }
}
