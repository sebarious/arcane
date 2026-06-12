<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('seller')) {
            abort(403);
        }

        $storeIds = $user->stores()->pluck('id');

        $invoices = Invoice::query()
            ->whereIn('store_id', $storeIds)
            ->orderByDesc('issued_on')
            ->paginate(20);

        return Inertia::render('Seller/InvoicesIndex', [
            'invoices' => $invoices,
        ]);
    }
}
