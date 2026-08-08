<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;

/**
 * A store's shareable "sell to us" link (/a/{slug}) — lands straight on the
 * Sell to Us page with their affiliate code prefilled and already validated,
 * so a customer doesn't have to know the code itself or type it in.
 */
class AffiliateLinkController extends Controller
{
    public function __invoke(Store $store): RedirectResponse
    {
        if (! $store->affiliate_code) {
            return redirect()->route('sell.create');
        }

        return redirect()->route('sell.create', ['affiliate' => $store->affiliate_code]);
    }
}
