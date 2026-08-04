<?php

namespace App\Filament\Exports;

use App\Models\CardInventory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\DatePicker;

class SoldCardExporter extends Exporter
{
    protected static ?string $model = CardInventory::class;

    /**
     * Extra fields shown in the export modal, alongside Filament's own column
     * picker — NOT set via ->schema() on the action itself, which would replace
     * that column picker (and the columnMap it produces) entirely.
     */
    public static function getOptionsFormComponents(): array
    {
        return [
            DatePicker::make('sold_from')->label('Sold from'),
            DatePicker::make('sold_until')->label('Sold until'),
        ];
    }

    /**
     * @return array<ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('card_name')->label('Card'),
            ExportColumn::make('set_name')->label('Set'),
            ExportColumn::make('card_number')->label('Number'),
            ExportColumn::make('rarity_band')->label('Rarity'),
            ExportColumn::make('condition')->label('Condition'),
            ExportColumn::make('acquired_from')->label('Bought from'),
            ExportColumn::make('acquired_at')
                ->label('Bought on')
                ->formatStateUsing(fn (?\Illuminate\Support\Carbon $state) => $state?->format('Y-m-d')),
            ExportColumn::make('cost_pence')
                ->label('Bought for')
                ->formatStateUsing(fn (?int $state) => $state !== null ? number_format($state / 100, 2) : ''),
            ExportColumn::make('allocated_sale_price_pence')
                ->label('Sold for')
                ->formatStateUsing(fn (?int $state) => $state !== null ? number_format($state / 100, 2) : ''),
            ExportColumn::make('delisted_at')
                ->label('Sold on')
                ->formatStateUsing(fn (?\Illuminate\Support\Carbon $state) => $state?->format('Y-m-d')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sold cards export has completed and '.number_format($export->successful_rows).' '.
            str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
