<?php

namespace App\Filament\Resources\AffiliateWithdrawals;

use App\Filament\Resources\AffiliateWithdrawals\Pages\EditAffiliateWithdrawal;
use App\Filament\Resources\AffiliateWithdrawals\Pages\ListAffiliateWithdrawals;
use App\Mail\AffiliateWithdrawalPaidMail;
use App\Models\AffiliateWithdrawal;
use App\Services\Affiliates\AffiliateCreditService;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use UnitEnum;

class AffiliateWithdrawalResource extends Resource
{
    protected static ?string $model = AffiliateWithdrawal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Affiliates';

    protected static ?string $navigationLabel = 'Withdrawals';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Withdrawal')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Placeholder::make('affiliate_name')
                        ->label('Affiliate')
                        ->content(fn (?AffiliateWithdrawal $record) => $record?->affiliate?->user?->name),
                    Forms\Components\Placeholder::make('amount_display')
                        ->label('Amount')
                        ->content(fn (?AffiliateWithdrawal $record) => Money::format($record?->amount_pence)),
                    Forms\Components\Placeholder::make('status')
                        ->label('Status')
                        ->content(fn (?AffiliateWithdrawal $record) => ucfirst($record?->status ?? '')),
                    Forms\Components\Placeholder::make('admin_notes')
                        ->label('Admin notes')
                        ->content(fn (?AffiliateWithdrawal $record) => $record?->admin_notes ?? '—'),
                ]),

            Section::make('Bank details (as requested)')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Placeholder::make('bank_account_name')
                        ->label('Name on account')
                        ->content(fn (?AffiliateWithdrawal $record) => $record?->bank_account_name),
                    Forms\Components\Placeholder::make('bank_sort_code')
                        ->label('Sort code')
                        ->content(fn (?AffiliateWithdrawal $record) => $record?->bank_sort_code),
                    Forms\Components\Placeholder::make('bank_account_number')
                        ->label('Account number')
                        ->content(fn (?AffiliateWithdrawal $record) => $record?->bank_account_number),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('affiliate.user.name')
                    ->label('Affiliate')
                    ->description(fn (AffiliateWithdrawal $record) => $record->affiliate?->affiliate_code)
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount_pence')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('bank_sort_code')
                    ->label('Sort code')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bank_account_number')
                    ->label('Account number')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                static::markPaidAction(),
                static::rejectAction(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function markPaidAction(): Action
    {
        return Action::make('markPaid')
            ->label('Mark as paid')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->visible(fn (AffiliateWithdrawal $record) => $record->status === 'pending')
            ->requiresConfirmation()
            ->modalDescription('Confirms this withdrawal has actually been paid out — emails the affiliate to let them know.')
            ->action(function (AffiliateWithdrawal $record, AffiliateCreditService $service) {
                $service->markWithdrawalPaid($record, auth()->user());

                Mail::to($record->affiliate->user->email)->send(new AffiliateWithdrawalPaidMail($record));

                Notification::make()
                    ->title('Withdrawal marked as paid')
                    ->success()
                    ->send();
            });
    }

    public static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (AffiliateWithdrawal $record) => $record->status === 'pending')
            ->schema([
                Forms\Components\Textarea::make('reason')
                    ->label('Reason')
                    ->required()
                    ->rows(2)
                    ->helperText('The requested amount is refunded back to the affiliate\'s wallet.'),
            ])
            ->action(function (AffiliateWithdrawal $record, array $data, AffiliateCreditService $service) {
                $service->rejectWithdrawal($record, auth()->user(), $data['reason']);

                Notification::make()
                    ->title('Withdrawal rejected')
                    ->body('The amount has been refunded to the affiliate\'s wallet.')
                    ->success()
                    ->send();
            });
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAffiliateWithdrawals::route('/'),
            'edit' => EditAffiliateWithdrawal::route('/{record}/edit'),
        ];
    }
}
