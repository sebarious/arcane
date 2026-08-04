<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Filament\Exports\BatchSalesExporter;
use App\Filament\Resources\Batches\BatchResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBatches extends ListRecords
{
    protected static string $resource = BatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            // For VAT margin-scheme reporting — a batch is bought as individual cards
            // but sold as one bundled transaction (the invoice to the store), so the
            // batch, not the card, is the unit that matters here.
            ExportAction::make()
                ->label('Export sales')
                ->exporter(BatchSalesExporter::class)
                ->modifyQueryUsing(function (Builder $query, array $options) {
                    return $query
                        ->whereIn('status', ['committed', 'dispatched', 'completed'])
                        ->when($options['committed_from'] ?? null, fn ($q, $date) => $q->whereDate('committed_at', '>=', $date))
                        ->when($options['committed_until'] ?? null, fn ($q, $date) => $q->whereDate('committed_at', '<=', $date));
                }),
        ];
    }
}
