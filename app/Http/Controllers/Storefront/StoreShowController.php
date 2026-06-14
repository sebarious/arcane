<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Inertia\Inertia;
use App\Enums\Game;

class StoreShowController extends Controller
{
    public function __invoke(Store $store)
    {
        if (! $store->public_page_enabled || $store->status !== 'active') {
            abort(404);
        }

        // Batches for this store that are live
        $batches = $store->batches()
            ->whereIn('status', ['committed', 'dispatched'])
            ->orderByDesc('created_at')
            ->get(['id', 'reference', 'type', 'game', 'pack_count', 'created_at']);

        // Recent pulls (sold cards)
        $recentPulls = $store->batches()
            ->whereIn('status', ['committed', 'dispatched', 'completed'])
            ->with(['packs.card.card'])
            ->get()
            ->flatMap(fn($batch) => $batch->packs)
            ->filter(fn($pack) => $pack->status === 'sold' && $pack->card)
            ->sortByDesc(fn($pack) => $pack->sold_at ?? $pack->card->delisted_at ?? $pack->updated_at)
            ->take(20)
            ->map(function ($pack) {
                $inv  = $pack->card;
                $card = $inv?->card;
                return [
                    'id'       => $pack->id,
                    'sequence' => $pack->sequence_no,
                    'sold_at'  => $pack->sold_at?->toIso8601String() ?? $inv?->delisted_at?->toIso8601String(),
                    'batch'    => [
                        'id'        => $pack->batch_id,
                        'reference' => $pack->batch?->reference,
                    ],
                    'card' => $card ? [
                        'name'   => $card->name,
                        'set'    => $card->set_name,
                        'number' => $card->card_number,
                        'image'  => $card->image_front,
                        'band'   => $inv?->rarity_band,
                    ] : null,
                ];
            })
            ->values();

        return Inertia::render('Storefront/StoreShow', [
            'store'   => [
                'id'       => $store->id,
                'slug'     => $store->slug,
                'name'     => $store->name,
                'city'     => $store->city,
                'postcode' => $store->postcode,
            ],
            'batches' => $batches->map(function ($batch) {
                return [
                    'id'        => $batch->id,
                    'reference' => $batch->reference,
                    'type'      => $batch->type?->value,
                    'game'       => $batch->game?->value,
                    'game_label' => $batch->game ? $batch->game->label() : null,
                    'created_at' => $batch->created_at?->toIso8601String(),
                    'pack_count' => $batch->pack_count,
                ];
            })->values(),
            'recentPulls' => $recentPulls,
        ]);
    }
}
