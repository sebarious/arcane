<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\CardInventory;
use App\Services\Kiosk\KioskCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrowseController extends Controller
{
    private const PER_PAGE = 24;

    /** GET /kiosk/browse?letter=A&page=1 — paginated, for the A-Z picker's infinite scroll. */
    public function __invoke(Request $request, KioskCheckoutService $checkout): JsonResponse
    {
        $validated = $request->validate([
            'letter' => ['required', 'string', 'size:1', 'alpha'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $letter = strtolower($validated['letter']);
        $page = (int) ($validated['page'] ?? 1);

        $query = CardInventory::query()
            ->available()
            ->whereRaw('LOWER(card_name) LIKE ?', [$letter.'%'])
            ->orderBy('card_name')
            ->orderBy('id');

        $total = (clone $query)->count();
        $cards = $query->forPage($page, self::PER_PAGE)->get();

        return response()->json([
            'data' => $cards->map(fn (CardInventory $card) => $checkout->present($card))->values(),
            'page' => $page,
            'has_more' => ($page * self::PER_PAGE) < $total,
        ]);
    }
}
