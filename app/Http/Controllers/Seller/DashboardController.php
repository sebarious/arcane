<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $stores = $user->stores()->get(['id', 'name', 'slug', 'city', 'credit_balance_pence']);
        $storeIds = $stores->pluck('id');

        $batches = Batch::query()
            ->whereIn('store_id', $storeIds)
            ->whereIn('status', ['committed', 'dispatched'])
            ->whereNull('merged_into_batch_id')
            ->orderByDesc('created_at')
            ->take(5)
            ->get([
                'id',
                'reference',
                'store_id',
                'type',
                'pack_count',
                'status',
                'created_at',
            ]);

        // Simple per-batch progress: packs sold vs total
        $progress = $batches->mapWithKeys(function (Batch $batch) {
            $sold = $batch->packs()->where('status', 'sold')->count();
            return [$batch->id => [
                'sold'   => $sold,
                'total'  => $batch->pack_count,
                'percent' => $batch->pack_count > 0
                    ? round($sold / $batch->pack_count * 100, 1)
                    : 0,
            ]];
        });

        $lifetimeBatches = Batch::query()
            ->whereIn('store_id', $storeIds)
            ->whereIn('status', ['committed', 'dispatched', 'completed']);

        $unpaidInvoices = Invoice::query()
            ->whereIn('store_id', $storeIds)
            ->whereIn('status', ['sent', 'overdue'])
            ->get(['total_pence', 'credit_applied_pence']);

        $stats = [
            'active_batches'         => (clone $lifetimeBatches)->whereIn('status', ['committed', 'dispatched'])->count(),
            'draft_requests'         => Batch::query()->whereIn('store_id', $storeIds)->where('status', 'draft')->count(),
            'lifetime_packs'         => (clone $lifetimeBatches)->sum('pack_count'),
            'lifetime_revenue_pence' => (clone $lifetimeBatches)->sum('sale_price_pence'),
            'wallet_balance_pence'   => $stores->sum('credit_balance_pence'),
            'unpaid_invoices_count'  => $unpaidInvoices->count(),
            'unpaid_invoices_pence'  => $unpaidInvoices->sum(fn (Invoice $invoice) => $invoice->amount_due_pence),
        ];

        $invoices = Invoice::query()
            ->whereIn('store_id', $storeIds)
            ->orderByDesc('issued_on')
            ->take(4)
            ->get(['id', 'number', 'store_id', 'total_pence', 'credit_applied_pence', 'status', 'issued_on', 'due_on'])
            ->map(fn (Invoice $invoice) => [
                'id'               => $invoice->id,
                'number'           => $invoice->number,
                'total_pence'      => $invoice->total_pence,
                'amount_due_pence' => $invoice->amount_due_pence,
                'status'           => $invoice->status,
                'issued_on'        => $invoice->issued_on?->toDateString(),
                'due_on'           => $invoice->due_on?->toDateString(),
            ]);

        return Inertia::render('Seller/Dashboard', [
            'stores'   => $stores->values(),
            'batches'  => $batches->values(),
            'progress' => $progress,
            'stats'    => $stats,
            'invoices' => $invoices,
        ]);
    }
}
