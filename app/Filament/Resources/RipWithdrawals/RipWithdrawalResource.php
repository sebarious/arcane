<?php

namespace App\Filament\Resources\RipWithdrawals;

use App\Filament\Resources\RipWithdrawals\Pages\ListRipWithdrawals;
use App\Models\RipWithdrawal;
use App\Services\Rips\RipWalletService;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Withdrawal requests off a customer's Digital Rips wallet — mirrors the
 * mark-paid/reject shape Store/Affiliate credit withdrawals use elsewhere,
 * via RipWalletService (a reject refunds the wallet, it never actually left).
 */
class RipWithdrawalResource extends Resource
{
    protected static ?string $model = RipWithdrawal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Digital Rips';

    protected static ?int $navigationSort = 30;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['wallet.user']))
            ->columns([
                Tables\Columns\TextColumn::make('wallet.user.name')
                    ->label('Customer')
                    ->description(fn (RipWithdrawal $record) => $record->wallet->user->email)
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount_pence')
                    ->label('Payout')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('fee_pence')
                    ->label('Fee')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('total_debited_pence')
                    ->label('Debited')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('bank_account_name')
                    ->label('Account name'),

                Tables\Columns\TextColumn::make('bank_sort_code')
                    ->label('Sort code'),

                Tables\Columns\TextColumn::make('bank_account_number')
                    ->label('Account number'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid', 'rejected' => 'Rejected'])
                    ->default('pending'),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('Mark paid')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (RipWithdrawal $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (RipWithdrawal $record, RipWalletService $wallet) {
                        $wallet->markWithdrawalPaid($record, auth()->user());

                        Notification::make()->title('Withdrawal marked as paid')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (RipWithdrawal $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (RipWithdrawal $record, array $data, RipWalletService $wallet) {
                        $wallet->rejectWithdrawal($record, auth()->user(), $data['reason']);

                        Notification::make()->title('Withdrawal rejected and refunded to wallet')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRipWithdrawals::route('/'),
        ];
    }
}
