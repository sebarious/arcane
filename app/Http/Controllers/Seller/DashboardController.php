<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $stores = $user->stores()->get(['id', 'name', 'slug', 'city']);

        $batches = Batch::query()
            ->whereIn('store_id', $stores->pluck('id'))
            ->whereIn('status', ['committed', 'dispatched'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get([
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

        // Simple per-batch progress: packs sold vs total
        $progress = $batches->mapWithKeys(function (Batch $batch) {
            $sold = $batch->packs()->where('status', 'sold')->count();
            return [$batch->id => [
                'sold'   => $sold,
                'total'  => $batch->pack_count,
                'percent' => $batch->pack_count > 0
                    ? round($sold / $batch->pack_count * 100, 1)
                    : 0,
            ]];
        });

        return Inertia::render('Seller/Dashboard', [
            'stores'   => $stores->values(),
            'batches'  => $batches->values(),
            'progress' => $progress,
        ]);
    }
}
