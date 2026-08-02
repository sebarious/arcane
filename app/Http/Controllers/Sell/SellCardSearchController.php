<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use App\Services\PulseApi\PulseApiCardMapper;
use App\Services\PulseApi\PulseApiClient;
use App\Services\Selling\SellOfferCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SellCardSearchController extends Controller
{
    public function __invoke(Request $request, PulseApiClient $client, SellOfferCalculator $calculator)
    {
        $validated = $request->validate([
            'q'      => ['nullable', 'string', 'max:100'],
            'number' => ['nullable', 'string', 'max:30'],
        ]);

        $q      = trim((string) ($validated['q'] ?? ''));
        $number = trim((string) ($validated['number'] ?? ''));

        if (mb_strlen($q) < 2 && $number === '') {
            return response()->json(['data' => []]);
        }

        // Sort by market price — PulseAPI's default (unsorted) order surfaces a flood of
        // priceless promo/rare-print variants first for popular multi-print cards (e.g. a
        // bare "charizard" search returns 0/20 priced results without this), which combined
        // with only showing eligible/banded cards below meant popular searches showed nothing.
        $filters = array_filter([
            'card_number' => $number !== '' ? $number : null,
            'sort_by'     => 'market_price',
            'sort_dir'    => 'desc',
        ]);
        $results = $client->search($q, PulseApiCardMapper::withDefaultFilters($filters), limit: 20);

        $cards = collect($results['data'] ?? [])
            ->reject(fn (array $card) => PulseApiCardMapper::isNonPokemon($card['set_id'] ?? null))
            ->reject(fn (array $card) => ! PulseApiCardMapper::isAllowedLanguage($card['language'] ?? null))
            ->map(function (array $card) use ($calculator) {
                $marketPounds = Arr::get($card, 'prices.market_price') ?? Arr::get($card, 'prices.market_price_global');
                $marketPence  = $marketPounds !== null ? (int) round($marketPounds * 100) : null;
                $quote        = $calculator->quote($marketPence);

                if (! $quote) {
                    return null;
                }

                return [
                    'product_id'         => $card['product_id'] ?? null,
                    'card_name'          => $card['card_name'] ?? 'Unknown',
                    'card_number'        => $card['card_number'] ?? null,
                    'set_name'           => $card['set_name'] ?? null,
                    'rarity'             => $card['rarity'] ?? null,
                    'image_url'          => $card['image_url'] ?? null,
                    'market_value_pence' => $marketPence,
                    'band'               => $quote['band'],
                    'unit_offer_pence'   => $quote['unit_offer_pence'],
                ];
            })
            ->filter()
            ->values();

        return response()->json(['data' => $cards]);
    }
}
