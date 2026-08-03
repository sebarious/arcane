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
            ->with('batch:id,reference')
            ->orderByDesc('issued_on')
            ->paginate(20)
            ->through(fn (Invoice $invoice) => [
                'id'                   => $invoice->id,
                'number'               => $invoice->number,
                'batch_reference'      => $invoice->batch?->reference,
                'total_pence'          => $invoice->total_pence,
                'credit_applied_pence' => $invoice->credit_applied_pence,
                'amount_due_pence'     => $invoice->amount_due_pence,
                'status'               => $invoice->status,
                'issued_on'            => $invoice->issued_on?->toDateString(),
                'due_on'               => $invoice->due_on?->toDateString(),
            ]);

        return Inertia::render('Seller/InvoicesIndex', [
            'invoices' => $invoices,
        ]);
    }
}
