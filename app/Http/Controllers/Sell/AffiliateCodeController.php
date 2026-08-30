<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Store;
use Illuminate\Http\Request;

class AffiliateCodeController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30'],
        ]);

        $code = strtoupper(trim($validated['code']));

        // A code belongs to exactly one of a Store or an independent Affiliate —
        // check the store first (existing behaviour), then fall back.
        $store = Store::query()
            ->where('affiliate_code', $code)
            ->where('status', 'active')
            ->first();

        $name = $store?->name;

        if (! $store) {
            $affiliate = Affiliate::query()
                ->where('affiliate_code', $code)
                ->where('status', 'active')
                ->with('user:id,name')
                ->first();

            $name = $affiliate?->user?->name;
        }

        if (! $name) {
            return response()->json([
                'valid'   => false,
                'message' => 'That affiliate code isn\'t recognised.',
            ]);
        }

        return response()->json([
            'valid'            => true,
            'name'             => $name,
            'bonus_percentage' => (float) config('selling.affiliate_bonus_percentage', 0.05),
        ]);
    }
}
