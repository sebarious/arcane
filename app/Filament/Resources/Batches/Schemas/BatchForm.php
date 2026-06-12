<?php

namespace App\Filament\Resources\Batches\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->required(),
                TextInput::make('store_id')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('pack_count')
                    ->required()
                    ->numeric()
                    ->default(250),
                TextInput::make('total_cost_pence')
                    ->required()
                    ->numeric(),
                TextInput::make('total_market_value_pence')
                    ->required()
                    ->numeric(),
                TextInput::make('sale_price_pence')
                    ->required()
                    ->numeric(),
                TextInput::make('margin_pence')
                    ->required()
                    ->numeric(),
                TextInput::make('margin_scheme_vat_pence')
                    ->required()
                    ->numeric(),
                TextInput::make('invoice_id')
                    ->numeric(),
                TextInput::make('qr_sheet_pdf_path'),
                DateTimePicker::make('dispatched_at'),
                DateTimePicker::make('committed_at'),
            ]);
    }
}
