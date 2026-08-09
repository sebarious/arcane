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
use App\Services\Batches\BatchGenerator;
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
                    Forms\Components\Placeholder::make('margin_total')
                        ->label('Profit')
                        ->content(fn (Batch $record) => $record->margin_pence !== null ? Money::format($record->margin_pence) : '—'),
                    Forms\Components\Placeholder::make('margin_percentage')
                        ->label('Profit %')
                        ->content(fn (Batch $record) => static::profitPercent($record) ?? '—'),
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
                    ->label('Profit')
                    ->tooltip('Sale price minus what we paid for the cards — the batch\'s total profit.')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('margin_percentage')
                    ->label('Profit %')
                    ->tooltip('Profit as a percentage of what we paid for the cards — your real accounting margin.')
                    ->alignEnd()
                    ->getStateUsing(fn (Batch $record) => static::profitPercent($record))
                    ->color(fn (Batch $record) => static::profitPercentColor($record))
                    ->badge()
                    ->default('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'draft' => 'Draft (not generated)',
                        'pending_review' => 'Pending review',
                        'awaiting_payment' => 'Awaiting payment',
                        'committed' => 'Live (in store)',
                        'dispatched' => 'Dispatched',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled (failed)',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'gray',
                        'pending_review' => 'warning',
                        'awaiting_payment' => 'info',
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

    /**
     * Second step: raises the invoice and emails the seller — see
     * BatchGenerator::sendInvoice(). Doesn't touch the storefront; that's
     * publishAction()'s job, once the invoice this raises has actually been
     * paid.
     */
    public static function sendInvoiceAction(): Action
    {
        return Action::make('sendInvoice')
            ->label('Send invoice')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('warning')
            ->visible(fn (Batch $record) => $record->status === 'pending_review')
            ->requiresConfirmation()
            ->modalHeading('Send invoice')
            ->modalDescription('Generates the invoice and emails it to the seller. The batch stays off the storefront until it\'s marked paid and put live.')
            ->action(function (Batch $record) {
                app(BatchGenerator::class)->sendInvoice($record);
                Notification::make()
                    ->title('Invoice sent')
                    ->body('Emailed to the seller — mark it paid on the invoice, then put the batch live.')
                    ->success()
                    ->send();
            });
    }

    /**
     * Third and final step, once the invoice sendInvoiceAction() raised has
     * been sent — see BatchGenerator::publish(). Visible as soon as the batch
     * is awaiting payment; can be used before the invoice is marked paid.
     */
    public static function publishAction(): Action
    {
        return Action::make('publish')
            ->label('Put it live')
            ->icon(Heroicon::OutlinedRocketLaunch)
            ->color('success')
            ->visible(fn (Batch $record) => $record->status === 'awaiting_payment')
            ->requiresConfirmation()
            ->modalHeading('Put this batch live')
            ->modalDescription('Publishes it to the storefront — there\'s no way to undo this from here.')
            ->action(function (Batch $record) {
                app(BatchGenerator::class)->publish($record);
                Notification::make()
                    ->title('Batch is live')
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
            ->visible(fn (Batch $record) => in_array($record->status, ['awaiting_payment', 'committed'], true));
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

    /**
     * Profit as a percentage of cost — the real accounting margin (mirrors the
     * cost-based worst-case floor CandidateSelector enforces at generation
     * time). Shared by the table column and the edit page's "Margin analysis"
     * section so the two figures can't drift apart.
     */
    protected static function profitPercent(Batch $record): ?string
    {
        $cost = $record->total_cost_pence;
        $margin = $record->margin_pence;

        if ($cost === null || $cost <= 0 || $margin === null) {
            return null;
        }

        return number_format(($margin / $cost) * 100, 1).'%';
    }

    /**
     * Green once profit clears this batch type's target margin
     * (config('batches...target_margin_on_cost')), red below it.
     */
    protected static function profitPercentColor(Batch $record): string
    {
        $cost = $record->total_cost_pence;
        $margin = $record->margin_pence;

        if ($cost === null || $cost <= 0 || $margin === null || ! $record->game instanceof Game || ! $record->type instanceof BatchType) {
            return 'gray';
        }

        $targetMargin = BatchDesign::targetMargin($record->game, $record->type);

        return ($margin / $cost) >= $targetMargin ? 'success' : 'danger';
    }
}
