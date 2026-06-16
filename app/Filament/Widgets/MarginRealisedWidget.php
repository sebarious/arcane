<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasGameFilter;
use App\Models\CardInventory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MarginRealisedWidget extends BaseWidget
{
    use HasGameFilter;

    protected ?string $heading = 'Margin realised vs unrealised';

    protected function getStats(): array
    {
        // Realised: cards with status=sold, their per-card margin_pence
        $realisedQuery = CardInventory::query()
            ->where('status', 'sold')
            ->whereNotNull('margin_pence');

        // Unrealised: allocated/dispatched (in unsold packs)
        $unrealisedQuery = CardInventory::query()
            ->whereIn('status', ['allocated', 'dispatched'])
            ->whereNotNull('margin_pence');

        if ($this->gameFilter) {
            $realisedQuery->where('game', $this->gameFilter);
            $unrealisedQuery->where('game', $this->gameFilter);
        }

        $realised   = $realisedQuery->sum('margin_pence');
        $unrealised = $unrealisedQuery->sum('margin_pence');
        $total      = $realised + $unrealised;
        $percent    = $total > 0 ? round(($realised / $total) * 100, 1) : 0;

        return [
            Stat::make('Realised margin', '£' . number_format($realised / 100, 2))
                ->description("{$percent}% of total committed margin banked")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Unrealised margin', '£' . number_format($unrealised / 100, 2))
                ->description('Awaiting pack sales')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total committed margin', '£' . number_format($total / 100, 2))
                ->description('Realised + unrealised')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }
}
