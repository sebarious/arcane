<?php

namespace App\Services\Vision;

class CardNumberExtractor
{
    // The only slash-style numbering prefixes Pokémon TCG actually uses (Trainer
    // Gallery, Galarian Gallery, Shiny Vault, Radiant Collection) — checked first
    // so real prefixed numbers (e.g. "TG01/TG30") keep their prefix.
    private const KNOWN_PREFIXES = ['TG', 'GG', 'SV', 'RC'];

    // Promo-era cards (Scarlet & Violet, Mega Evolutions, Sword & Shield, Sun &
    // Moon, XY, Black & White, Diamond & Pearl, HeartGold SoulSilver, ...) don't
    // carry a numerator/denominator at all — just a short prefix next to a bare
    // number, either spaced ("MEP 028") or glued ("SWSH235").
    //
    // Values are [PulseAPI's actual stored prefix, zero-pad width] — confirmed
    // live against PulseAPI, not assumed: padding is NOT uniform across eras.
    // SWSH/SVP/MEP pad to 3 digits (e.g. "SWSH041"); XY/BW/DP/HGSS pad to only 2
    // (e.g. "XY01", "DP07", "HGSS04" — "XY122" is left alone, already 3 digits);
    // SM (Sun & Moon) isn't padded at all (e.g. "SM48", "SM82"), and PulseAPI
    // stores it under "SM", not "SMP" — despite that being the more intuitive
    // guess (and this codebase's previous assumption). "SVI" is a common
    // print/OCR read for what PulseAPI stores as "SVP".
    private const PROMO_PREFIXES = [
        'SWSHP' => ['SWSH', 3],
        'SWSH' => ['SWSH', 3],
        'SVP' => ['SVP', 3],
        'SVI' => ['SVP', 3],
        'MEP' => ['MEP', 3],
        'SMP' => ['SM', 0],
        'SM' => ['SM', 0],
        'HGSS' => ['HGSS', 2],
        'XY' => ['XY', 2],
        'BW' => ['BW', 2],
        'DP' => ['DP', 2],
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
        $pattern = '/\b(('.$prefixAlt.')\d{1,4})\s*\/\s*(('.$prefixAlt.')\d{1,4})\b/i';

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
        $pattern = '/\b[A-Z]*(\d{1,4})\s*\/\s*[A-Z]*(\d{1,4})\b/i';

        if (! preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $last = end($matches);

        return $last[1].'/'.$last[2];
    }

    /**
     * Promo numbers have no slash at all, so this only runs once both slash-based
     * passes above have failed. PulseAPI zero-pads these to a fixed width per
     * era (see PROMO_PREFIXES) regardless of how many digits are actually
     * printed/read, so this pads to match rather than passing through whatever
     * OCR saw.
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
     *
     * Newer promo prints (confirmed on SVP) also glue/space a 2-letter language
     * code in between, e.g. "SVPEN212" or "SVP EN 212" — PulseAPI's card_number
     * for that card is plain "SVP212", no language code at all, so a stray
     * 2-letter run here is matched and discarded rather than folded into the
     * number. Digits can't be mistaken for it (`[A-Z]` only), so this can't
     * eat part of a genuine number.
     */
    private static function extractPromo(string $text): ?string
    {
        $prefixAlt = implode('|', array_keys(self::PROMO_PREFIXES));
        $pattern = '/\b('.$prefixAlt.')[ \t]*-?[ \t]*(?:[A-Z]{2}[ \t]*-?[ \t]*)?\R?[ \t]*(\d{2,4})\b/i';

        if (! preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $last = end($matches);
        [$prefix, $padWidth] = self::PROMO_PREFIXES[strtoupper($last[1])];

        $number = $padWidth > 0 ? str_pad($last[2], $padWidth, '0', STR_PAD_LEFT) : $last[2];

        return $prefix.$number;
    }
}
