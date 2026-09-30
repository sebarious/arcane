<?php

namespace App\Http\Controllers\Rips;

use App\Http\Controllers\Controller;
use App\Models\Rip;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyRipsController extends Controller
{
    /** GET /rips/my — every pack the logged-in customer has ever bought, newest first. */
    public function __invoke(Request $request)
    {
        $rips = Rip::query()
            ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id)->where('status', 'paid'))
            ->with('card')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Rip $rip) => [
                'id' => $rip->id,
                'pack_name' => $rip->pack_name,
                'price_pence' => $rip->price_pence,
                'opened' => $rip->isOpened(),
                'decision' => $rip->decision,
                'sold_back_pence' => $rip->sold_back_pence,
                'card' => $rip->isOpened() ? [
                    'name' => $rip->card?->card_name,
                    'image' => $rip->card?->image_url,
                    'band' => $rip->card?->rarity_band,
                ] : null,
                'created_at' => $rip->created_at->toIso8601String(),
            ]);

        return Inertia::render('Rips/My', [
            'rips' => $rips,
        ]);
    }
}
