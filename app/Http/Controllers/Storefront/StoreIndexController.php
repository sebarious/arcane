<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Inertia\Inertia;
use App\Enums\Game;
use App\Models\Batch;

class StoreIndexController extends Controller
{
    public function __invoke()
    {
        $stores = Store::query()
            ->where('public_page_enabled', true)
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'slug',
                'name',
                'city',
                'postcode',
            ]);
        // Get games per store by looking at batches
        $gamesByStore = Batch::query()
            ->select('store_id', 'game')
            ->whereIn('status', ['committed', 'dispatched'])
            ->whereIn('store_id', $stores->pluck('id'))
            ->get()
            ->groupBy('store_id')
            ->map(function ($rows) {
                return $rows->pluck('game')->unique()->values();
            });
        return Inertia::render('Storefront/StoreIndex', [
            'stores' => $stores->map(function (Store $store) use ($gamesByStore) {
                $gameValues = $gamesByStore[$store->id] ?? collect();

                return [
                    'id'       => $store->id,
                    'slug'     => $store->slug,
                    'name'     => $store->name,
                    'city'     => $store->city,
                    'postcode' => $store->postcode,
                    'games'    => $gameValues->map(
                        fn(Game $g) => [
                            'value' => $g->value,
                            'label' => $g->label(),
                        ]
                    )->values(),
                ];
            })->values(),
        ]);
    }
}
