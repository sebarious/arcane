<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Separate approval gate from api_enabled — a store can already list/read
 * batches once their integration is switched on, but marking cards sold stays
 * off until an admin has reviewed it (see StoreResource's "API access" section
 * and the API logs relation manager it sits next to).
 */
class EnsureMarkAsSoldEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Store $store */
        $store = $request->attributes->get('api_store');

        if (! $store->mark_as_sold_enabled) {
            return response()->json([
                'message' => 'The mark-as-sold endpoint has not been enabled for this store yet.',
            ], 403);
        }

        return $next($request);
    }
}
