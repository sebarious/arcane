<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Services\Kiosk\KioskStockQuery;
use Illuminate\Http\JsonResponse;

class FilterOptionsController extends Controller
{
    /** GET /kiosk/filters — the sets and rarities currently sitting in sellable stock. */
    public function __invoke(KioskStockQuery $stock): JsonResponse
    {
        return response()->json(['data' => $stock->filterOptions()]);
    }
}
