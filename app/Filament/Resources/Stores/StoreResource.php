<?php

namespace App\Filament\Resources\Stores;

use App\Filament\Resources\Stores\Pages;
use App\Models\Store;
use App\Models\User;
use BackedEnum;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use UnitEnum;
use Filament\Schemas\Components\Section;

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
                        ->afterStateUpdated(fn ($state, $set) =>
                            $set('slug', Str::slug($state))),

                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('Public URL: /{slug}'),

                    Forms\Components\FileUpload::make('logo')
                        ->image()
                        ->maxSize(1024)
                        ->directory('store-logos')
                        ->visibility('public')
                        ->helperText('Recommended size: 300x300px'),

                    Forms\Components\Select::make('user_id')
                        ->label('Seller account')
                        ->relationship('user', 'email')
                        ->searchable()
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
                            'active'    => 'Active',
                            'paused'    => 'Paused (not selling)',
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
                        'ebay'           => 'eBay',
                        'cardmarket'     => 'Cardmarket',
                        'whatnot'        => 'Whatnot',
                        'instagram'      => 'Instagram',
                        'tiktok_shop'    => 'TikTok Shop',
                        'website'        => 'Website',
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
                            ->filter(fn($enabled) => (bool) $enabled)
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
                                'website'   => 'Website',
                                'instagram' => 'Instagram',
                                'tiktok'    => 'TikTok',
                                'youtube'   => 'YouTube',
                                'x'         => 'X / Twitter',
                                'facebook'  => 'Facebook',
                                'discord'   => 'Discord',
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
                            ->map(fn($url, $platform) => [
                                'platform' => $platform,
                                'url'      => $url,
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
                Tables\Columns\TextColumn::make('city')->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Seller')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'active'    => 'Active',
                        'paused'    => 'Paused (not selling)',
                        'suspended' => 'Suspended',
                        default     => ucfirst($state),
                    })
                    ->color(fn(string $state) => match ($state) {
                        'active'    => 'success',
                        'paused'    => 'warning',
                        'suspended' => 'danger',
                        default     => 'gray',
                    }),
                Tables\Columns\IconColumn::make('public_page_enabled')
                    ->label('Public')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active'    => 'Active',
                        'paused'    => 'Paused (not selling)',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
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
            'index'  => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit'   => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}
