<?php

namespace App\Filament\Resources\Cards;

use App\Filament\Resources\Cards\Pages;
use App\Models\Card;
use App\Services\Scrydex\CardMapper;
use App\Services\Scrydex\ScrydexClient;
use BackedEnum;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use UnitEnum;
use Filament\Schemas\Components\Section;
use App\Enums\Game;
use App\Models\Expansion;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Card')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('scrydex_id')
                        ->label('Scrydex ID')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('printed_rarity')->label('Printed rarity'),
                    Forms\Components\TextInput::make('set_code')->required(),
                    Forms\Components\TextInput::make('set_name')->required(),
                    Forms\Components\TextInput::make('card_number')->required(),
                    Forms\Components\TextInput::make('variant'),
                    Forms\Components\TextInput::make('language_code')->default('EN')->maxLength(5),
                ]),

            Section::make('Imagery')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('image_front')->url()->columnSpanFull(),
                    Forms\Components\TextInput::make('image_back')->url()->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_front')
                    ->label('')
                    ->height(60)
                    ->extraImgAttributes(['class' => 'rounded']),

                Tables\Columns\TextColumn::make('game')
                    ->label('Game')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof Game) {
                            return $state->label();
                        }
                        if (is_string($state)) {
                            return Game::from($state)->label();
                        }
                        return (string) $state;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()->sortable()
                    ->description(fn (Card $r) => "{$r->set_name} · {$r->card_number}"),

                Tables\Columns\TextColumn::make('current_market_pence')
                        ->label('Market price')
                        ->formatStateUsing(fn ($state) => $state !== null ? '£' . number_format($state / 100, 2) : null)
                        ->sortable()
                        ->toggleable(),

                Tables\Columns\TextColumn::make('printed_rarity')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('variant')->toggleable(),

                Tables\Columns\TextColumn::make('inventory_count')
                    ->label('In stock')
                    ->counts('inventory')
                    ->sortable(),

                Tables\Columns\TextColumn::make('set_code')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d M Y')->toggleable()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('set_code')
                    ->label('Set')
                    ->options(fn () => Card::query()
                        ->select('set_code', 'set_name')
                        ->distinct()
                        ->orderBy('set_name')
                        ->pluck('set_name', 'set_code')
                        ->all()),
            ])
            ->headerActions([
                Action::make('importExpansion')
                    ->label('Import expansion')
                    ->icon(Heroicon::OutlinedCloudArrowDown)
                    ->schema([
                Forms\Components\Select::make('game')
                    ->label('Game')
                    ->options(
                        fn() => collect(Game::cases())
                            ->mapWithKeys(fn(Game $game) => [
                                $game->value => $game->label(),
                            ])
                            ->all()
                    )
                    ->default(Game::Pokemon->value)
                    ->live()
                    ->afterStateUpdated(fn($set) => $set('expansion_id', null))
                    ->required(),

                Forms\Components\Select::make('expansion_id')
                    ->label('Expansion')
                    ->options(function ($get) {
                        return Expansion::query()
                            ->where('game', $get('game')) // adjust column name if needed
                            ->where('is_online_only', false)
                            ->orderBy('release_date', 'desc')
                            ->get()
                            ->mapWithKeys(function ($expansion) {
                                return [
                                    $expansion->scrydex_id => "{$expansion->series} - {$expansion->name} ({$expansion->code})",
                                ];
                            })
                            ->all();
                    })
                    ->disabled(fn($get): bool => blank($get('game')))
                    ->required(),
                    ])
                    ->action(function (array $data, ScrydexClient $client) {
                        $lang = $data['game'] === Game::Pokemon->value ? 'en' : null;

                        // Dispatch a job to fetch cards for the selected expansion
                        \App\Jobs\GetCardsForExpansion::dispatch($data['expansion_id'], $data['game'], $lang);

                        // Notify the user that the job has been dispatched
                        Notification::make()
                            ->title('Import started')
                            ->body('The import job has been dispatched. It may take a few minutes to complete.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCards::route('/'),
            'create' => Pages\CreateCard::route('/create'),
            'edit'   => Pages\EditCard::route('/{record}/edit'),
        ];
    }
}
