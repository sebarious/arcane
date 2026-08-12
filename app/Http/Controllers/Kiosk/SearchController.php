<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\CardInventory;
use App\Services\Kiosk\KioskCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /** GET /kiosk/search — cards physically in stock (and not reserved elsewhere) matching the query, priced at kiosk markup. */
    public function __invoke(Request $request, KioskCheckoutService $checkout): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $like = '%'.strtolower($q).'%';

        $cards = CardInventory::query()
            ->available()
            ->where(function ($query) use ($like) {
                $query->whereRaw('LOWER(card_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(set_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(card_number) LIKE ?', [$like]);
            })
            ->orderBy('card_name')
            ->limit(30)
            ->get();

        return response()->json([
            'data' => $cards->map(fn (CardInventory $card) => $checkout->present($card))->values(),
        ]);
    }
}
