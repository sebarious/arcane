<?php

namespace App\Http\Controllers\SellerApplication;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class CreateSellerApplicationController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('SellerApplications/Create');
    }
}
