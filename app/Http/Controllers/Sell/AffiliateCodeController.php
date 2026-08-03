<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
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

        $store = Store::query()
            ->where('affiliate_code', $code)
            ->where('status', 'active')
            ->first();

        if (! $store) {
            return response()->json([
                'valid'   => false,
                'message' => 'That affiliate code isn\'t recognised.',
            ]);
        }

        return response()->json([
            'valid'            => true,
            'store_name'       => $store->name,
            'bonus_percentage' => (float) config('selling.affiliate_bonus_percentage', 0.05),
        ]);
    }
}
