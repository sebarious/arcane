<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\CardInventory;
use App\Services\Kiosk\KioskBasketService;
use App\Services\Kiosk\KioskCheckoutService;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Basket membership is Laravel session state (a plain array of card_inventory
 * ids under 'kiosk_basket'); the actual hold on each card is a real DB row
 * lock via KioskBasketService, keyed to this same session id.
 */
class BasketController extends Controller
{
    /** Enough for a counter sale's worth of odds and ends without the list running away. */
    private const MAX_CUSTOM_LINES = 20;

    public function index(Request $request, KioskBasketService $basket, KioskCheckoutService $checkout): JsonResponse
    {
        $ids = $this->sessionIds($request);
        $held = $basket->stillHeld($ids, $request->session()->getId());

        if (count($held) !== count($ids)) {
            $request->session()->put('kiosk_basket', $held);
        }

        return $this->basketResponse($request, $checkout, CardInventory::whereIn('id', $held)->get());
    }

    public function store(Request $request, KioskBasketService $basket, KioskCheckoutService $checkout): JsonResponse
    {
        $validated = $request->validate(['card_inventory_id' => ['required', 'integer']]);

        $card = $basket->reserve((int) $validated['card_inventory_id'], $request->session()->getId());

        if (! $card) {
            return response()->json(['message' => 'That card is no longer available.'], 409);
        }

        $ids = array_values(array_unique([...$this->sessionIds($request), $card->id]));
        $request->session()->put('kiosk_basket', $ids);

        return $this->basketResponse($request, $checkout, CardInventory::whereIn('id', $ids)->get());
    }

    public function destroy(Request $request, int $cardInventoryId, KioskBasketService $basket, KioskCheckoutService $checkout): JsonResponse
    {
        $basket->release($cardInventoryId, $request->session()->getId());

        $ids = array_values(array_diff($this->sessionIds($request), [$cardInventoryId]));
        $request->session()->put('kiosk_basket', $ids);

        return $this->basketResponse($request, $checkout, CardInventory::whereIn('id', $ids)->get());
    }

    /** DELETE /kiosk/basket — the "Clear basket" button: releases every hold this session has in one go. */
    public function clear(Request $request, KioskBasketService $basket, KioskCheckoutService $checkout): JsonResponse
    {
        $basket->releaseAllForSession($request->session()->getId());
        $request->session()->put('kiosk_basket', []);
        // Manual lines and any discount go with it — they belong to this sale,
        // not to the tablet, and the next customer must not inherit them.
        $request->session()->forget(['kiosk_custom_lines', 'kiosk_discount']);

        return $this->basketResponse($request, $checkout, collect());
    }

    /**
     * A manual line — anything with no inventory row behind it: a supplies
     * sale, a deposit, a price agreed at the counter.
     */
    public function storeCustom(Request $request, KioskCheckoutService $checkout): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:10000'],
        ]);

        $lines = $this->customLines($request);

        if (count($lines) >= self::MAX_CUSTOM_LINES) {
            return response()->json(['message' => 'That\'s as many manual items as one sale can take.'], 422);
        }

        $lines[] = [
            'id' => (string) Str::uuid(),
            'label' => $validated['label'],
            'price_pence' => Money::toPence($validated['amount']),
        ];

        $request->session()->put('kiosk_custom_lines', $lines);

        return $this->basketResponse($request, $checkout);
    }

    public function destroyCustom(Request $request, string $lineId, KioskCheckoutService $checkout): JsonResponse
    {
        $request->session()->put('kiosk_custom_lines', array_values(array_filter(
            $this->customLines($request),
            fn (array $line) => $line['id'] !== $lineId,
        )));

        return $this->basketResponse($request, $checkout);
    }

    public function setDiscount(Request $request, KioskCheckoutService $checkout): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:percent,fixed'],
            // Percent is capped at 100; a fixed amount is capped by the basket
            // itself at total time, so an over-large one just zeroes it out.
            'value' => ['required', 'numeric', 'min:0.01', $request->input('type') === 'percent' ? 'max:100' : 'max:10000'],
        ]);

        $request->session()->put('kiosk_discount', [
            'type' => $validated['type'],
            // Percent stays a percentage; a money discount is stored in pence,
            // so nothing downstream has to guess which unit it is.
            'value' => $validated['type'] === 'percent'
                ? (int) round((float) $validated['value'])
                : Money::toPence($validated['value']),
        ]);

        return $this->basketResponse($request, $checkout);
    }

    public function clearDiscount(Request $request, KioskCheckoutService $checkout): JsonResponse
    {
        $request->session()->forget('kiosk_discount');

        return $this->basketResponse($request, $checkout);
    }

    /** @return int[] */
    private function sessionIds(Request $request): array
    {
        return array_map('intval', (array) $request->session()->get('kiosk_basket', []));
    }

    /** @return array<int, array{id: string, label: string, price_pence: int}> */
    private function customLines(Request $request): array
    {
        return array_values((array) $request->session()->get('kiosk_custom_lines', []));
    }

    /** @return array{type: string, value: int}|null */
    private function discount(Request $request): ?array
    {
        $discount = $request->session()->get('kiosk_discount');

        return is_array($discount) ? $discount : null;
    }

    /**
     * Every basket response carries the manual lines and the worked-out
     * totals too, so the tablet never has to add anything up itself — the
     * figure on screen is the figure the reader will ask for.
     */
    private function basketResponse(Request $request, KioskCheckoutService $checkout, ?Collection $cards = null): JsonResponse
    {
        $cards ??= CardInventory::whereIn('id', $this->sessionIds($request))->get();

        return response()->json($checkout->summarise(
            $cards,
            $this->customLines($request),
            $this->discount($request),
        ));
    }
}
