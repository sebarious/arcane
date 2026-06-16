<?php

namespace App\Services\Scrydex;

use App\Models\Card;
use App\Models\MarketPriceSnapshot;
use Illuminate\Support\Facades\Storage;

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
        $imageFront = $payload['images'][0]['large']
            ?? $payload['images'][0]['small']
            ?? null;

        if ($imageFront) {
            // Skip if the image is already stored locally for this card.
            $imagePath = 'cards/' . $payload['id'] . '.jpg';
            if (!Storage::disk('local')->exists($imagePath)) {
                $imageContents = file_get_contents($imageFront);
                Storage::disk('local')->put($imagePath, $imageContents);
            }
        }

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
                'image_front'    => $imageFront ? $imagePath : null,
                'external_ids'   => [
                    'scrydex_id' => $payload['id'] ?? null,
                ],
            ],
        );

        // // If pricing came back in the same payload, snapshot it.
        // if (isset($payload['variants'][0]['prices'])) {
        //     self::snapshotPrices($card, $payload['variants'][0]['prices']);
        // }

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

            // For now lets only import NM
            if (isset($entry['condition']) && strtoupper(trim($entry['condition'])) !== 'NM') {
                continue;
            }

            // Let's convert USD to GBP if the source is Scrydex and the currency is USD.
            $median = usd_to_gbp($median);
            $low   = isset($entry['low'])  ? usd_to_gbp($entry['low'])  : null;
            $high  = isset($entry['high']) ? usd_to_gbp($entry['high']) : null;

            MarketPriceSnapshot::create([
                'card_id'     => $card->id,
                'condition'   => $entry['condition'] ?? 'NM',
                'source'      => $entry['source'] ?? 'scrydex',
                'currency'    => 'GBP',
                'median_pence'=> (int) round(((float) $median) * 100),
                'low_pence'   => isset($low)  ? (int) round(((float) $low)  * 100) : null,
                'high_pence'  => isset($high) ? (int) round(((float) $high) * 100) : null,
                'sample_size' => null,
                'raw_payload' => $entry,
                'fetched_at'  => now(),
            ]);
        }
    }
}
