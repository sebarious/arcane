<?php

namespace App\Filament\Widgets;

use App\Models\Pack;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use App\Filament\Widgets\Concerns\HasGameFilter;

class PacksSoldChart extends ChartWidget
{
    use HasGameFilter;

    protected ?string $heading = 'Packs sold (last 30 days)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $from = Carbon::now()->subDays(29)->startOfDay();
        $to   = Carbon::now()->endOfDay();

        $rows = Pack::query()
            ->where('status', 'sold')
            ->whereBetween('sold_at', [$from, $to])
            ->when($this->gameFilter, fn($q) => $q->whereHas(
                'batch',
                fn($b) => $b->where('game', $this->gameFilter)
            ))
            ->selectRaw("DATE(sold_at) as day, COUNT(*) as n")
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy(fn($r) => (string) $r->day);

        $labels = [];
        $counts = [];

        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $key = $d->toDateString();
            $labels[] = $d->format('d M');
            $counts[] = (int) ($rows[$key]?->n ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Packs sold',
                    'data'            => $counts,
                    'fill'            => true,
                    'backgroundColor' => 'rgba(167, 139, 250, 0.2)',
                    'borderColor'     => 'rgb(167, 139, 250)',
                    'tension'         => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
