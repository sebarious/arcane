<?php

namespace App\Services\Vision;

class CardNumberExtractor
{
    /**
     * Pull the most likely set number (e.g. "199/165", "SV49/SV122") out of a raw
     * OCR text blob. The number is printed at the very bottom of a physical card,
     * below everything else (flavor text, illustrator credit, etc) — Vision's text
     * detection roughly follows reading order, so of any candidate matches, the
     * *last* one in the blob is the best bet.
     *
     * This is a first pass — Vision commonly confuses 0/O and 1/I/l in small print,
     * which isn't corrected for here. Expect to refine this against real scans.
     */
    public static function extract(string $text): ?string
    {
        $pattern = '/\b([A-Z]{0,3}\d{1,3})\s*\/\s*([A-Z]{0,3}\d{1,3})\b/i';

        if (! preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $last = end($matches);

        return strtoupper($last[1]).'/'.strtoupper($last[2]);
    }
}
