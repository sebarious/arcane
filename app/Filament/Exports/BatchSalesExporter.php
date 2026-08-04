<?php

namespace App\Filament\Exports;

use App\Models\Batch;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\DatePicker;

/**
 * VAT margin-scheme export — one row per batch, not per card. A batch is bought as
 * individual cards (each with its own cost/date) but sold as a single bundled
 * transaction (the invoice to the store), so the batch — not the card — is the real
 * purchase-vs-sale unit for margin scheme reporting.
 */
class BatchSalesExporter extends Exporter
{
    protected static ?string $model = Batch::class;

    /**
     * Extra fields shown in the export modal, alongside Filament's own column
     * picker — NOT set via ->schema() on the action itself, which would replace
     * that column picker (and the columnMap it produces) entirely.
     */
    public static function getOptionsFormComponents(): array
    {
        return [
            DatePicker::make('committed_from')->label('Sold from'),
            DatePicker::make('committed_until')->label('Sold until'),
        ];
    }

    /**
     * @return array<ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('reference')->label('Batch'),
            ExportColumn::make('store.name')->label('Store'),
            ExportColumn::make('type')
                ->label('Product')
                ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\BatchType ? $state->label() : (string) $state),
            ExportColumn::make('pack_count')->label('Packs'),
            ExportColumn::make('committed_at')
                ->label('Sold on')
                ->formatStateUsing(fn (?\Illuminate\Support\Carbon $state) => $state?->format('Y-m-d')),
            ExportColumn::make('total_cost_pence')
                ->label('Cost price')
                ->formatStateUsing(fn (?int $state) => $state !== null ? number_format($state / 100, 2) : ''),
            ExportColumn::make('sale_price_pence')
                ->label('Sale price')
                ->formatStateUsing(fn (?int $state) => $state !== null ? number_format($state / 100, 2) : ''),
            ExportColumn::make('margin_pence')
                ->label('Margin')
                ->formatStateUsing(fn (?int $state) => $state !== null ? number_format($state / 100, 2) : ''),
            ExportColumn::make('margin_scheme_vat_pence')
                ->label('VAT due (margin scheme)')
                ->formatStateUsing(fn (?int $state) => $state !== null ? number_format($state / 100, 2) : ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your batch sales export has completed and '.number_format($export->successful_rows).' '.
            str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
