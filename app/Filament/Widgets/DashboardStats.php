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

    protected const BAND_COLORS = [
        'common'    => 'gray',
        'rare'      => 'info',
        'super'     => 'primary',
        'legendary' => 'warning',
        'mythic'    => 'danger',
    ];

    protected function getStats(): array
    {
        // Inventory
        $inStock = $this->applyGameFilter(
            CardInventory::query()->where('status', 'in_stock')
        );
        $inStockCount  = (clone $inStock)->count();
        $inStockMarket = (clone $inStock)->sum('market_value_pence');
        $inStockCost   = (clone $inStock)->sum('cost_pence');

        // Per-rarity-band breakdown of in-stock inventory (one grouped query).
        $bandStats = (clone $inStock)
            ->whereNotNull('rarity_band')
            ->selectRaw('rarity_band, COUNT(*) as card_count, SUM(market_value_pence) as market_pence, SUM(cost_pence) as cost_pence')
            ->groupBy('rarity_band')
            ->get()
            ->keyBy('rarity_band');

        $bandStatCards = [];
        foreach (self::BAND_COLORS as $band => $color) {
            $row = $bandStats->get($band);

            $bandStatCards[] = Stat::make(ucfirst($band), number_format($row->card_count ?? 0))
                ->description(sprintf(
                    'Market: £%s · Cost: £%s',
                    number_format(($row->market_pence ?? 0) / 100, 0),
                    number_format(($row->cost_pence ?? 0) / 100, 0),
                ))
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($color);
        }

        // Batches — applyGameFilter() needs the Builder, not an already-resolved
        // count, so ->count() must come after it (it used to come before, which
        // would fatal as soon as a game filter was actually selected).
        $liveBatches  = $this->applyGameFilter(
            Batch::query()->whereIn('status', ['committed', 'dispatched'])
        )->count();
        $draftBatches = $this->applyGameFilter(
            Batch::query()->where('status', 'draft')
        )->count();

        // Invoices
        $unpaidInvoices = Invoice::query()->whereIn('status', ['sent', 'overdue'])->count();
        $unpaidTotal    = Invoice::query()->whereIn('status', ['sent', 'overdue'])->sum('total_pence');

        // Sell-to-us submissions awaiting review
        $pendingSubmissions = CustomerSellSubmission::query()
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        // All cards regardless of status (in stock, allocated, dispatched, sold, ...).
        $totalCards = $this->applyGameFilter(CardInventory::query())->count();

        $cancelledBatches = Batch::query()->where('status', 'cancelled')->count();

        return [
            Stat::make('Cards in stock', number_format($inStockCount))
                ->description(sprintf(
                    'Market: £%s · Cost: £%s',
                    number_format($inStockMarket / 100, 0),
                    number_format($inStockCost / 100, 0),
                ))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),

            ...$bandStatCards,

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

            Stat::make('Total cards', number_format($totalCards))
                ->description('All statuses — in stock, allocated, sold, etc.')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('gray'),

            Stat::make('Cancelled batches', $cancelledBatches)
                ->description($cancelledBatches > 0 ? 'Requires review' : 'No failed batches')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($cancelledBatches > 0 ? 'danger' : 'success'),
        ];
    }
}
