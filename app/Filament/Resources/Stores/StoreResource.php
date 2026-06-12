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
                ->columns(2)
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
                            'paused'    => 'Paused',
                            'suspended' => 'Suspended',
                        ])
                        ->default('active')
                        ->required(),
                ]),

            Section::make('Contact')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('contact_email')->email()->required(),
                    Forms\Components\TextInput::make('phone')->tel(),
                ]),

            Section::make('Address')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('address_line_1')->required()->columnSpanFull(),
                    Forms\Components\TextInput::make('address_line_2')->columnSpanFull(),
                    Forms\Components\TextInput::make('city')->required(),
                    Forms\Components\TextInput::make('postcode')->required(),
                    Forms\Components\TextInput::make('country')->default('GB')->maxLength(2)->required(),
                    Forms\Components\TextInput::make('vat_number')->label('VAT number'),
                ]),

            Section::make('Public page')
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
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'paused'    => 'warning',
                        'suspended' => 'danger',
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
                        'paused'    => 'Paused',
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
