<?php

namespace App\Http\Controllers\Rips;

use App\Http\Controllers\Controller;
use App\Models\Rip;
use App\Services\Rips\RipCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DecisionController extends Controller
{
    /** POST /rips/my/{rip}/decide — keep, or sell back for the pack's configured %. */
    public function store(Request $request, Rip $rip, RipCheckoutService $checkout): JsonResponse
    {
        abort_unless($rip->order->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'decision' => ['required', 'in:kept,sold_back'],
        ]);

        try {
            $rip = $checkout->decide($rip, $data['decision']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'decision' => $rip->decision,
                'sold_back_pence' => $rip->sold_back_pence,
            ],
        ]);
    }
}
