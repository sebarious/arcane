<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\Game;
use App\Http\Controllers\Controller;
use App\Models\Pack;
use App\Models\Store;
use Inertia\Inertia;

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

        $totalNumberOfPulls = $store->batches()
            ->whereIn('status', ['committed', 'dispatched', 'completed'])
            ->withCount(['packs as sold_packs_count' => function ($query) {
                $query->where('status', 'sold');
            }])
            ->get()
            ->sum('sold_packs_count');

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

        // Total packs remaining are the $batches pack_count - sold packs count
        $totalActivePacks = $batches->sum('pack_count');

        // Get pack count of sold packs for the batches
        $soldPackCount = Pack::whereIn('batch_id', $batches->pluck('id'))
            ->where('status', 'sold')
            ->count();

        return Inertia::render('Storefront/StoreShow', [
            'store'   => [
                'id'          => $store->id,
                'slug'        => $store->slug,
                'name'        => $store->name,
                'city'        => $store->city,
                'postcode'    => $store->postcode,
                'location'    => $store->location,
                'description' => $store->description,
                'platforms'   => $store->platforms,
                'logo'        => $store->logo,
                'social_links' => $store->social_links,
                'created_at'  => $store->created_at?->format('M Y'),
                'total_batches' => $store->batches()->count(),
                'total_packs_remaining' => $totalActivePacks - $soldPackCount,
                'total_pull_count' => $totalNumberOfPulls,
            ],
            'batches' => $batches->map(function ($batch) {
                return [
                    'id'        => $batch->id,
                    'reference' => $batch->reference,
                    'type'      => $batch->type?->value,
                    'game'       => $batch->game?->value,
                    'game_label' => $batch->game ? $batch->game->label() : null,
                    'created_at' => $batch->created_at?->format('M Y'),
                    'pack_count' => $batch->pack_count,
                    'remaining_packs' => $batch->pack_count - $batch->packs()->where('status', 'sold')->count(),
                ];
            })->values(),
            'recentPulls' => $recentPulls,
        ]);
    }
}
