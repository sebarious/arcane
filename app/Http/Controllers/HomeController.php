<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pack;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $recentPulls = Pack::query()
            ->where('status', 'sold')
            ->whereHas('batch', function ($query) {
                $query->whereIn('status', ['committed', 'dispatched', 'completed']);
            })
            ->whereHas('card')
            ->with([
                'batch.store',
                'card.card',
            ])
            ->get()
            ->sortByDesc(
                fn($pack) =>
                $pack->sold_at
                    ?? $pack->card->delisted_at
                    ?? $pack->updated_at
            )
            ->take(10)
            ->map(function ($pack) {
                $inv  = $pack->card;
                $card = $inv?->card;

                return [
                    'id'       => $pack->id,
                    'sequence' => $pack->sequence_no,
                    'sold_at'  => $pack->sold_at?->toIso8601String()
                        ?? $inv?->delisted_at?->toIso8601String(),

                    'store' => [
                        'id'   => $pack->batch?->store?->id,
                        'name' => $pack->batch?->store?->name,
                    ],

                    'batch' => [
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

        $newStores = \App\Models\Store::query()
            ->where('status', 'active')
            ->where('public_page_enabled', true)
            ->orderByDesc('created_at')
            ->take(3)
            ->get()
            ->map(fn($store) => [
                'id'   => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'logo' => $store->logo,
            ]);

        return Inertia::render('Welcome', [
            'recentPulls' => $recentPulls,
            'newStores'   => $newStores,
        ]);
    }
}
