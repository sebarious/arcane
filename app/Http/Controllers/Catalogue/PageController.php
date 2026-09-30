<?php

namespace App\Http\Controllers\Catalogue;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /** GET /catalogue — public, read-only stock browsing (search + A-Z) for customers on their own phones. No basket/checkout; reuses the kiosk's search/browse endpoints, which are already read-only. */
    public function __invoke(): Response
    {
        return Inertia::render('Catalogue/Index');
    }

    /**
     * GET /catalogue/kiosk — the same stock, laid out for a tablet standing
     * in the shop: full height, no site header or footer, bigger touch
     * targets. Its own page rather than a mode of the one above, which stays
     * the public page customers open on their own phones.
     *
     * Look-only, exactly like /catalogue — taking money is the till at
     * /kiosk, which is PIN-gated. Nothing here needs gating: it shows the
     * same stock list anyone can already see on the website.
     */
    public function kiosk(): Response
    {
        return Inertia::render('Catalogue/Kiosk');
    }
}
