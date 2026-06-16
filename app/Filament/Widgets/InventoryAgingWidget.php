<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasGameFilter;
use App\Models\CardInventory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class InventoryAgingWidget extends ChartWidget
{
    use HasGameFilter;

    protected ?string $heading = 'Inventory aging (in stock)';

    protected ?string $description = 'How long have cards been sitting in your stock?';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $now = Carbon::now();

        $buckets = [
            '0–30 days'   => [$now->copy()->subDays(30), $now],
            '31–60 days'  => [$now->copy()->subDays(60), $now->copy()->subDays(31)],
            '61–90 days'  => [$now->copy()->subDays(90), $now->copy()->subDays(61)],
            '90+ days'    => [Carbon::create(2000, 1, 1), $now->copy()->subDays(91)],
        ];

        $labels = array_keys($buckets);
        $counts = [];
        $values = [];

        foreach ($buckets as [$from, $to]) {
            $q = CardInventory::query()
                ->where('status', 'in_stock')
                ->whereBetween('acquired_at', [$from->toDateString(), $to->toDateString()]);

            if ($this->gameFilter) {
                $q->where('game', $this->gameFilter);
            }

            $counts[] = (clone $q)->count();
            $values[] = round((clone $q)->sum('market_value_pence') / 100, 2);
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Card count',
                    'data'            => $counts,
                    'backgroundColor' => 'rgba(96, 165, 250, 0.6)',
                    'borderColor'     => 'rgb(96, 165, 250)',
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Market value (£)',
                    'data'            => $values,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.6)',
                    'borderColor'     => 'rgb(245, 158, 11)',
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y'  => ['type' => 'linear', 'position' => 'left', 'title' => ['display' => true, 'text' => 'Cards']],
                'y1' => ['type' => 'linear', 'position' => 'right', 'title' => ['display' => true, 'text' => '£'], 'grid' => ['drawOnChartArea' => false]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
