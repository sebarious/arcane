<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BatchListController extends Controller
{
    /** GET /api/v1/batches — references of every active batch belonging to the authenticated store. */
    public function index(Request $request): JsonResponse
    {
        /** @var Store $store */
        $store = $request->attributes->get('api_store');

        $batches = Batch::visibleToStoreApi($store)
            ->where('status', 'committed')
            ->whereNull('merged_into_batch_id')
            ->orderByDesc('committed_at')
            ->get(['reference', 'status']);

        return response()->json([
            'data' => $batches->map(fn (Batch $batch) => [
                'reference' => $batch->reference,
                'status' => $batch->status,
            ])->values(),
        ]);
    }
}
