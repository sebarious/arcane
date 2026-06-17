<?php

namespace App\Http\Controllers\SellerApplication;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SellerApplicationThankYouController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('SellerApplications/ThankYou');
    }
}
