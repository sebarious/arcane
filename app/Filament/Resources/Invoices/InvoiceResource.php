<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\Pages;
use App\Models\Invoice;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Section;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static string|UnitEnum|null $navigationGroup = 'Batches & billing';
    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Invoice')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('number')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Select::make('store_id')
                        ->relationship('store', 'name')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Select::make('batch_id')
                        ->relationship('batch', 'reference')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft'     => 'Draft',
                            'sent'      => 'Sent',
                            'paid'      => 'Paid',
                            'overdue'   => 'Overdue',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required(),
                    Forms\Components\DatePicker::make('issued_on')
                        ->label('Issue date')
                        ->required(),
                    Forms\Components\DatePicker::make('due_on')
                        ->label('Due date'),
                    Forms\Components\DateTimePicker::make('paid_at')
                        ->label('Paid at')
                        ->seconds(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('store.name')
                    ->label('Store')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('batch.reference')
                    ->label('Batch')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_pence')
                    ->label('Total')
                    ->formatStateUsing(fn($state) => Money::format($state))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'draft'     => 'gray',
                        'sent'      => 'info',
                        'paid'      => 'success',
                        'overdue'   => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('issued_on')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('due_on')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make()->label('Status'),
                Action::make('pdf')
                    ->label('Download PDF')
                    ->icon(\Filament\Support\Icons\Heroicon::OutlinedArrowDownTray)
                    ->url(fn(\App\Models\Invoice $record) => route('invoices.pdf', $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('issued_on', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'edit'  => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
