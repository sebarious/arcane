<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Inertia\Inertia;

class StoreIndexController extends Controller
{
    public function __invoke()
    {
        $stores = Store::query()
            ->where('public_page_enabled', true)
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'slug',
                'name',
                'city',
                'postcode',
            ]);

        return Inertia::render('Storefront/StoreIndex', [
            'stores' => $stores,
        ]);
    }
}
