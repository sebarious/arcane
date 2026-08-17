<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Store;
use Inertia\Inertia;

class BatchListController extends Controller
{
    public function __invoke(Store $store, Batch $batch)
    {
        // Ensure the batch belongs to the store
        if ($batch->store_id !== $store->id) {
            abort(404);
        }

        $packs = $batch->packs()->with('card')->get();

        // Live tab: what's still in the pool (sealed packs). This also drives the pull-odds
        // percentages above the grid — those must only ever reflect live stock, regardless of
        // which tab is being viewed, so they're computed from this grouping alone.
        $remaining = $packs
            ->filter(fn ($pack) => $pack->status === 'sealed')
            ->map(fn ($pack) => $pack->card)
            ->filter()
            ->groupBy('rarity_band');

        // Pulled tab: cards already sold — voided packs are neither live stock nor a genuine
        // pull, so they're excluded from both groupings, same as before this feature existed.
        $sold = $packs
            ->filter(fn ($pack) => $pack->status === 'sold')
            ->filter(fn ($pack) => $pack->card !== null)
            ->sortByDesc('sold_at')
            ->map(fn ($pack) => [$pack->card, $pack->sold_at])
            ->groupBy(fn ($pair) => $pair[0]->rarity_band);

        $mapCard = function ($inv, $soldAt = null) {
            return [
                'name' => $inv->card_name,
                'set' => $inv->set_name,
                'number' => $inv->card_number,
                'image' => $inv->image_url,
                'band' => $inv->rarity_band,
                'market_price' => $inv->market_value_pence ? round($inv->market_value_pence / 100, 2) : null,
                'product_badges' => $inv->product_badges,
                'sold_at' => $soldAt?->format('d M Y'),
            ];
        };

        $bands = [];
        $pulledBands = [];

        foreach (['mythic', 'legendary', 'super', 'rare', 'common'] as $band) {
            $liveCards = $remaining[$band] ?? collect();
            $bands[$band] = [
                'count' => $liveCards->count(),
                'cards' => $liveCards->map(fn ($inv) => $mapCard($inv))->values(),
            ];

            $soldPairs = $sold[$band] ?? collect();
            $pulledBands[$band] = [
                'count' => $soldPairs->count(),
                'cards' => $soldPairs->map(fn ($pair) => $mapCard($pair[0], $pair[1]))->values(),
            ];
        }

        return Inertia::render('Storefront/BatchListShow', [
            'store' => [
                'id' => $store->id,
                'slug' => $store->slug,
                'name' => $store->name,
                'city' => $store->city,
                'postcode' => $store->postcode,
            ],
            'batch' => [
                'id' => $batch->id,
                'reference' => $batch->reference,
                'type' => $batch->type?->value,
                'game' => $batch->game?->value,
                'game_label' => $batch->game ? $batch->game->label() : null,
                'created_at' => $batch->created_at?->format('d M Y'),
                'pack_count' => $batch->pack_count,
                'verification' => [
                    'id' => $batch->verification_hash,
                    'revealed' => $batch->isVerificationRevealed(),
                    'seed' => $batch->isVerificationRevealed() ? $batch->verification_seed : null,
                ],
            ],
            'bands' => $bands,
            'pulled_bands' => $pulledBands,
        ]);
    }
}
