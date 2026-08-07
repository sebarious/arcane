<?php

namespace App\Filament\Resources\Batches;

use App\Enums\BatchType;
use App\Enums\Game;
use App\Filament\Exports\BatchSalesExporter;
use App\Jobs\GenerateBatchJob;
use App\Models\Batch;
use App\Models\Store;
use App\Services\Batches\BatchDeleter;
use App\Services\Batches\BatchDesign;
use App\Services\Batches\BatchMerger;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

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
                            fn (Game $g) => [$g->value => $g->label()]
                        ))
                        ->default(Game::Pokemon->value)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $typeValue = $get('type') ?: BatchType::Ruby->value;
                            $game = Game::from($state ?: Game::Pokemon->value);
                            $type = BatchType::from($typeValue);
                            $set('pack_count', BatchDesign::packCount($game, $type));
                            $set('sale_price_pounds', BatchDesign::targetSalePrice($game, $type) / 100);
                        }),
                    Forms\Components\Select::make('type')
                        ->label('Product')
                        ->options(collect(BatchType::cases())->mapWithKeys(
                            fn (BatchType $t) => [$t->value => $t->label()]
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
                            $game = Game::from($gameValue);
                            $type = BatchType::from($state);
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
                        ->label(config('vat.registered') ? 'Batch price (ex VAT)' : 'Batch price')
                        ->prefix('£')
                        ->numeric()
                        ->disabled()
                        ->formatStateUsing(
                            fn ($state) => $state !== null
                                ? number_format((float) $state, 2, '.', ',')
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
            Section::make('Verification')
                ->description('Provably-fair generation')
                ->columns(2)
                ->schema([
                    Forms\Components\Placeholder::make('verification_hash_display')
                        ->label('Verification ID (published on creation)')
                        ->content(fn (?Batch $record) => $record?->verification_hash ?? '—'),
                    Forms\Components\Placeholder::make('verification_seed_display')
                        ->label('Seed')
                        ->content(fn (?Batch $record) => $record?->isVerificationRevealed()
                            ? $record->verification_seed
                            : 'Hidden until this batch is generated'),
                ]),
            Section::make('Failure')
                ->visible(fn (?Batch $record) => $record?->status === 'cancelled')
                ->schema([
                    Forms\Components\Placeholder::make('failed_at_display')
                        ->label('Failed at')
                        ->content(fn (?Batch $record) => $record?->failed_at?->format('d M Y H:i') ?? '—'),
                    Forms\Components\Textarea::make('failure_reason')
                        ->label('Reason')
                        ->disabled()
                        ->rows(6)
                        ->columnSpanFull(),
                ]),
            Section::make('Internal notes')
                ->schema([
                    TextEntry::make('merge_request_notice')
                        ->label('')
                        ->visible(fn (?Batch $record) => filled($record?->merge_request_batch_id))
                        ->state(fn (?Batch $record) => 'Seller asked for batch #'.$record?->mergeRequestBatch?->reference.' to be merged into this one once generated — use the "Merge into" action on that batch after this one is committed.')
                        ->color('warning')
                        ->columnSpanFull(),
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
                        ->content(function (Batch $record) {
                            if (! $record->total_cost_pence) {
                                return '—';
                            }
                            $pct = ($record->margin_pence / $record->total_cost_pence) * 100;

                            return number_format($pct, 1).'%';
                        }),
                    Forms\Components\Placeholder::make('margin_vs_market')
                        ->label('Profit % vs market value')
                        ->content(function (Batch $record) {
                            if (! $record->total_market_value_pence) {
                                return '—';
                            }
                            $pct = (($record->sale_price_pence - $record->total_market_value_pence)
                                / $record->total_market_value_pence) * 100;

                            return number_format($pct, 1).'%';
                        }),
                ])
                ->visibleOn('edit'),
            Section::make('Cards in this batch')
                ->columnSpanFull()
                ->visibleOn('edit')
                ->visible(fn (?Batch $record) => $record?->packs()->exists())
                ->schema([
                    Forms\Components\ViewField::make('cards')
                        ->label('')
                        ->view('filament.forms.components.batch-cards')
                        ->dehydrated(false),
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
                        if ($state instanceof BatchType) {
                            return $state->label();
                        }
                        if (is_string($state)) {
                            return BatchType::from($state)->label();
                        }

                        return (string) $state;
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('pack_count'),
                Tables\Columns\TextColumn::make('sale_price_pence')
                    ->label(config('vat.registered') ? 'Sale (ex VAT)' : 'Sale')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('total_cost_pence')
                    ->label('Cost')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('margin_pence')
                    ->label('Margin')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('margin_percentage')
                    ->label('Profit % (cost)')
                    ->tooltip('Sale price minus what we paid for the cards, as a percentage of cost. This is your actual accounting margin.')
                    ->alignEnd()
                    ->getStateUsing(function (Batch $record) {
                        $cost = $record->total_cost_pence;
                        $margin = $record->margin_pence;
                        if ($cost <= 0 || $margin === null) {
                            return null;
                        }
                        $percent = ($margin / $cost) * 100;

                        return number_format($percent, 1).'%';
                    })
                    ->default('—'),
                Tables\Columns\TextColumn::make('margin_vs_market')
                    ->label('Profit % (market)')
                    ->alignEnd()
                    ->tooltip('Sale price minus total market value, as a percentage of market value. Tells you whether the pack is generous or stingy versus what the cards are worth.')
                    ->getStateUsing(function (Batch $record) {
                        $market = $record->total_market_value_pence;
                        $sale = $record->sale_price_pence;
                        if ($market <= 0 || $sale === null) {
                            return null;
                        }
                        $marginVsMarket = (($sale - $market) / $market) * 100;

                        return number_format($marginVsMarket, 1).'%';
                    })
                    ->color(function (Batch $record) {
                        $market = $record->total_market_value_pence;
                        $sale = $record->sale_price_pence;
                        if ($market <= 0 || $sale === null) {
                            return 'gray';
                        }

                        $marginVsMarket = ($sale - $market) / $market;

                        // Negative (sale < market) is good for end customers / pack EV;
                        // strongly positive means you're charging more than the cards are worth.
                        return match (true) {
                            $marginVsMarket < -0.05 => 'success',  // pack EV > sale = great for customer
                            $marginVsMarket < 0.10 => 'info',     // roughly fair
                            $marginVsMarket < 0.30 => 'warning',  // store-favourable
                            default => 'danger',   // too rich, customers will feel it
                        };
                    })
                    ->badge()
                    ->default('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'draft' => 'Draft (not generated)',
                        'committed' => 'Live (in store)',
                        'dispatched' => 'Dispatched',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled (failed)',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'gray',
                        'committed' => 'success',
                        'dispatched' => 'warning',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('merged_into_batch_id')
                    ->label('Merged')
                    ->badge()
                    ->color(fn ($state) => $state ? 'gray' : 'success')
                    ->formatStateUsing(fn ($state, Batch $record) => $state
                        ? 'Merged → '.$record->mergedInto?->reference
                        : 'Active')
                    ->toggleable(),
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
            ->filters([
                Tables\Filters\Filter::make('committed_between')
                    ->label('Sold between')
                    ->schema([
                        Forms\Components\DatePicker::make('committed_from'),
                        Forms\Components\DatePicker::make('committed_until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['committed_from'] ?? null, fn ($q, $date) => $q->whereDate('committed_at', '>=', $date))
                            ->when($data['committed_until'] ?? null, fn ($q, $date) => $q->whereDate('committed_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                static::retryAction(),
                static::deleteBatchAction(),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Batch $record) => ! $record->packs()->where('status', 'sold')->exists(),
            )
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()->exporter(BatchSalesExporter::class),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function generateAction(): Action
    {
        return Action::make('generate')
            ->label('Generate cards')
            ->icon(Heroicon::OutlinedSparkles)
            ->visible(fn (Batch $record) => $record->status === 'draft')
            ->requiresConfirmation()
            ->action(function (Batch $record) {
                GenerateBatchJob::dispatch($record->id);
                Notification::make()
                    ->title('Batch generation started')
                    ->body('Cards will be assigned shortly.')
                    ->success()
                    ->send();
            });
    }

    public static function qrSheetAction(): Action
    {
        return Action::make('qrSheet')
            ->label('QR sheet')
            ->icon(Heroicon::OutlinedQrCode)
            ->url(fn (Batch $record) => route('batches.qr-sheet', $record))
            ->openUrlInNewTab()
            ->visible(fn (Batch $record) => $record->status === 'committed');
    }

    public static function verifyAction(): Action
    {
        return Action::make('verify')
            ->label('Verification page')
            ->icon(Heroicon::OutlinedShieldCheck)
            ->color('gray')
            ->url(fn (Batch $record) => route('stores.lists.verify', ['store' => $record->store, 'batch' => $record]))
            ->openUrlInNewTab()
            ->visible(fn (Batch $record) => $record->isVerificationRevealed());
    }

    public static function retryAction(): Action
    {
        return Action::make('retry')
            ->label('Retry')
            ->icon(Heroicon::OutlinedArrowPath)
            ->visible(fn (Batch $record) => $record->status === 'cancelled')
            ->requiresConfirmation()
            ->action(function (Batch $record) {
                $record->update([
                    'status' => 'draft',
                    'failure_reason' => null,
                    'failed_at' => null,
                ]);
                GenerateBatchJob::dispatch($record->id);
                Notification::make()
                    ->title('Batch retry queued')
                    ->success()
                    ->send();
            });
    }

    public static function mergeIntoAction(): Action
    {
        return Action::make('mergeInto')
            ->label('Merge into…')
            ->icon(Heroicon::OutlinedArrowsPointingIn)
            ->color('warning')
            ->visible(fn (Batch $record) => ! $record->isMerged()
                && in_array($record->status, ['committed', 'dispatched'], true)
                && $record->packs()->where('status', 'sealed')->exists())
            ->requiresConfirmation()
            ->modalHeading('Merge batch')
            ->modalDescription('Moves this batch\'s remaining sealed packs into another batch\'s pool. Already-sold packs and this batch\'s invoice are untouched — this only reorganizes what\'s left to sell.')
            ->schema(fn (Batch $record) => [
                Forms\Components\Select::make('target_batch_id')
                    ->label('Merge into')
                    ->options(fn () => Batch::query()
                        ->where('store_id', $record->store_id)
                        ->where('id', '!=', $record->id)
                        ->whereIn('status', ['committed', 'dispatched'])
                        ->whereNull('merged_into_batch_id')
                        ->orderByDesc('created_at')
                        ->pluck('reference', 'id'))
                    ->searchable()
                    ->required()
                    ->helperText('Only other live batches at the same store are shown.'),
            ])
            ->action(function (Batch $record, array $data, BatchMerger $merger) {
                try {
                    $merger->merge($record, Batch::findOrFail($data['target_batch_id']));
                } catch (\RuntimeException $e) {
                    Notification::make()
                        ->title('Merge failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Batch merged')
                    ->body("Remaining sealed packs moved. {$record->reference} is now flagged as merged.")
                    ->success()
                    ->send();
            });
    }

    /**
     * A batch with any sold packs is never deletable — its packs are already out in
     * the world (QR-scanned, invoiced) so removing the batch record would orphan
     * that history. Shared between the listing page and the Edit page so both go
     * through BatchDeleter (proper card/pack/invoice cleanup) with the same guard.
     */
    public static function deleteBatchAction(): Action
    {
        return Action::make('deleteBatch')
            ->label('Delete batch')
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->visible(fn (Batch $record) => ! $record->packs()->where('status', 'sold')->exists())
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
            ->action(function (Batch $record, array $data, BatchDeleter $deleter) {
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
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBatches::route('/'),
            'create' => Pages\CreateBatch::route('/create'),
            'edit' => Pages\EditBatch::route('/{record}/edit'),
            'view' => Pages\ViewBatch::route('/{record}'),
        ];
    }
}
