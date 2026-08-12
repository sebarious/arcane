<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /** GET /kiosk — the fullscreen kiosk page itself. No site nav/footer; search, basket, and checkout are all client-driven from here. */
    public function __invoke(): Response
    {
        return Inertia::render('Kiosk/Index');
    }
}
