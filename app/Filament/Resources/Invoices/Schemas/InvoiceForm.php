<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->required(),
                Select::make('store_id')
                    ->relationship('store', 'name')
                    ->required(),
                Select::make('batch_id')
                    ->relationship('batch', 'id'),
                TextInput::make('total_pence')
                    ->numeric()
                    ->default(0),
                TextInput::make('internal_cost_pence')
                    ->numeric()
                    ->default(0),
                TextInput::make('internal_margin_pence')
                    ->numeric()
                    ->default(0),
                TextInput::make('internal_margin_vat_pence')
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                DatePicker::make('issued_on'),
                DatePicker::make('due_on'),
                DateTimePicker::make('paid_at'),
                TextInput::make('stripe_invoice_id'),
                TextInput::make('stripe_payment_intent_id'),
                TextInput::make('pdf_path'),
            ]);
    }
}
