<?php

namespace App\Services\Vision;

class CardNameExtractor
{
    /**
     * Best-effort pull of a card's name from Vision's per-word bounding boxes.
     *
     * A card always prints its name in the very top band, but Vision's flattened
     * `fullTextAnnotation.text` string follows internal paragraph-clustering
     * order, not real vertical position — a stylised name plate can easily sort
     * *after* an ability/flavour-text paragraph in that string (this is why a
     * scan of a real card returned an ability's flavour text as the "name"
     * instead of the Pokémon printed above it). Grouping words by their actual
     * pixel Y-position into lines and taking the topmost one sidesteps that.
     *
     * @param  array  $annotations  Vision's `textAnnotations` array (index 0 is
     *         the aggregate full-text entry and is skipped; 1+ are per-word,
     *         each with a `boundingPoly.vertices` pixel box).
     */
    public static function extract(array $annotations): ?string
    {
        $words = self::wordsWithPosition($annotations);

        if ($words->isEmpty()) {
            return null;
        }

        foreach (self::linesTopToBottom($words) as $line) {
            $cleaned = self::clean($line);

            if ($cleaned !== '') {
                return $cleaned;
            }
        }

        return null;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{text: string, x: float, y: float, height: float}>
     */
    private static function wordsWithPosition(array $annotations): \Illuminate\Support\Collection
    {
        return collect($annotations)
            ->skip(1)
            ->map(function (array $annotation) {
                $vertices = $annotation['boundingPoly']['vertices'] ?? [];
                $xs = array_column($vertices, 'x');
                $ys = array_column($vertices, 'y');

                if (empty($xs) || empty($ys) || blank($annotation['description'] ?? null)) {
                    return null;
                }

                return [
                    'text'   => $annotation['description'],
                    'x'      => min($xs),
                    'y'      => array_sum($ys) / count($ys),
                    'height' => max($ys) - min($ys),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Groups words into lines by proximity of their vertical center, then
     * returns each line's text ordered top to bottom (words within a line
     * ordered left to right).
     *
     * @param  \Illuminate\Support\Collection  $words
     * @return array<int, string>
     */
    private static function linesTopToBottom(\Illuminate\Support\Collection $words): array
    {
        $sorted    = $words->sortBy('y')->values();
        $tolerance = ($sorted->avg('height') ?: 20) * 0.6;

        $lines   = [];
        $current = [];
        $lineY   = null;

        foreach ($sorted as $word) {
            if ($lineY !== null && abs($word['y'] - $lineY) > $tolerance) {
                $lines[]  = self::joinLeftToRight($current);
                $current  = [];
                $lineY    = null;
            }

            $current[] = $word;
            $lineY     = $lineY === null ? $word['y'] : ($lineY + $word['y']) / 2;
        }

        if ($current) {
            $lines[] = self::joinLeftToRight($current);
        }

        return $lines;
    }

    private static function joinLeftToRight(array $words): string
    {
        usort($words, fn (array $a, array $b) => $a['x'] <=> $b['x']);

        return implode(' ', array_column($words, 'text'));
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
