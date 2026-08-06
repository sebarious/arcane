<?php

namespace App\Filament\Resources\Users;

use App\Models\User;
use App\Services\Auth\ImpersonationManager;
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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\Select::make('roles')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->required()
                        ->helperText('Admins get full access to this panel; sellers can manage their own store.'),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->helperText(fn (string $operation) => $operation === 'edit'
                            ? 'Leave blank to keep the current password.'
                            : null),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name'),
            ])
            ->recordActions([
                static::impersonateAction(),
                static::resetPasswordAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** Emails the user a password-reset link — the same flow as the public "forgot password" page. */
    public static function resetPasswordAction(): Action
    {
        return Action::make('resetPassword')
            ->label('Reset password')
            ->icon(Heroicon::OutlinedKey)
            ->color('warning')
            ->requiresConfirmation()
            ->modalDescription(fn (User $record) => "Send a password reset link to {$record->email}?")
            ->action(function (User $record) {
                $status = Password::sendResetLink(['email' => $record->email]);

                if ($status === Password::RESET_LINK_SENT) {
                    Notification::make()
                        ->title('Password reset email sent')
                        ->body("Sent to {$record->email}.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title("Couldn't send reset email")
                        ->body(__($status))
                        ->danger()
                        ->send();
                }
            });
    }

    /** Logs the admin in as another user, without knowing their password, via ImpersonationManager. */
    public static function impersonateAction(): Action
    {
        return Action::make('impersonate')
            ->label('Impersonate')
            ->icon(Heroicon::OutlinedUserCircle)
            ->color('gray')
            ->visible(fn (User $record) => ! $record->is(auth()->user()))
            ->requiresConfirmation()
            ->modalDescription(fn (User $record) => "You'll be logged in as {$record->name} ({$record->email}). "
                .'A banner will let you end this and return to your own account at any time.')
            ->action(function (User $record, ImpersonationManager $impersonation) {
                $impersonation->start(request(), $record);
            })
            ->successRedirectUrl(fn (User $record, ImpersonationManager $impersonation) => $impersonation->redirectPathFor($record));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
