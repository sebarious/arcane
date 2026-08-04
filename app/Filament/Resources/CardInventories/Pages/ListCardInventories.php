<?php
namespace App\Filament\Resources\CardInventories\Pages;

use App\Filament\Exports\SoldCardExporter;
use App\Filament\Resources\CardInventories\CardInventoryResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCardInventories extends ListRecords
{
    protected static string $resource = CardInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rapidIntake')
                ->label('Rapid intake')
                ->icon(Heroicon::OutlinedBolt)
                ->url(CardInventoryResource::getUrl('rapid'))
                ->color('primary'),
            CreateAction::make()->label('Add single card'),
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
