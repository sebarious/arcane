<?php

namespace App\Filament\Resources\CardInventories\Pages;

use App\Filament\Exports\SoldCardExporter;
use App\Filament\Resources\CardInventories\CardInventoryResource;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListCardInventories extends ListRecords
{
    protected static string $resource = CardInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Rapid Intake is the only way cards enter inventory — no "Add single card".
            Action::make('rapidIntake')
                ->label('Rapid intake')
                ->icon(Heroicon::OutlinedBolt)
                ->url(CardInventoryResource::getUrl('rapid'))
                ->color('primary'),
            // Exports everything matching the current table filters/search, further
            // narrowed by the date range picked here — e.g. Status = Sold on the table
            // filter, plus a quarter's start/end date right in the export modal.
            ExportAction::make()
                ->label('Export')
                ->exporter(SoldCardExporter::class)
                ->modifyQueryUsing(function (Builder $query, array $options) {
                    return $query
                        ->when($options['sold_from'] ?? null, fn ($q, $date) => $q->whereDate('delisted_at', '>=', $date))
                        ->when($options['sold_until'] ?? null, fn ($q, $date) => $q->whereDate('delisted_at', '<=', $date));
                }),
        ];
    }
}
