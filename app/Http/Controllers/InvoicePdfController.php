<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoicePdfController extends Controller
{
    public function __invoke(Request $request, Invoice $invoice)
    {
        $user = $request->user();
        if (! $user) abort(403);

        // Admins can view any; sellers only their own store invoices
        if (! $user->hasRole('admin')) {
            if (! $user->stores()->where('id', $invoice->store_id)->exists()) {
                abort(403);
            }
        }

        $invoice->load(['store', 'batch']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ])->setPaper('a4', 'portrait');

        $filename = "{$invoice->number}.pdf";

        return $pdf->download($filename);
    }
}
