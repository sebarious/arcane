<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasGameFilter;
use App\Models\Store;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class StorePerformanceWidget extends BaseWidget
{
    use HasGameFilter;

    protected static ?string $heading = 'Store performance (last 30 days)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $from = Carbon::now()->subDays(30);

        return $table
            ->query(function () use ($from) {
                // Subquery: packs sold per store in last 30 days
                $packsSold = \App\Models\Pack::query()
                    ->selectRaw('batches.store_id, COUNT(*) as packs_sold')
                    ->join('batches', 'batches.id', '=', 'packs.batch_id')
                    ->where('packs.status', 'sold')
                    ->where('packs.sold_at', '>=', $from)
                    ->when($this->gameFilter, fn($q) =>
                    $q->where('batches.game', $this->gameFilter))
                    ->groupBy('batches.store_id');

                return Store::query()
                    ->leftJoinSub($packsSold, 'sold_stats', 'sold_stats.store_id', '=', 'stores.id')
                    ->select('stores.*')
                    ->selectRaw('COALESCE(sold_stats.packs_sold, 0) as packs_sold_30d')
                    ->orderByDesc('packs_sold_30d');
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Store'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('packs_sold_30d')
                    ->label('Packs sold (30d)')
                    ->alignEnd()
                    ->sortable(),
            ])
            ->paginated([10]);
    }
}
