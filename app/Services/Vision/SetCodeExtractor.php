<?php

namespace App\Services\Vision;

class SetCodeExtractor
{
    // Print-language suffixes sometimes glued directly onto a set code right next
    // to the card number (e.g. "DRIEN" = set code "DRI" + language "EN"), and
    // sometimes printed as their own space-separated word instead ("CRI EN").
    private const LANGUAGE_SUFFIXES = ['EN', 'JP', 'DE', 'FR', 'IT', 'ES', 'PT', 'NL', 'KO', 'ZH'];

    /**
     * Modern cards often print a short set code right next to (or glued onto)
     * the collector number — e.g. "DRI 186/182", or with no space at all,
     * "ASC226/217" (this is the exact same text CardNumberExtractor's
     * digits-only fallback strips off the number as noise; here it's the
     * signal). This lines up with PulseAPI's image-folder naming (see
     * PulseApiCardMapper::setCodeFromImageUrl), which — unlike their own
     * internal set_id — matches what's actually printed on the card, so when
     * both are present it can narrow a same-number match straight to the
     * right set without a human choosing from a list.
     *
     * The set code, an optional leading rotation/format marker, and an
     * optional language suffix can all show up as one glued token ("PFLEN",
     * "HPFLEN"), or as separate space-separated words on the same line
     * ("CRI EN", "J CRIEN") — this captures every same-line letter-run
     * immediately touching the number so it doesn't stop short at just the
     * last word. Restricted to `[ \t]` (not `\s`) between words within that
     * run, so it can't bridge across a newline onto an unrelated word from a
     * line further up (e.g. an illustrator credit) — but a *single* line
     * break is allowed right before the number itself, since Vision
     * sometimes puts the code and the number on their own consecutive lines
     * ("PFLEN\n103/094") rather than one line.
     */
    public static function extract(string $text): ?string
    {
        if (! preg_match_all('/\b((?:[A-Z]{1,8}[ \t]*){1,3})[ \t]*\R?[ \t]*\d{1,4}[ \t]*\/[ \t]*\d{1,4}\b/i', $text, $matches)) {
            return null;
        }

        $code = strtoupper(preg_replace('/\s+/', '', end($matches[1])));

        foreach (self::LANGUAGE_SUFFIXES as $lang) {
            if (strlen($code) > strlen($lang) && str_ends_with($code, $lang)) {
                $code = substr($code, 0, -strlen($lang));
                break;
            }
        }

        // Every core set code seen from real scans (ASC, DRI, PFL, CRI, ...) is
        // exactly 3 letters — a rotation/format marker occasionally glued in
        // front by print or OCR ("J CRIEN" -> "JCRI", "HPFLEN" -> "HPFL") can be
        // any letter, so rather than maintaining a growing allowlist, anything
        // still longer than 3 characters at this point is trimmed down to the
        // trailing 3 — the actual code always sits at the end of the run.
        return strlen($code) > 3 ? substr($code, -3) : $code;
    }
}
