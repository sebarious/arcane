<?php

namespace App\Http\Controllers\Rips;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /** GET /rips/profile — shipping address, required before buying a pack (see RipCheckoutService). */
    public function show(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Rips/Profile', [
            'name' => $user->name,
            'email' => $user->email,
            'shipping' => [
                'shipping_name' => $user->shipping_name,
                'shipping_address_line_1' => $user->shipping_address_line_1,
                'shipping_address_line_2' => $user->shipping_address_line_2,
                'shipping_city' => $user->shipping_city,
                'shipping_postcode' => $user->shipping_postcode,
                'shipping_country' => $user->shipping_country ?? 'GB',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_address_line_1' => ['required', 'string', 'max:255'],
            'shipping_address_line_2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:255'],
            'shipping_postcode' => ['required', 'string', 'max:20'],
            'shipping_country' => ['required', 'string', 'size:2'],
        ]);

        $request->user()->update($data);

        return back()->with('success', 'Shipping address saved.');
    }
}
