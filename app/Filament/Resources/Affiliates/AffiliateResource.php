<?php

namespace App\Filament\Resources\Affiliates;

use App\Filament\Resources\Affiliates\Pages\EditAffiliate;
use App\Filament\Resources\Affiliates\Pages\ListAffiliates;
use App\Mail\AffiliateApprovedMail;
use App\Models\Affiliate;
use App\Services\Affiliates\AffiliateCreditService;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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

class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Affiliates';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Affiliate')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Placeholder::make('user_name')
                        ->label('Name')
                        ->content(fn (?Affiliate $record) => $record?->user?->name),
                    Forms\Components\Placeholder::make('user_email')
                        ->label('Email')
                        ->content(fn (?Affiliate $record) => $record?->user?->email),
                    Forms\Components\TextInput::make('affiliate_code')
                        ->label('Affiliate code')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\Placeholder::make('credit_balance_display')
                        ->label('Wallet balance')
                        ->content(fn (?Affiliate $record) => Money::format($record?->credit_balance_pence)),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending approval',
                            'active' => 'Active',
                            'suspended' => 'Suspended',
                        ])
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Use the Approve/Suspend/Reactivate actions above, not this field directly.'),
                ]),

            Section::make('Bank details')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Placeholder::make('bank_account_name')
                        ->label('Name on account')
                        ->content(fn (?Affiliate $record) => $record?->bank_account_name ?? 'Not set'),
                    Forms\Components\Placeholder::make('bank_sort_code')
                        ->label('Sort code')
                        ->content(fn (?Affiliate $record) => $record?->bank_sort_code ?? 'Not set'),
                    Forms\Components\Placeholder::make('bank_account_number')
                        ->label('Account number')
                        ->content(fn (?Affiliate $record) => $record?->bank_account_number ?? 'Not set'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->description(fn (Affiliate $record) => $record->user?->email)
                    ->searchable(['name', 'email']),
                Tables\Columns\TextColumn::make('affiliate_code')
                    ->label('Code')
                    ->copyable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('credit_balance_pence')
                    ->label('Balance')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Pending approval',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending approval',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->recordActions([
                static::approveAction(),
                static::addCreditAction(),
                static::suspendAction(),
                static::reactivateAction(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * The one thing that actually unlocks an affiliate's dashboard/code — see
     * EnsureAffiliateIsApproved. Mirrors StoreResource::approveOnboardingAction()'s
     * shape.
     */
    public static function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->visible(fn (Affiliate $record) => $record->status === 'pending')
            ->requiresConfirmation()
            ->modalHeading('Approve affiliate')
            ->modalDescription('Unlocks their dashboard and makes their affiliate code start working on Sell to Us. Emails them to let them know.')
            ->action(function (Affiliate $record) {
                $record->update(['status' => 'active']);

                Mail::to($record->user->email)->send(new AffiliateApprovedMail($record));

                Notification::make()
                    ->title('Affiliate approved')
                    ->success()
                    ->send();
            });
    }

    /**
     * A manual top-up — e.g. after appraising cards submitted via their
     * affiliate code outside the normal CustomerSellSubmission flow, or any
     * other one-off adjustment. Mirrors StoreResource::addCreditAction()
     * exactly, against AffiliateCreditService instead of StoreCreditService.
     */
    public static function addCreditAction(): Action
    {
        return Action::make('addCredit')
            ->label('Add credit')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('success')
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->label('Amount (£)')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.01)
                    ->prefix('£')
                    ->required(),
                Forms\Components\Textarea::make('reason')
                    ->label('Reason')
                    ->required()
                    ->rows(2)
                    ->placeholder('e.g. Appraised cards submitted via affiliate code — outside a normal submission'),
            ])
            ->modalHeading('Add credit')
            ->modalDescription('This is added to the affiliate\'s wallet immediately — they can request a withdrawal once their balance exceeds £100.')
            ->action(function (array $data, Affiliate $record) {
                app(AffiliateCreditService::class)->addCredit(
                    $record,
                    Money::toPence($data['amount']),
                    $data['reason'],
                    addedBy: auth()->user(),
                );

                Notification::make()
                    ->title('Credit added')
                    ->body(Money::format($record->fresh()->credit_balance_pence).' now in this affiliate\'s wallet.')
                    ->success()
                    ->send();
            });
    }

    public static function suspendAction(): Action
    {
        return Action::make('suspend')
            ->label('Suspend')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->visible(fn (Affiliate $record) => $record->status === 'active')
            ->requiresConfirmation()
            ->modalDescription('Locks their dashboard and stops their affiliate code from working, immediately.')
            ->action(function (Affiliate $record) {
                $record->update(['status' => 'suspended']);

                Notification::make()->title('Affiliate suspended')->success()->send();
            });
    }

    public static function reactivateAction(): Action
    {
        return Action::make('reactivate')
            ->label('Reactivate')
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->color('success')
            ->visible(fn (Affiliate $record) => $record->status === 'suspended')
            ->requiresConfirmation()
            ->action(function (Affiliate $record) {
                $record->update(['status' => 'active']);

                Notification::make()->title('Affiliate reactivated')->success()->send();
            });
    }

    // Affiliates only ever originate from self-signup — no admin-created ones.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAffiliates::route('/'),
            'edit' => EditAffiliate::route('/{record}/edit'),
        ];
    }
}
