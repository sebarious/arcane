<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\ApiSetting;
use Inertia\Inertia;

class ApiDocsController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Marketing/ApiDocs', [
            'rateLimitPerMinute' => ApiSetting::current()->rate_limit_per_minute,
        ]);
    }
}
