<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\Invoicing\InvoicePdfBuilder;
use Illuminate\Http\Request;

class InvoicePdfController extends Controller
{
    public function __invoke(Request $request, Invoice $invoice, InvoicePdfBuilder $pdfBuilder)
    {
        $user = $request->user();
        if (! $user) abort(403);

        // Admins can view any; sellers only their own store invoices
        if (! $user->hasRole('admin')) {
            if (! $user->stores()->where('id', $invoice->store_id)->exists()) {
                abort(403);
            }
        }

        return $pdfBuilder->build($invoice)->download("{$invoice->number}.pdf");
    }
}
