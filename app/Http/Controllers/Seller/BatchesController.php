<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BatchesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $stores = $user->stores()->get(['id', 'name']);
        $batches = Batch::query()
            ->whereIn('store_id', $stores->pluck('id'))
            ->orderByDesc('created_at')
            ->paginate(15, [
                'id',
                'reference',
                'store_id',
                'type',
                'pack_count',
                'sale_price_pence',
                'total_cost_pence',
                'margin_pence',
                'status',
                'created_at',
            ]);
        return Inertia::render('Seller/BatchesIndex', [
            'batches'    => $batches,
            'storesById' => $stores->keyBy('id'),
        ]);
    }

    public function show(Request $request, Batch $batch)
    {
        $user = $request->user();

        if (! $user->stores()->where('id', $batch->store_id)->exists()) {
            abort(403);
        }

        $batch->load(['store', 'packs.card.card']);

        $packs = $batch->packs->map(function ($pack) {
            $inv  = $pack->card;
            $card = $inv?->card;

            return [
                'id'       => $pack->id,
                'sequence' => $pack->sequence_no,
                'status'   => $pack->status,
                'card'     => $card ? [
                    'name'   => $card->name,
                    'set'    => $card->set_name,
                    'number' => $card->card_number,
                    'image'  => $card->image_front,
                    'band'   => $inv?->rarity_band,
                ] : null,
            ];
        });

        return Inertia::render('Seller/BatchShow', [
            'batch' => [
                'id'        => $batch->id,
                'reference' => $batch->reference,
                'type'      => $batch->type?->value,
                'store'     => [
                    'id'   => $batch->store->id,
                    'name' => $batch->store->name,
                ],
                'pack_count'             => $batch->pack_count,
                'sale_price_pence'       => $batch->sale_price_pence,
                'total_cost_pence'       => $batch->total_cost_pence,
                'total_market_value_pence' => $batch->total_market_value_pence,
                'margin_pence'           => $batch->margin_pence,
                'status'                 => $batch->status,
            ],
            'packs' => $packs,
        ]);
    }
}
