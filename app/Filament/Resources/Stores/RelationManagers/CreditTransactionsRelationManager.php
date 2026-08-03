<?php

namespace App\Filament\Resources\Stores\RelationManagers;

use App\Support\Money;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CreditTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'creditTransactions';

    protected static ?string $title = 'Credit ledger';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'credit'     => 'success',
                        'redemption' => 'danger',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount_pence')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '-').Money::format(abs($state)))
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('balance_after_pence')
                    ->label('Balance after')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('reason')
                    ->wrap()
                    ->limit(60),
                Tables\Columns\TextColumn::make('invoice.number')
                    ->label('Invoice')
                    ->placeholder('—')
                    ->url(fn ($record) => $record->invoice
                        ? route('filament.admin.resources.invoices.edit', $record->invoice)
                        : null),
                Tables\Columns\TextColumn::make('customerSellSubmission.reference')
                    ->label('Submission')
                    ->placeholder('—')
                    ->url(fn ($record) => $record->customerSellSubmission
                        ? route('filament.admin.resources.customer-sell-submissions.edit', $record->customerSellSubmission)
                        : null),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('By')
                    ->placeholder('System'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
