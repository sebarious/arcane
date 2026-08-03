<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class TermsController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Legal/Terms', [
            'contactEmail' => config('mail.from.address'),
        ]);
    }
}
