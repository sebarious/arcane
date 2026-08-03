<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class PrivacyPolicyController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Legal/PrivacyPolicy', [
            'contactEmail' => config('mail.from.address'),
        ]);
    }
}
