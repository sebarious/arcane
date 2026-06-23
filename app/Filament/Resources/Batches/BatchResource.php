<?php

namespace App\Filament\Resources\Batches;

use App\Enums\BatchType;
use App\Filament\Resources\Batches\Pages;
use App\Models\Batch;
use App\Models\Store;
use App\Services\Batches\BatchDesign;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use Filament\Support\Enums\ActionSize;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Section;
use App\Enums\Game;
use App\Services\Batches\BatchDeleter;
use Filament\Notifications\Notification;


class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquaresPlus;
    protected static string|UnitEnum|null $navigationGroup = 'Batches & billing';
    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Batch')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('store_id')
                        ->label('Store')
                        ->relationship('store', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('game')
                        ->label('Game')
                        ->options(collect(Game::cases())->mapWithKeys(
                            fn(Game $g) => [$g->value => $g->label()]
                        ))
                        ->default(Game::Pokemon->value)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $typeValue = $get('type') ?: BatchType::Ruby->value;
                            $game      = Game::from($state ?: Game::Pokemon->value);
                            $type      = BatchType::from($typeValue);
                            $set('pack_count', BatchDesign::packCount($game, $type));
                            $set('sale_price_pounds', BatchDesign::targetSalePrice($game, $type) / 100);
                        }),
                    Forms\Components\Select::make('type')
                        ->label('Product')
                        ->options(collect(BatchType::cases())->mapWithKeys(
                            fn(BatchType $t) => [$t->value => $t->label()]
                        ))
                        ->default(BatchType::Ruby->value)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            if (! $state) {
                                $set('pack_count', null);
                                $set('sale_price_pounds', null);
                                return;
                            }
                            $gameValue = $get('game') ?: Game::Pokemon->value;
                            $game      = Game::from($gameValue);
                            $type      = BatchType::from($state);
                            $set('pack_count', BatchDesign::packCount($game, $type));
                            $set('sale_price_pounds', BatchDesign::targetSalePrice($game, $type) / 100);
                        }),
                    // Hidden field for pence, stored in DB
                    Forms\Components\Hidden::make('sale_price_pence')
                        ->dehydrateStateUsing(function ($state, $get) {
                            $pounds = $get('sale_price_pounds');
                            return $pounds ? (int) round($pounds * 100) : null;
                        }),
                    Forms\Components\TextInput::make('pack_count')
                        ->label('Pack count')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),
                    Forms\Components\TextInput::make('sale_price_pounds')
                        ->label('Batch price (ex VAT)')
                        ->prefix('£')
                        ->numeric()
                        ->disabled()
                        ->formatStateUsing(
                            fn($state) => $state !== null
                                ? number_format((float) $state, 2, '.', '')
                                : null
                        )
                        ->afterStateHydrated(function ($state, $set, $get) {
                            // Initial load: if no price yet, derive from current game+type
                            if ($state !== null) {
                                return;
                            }
                            $gameValue = $get('game') ?: Game::Pokemon->value;
                            $typeValue = $get('type') ?: BatchType::Ruby->value;
                            $game = Game::from($gameValue);
                            $type = BatchType::from($typeValue);
                            $set('pack_count', BatchDesign::packCount($game, $type));
                            $set('sale_price_pounds', BatchDesign::targetSalePrice($game, $type) / 100);
                        }),
                ]),
            Section::make('Internal notes')
                ->schema([
                    Forms\Components\Textarea::make('admin_notes')
                        ->columnSpanFull()
                        ->rows(3)
                        ->maxLength(2000),
                ]),
            Section::make('Margin analysis')
                ->columns(2)
                ->schema([
                    Forms\Components\Placeholder::make('margin_vs_cost')
                        ->label('Profit % vs our cost')
                        ->content(function (\App\Models\Batch $record) {
                            if (! $record->total_cost_pence) return '—';
                            $pct = ($record->margin_pence / $record->total_cost_pence) * 100;
                            return number_format($pct, 1) . '%';
                        }),
                    Forms\Components\Placeholder::make('margin_vs_market')
                        ->label('Profit % vs market value')
                        ->content(function (\App\Models\Batch $record) {
                            if (! $record->total_market_value_pence) return '—';
                            $pct = (($record->sale_price_pence - $record->total_market_value_pence)
                                / $record->total_market_value_pence) * 100;
                            return number_format($pct, 1) . '%';
                        }),
                ])
                ->visibleOn('edit'),
            Section::make('Failure')
                ->visible(fn(?Batch $record) => $record?->status === 'cancelled')
                ->schema([
                    Forms\Components\Placeholder::make('failed_at_display')
                        ->label('Failed at')
                        ->content(fn(?Batch $record) => $record?->failed_at?->format('d M Y H:i') ?? '—'),
                    Forms\Components\Textarea::make('failure_reason')
                        ->label('Reason')
                        ->disabled()
                        ->rows(6)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('store.name')->label('Store')->sortable()->searchable(),
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
                Tables\Columns\TextColumn::make('type')
                ->label('Product')
                ->formatStateUsing(function ($state) {
                    if ($state instanceof \App\Enums\BatchType) {
                        return $state->label();
                    }
                    if (is_string($state)) {
                        return \App\Enums\BatchType::from($state)->label();
                    }
                    return (string) $state;
                })
                ->badge(),
                Tables\Columns\TextColumn::make('pack_count'),
                Tables\Columns\TextColumn::make('sale_price_pence')
                    ->label('Sale (ex VAT)')
                    ->formatStateUsing(fn($state) => \App\Support\Money::format($state))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('total_cost_pence')
                    ->label('Cost')
                    ->formatStateUsing(fn($state) => \App\Support\Money::format($state))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('margin_pence')
                    ->label('Margin')
                    ->formatStateUsing(fn($state) => \App\Support\Money::format($state))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('margin_percentage')
                    ->label('Profit % (cost)')
                    ->tooltip('Sale price minus what we paid for the cards, as a percentage of cost. This is your actual accounting margin.')
                    ->alignEnd()
                    ->getStateUsing(function (\App\Models\Batch $record) {
                        $cost   = $record->total_cost_pence;
                        $margin = $record->margin_pence;
                        if ($cost <= 0 || $margin === null) {
                            return null;
                        }
                        $percent = ($margin / $cost) * 100;
                        return number_format($percent, 1) . '%';
                    })
                    ->default('—'),
                Tables\Columns\TextColumn::make('margin_vs_market')
                    ->label('Profit % (market)')
                    ->alignEnd()
                    ->tooltip('Sale price minus total market value, as a percentage of market value. Tells you whether the pack is generous or stingy versus what the cards are worth.')
                    ->getStateUsing(function (\App\Models\Batch $record) {
                        $market = $record->total_market_value_pence;
                        $sale   = $record->sale_price_pence;
                        if ($market <= 0 || $sale === null) return null;
                        $marginVsMarket = (($sale - $market) / $market) * 100;
                        return number_format($marginVsMarket, 1) . '%';
                    })
                    ->color(function (\App\Models\Batch $record) {
                        $market = $record->total_market_value_pence;
                        $sale   = $record->sale_price_pence;
                        if ($market <= 0 || $sale === null) return 'gray';

                        $marginVsMarket = ($sale - $market) / $market;

                        // Negative (sale < market) is good for end customers / pack EV;
                        // strongly positive means you're charging more than the cards are worth.
                        return match (true) {
                            $marginVsMarket < -0.05 => 'success',  // pack EV > sale = great for customer
                            $marginVsMarket < 0.10  => 'info',     // roughly fair
                            $marginVsMarket < 0.30  => 'warning',  // store-favourable
                            default                 => 'danger',   // too rich, customers will feel it
                        };
                    })
                    ->badge()
                    ->default('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'draft'      => 'Draft (not generated)',
                        'committed'  => 'Live (in store)',
                        'dispatched' => 'Dispatched',
                        'completed'  => 'Completed',
                        'cancelled'  => 'Cancelled (failed)',
                        default      => ucfirst($state),
                    })
                    ->color(fn(string $state) => match ($state) {
                        'draft'      => 'gray',
                        'committed'  => 'success',
                        'dispatched' => 'warning',
                        'completed'  => 'info',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),
            Tables\Columns\TextColumn::make('failed_at')
                ->label('Failed')
                ->dateTime('d M Y H:i')
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('failure_reason')
                ->label('Failure reason')
                ->limit(80)
                ->wrap()
                ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('generate')
                    ->label('Generate cards')
                    ->icon(\Filament\Support\Icons\Heroicon::OutlinedSparkles)
                    ->visible(fn(\App\Models\Batch $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (\App\Models\Batch $record) {
                        \App\Jobs\GenerateBatchJob::dispatch($record->id);
                        \Filament\Notifications\Notification::make()
                            ->title('Batch generation started')
                            ->body('Cards will be assigned shortly.')
                            ->success()
                            ->send();
                    }),
                Action::make('qrSheet')
                    ->label('QR sheet')
                    ->icon(\Filament\Support\Icons\Heroicon::OutlinedQrCode)
                    ->url(fn(\App\Models\Batch $record) => route('batches.qr-sheet', $record))
                    ->openUrlInNewTab()
                    ->visible(fn(\App\Models\Batch $record) => $record->status === 'committed'),
                Action::make('retry')
                    ->label('Retry')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->visible(fn(Batch $record) => $record->status === 'cancelled')
                    ->requiresConfirmation()
                    ->action(function (Batch $record) {
                        $record->update([
                            'status'         => 'draft',
                            'failure_reason' => null,
                            'failed_at'      => null,
                        ]);
                        \App\Jobs\GenerateBatchJob::dispatch($record->id);
                        \Filament\Notifications\Notification::make()
                            ->title('Batch retry queued')
                            ->success()
                            ->send();
                    }),
                Action::make('deleteBatch')
                    ->label('Delete batch')
                    ->icon(\Filament\Support\Icons\Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete batch')
                    ->modalDescription('Choose how to handle the cards in this batch when deleting.')
                    ->schema([
                        Forms\Components\Toggle::make('reallocate_inventory')
                            ->label('Return cards to stock')
                            ->helperText('On = cards become "In stock" again. Off = cards are permanently deleted.')
                            ->default(true),
                        Forms\Components\Toggle::make('delete_invoice')
                            ->label('Also delete the linked invoice')
                            ->default(false),
                    ])
                    ->action(function (\App\Models\Batch $record, array $data, BatchDeleter $deleter) {
                        $deleter->delete(
                            $record,
                            reallocateInventory: (bool) $data['reallocate_inventory'],
                            deleteInvoice: (bool) $data['delete_invoice'],
                        );
                        Notification::make()
                            ->title('Batch deleted')
                            ->body($data['reallocate_inventory']
                                ? 'Cards have been returned to stock.'
                                : 'Cards have been permanently deleted.')
                            ->success()
                            ->send();
                    }),
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
            'index'  => Pages\ListBatches::route('/'),
            'create' => Pages\CreateBatch::route('/create'),
            'edit'   => Pages\EditBatch::route('/{record}/edit'),
            'view'   => Pages\ViewBatch::route('/{record}'),
        ];
    }
}
