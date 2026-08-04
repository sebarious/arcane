<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A seller's store only goes live once an admin has finished setting it up and
 * flipped Store::public_page_enabled — sellers can't do this themselves (see
 * Seller\ProfileController, which only edits content, not visibility). Until
 * then, the dashboard and its sub-pages redirect to a holding page instead —
 * a user can own several stores, so this only blocks if *none* of them are
 * live yet.
 */
class EnsureSellerStoreIsPublic
{
    public function handle(Request $request, Closure $next): Response
    {
        $hasLiveStore = $request->user()
            ->stores()
            ->where('public_page_enabled', true)
            ->where('status', 'active')
            ->exists();

        if (! $hasLiveStore) {
            return redirect()->route('seller.pending');
        }

        return $next($request);
    }
}
