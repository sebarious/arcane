<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SubmissionCreateController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Sell/Create');
    }
}
