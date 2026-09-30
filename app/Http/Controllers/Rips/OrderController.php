<?php

namespace App\Http\Controllers\Rips;

use App\Http\Controllers\Controller;
use App\Models\Rip;
use App\Models\RipOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * GET /rips/orders/{ripOrder} — every pack from one order shown together
     * in a row, so a multi-pack purchase can be opened one at a time (and
     * decided on) without leaving the page. A single-pack order still lands
     * here fine, but Show.vue only ever routes here when there's more than
     * one — a lone pack goes straight to the immersive /rips/my/{rip} page.
     */
    public function show(Request $request, RipOrder $ripOrder)
    {
        abort_unless($ripOrder->user_id === $request->user()->id, 403);
        abort_unless($ripOrder->status === 'paid', 404);

        $rips = $ripOrder->rips()
            ->with('card')
            ->orderBy('id')
            ->get()
            ->map(fn (Rip $rip) => $rip->toStackArray());

        return Inertia::render('Rips/Order', [
            'title' => "Order {$ripOrder->reference}",
            'rips' => $rips,
        ]);
    }
}
