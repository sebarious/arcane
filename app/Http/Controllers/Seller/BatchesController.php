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

        $status = $request->string('status')->toString();

        $batches = Batch::query()
            ->visibleToSeller()
            ->whereIn('store_id', $stores->pluck('id'))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15, [
                'id',
                'reference',
                'store_id',
                'type',
                'pack_count',
                'status',
                'merged_into_batch_id',
                'created_at',
            ])
            ->withQueryString()
            ->through(function (Batch $batch) {
                $sold = $batch->packs()->where('status', 'sold')->count();

                return [
                    'id' => $batch->id,
                    'reference' => $batch->reference,
                    'store_id' => $batch->store_id,
                    'type' => $batch->type?->value,
                    'pack_count' => $batch->pack_count,
                    'status' => $batch->status,
                    'is_merged' => ! is_null($batch->merged_into_batch_id),
                    'created_at' => $batch->created_at?->toDateString(),
                    'sold' => $sold,
                ];
            });

        return Inertia::render('Seller/BatchesIndex', [
            'batches' => $batches,
            'storesById' => $stores->keyBy('id'),
            'filters' => ['status' => $status !== '' ? $status : null],
        ]);
    }

    public function show(Request $request, Batch $batch)
    {
        $user = $request->user();

        if (! $user->stores()->where('id', $batch->store_id)->exists()) {
            abort(403);
        }

        // Same rule as index() — a direct link to a batch that's still mid
        // admin-review shouldn't work either, not just be absent from the list.
        if (! $batch->isVisibleToSeller()) {
            abort(404);
        }

        $batch->load(['store', 'packs.card', 'invoice', 'mergedInto', 'mergeRequestBatch']);

        $bands = [];
        foreach (['mythic', 'legendary', 'super', 'rare', 'common'] as $band) {
            $bandPacks = $batch->packs->filter(fn ($pack) => $pack->card?->rarity_band === $band);

            $bands[$band] = $bandPacks->map(function ($pack) {
                $inv = $pack->card;

                return [
                    'sequence' => $pack->sequence_no,
                    'status' => $pack->status,
                    'name' => $inv->card_name,
                    'set' => $inv->set_name,
                    'number' => $inv->card_number,
                    'image' => $inv->image_url,
                ];
            })->values();
        }

        $sold = $batch->packs->where('status', 'sold')->count();

        return Inertia::render('Seller/BatchShow', [
            'batch' => [
                'id' => $batch->id,
                'reference' => $batch->reference,
                'type' => $batch->type?->value,
                'store' => [
                    'id' => $batch->store->id,
                    'name' => $batch->store->name,
                ],
                'pack_count' => $batch->pack_count,
                'status' => $batch->status,
                'created_at' => $batch->created_at?->toDateString(),
                'sold' => $sold,
                'invoice' => $batch->invoice ? [
                    'id' => $batch->invoice->id,
                    'number' => $batch->invoice->number,
                ] : null,
                'merged_into' => $batch->mergedInto ? [
                    'id' => $batch->mergedInto->id,
                    'reference' => $batch->mergedInto->reference,
                ] : null,
                'merge_request_batch' => $batch->mergeRequestBatch ? [
                    'id' => $batch->mergeRequestBatch->id,
                    'reference' => $batch->mergeRequestBatch->reference,
                ] : null,
            ],
            'bands' => $bands,
        ]);
    }
}
