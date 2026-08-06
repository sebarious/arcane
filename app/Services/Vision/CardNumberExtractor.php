<?php

namespace App\Services\Vision;

class CardNumberExtractor
{
    // The only slash-style numbering prefixes Pokémon TCG actually uses (Trainer
    // Gallery, Galarian Gallery, Shiny Vault, Radiant Collection) — checked first
    // so real prefixed numbers (e.g. "TG01/TG30") keep their prefix.
    private const KNOWN_PREFIXES = ['TG', 'GG', 'SV', 'RC'];

    // Promo-era cards (Scarlet & Violet Promos, Mega Evolutions Promos, Sword &
    // Shield Promos, ...) don't carry a numerator/denominator at all — just a
    // short prefix next to a bare number, either spaced ("MEP 028") or glued
    // ("SWSH235"). Values are the prefix PulseAPI's own card_number actually
    // uses, confirmed against existing card_inventory rows — "SVI" is a common
    // print/OCR read for what PulseAPI stores as "SVP".
    private const PROMO_PREFIXES = [
        'SWSHP' => 'SWSH',
        'SWSH'  => 'SWSH',
        'SVP'   => 'SVP',
        'SVI'   => 'SVP',
        'MEP'   => 'MEP',
        'SMP'   => 'SMP',
    ];

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

        if ($digits = self::extractDigitsOnly($text)) {
            return $digits;
        }

        return self::extractPromo($text);
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

    /**
     * Promo numbers have no slash at all, so this only runs once both slash-based
     * passes above have failed. PulseAPI stores these zero-padded to 3 digits
     * (e.g. "MEP022") regardless of how many digits are actually printed/read,
     * so this pads to match rather than passing through whatever OCR saw.
     *
     * `fullTextAnnotation.text` is the *whole card's* OCR text with a real
     * newline between every detected line (HP, attack costs, illustrator
     * credit, copyright, flavor text, ...) — an unrestricted whitespace gap
     * here would happily bridge a hallucinated short prefix on one line to an
     * unrelated digit run several lines away and misfire on ordinary cards.
     * Restricted to a single space/dash on the same line, or at most one line
     * break directly in between (Vision sometimes puts the prefix and the
     * number on their own consecutive lines rather than one), to keep this to
     * genuine "MEP 028" / "SWSH1234" / "MEP\n028"-style adjacency only — never
     * two tokens separated by an intervening line of unrelated card text.
     */
    private static function extractPromo(string $text): ?string
    {
        $prefixAlt = implode('|', array_keys(self::PROMO_PREFIXES));
        $pattern   = '/\b('.$prefixAlt.')[ \t]*-?[ \t]*\R?[ \t]*(\d{2,4})\b/i';

        if (! preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $last   = end($matches);
        $prefix = self::PROMO_PREFIXES[strtoupper($last[1])];

        return $prefix.str_pad($last[2], 3, '0', STR_PAD_LEFT);
    }
}
