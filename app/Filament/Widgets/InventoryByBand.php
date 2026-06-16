<?php

namespace App\Filament\Widgets;

use App\Models\CardInventory;
use Filament\Widgets\ChartWidget;
use App\Filament\Widgets\Concerns\HasGameFilter;

class InventoryByBand extends ChartWidget
{
    use HasGameFilter;

    protected ?string $heading = 'Inventory value by rarity';

    protected ?string $description = 'Sum of market value of in-stock cards per band';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $bands = ['common', 'rare', 'super', 'legendary', 'mythic'];

        $data = $this->applyGameFilter(CardInventory::query()
            ->where('status', 'in_stock')
            ->selectRaw('rarity_band,
                         COUNT(*) as count,
                         SUM(market_value_pence) as market,
                         SUM(cost_pence) as cost')
            ->groupBy('rarity_band')
            ->get())
            ->keyBy('rarity_band');

        $labels = array_map('ucfirst', $bands);

        $marketValues = [];
        $costValues   = [];

        foreach ($bands as $band) {
            $marketValues[] = round(($data[$band]?->market ?? 0) / 100, 2);
            $costValues[]   = round(($data[$band]?->cost ?? 0) / 100, 2);
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Market value (£)',
                    'data'            => $marketValues,
                    'backgroundColor' => 'rgba(167, 139, 250, 0.6)',
                    'borderColor'     => 'rgb(167, 139, 250)',
                ],
                [
                    'label'           => 'Cost (£)',
                    'data'            => $costValues,
                    'backgroundColor' => 'rgba(34, 211, 238, 0.6)',
                    'borderColor'     => 'rgb(34, 211, 238)',
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
