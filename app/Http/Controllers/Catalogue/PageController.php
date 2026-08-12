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
}
