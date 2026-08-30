<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * An affiliate can log in the moment they sign up, but their bank
 * details/withdrawals pages stay locked until an admin approves them (see
 * AffiliateResource::approveAction()) — mirrors EnsureSellerStoreIsPublic's
 * shape exactly. The dashboard root itself (Affiliate\DashboardController)
 * is deliberately NOT behind this gate — it's reachable in every status and
 * renders the right thing (pending/active/suspended) itself, same as
 * seller.pending is reachable outside store.live.
 */
class EnsureAffiliateIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $affiliate = $request->user()->affiliate;

        if (! $affiliate || $affiliate->status !== 'active') {
            return redirect()->route('affiliate.dashboard');
        }

        return $next($request);
    }
}
