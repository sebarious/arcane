<?php

namespace App\Services\Vision;

use Illuminate\Support\Collection;

class CardNameExtractor
{
    // Words that are never part of an actual card/trainer name, however close
    // Vision's bounding boxes place them to the real name — HP legitimately sits
    // right next to the name on the same row of a real card, and a stage/category
    // label ("Basic", "Stage 1") often sits close enough above it to get grouped
    // onto the same line too, so these have to be dropped per-word rather than
    // only rejecting a line that's *entirely* one of these.
    private const NOISE_WORDS = [
        'hp', 'basic', 'stage', 'trainer', 'supporter', 'item', 'stadium', 'tool', 'energy',
    ];

    /**
     * Best-effort pull of a card's name from Vision's per-word bounding boxes.
     *
     * A card always prints its name in the very top band, but Vision's flattened
     * `fullTextAnnotation.text` string follows internal paragraph-clustering
     * order, not real vertical position — a stylised name plate can easily sort
     * *after* an ability/flavour-text paragraph in that string. Grouping words by
     * their actual pixel Y-position into lines and taking the topmost one
     * sidesteps that.
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

        $candidates = collect(self::linesTopToBottom($words))
            ->map(fn (string $line) => self::clean($line))
            ->filter(fn (string $line) => $line !== '')
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        // Real card/trainer names are essentially never printed in solid capitals
        // — that's reserved for category banners and stray fragments picked up
        // near logos/watermarks. Prefer the first line that isn't, falling back
        // to whatever's left only if nothing else was found at all.
        return $candidates->first(fn (string $line) => ! self::isAllCaps($line))
            ?? $candidates->first();
    }

    /**
     * @return Collection<int, array{text: string, x: float, y: float, height: float}>
     */
    private static function wordsWithPosition(array $annotations): Collection
    {
        return collect($annotations)
            ->skip(1)
            ->map(function (array $annotation) {
                $vertices = $annotation['boundingPoly']['vertices'] ?? [];
                $xs = array_column($vertices, 'x');
                $ys = array_column($vertices, 'y');
                $text = trim($annotation['description'] ?? '');

                if (empty($xs) || empty($ys) || $text === '' || self::isNoiseWord($text)) {
                    return null;
                }

                return [
                    'text'   => $text,
                    'x'      => min($xs),
                    'y'      => array_sum($ys) / count($ys),
                    'height' => max($ys) - min($ys),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * A pure number (an HP value, a stage number) or one of NOISE_WORDS on its
     * own — never part of a real name, so dropped before lines are even formed
     * rather than relying on the joined line happening to look rejectable.
     */
    private static function isNoiseWord(string $word): bool
    {
        if (preg_match('/^\d+$/', $word)) {
            return true;
        }

        $normalized = strtolower(rtrim($word, '.,:'));

        return in_array($normalized, self::NOISE_WORDS, true);
    }

    /**
     * Groups words into lines by proximity of their vertical center, then
     * returns each line's text ordered top to bottom (words within a line
     * ordered left to right).
     *
     * @param  Collection  $words
     * @return array<int, string>
     */
    private static function linesTopToBottom(Collection $words): array
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

        if ($line === '' || ! preg_match('/[A-Za-z]{2,}/', $line)) {
            return '';
        }

        // Reject anything shorter than a real name could plausibly be ("PA", a
        // stray 2-letter fragment picked up near a logo/watermark) and guard
        // against accidentally grabbing a long line (ability/flavor text) if the
        // real name line went undetected — card names are always in between.
        $length = mb_strlen($line);

        return ($length < 3 || $length > 40) ? '' : $line;
    }

    private static function isAllCaps(string $line): bool
    {
        // clean() already guarantees at least 2 letters are present, so the
        // absence of any lowercase letter means it's genuinely all-caps.
        return ! preg_match('/[a-z]/', $line);
    }
}
