<?php

namespace App\Services\Scrydex;

use App\Models\Card;
use App\Models\MarketPriceSnapshot;

class CardMapper
{
    /**
     * Upsert a card from a Scrydex payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function upsert(array $payload): Card
    {
        $expansionId   = $payload['expansion']['id']   ?? null;
        $expansionName = $payload['expansion']['name'] ?? '';
        $languageCode  = $payload['language_code']     ?? 'EN';

        // Scrydex provides 'images' as { small, large } — large is preferred.
        $imageFront = $payload['images']['large']
            ?? $payload['images']['small']
            ?? null;

        $card = Card::updateOrCreate(
            ['scrydex_id' => $payload['id']],
            [
                'name'           => $payload['name'] ?? '',
                'set_code'       => $expansionId ?? 'unknown',
                'set_name'       => $expansionName,
                'card_number'    => (string) ($payload['number'] ?? $payload['printed_number'] ?? ''),
                'variant'        => self::variantFrom($payload),
                'language'       => strtolower($languageCode),
                'language_code'  => strtoupper($languageCode),
                'printed_rarity' => $payload['rarity'] ?? null,
                'image_front'    => $imageFront,
                'external_ids'   => [
                    'scrydex_id' => $payload['id'] ?? null,
                ],
            ],
        );

        // If pricing came back in the same payload, snapshot it.
        if (isset($payload['prices'])) {
            self::snapshotPrices($card, $payload['prices']);
        }

        return $card;
    }

    protected static function variantFrom(array $payload): ?string
    {
        $rarity = $payload['rarity'] ?? null;
        if (! $rarity) return 'standard';

        return match (true) {
            str_contains($rarity, 'Special Illustration') => 'special-illustration',
            str_contains($rarity, 'Illustration')         => 'illustration',
            str_contains($rarity, 'Hyper')                => 'hyper',
            str_contains($rarity, 'Gold')                 => 'gold',
            default                                       => 'standard',
        };
    }

    /**
     * Persist whatever raw/graded prices Scrydex returned alongside the card.
     * Stored in pence with currency preserved on the snapshot.
     */
    protected static function snapshotPrices(Card $card, array $prices): void
    {
        // Scrydex returns an array of price entries, each with source/currency/values.
        // We snapshot each one — the price provider layer (Step 24) chooses which to use.
        foreach ($prices as $entry) {
            $median = $entry['market'] ?? $entry['median'] ?? $entry['low'] ?? null;
            if ($median === null) continue;

            MarketPriceSnapshot::create([
                'card_id'     => $card->id,
                'condition'   => 'NM',
                'source'      => $entry['source'] ?? 'scrydex',
                'currency'    => strtoupper($entry['currency'] ?? 'USD'),
                'median_pence'=> (int) round(((float) $median) * 100),
                'low_pence'   => isset($entry['low'])  ? (int) round(((float) $entry['low'])  * 100) : null,
                'high_pence'  => isset($entry['high']) ? (int) round(((float) $entry['high']) * 100) : null,
                'sample_size' => $entry['count'] ?? null,
                'raw_payload' => $entry,
                'fetched_at'  => now(),
            ]);
        }
    }
}
