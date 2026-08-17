<?php

namespace App\Services\Intake;

use App\Enums\Game;
use App\Services\PulseApi\PulseApiCardMapper;

/**
 * Card row resolution logic shared between Rapid Intake's desktop Livewire page
 * and the phone remote-scanner controller — neither carries Livewire component
 * state, so callers pass buyPercentage explicitly rather than this reading it
 * off `$this->data['buy_percentage']`.
 */
class CardRowResolver
{
    public function emptyRow(): array
    {
        return [
            'product_id' => null,
            'card_name' => null,
            'search_number' => null,
            'resolved' => null,
            'candidates' => null,
            'market_value_pounds' => null,
            'cost_pounds' => null,
            'quantity' => 1,
        ];
    }

    /**
     * Apply a single, unambiguous PulseAPI match to a row: sets product_id/resolved,
     * pre-fills the market price, and auto-fills Cost (£) when it's still blank.
     * Clears any pending disambiguation candidates.
     */
    public function applyResolvedAttributes(array &$rows, int|string $key, array $attributes, float $buyPercentage, Game $game): void
    {
        $costPounds = $rows[$key]['cost_pounds'] ?? null;

        $rows[$key]['product_id'] = $attributes['product_id'];
        $rows[$key]['resolved'] = json_encode([...$attributes, 'game' => $game->value]);
        $rows[$key]['candidates'] = null;
        $rows[$key]['market_value_pounds'] = $attributes['market_value_pence'] !== null
            ? round($attributes['market_value_pence'] / 100, 2)
            : null;

        if (blank($costPounds) && $attributes['market_value_pence'] !== null) {
            $rows[$key]['cost_pounds'] = round(($attributes['market_value_pence'] * $buyPercentage / 100) / 100, 2);
        }
    }

    /**
     * Search a row's name/number and apply the result: a single match auto-resolves
     * (via applyResolvedAttributes), several matches are stashed as candidates for the
     * "Choose variant" action rather than silently guessing which print the row means
     * (Pokémon Center exclusives, stamped/staff variants, etc. can share a name+number),
     * and no matches leaves the row untouched for "Fill in manually".
     *
     * @param  string|null  $ocrSetCode  A set code read off the same scan (see
     *                                   SetCodeExtractor) — the card number alone is often shared by
     *                                   several different Pokémon across different sets, but a set code
     *                                   match narrows straight to the real one when exactly one candidate
     *                                   matches it, same as if only one had turned up in the first place.
     * @param  bool  $excludeRareVariants  Phone-scan-only (see PhoneScanController)
     *                                     — drops Play!/stamped/jumbo candidates before anything else runs, so
     *                                     they can't force an otherwise-ordinary card into "choose variant" and
     *                                     stall continuous scanning. Left off for the desktop flows, where
     *                                     going to "Fill in manually"/"Fetch card data" for one of these rare
     *                                     variants is a rare, deliberate, one-at-a-time action rather than
     *                                     something slowing down a batch.
     * @return 'resolved'|'ambiguous'|'not_found'
     */
    public function applySearchResolution(array &$rows, int|string $key, float $buyPercentage, Game $game, ?string $ocrSetCode = null, bool $excludeRareVariants = false): string
    {
        $row = $rows[$key];

        $candidates = PulseApiCardMapper::searchCandidates(
            (string) ($row['card_name'] ?? ''),
            (string) ($row['search_number'] ?? ''),
            $game,
        );

        if ($excludeRareVariants) {
            $candidates = array_values(array_filter(
                $candidates,
                fn (array $c) => ! PulseApiCardMapper::isRareVariant($c),
            ));
        }

        if (empty($candidates)) {
            return 'not_found';
        }

        if (count($candidates) === 1) {
            $this->applyResolvedAttributes($rows, $key, $candidates[0], $buyPercentage, $game);

            return 'resolved';
        }

        if ($ocrSetCode) {
            $matches = array_values(array_filter(
                $candidates,
                fn (array $c) => PulseApiCardMapper::setCodeFromImageUrl($c['image_url'] ?? null) === $ocrSetCode,
            ));

            if (count($matches) === 1) {
                $this->applyResolvedAttributes($rows, $key, $matches[0], $buyPercentage, $game);

                return 'resolved';
            }
        }

        $rows[$key]['candidates'] = json_encode($candidates);
        $rows[$key]['resolved'] = null;

        return 'ambiguous';
    }

    public static function decodeResolved(mixed $resolved): ?array
    {
        if (is_array($resolved)) {
            return $resolved;
        }
        if (is_string($resolved) && $resolved !== '') {
            return json_decode($resolved, true);
        }

        return null;
    }
}
