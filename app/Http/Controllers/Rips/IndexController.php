<?php

namespace App\Http\Controllers\Rips;

use App\Enums\Game;
use App\Http\Controllers\Controller;
use App\Models\RipPack;
use App\Services\Rips\RipDrawer;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(RipDrawer $drawer)
    {
        $packs = RipPack::where('status', 'active')->orderBy('price_pence')->get();

        return Inertia::render('Rips/Index', [
            'walletBalancePence' => auth()->user()?->ripWallet?->credit_balance_pence,
            'packs' => $packs->map(fn (RipPack $pack) => [
                'slug' => $pack->slug,
                'name' => $pack->name,
                'description' => $pack->description,
                'image_path' => $pack->image_path,
                'price_pence' => $pack->price_pence,
                'games' => array_map(fn (string $g) => Game::from($g)->label(), $pack->games),
                'band_odds' => $pack->band_odds,
                'buy_back_percentage' => $pack->buy_back_percentage,
                // Live stock check (same one checkout relies on) so a pack
                // that currently can't be fulfilled shows as unavailable
                // rather than letting someone try to buy it.
                'in_stock' => $drawer->isFulfillable($pack),
            ])->values(),
        ]);
    }
}
