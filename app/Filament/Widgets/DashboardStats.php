<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use App\Models\CardInventory;
use App\Models\CustomerSellSubmission;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Widgets\Concerns\HasGameFilter;

class DashboardStats extends BaseWidget
{
    use HasGameFilter;

    protected ?string $heading = 'Business at a glance';

    protected function getStats(): array
    {
        // Inventory
        $inStock = $this->applyGameFilter(
            CardInventory::query()->where('status', 'in_stock')
        );
        $inStockCount  = (clone $inStock)->count();
        $inStockMarket = (clone $inStock)->sum('market_value_pence');
        $inStockCost   = (clone $inStock)->sum('cost_pence');

        // Batches
        $liveBatches  = $this->applyGameFilter(
            Batch::query()->whereIn('status', ['committed', 'dispatched'])->count()
        );
        $draftBatches = $this->applyGameFilter(
            Batch::query()->where('status', 'draft')->count()
        );

        // Invoices
        $unpaidInvoices = Invoice::query()->whereIn('status', ['sent', 'overdue'])->count();
        $unpaidTotal    = Invoice::query()->whereIn('status', ['sent', 'overdue'])->sum('total_pence');

        // Sell-to-us submissions awaiting review
        $pendingSubmissions = CustomerSellSubmission::query()
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        return [
            Stat::make('Cards in stock', number_format($inStockCount))
                ->description(sprintf(
                    'Market: £%s · Cost: £%s',
                    number_format($inStockMarket / 100, 0),
                    number_format($inStockCost / 100, 0),
                ))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),

            Stat::make('Live batches', $liveBatches)
                ->description($draftBatches > 0 ? "{$draftBatches} draft awaiting generation" : 'No drafts pending')
                ->descriptionIcon('heroicon-m-squares-plus')
                ->color($draftBatches > 0 ? 'warning' : 'success'),

            Stat::make('Unpaid invoices', $unpaidInvoices)
                ->description('£' . number_format($unpaidTotal / 100, 2) . ' outstanding')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($unpaidInvoices > 0 ? 'warning' : 'success'),

            Stat::make('Sell submissions', $pendingSubmissions)
                ->description($pendingSubmissions > 0 ? 'Awaiting review' : 'Inbox clear')
                ->descriptionIcon('heroicon-m-envelope')
                ->color($pendingSubmissions > 0 ? 'warning' : 'success'),
        ];
    }
}
