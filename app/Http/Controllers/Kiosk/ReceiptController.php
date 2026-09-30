<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Jobs\SendKioskReceiptJob;
use App\Models\KioskOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /** POST /kiosk/orders/{order}/receipt — emails the customer a receipt for the sale just completed. */
    public function store(Request $request, KioskOrder $order): JsonResponse
    {
        // Scoped to the sale this tablet just took, the same way cancelling is
        // scoped to its own in-flight order. Without it, any order id would be
        // a way to have a receipt posted to an address of your choosing.
        if ((int) $request->session()->get('kiosk_last_order_id') !== $order->id) {
            return response()->json(['message' => 'That sale isn\'t the one just completed on this kiosk.'], 403);
        }

        if ($order->status !== 'paid') {
            return response()->json(['message' => 'A receipt can only be sent for a completed sale.'], 422);
        }

        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        SendKioskReceiptJob::dispatch($order->id, $validated['email']);

        return response()->json(['data' => ['email' => $validated['email']]]);
    }
}
