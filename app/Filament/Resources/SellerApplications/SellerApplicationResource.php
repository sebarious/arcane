<?php

namespace App\Filament\Resources\SellerApplications;

use App\Filament\Resources\SellerApplications\Pages\ListSellerApplication;
use App\Filament\Resources\SellerApplications\Pages\EditSellerApplication;
use App\Models\SellerApplication;
use App\Services\Sellers\SellerApplicationApprover;
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
use UnitEnum;
use App\Mail\SellerApplicationRejectedMail;
use Illuminate\Support\Facades\Mail;

class SellerApplicationResource extends Resource
{
    protected static ?string $model = SellerApplication::class;
    protected static string|UnitEnum|null $navigationGroup = 'Sellers';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static ?string $navigationLabel = 'Seller Applications';
    protected static ?int $navigationSort = 5;
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Business')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('business_name')
                        ->disabled(),
                    Forms\Components\TextInput::make('contact_name')
                        ->disabled(),
                    Forms\Components\TextInput::make('contact_email')
                        ->disabled(),
                    Forms\Components\TextInput::make('phone')
                        ->disabled(),
                    Forms\Components\TextInput::make('vat_number')
                        ->disabled(),
                ]),
            Section::make('Address')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('address_line_1')
                        ->disabled()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('address_line_2')
                        ->disabled()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('city')
                        ->disabled(),
                    Forms\Components\TextInput::make('postcode')
                        ->disabled(),
                    Forms\Components\TextInput::make('country')
                        ->disabled(),
                ]),
            Section::make('About')
                ->schema([
                    Forms\Components\Textarea::make('about')
                        ->rows(5)
                        ->disabled()
                        ->columnSpanFull(),
                ]),
            Section::make('Review')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending'  => 'Pending review',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('user.email')
                        ->label('Linked seller user')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Textarea::make('admin_notes')
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\DateTimePicker::make('reviewed_at')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TextInput::make('reviewedBy.email')
                        ->label('Reviewed by')
                        ->disabled()
                        ->dehydrated(false),
                ]),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Contact')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'pending'  => 'Pending review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default    => ucfirst($state),
                    })
                    ->color(fn(string $state) => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn(SellerApplication $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (SellerApplication $record, SellerApplicationApprover $approver) {
                        $approver->approve($record, auth()->user());
                        Notification::make()
                            ->title('Seller application approved')
                            ->body('Seller user and store created successfully.')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                ->label('Reject')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->visible(fn(SellerApplication $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(function (SellerApplication $record) {
                    $record->update([
                        'status'              => 'rejected',
                        'reviewed_by_user_id' => auth()->id(),
                        'reviewed_at'         => now(),
                    ]);
                    if ($record->contact_email) {
                        Mail::to($record->contact_email)->send(
                            new SellerApplicationRejectedMail($record)
                        );
                    }
                    Notification::make()
                        ->title('Seller application rejected')
                        ->body('The applicant has been notified by email.')
                        ->success()
                        ->send();
                }),
            ])
            ->defaultSort('created_at', 'desc');
    }
    public static function getPages(): array
    {
        return [
            'index' => ListSellerApplication::route('/'),
            'edit'  => EditSellerApplication::route('/{record}/edit'),
        ];
    }
}