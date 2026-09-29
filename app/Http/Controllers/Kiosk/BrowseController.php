<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\CardInventory;
use App\Services\Kiosk\KioskCheckoutService;
use App\Services\Kiosk\KioskStockQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrowseController extends Controller
{
    private const PER_PAGE = 24;

    /**
     * GET /kiosk/browse?letter=A&page=1 — paginated, for the A-Z picker's
     * infinite scroll.
     *
     * letter is optional so the same endpoint backs "show me everything in
     * this set/rarity", which has no initial letter to hang off.
     */
    public function __invoke(Request $request, KioskCheckoutService $checkout, KioskStockQuery $stock): JsonResponse
    {
        $validated = $request->validate([
            'letter' => ['nullable', 'string', 'size:1', 'alpha'],
            'page' => ['nullable', 'integer', 'min:1'],
            'set' => ['nullable', 'string', 'max:120'],
            'rarity' => ['nullable', 'string', 'max:20'],
        ]);

        $page = (int) ($validated['page'] ?? 1);

        $query = $stock->build([
            'letter' => $validated['letter'] ?? null,
            'set' => $validated['set'] ?? null,
            'rarity' => $validated['rarity'] ?? null,
        ])
            ->orderBy('card_name')
            ->orderBy('id');

        $total = (clone $query)->count();
        $cards = $query->forPage($page, self::PER_PAGE)->get();

        return response()->json([
            'data' => $cards->map(fn (CardInventory $card) => $checkout->present($card))->values(),
            'page' => $page,
            'has_more' => ($page * self::PER_PAGE) < $total,
            'total' => $total,
        ]);
    }
}
