<?php

namespace App\Filament\Resources\Stores;

use App\Enums\ApiMode;
use App\Filament\Resources\Stores\RelationManagers\CreditTransactionsRelationManager;
use App\Models\Store;
use App\Models\User;
use App\Services\Stores\StoreCreditService;
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
use Illuminate\Support\Str;
use UnitEnum;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Sellers';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identity')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('Public URL: /{slug}'),

                    Forms\Components\FileUpload::make('logo')
                        ->image()
                        ->maxSize(1024)
                        ->directory('store-logos')
                        ->visibility('public')
                        ->helperText('Recommended size: 300x300px')
                        // Store::getLogoAttribute() rewrites this into a full display URL for
                        // the rest of the app — FileUpload needs the raw stored disk path (what
                        // it was actually saved as) to recognise the existing file, otherwise it
                        // shows empty and wipes the logo on save.
                        ->afterStateHydrated(function (Forms\Components\FileUpload $component, ?Store $record) {
                            $component->state($record?->getRawOriginal('logo'));
                        }),

                    Forms\Components\Select::make('user_id')
                        ->label('Seller account')
                        ->relationship('user', 'email')
                        ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->email})")
                        ->searchable(['name', 'email'])
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('email')->email()->required()->unique(User::class),
                            Forms\Components\TextInput::make('password')
                                ->password()->required()->minLength(8)
                                ->dehydrateStateUsing(fn ($s) => bcrypt($s)),
                        ])
                        ->createOptionUsing(function (array $data) {
                            $user = User::create($data);
                            $user->assignRole('seller');

                            return $user->id;
                        }),

                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'paused' => 'Paused (not selling)',
                            'suspended' => 'Suspended',
                        ])
                        ->default('active')
                        ->required(),
                ]),

            Section::make('Contact')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('contact_email')->email()->required(),
                    Forms\Components\TextInput::make('phone')->tel(),
                ]),

            Section::make('Address')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('address_line_1')->required()->columnSpanFull(),
                    Forms\Components\TextInput::make('address_line_2')->columnSpanFull(),
                    Forms\Components\TextInput::make('city')->required(),
                    Forms\Components\TextInput::make('postcode')->required(),
                    Forms\Components\TextInput::make('country')->default('GB')->maxLength(2)->required(),
                    Forms\Components\TextInput::make('vat_number')->label('VAT number'),
                ]),

            Section::make('Public profile')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('affiliate_code')
                        ->label('Affiliate code')
                        ->disabled()
                        ->dehydrated(false)
                        ->copyable()
                        ->helperText('Auto-generated when the store is created. Shown on the store\'s public profile page — quoted on Sell to Us for a bonus offer.')
                        ->visibleOn('edit'),

                    Forms\Components\TextInput::make('location')
                        ->label('Public location')
                        ->placeholder('e.g. Leeds, Bristol, Online only')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Brief description')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('Short public-facing description of the store'),

                    Forms\Components\CheckboxList::make('platforms_form')
                        ->label('Platforms used')
                        ->options([
                            'physical_store' => 'Physical store',
                            'ebay' => 'eBay',
                            'cardmarket' => 'Cardmarket',
                            'whatnot' => 'Whatnot',
                            'instagram' => 'Instagram',
                            'tiktok_shop' => 'TikTok Shop',
                            'website' => 'Website',
                        ])
                        ->columns(2)
                        ->columnSpanFull()
                        ->dehydrated(true)
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if (! $record || ! is_array($record->platforms)) {
                                $component->state([]);

                                return;
                            }
                            $selected = collect($record->platforms)
                                ->filter(fn ($enabled) => (bool) $enabled)
                                ->keys()
                                ->values()
                                ->all();
                            $component->state($selected);
                        }),

                    Forms\Components\Repeater::make('social_links_form')
                        ->label('Social links')
                        ->schema([
                            Forms\Components\Select::make('platform')
                                ->label('Platform')
                                ->options([
                                    'website' => 'Website',
                                    'instagram' => 'Instagram',
                                    'tiktok' => 'TikTok',
                                    'youtube' => 'YouTube',
                                    'x' => 'X / Twitter',
                                    'facebook' => 'Facebook',
                                    'discord' => 'Discord',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('url')
                                ->label('URL')
                                ->url()
                                ->required()
                                ->placeholder('https://...')
                                ->maxLength(2048),
                        ])
                        ->columns(2)
                        ->columnSpanFull()
                        ->defaultItems(0)
                        ->dehydrated(true)
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if (! $record || ! is_array($record->social_links)) {
                                $component->state([]);

                                return;
                            }
                            $rows = collect($record->social_links)
                                ->map(fn ($url, $platform) => [
                                    'platform' => $platform,
                                    'url' => $url,
                                ])
                                ->values()
                                ->all();
                            $component->state($rows);
                        }),
                ]),

            Section::make('Public page')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Toggle::make('public_page_enabled')
                        ->label('Visible on the storefront list')
                        ->default(true),
                ]),

            Section::make('API access')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Toggle::make('api_access_granted')
                        ->label('Allow this seller to use the API')
                        ->helperText('Unlocks the API access area on their dashboard, where they can turn the API on for their own store and grab their token. Turning this off immediately blocks every endpoint, even if they\'d switched it on themselves.')
                        ->default(false),
                    Forms\Components\Placeholder::make('api_mode_display')
                        ->label('Mode')
                        ->content(fn (?Store $record) => $record?->api_mode?->label() ?? ApiMode::Test->label())
                        ->helperText('Use the "Approve → go live" button above to switch this once you\'ve reviewed their integration on the API logs page (filter by this store).')
                        ->visibleOn('edit'),
                    Forms\Components\Toggle::make('mark_as_sold_enabled')
                        ->label('Allow markAsSold')
                        ->helperText('Separate approval gate from the toggle above — turn this on once you\'ve reviewed their integration on the API logs page (filter by this store). Reading batch/pack data doesn\'t need it.')
                        ->default(false),
                    Forms\Components\TextInput::make('daily_request_limit')
                        ->label('Daily request limit')
                        ->numeric()
                        ->minValue(1)
                        ->default(1000)
                        ->required()
                        ->helperText('Resets at midnight UTC.'),
                ]),

            Section::make('Wallet')
                ->columnSpanFull()
                ->visibleOn('edit')
                ->schema([
                    Forms\Components\TextInput::make('credit_balance_pence')
                        ->label('Credit balance')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => Money::format($state))
                        ->helperText('Use the "Add credit" button above to top this up — it\'s automatically deducted from this store\'s next invoice(s).'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->prefix('/')
                    ->copyable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('affiliate_code')
                    ->label('Affiliate code')
                    ->copyable()
                    ->toggleable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('credit_balance_pence')
                    ->label('Credit')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Seller')
                    ->description(fn (Store $record) => $record->user?->email)
                    ->searchable(['name', 'email']),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => 'Active',
                        'paused' => 'Paused (not selling)',
                        'suspended' => 'Suspended',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('public_page_enabled')
                    ->label('Public')
                    ->boolean(),
                Tables\Columns\IconColumn::make('api_access_granted')
                    ->label('API')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('api_mode')
                    ->label('API mode')
                    ->badge()
                    ->formatStateUsing(fn (?ApiMode $state) => $state?->label() ?? ApiMode::Test->label())
                    ->color(fn (?ApiMode $state) => $state === ApiMode::Live ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'paused' => 'Paused (not selling)',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->recordActions([
                static::viewLiveAction(),
                static::addCreditAction(),
                static::toggleApiModeAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Top up a store's credit balance — e.g. after appraising cards submitted via
     * their affiliate code. Shared between the table row action and the Edit page
     * header action. Deduction against invoices is automatic (see BatchGenerator);
     * this is only ever a manual top-up.
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
                    ->placeholder('e.g. Appraised cards submitted via affiliate code — SELL-2026-0004'),
            ])
            ->modalHeading('Add credit')
            ->modalDescription('This is added to the store\'s wallet immediately and automatically deducted from its next invoice(s).')
            ->action(function (array $data, Store $record) {
                app(StoreCreditService::class)->addCredit(
                    $record,
                    Money::toPence($data['amount']),
                    $data['reason'],
                    addedBy: auth()->user(),
                );

                Notification::make()
                    ->title('Credit added')
                    ->body(Money::format($record->fresh()->credit_balance_pence).' now in this store\'s wallet.')
                    ->success()
                    ->send();
            });
    }

    /** Opens the store's public profile page (arcanepacks.com/{slug}) in a new tab. */
    public static function viewLiveAction(): Action
    {
        return Action::make('viewLive')
            ->label('View live page')
            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
            ->color('gray')
            ->url(fn (Store $record) => route('stores.show', $record))
            ->openUrlInNewTab();
    }

    /**
     * Flips a store between ApiMode::Test (sandbox data, the default) and
     * ApiMode::Live (their real batches) — the actual "approve their
     * integration" step, meant to be used after reviewing them on the API
     * logs page (App\Filament\Resources\ApiRequestLogs\ApiRequestLogResource).
     * Only relevant once api_access_granted is on.
     */
    public static function toggleApiModeAction(): Action
    {
        return Action::make('toggleApiMode')
            ->label(fn (Store $record) => $record->api_mode === ApiMode::Live ? 'Revert to test mode' : 'Approve → go live')
            ->icon(fn (Store $record) => $record->api_mode === ApiMode::Live ? Heroicon::OutlinedArrowUturnLeft : Heroicon::OutlinedCheckBadge)
            ->color(fn (Store $record) => $record->api_mode === ApiMode::Live ? 'warning' : 'success')
            ->visible(fn (Store $record) => $record->api_access_granted)
            ->requiresConfirmation()
            ->modalHeading(fn (Store $record) => $record->api_mode === ApiMode::Live ? 'Revert to test mode' : 'Approve API integration')
            ->modalDescription(fn (Store $record) => $record->api_mode === ApiMode::Live
                ? 'Switches this store\'s API back to sandbox data. Their real batches stop being reachable via the API until you approve them again.'
                : 'Switches this store\'s API from sandbox data to their real batches. Only do this once you\'ve reviewed their integration on the API logs page.')
            ->action(function (Store $record) {
                $newMode = $record->api_mode === ApiMode::Live ? ApiMode::Test : ApiMode::Live;
                $record->update(['api_mode' => $newMode]);

                Notification::make()
                    ->title($newMode === ApiMode::Live ? 'API integration approved — now live' : 'Reverted to test mode')
                    ->success()
                    ->send();
            });
    }

    public static function getRelations(): array
    {
        return [
            CreditTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit' => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}
