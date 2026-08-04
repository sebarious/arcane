<?php

namespace App\Services\Vision;

class CardNumberExtractor
{
    // The only slash-style numbering prefixes Pokémon TCG actually uses (Trainer
    // Gallery, Galarian Gallery, Shiny Vault, Radiant Collection) — checked first
    // so real prefixed numbers (e.g. "TG01/TG30") keep their prefix.
    private const KNOWN_PREFIXES = ['TG', 'GG', 'SV', 'RC'];

    /**
     * Pull the most likely set number (e.g. "199/165", "TG01/TG30") out of a raw
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
        if ($prefixed = self::extractWithKnownPrefix($text)) {
            return $prefixed;
        }

        return self::extractDigitsOnly($text);
    }

    private static function extractWithKnownPrefix(string $text): ?string
    {
        $prefixAlt = implode('|', self::KNOWN_PREFIXES);
        $pattern   = '/\b(('.$prefixAlt.')\d{1,3})\s*\/\s*(('.$prefixAlt.')\d{1,3})\b/i';

        if (! preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $last = end($matches);

        return strtoupper($last[1]).'/'.strtoupper($last[3]);
    }

    /**
     * Falls back to the trailing digit run on each side of the slash, discarding
     * any letters in front of it — Vision often glues unrelated printed text
     * straight onto the number with no separating space (e.g. a nearby set code
     * turns "226/217" into "ASC226/217"), and since letters and digits are both
     * "word" characters, a boundary-anchored digits-only pattern can't split them
     * apart on its own. `[A-Z]*` here absorbs and discards whatever's glued on.
     */
    private static function extractDigitsOnly(string $text): ?string
    {
        $pattern = '/\b[A-Z]*(\d{1,3})\s*\/\s*[A-Z]*(\d{1,3})\b/i';

        if (! preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $last = end($matches);

        return $last[1].'/'.$last[2];
    }
}
