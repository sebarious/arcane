<?php

namespace App\Filament\Resources\CreditNotes;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Services\Invoicing\CreditNoteMailSender;
use App\Services\Invoicing\CreditNoteService;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class CreditNoteResource extends Resource
{
    protected static ?string $model = CreditNote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;

    protected static string|UnitEnum|null $navigationGroup = 'Batches & billing';

    protected static ?int $navigationSort = 45;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Credit note')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('number')
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn (?CreditNote $record) => $record !== null),
                    Forms\Components\Select::make('store_id')
                        ->label('Store')
                        ->relationship('store', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->disabled(fn (?CreditNote $record) => $record && $record->status !== 'issued'),
                    Forms\Components\TextInput::make('amount_pounds')
                        ->label('Amount')
                        ->prefix('£')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0.01)
                        ->required()
                        ->disabled(fn (?CreditNote $record) => $record && $record->status !== 'issued')
                        ->afterStateHydrated(fn ($component, ?CreditNote $record) => $record
                            && $component->state($record->amount_pence / 100)),
                    Forms\Components\Select::make('status')
                        ->options([
                            'issued' => 'Issued (pending)',
                            'applied' => 'Applied to invoice',
                            'paid' => 'Paid back manually',
                        ])
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn (?CreditNote $record) => $record !== null),
                    Forms\Components\Textarea::make('reason')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull()
                        ->disabled(fn (?CreditNote $record) => $record && $record->status !== 'issued'),
                    Forms\Components\Select::make('invoice_id')
                        ->label('Applied to invoice')
                        ->relationship('invoice', 'number')
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn (?CreditNote $record) => $record?->invoice_id !== null),
                    Forms\Components\DateTimePicker::make('paid_at')
                        ->label('Paid at')
                        ->disabled()
                        ->dehydrated(false)
                        ->seconds(false)
                        ->visible(fn (?CreditNote $record) => $record?->paid_at !== null),
                    Forms\Components\DateTimePicker::make('last_emailed_at')
                        ->label('Last emailed')
                        ->disabled()
                        ->dehydrated(false)
                        ->seconds(false)
                        ->visible(fn (?CreditNote $record) => $record !== null),
                ]),
        ]);
    }

    /** Sends (or resends) the credit note email — synchronous, immediate confirmation. */
    public static function resendEmailAction(): Action
    {
        return Action::make('resendEmail')
            ->label('Resend email')
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription(fn (CreditNote $record) => "Email this credit note to {$record->store->contact_email}?")
            ->action(function (CreditNote $record) {
                app(CreditNoteMailSender::class)->send($record);

                Notification::make()
                    ->title('Credit note emailed')
                    ->body("Sent to {$record->store->contact_email}.")
                    ->success()
                    ->send();
            });
    }

    /** Resolution path 1: reduce a specific invoice's outstanding balance by this credit note's amount. */
    public static function applyToInvoiceAction(): Action
    {
        return Action::make('applyToInvoice')
            ->label('Apply to invoice')
            ->icon(Heroicon::OutlinedDocumentText)
            ->color('gray')
            ->visible(fn (CreditNote $record) => $record->status === 'issued')
            ->schema(fn (CreditNote $record) => [
                Forms\Components\Select::make('invoice_id')
                    ->label('Invoice')
                    ->options(fn () => Invoice::where('store_id', $record->store_id)
                        ->get()
                        ->filter(fn (Invoice $invoice) => $invoice->amount_due_pence > 0)
                        ->mapWithKeys(fn (Invoice $invoice) => [
                            $invoice->id => "{$invoice->number} — outstanding ".Money::format($invoice->amount_due_pence),
                        ]))
                    ->required()
                    ->searchable()
                    ->helperText('Only invoices for this store with an outstanding balance are listed.'),
            ])
            ->action(function (CreditNote $record, array $data, CreditNoteService $service) {
                try {
                    $service->applyToInvoice($record, Invoice::findOrFail($data['invoice_id']));
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Could not apply credit note')
                        ->body(collect($e->errors())->flatten()->join(' '))
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Credit note applied to invoice')
                    ->success()
                    ->send();
            });
    }

    /** Resolution path 2: money already left the business (e.g. bank transfer) — just record it. */
    public static function markPaidAction(): Action
    {
        return Action::make('markPaid')
            ->label('Mark as paid')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->visible(fn (CreditNote $record) => $record->status === 'issued')
            ->requiresConfirmation()
            ->modalDescription('Confirms this credit note has been paid back to the store directly (e.g. bank transfer).')
            ->action(function (CreditNote $record, CreditNoteService $service) {
                $service->markPaid($record, auth()->user());

                Notification::make()
                    ->title('Credit note marked as paid')
                    ->success()
                    ->send();
            });
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
                Tables\Columns\TextColumn::make('amount_pence')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'issued' => 'Issued (pending)',
                        'applied' => 'Applied to invoice',
                        'paid' => 'Paid back',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state) => match ($state) {
                        'issued' => 'warning',
                        'applied' => 'info',
                        'paid' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('invoice.number')
                    ->label('Applied to')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Issued')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_emailed_at')
                    ->label('Last emailed')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Never')
                    ->toggleable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'issued' => 'Issued (pending)',
                        'applied' => 'Applied to invoice',
                        'paid' => 'Paid back',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                static::applyToInvoiceAction(),
                static::markPaidAction(),
                static::resendEmailAction(),
                Action::make('pdf')
                    ->label('Download PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->url(fn (CreditNote $record) => route('credit-notes.pdf', $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditNotes::route('/'),
            'create' => Pages\CreateCreditNote::route('/create'),
            'edit' => Pages\EditCreditNote::route('/{record}/edit'),
        ];
    }
}
