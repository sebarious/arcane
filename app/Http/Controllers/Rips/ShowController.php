<?php

namespace App\Http\Controllers\Rips;

use App\Enums\Game;
use App\Http\Controllers\Controller;
use App\Models\RipPack;
use App\Services\Rips\RipDrawer;
use Inertia\Inertia;

class ShowController extends Controller
{
    public function __invoke(RipPack $ripPack, RipDrawer $drawer)
    {
        abort_unless($ripPack->status === 'active', 404);

        return Inertia::render('Rips/Show', [
            'stripeKey' => config('services.stripe.key'),
            'walletBalancePence' => auth()->user()?->ripWallet?->credit_balance_pence,
            // Required before checkout (see RipCheckoutService) — null when
            // logged out, since that's decided by the auth modal first anyway.
            'hasShippingAddress' => auth()->check() ? auth()->user()->hasShippingAddress() : null,
            'pack' => [
                'id' => $ripPack->id,
                'slug' => $ripPack->slug,
                'name' => $ripPack->name,
                'description' => $ripPack->description,
                'image_path' => $ripPack->image_path,
                'price_pence' => $ripPack->price_pence,
                'games' => array_map(fn (string $g) => Game::from($g)->label(), $ripPack->games),
                'band_odds' => $ripPack->band_odds,
                'buy_back_percentage' => $ripPack->buy_back_percentage,
                // Disclosed before purchase: a pack that can (or can only)
                // contain graded slabs is a materially different product from
                // one drawing raw cards, and the buyer can't see which.
                'graded_policy' => $ripPack->graded_policy->value,
                'in_stock' => $drawer->isFulfillable($ripPack),
            ],
        ]);
    }
}
