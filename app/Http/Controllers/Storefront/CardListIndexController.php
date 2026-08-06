<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Inertia\Inertia;

class CardListIndexController extends Controller
{
    public function __invoke()
    {
        // Same visibility rule as /stores — a batch only shows up here if the
        // store it belongs to is actually browsable on the public storefront.
        $batches = Batch::query()
            ->whereIn('status', ['committed', 'dispatched'])
            ->whereNull('merged_into_batch_id')
            ->whereHas('store', function ($query) {
                $query->where('public_page_enabled', true)->where('status', 'active');
            })
            ->with([
                'store:id,slug,name',
                'topCard1:id,image_url',
                'topCard2:id,image_url',
            ])
            ->withCount(['packs as sold_packs_count' => function ($query) {
                $query->where('status', 'sold');
            }])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Storefront/CardListIndex', [
            'batches' => $batches->map(fn (Batch $batch) => [
                'id'              => $batch->id,
                'reference'       => $batch->reference,
                'type'            => $batch->type?->value,
                'type_label'      => $batch->type?->label(),
                'game'            => $batch->game?->value,
                'game_label'      => $batch->game?->label(),
                'pack_count'      => $batch->pack_count,
                'remaining_packs' => $batch->pack_count - $batch->sold_packs_count,
                'store'           => [
                    'slug' => $batch->store->slug,
                    'name' => $batch->store->name,
                ],
                'top_card_1_image' => $batch->topCard1?->image_url,
                'top_card_2_image' => $batch->topCard2?->image_url,
            ])->values(),
        ]);
    }
}
