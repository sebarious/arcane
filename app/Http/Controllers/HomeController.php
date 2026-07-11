<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pack;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $totalAvailableCards = Cache::remember('pulls.total-available', now()->addHour(), function () {
            return Pack::query()
                ->where('status', 'sealed')
                ->whereHas('batch', function ($query) {
                    $query->whereIn('status', ['committed', 'dispatched', 'completed']);
                })
                ->whereHas('card')
                ->count();
        });

        $recentPulls = Cache::remember('pulls.recent', now()->addHour(), function () {
            return Pack::query()
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
                ->values()
                ->toArray();
        });

        $whatsInThePool = Cache::remember('pulls.live-pool', now()->addHour(), function () {
            return whats_in_the_pool();
        });

        return Inertia::render('Welcome', [
            'recentPulls' => $recentPulls,
            'whatsInThePool' => $whatsInThePool,
            'totalAvailableCards' => $totalAvailableCards,
        ]);
    }
}
