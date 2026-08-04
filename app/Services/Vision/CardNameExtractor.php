<?php

namespace App\Services\Vision;

class CardNameExtractor
{
    /**
     * Best-effort pull of a card's name from a scanned frame's OCR text — a
     * Pokémon card prints its name at the very top, above everything else, and
     * Vision's text roughly follows reading order, so the first line that looks
     * like a name (not just an HP value sharing that top band) is generally it.
     *
     * This only needs to be good enough to narrow a PulseAPI search alongside the
     * number, not exact — set numbers are commonly reused across many different
     * sets, so even a rough name cuts an otherwise long "choose variant" list
     * down to the real match most of the time.
     */
    public static function extract(string $text): ?string
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($text));

        foreach ($lines as $line) {
            $line = self::clean($line);

            if ($line !== '') {
                return $line;
            }
        }

        return null;
    }

    private static function clean(string $line): string
    {
        $line = trim($line);

        // Strip a trailing HP value that sometimes shares the name's line, e.g.
        // "Charizard ex 330 HP".
        $line = trim(preg_replace('/\s*\d{1,3}\s*HP\s*$/i', '', $line));

        // Reject a line that's just an HP value on its own ("330 HP", "HP 330")
        // or otherwise has no letters — not a name, just stat text on the same
        // top band, which Vision can return as a separate line before the name.
        if ($line === '' || ! preg_match('/[A-Za-z]{2,}/', $line)) {
            return '';
        }

        if (self::isCategoryLabel($line)) {
            return '';
        }

        // Guard against accidentally grabbing a long line (ability/flavor text)
        // if the real name line went undetected — card names are always short.
        return mb_strlen($line) > 40 ? '' : $line;
    }

    /**
     * Trainer cards print a small "Trainer - Supporter/Item/Stadium/Tool" (energy
     * cards similarly print "Basic {Type} Energy" as a category strip) right above
     * the actual name — reject an exact match against that strip rather than
     * mistaking it for the name itself.
     */
    private static function isCategoryLabel(string $line): bool
    {
        $normalized = strtolower(trim(preg_replace('/[\s\-–—]+/', ' ', $line)));

        return in_array($normalized, [
            'trainer', 'supporter', 'item', 'stadium', 'tool', 'pokemon tool', 'pokémon tool',
            'trainer supporter', 'trainer item', 'trainer stadium', 'trainer tool',
            'energy', 'basic energy', 'special energy',
        ], true);
    }
}
