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

        // Remaining cards in this batch (sealed packs)
        $remaining = $batch->packs()
            ->with('card.card')
            ->get()
            ->filter(fn($pack) => $pack->status === 'sealed')
            ->map(fn($pack) => $pack->card)
            ->filter()
            ->groupBy('rarity_band');

        $bands = [];

        foreach (['mythic', 'legendary', 'super', 'rare', 'common'] as $band) {
            $cards = $remaining[$band] ?? collect();
            $bands[$band] = [
                'count' => $cards->count(),
                'cards' => $cards->map(function ($inv) {
                    $card = $inv->card;
                    return [
                        'name'   => $card?->name,
                        'set'    => $card?->set_name,
                        'number' => $card?->card_number,
                        'image'  => $card?->image_front,
                        'band'   => $inv->rarity_band,
                        'market_price' => $inv->market_value_pence ? round($inv->market_value_pence / 100, 2) : null,
                    ];
                })->values(),
            ];
        }

        return Inertia::render('Storefront/BatchListShow', [
            'store' => [
                'id'       => $store->id,
                'slug'     => $store->slug,
                'name'     => $store->name,
                'city'     => $store->city,
                'postcode' => $store->postcode,
            ],
            'batch' => [
                'id'        => $batch->id,
                'reference' => $batch->reference,
                'type'      => $batch->type?->value,
                'game'      => $batch->game?->value,
                'game_label' => $batch->game ? $batch->game->label() : null,
                'created_at' => $batch->created_at?->toIso8601String(),
                'pack_count' => $batch->pack_count,
            ],
            'bands' => $bands,
        ]);
    }
}
