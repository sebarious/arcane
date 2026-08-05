<?php

namespace App\Services\Vision;

class SetCodeExtractor
{
    // Print-language suffixes sometimes glued directly onto a set code right next
    // to the card number (e.g. "DRIEN" = set code "DRI" + language "EN").
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
     */
    public static function extract(string $text): ?string
    {
        if (! preg_match_all('/\b([A-Z]{2,8})\s*\d{1,3}\s*\/\s*\d{1,3}\b/', $text, $matches)) {
            return null;
        }

        $code = strtoupper(end($matches[1]));

        foreach (self::LANGUAGE_SUFFIXES as $lang) {
            if (strlen($code) > strlen($lang) && str_ends_with($code, $lang)) {
                return substr($code, 0, -strlen($lang));
            }
        }

        return $code;
    }
}
