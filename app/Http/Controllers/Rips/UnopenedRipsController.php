<?php

namespace App\Http\Controllers\Rips;

use App\Http\Controllers\Controller;
use App\Models\Rip;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnopenedRipsController extends Controller
{
    /**
     * GET /rips/my/unopened — every unopened pack the customer has, across
     * every order, shown in the same stacked popup as a fresh multi-pack
     * purchase (see Rips/Order.vue) — My.vue routes here instead of to a
     * single /rips/my/{rip} whenever there's more than one to get through.
     */
    public function show(Request $request)
    {
        $rips = Rip::query()
            ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id)->where('status', 'paid'))
            ->whereNull('opened_at')
            ->with('card')
            ->orderBy('created_at')
            ->get()
            ->map(fn (Rip $rip) => $rip->toStackArray());

        return Inertia::render('Rips/Order', [
            'title' => 'Unopened packs',
            'rips' => $rips,
        ]);
    }
}
