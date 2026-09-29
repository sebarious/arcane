<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\CardInventory;
use App\Services\Kiosk\KioskCheckoutService;
use App\Services\Kiosk\KioskStockQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /** GET /kiosk/search — cards physically in stock (and not reserved elsewhere) matching the query, priced at kiosk markup. */
    public function __invoke(Request $request, KioskCheckoutService $checkout, KioskStockQuery $stock): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'set' => ['nullable', 'string', 'max:120'],
            'rarity' => ['nullable', 'string', 'max:20'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $cards = $stock->build([
            'search' => $q,
            'set' => $validated['set'] ?? null,
            'rarity' => $validated['rarity'] ?? null,
        ])
            ->orderBy('card_name')
            ->limit(30)
            ->get();

        return response()->json([
            'data' => $cards->map(fn (CardInventory $card) => $checkout->present($card))->values(),
        ]);
    }
}
