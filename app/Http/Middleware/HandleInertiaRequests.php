<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'route' => fn () => $request->route() ? [
                'name' => $request->route()->getName(),
                'params' => $request->route()->parameters(),
            ] : null,
            'auth' => [
                'user' => fn () => $request->user()?->only('id', 'name', 'email'),
            ],
            // Drives the impersonation banner shown on every Inertia page — see
            // App\Services\Auth\ImpersonationManager and Components/ImpersonationBanner.vue.
            'impersonating' => fn () => $request->session()->has('impersonator_id'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
            ],
            // Absolute URL fallback for og:image/twitter:image on pages that don't have
            // a more specific image (a store logo, a card image, etc) to point to.
            'defaultOgImage' => asset('images/pack.png'),
            // Persistent wallet balance for the seller area layout — summed across all
            // of a seller's stores, so it stays visible no matter which page they're on.
            'sellerWallet' => fn () => $request->user()?->hasRole('seller')
                ? (int) $request->user()->stores()->sum('credit_balance_pence')
                : null,
            // Affiliate code for the seller layout's header — same "1 store per
            // seller" assumption as the store() convenience relation.
            'sellerAffiliateCode' => fn () => $request->user()?->hasRole('seller')
                ? $request->user()->store?->affiliate_code
                : null,
            // Drives the "Request batch" button in the seller layout — shared globally
            // since that button appears on every seller page, not just BatchRequest's own.
            'batchRequestsEnabled' => (bool) config('batches.requests_enabled', true),
            // Whether the business is VAT-registered — while off, no VAT-related copy
            // ("ex VAT", etc) should appear anywhere seller/storefront-facing. Shared
            // globally since price displays touching this live on several pages.
            'vatRegistered' => (bool) config('vat.registered'),
        ];
    }
}
