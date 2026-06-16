<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use Filament\Widgets\ChartWidget;
use App\Filament\Widgets\Concerns\HasGameFilter;

class MarginByProduct extends ChartWidget
{
    use HasGameFilter;

    protected ?string $heading = 'Average margin % by product (committed batches)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $rows = $this->applyGameFilter(Batch::query()
            ->whereIn('status', ['committed', 'dispatched', 'completed'])
            ->where('total_cost_pence', '>', 0)
            ->get(['type', 'sale_price_pence', 'total_cost_pence', 'total_market_value_pence', 'margin_pence']));

        $groups = $rows->groupBy(fn($b) => $b->type?->value ?? 'unknown');

        $labels       = [];
        $marginCost   = [];
        $marginMarket = [];

        foreach (['sapphire', 'ruby', 'diamond'] as $type) {
            $batches = $groups->get($type, collect());

            if ($batches->isEmpty()) {
                $labels[]       = ucfirst($type);
                $marginCost[]   = 0;
                $marginMarket[] = 0;
                continue;
            }

            $avgMarginCost = $batches->avg(
                fn($b) => $b->total_cost_pence > 0 ? $b->margin_pence / $b->total_cost_pence : 0
            ) * 100;

            $avgMarginMarket = $batches->avg(
                fn($b) => $b->total_market_value_pence > 0
                    ? ($b->sale_price_pence - $b->total_market_value_pence) / $b->total_market_value_pence
                    : 0
            ) * 100;

            $labels[]       = ucfirst($type);
            $marginCost[]   = round($avgMarginCost, 1);
            $marginMarket[] = round($avgMarginMarket, 1);
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Margin vs cost (%)',
                    'data'            => $marginCost,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.6)',
                    'borderColor'     => 'rgb(34, 197, 94)',
                ],
                [
                    'label'           => 'Margin vs market (%)',
                    'data'            => $marginMarket,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.6)',
                    'borderColor'     => 'rgb(245, 158, 11)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
