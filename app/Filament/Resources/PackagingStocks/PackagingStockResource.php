<?php

namespace App\Filament\Resources\PackagingStocks;

use App\Models\PackagingStock;
use App\Services\Packaging\PackagingStockService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class PackagingStockResource extends Resource
{
    protected static ?string $model = PackagingStock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBoxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Packaging stock';

    protected static ?int $navigationSort = 21;

    protected static ?string $modelLabel = 'Packaging stock';

    protected static ?string $pluralModelLabel = 'Packaging stock';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Item'),
                Tables\Columns\TextColumn::make('quantity_on_hand')
                    ->label('On hand')
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->color(fn (PackagingStock $record) => $record->quantity_on_hand <= 0 ? 'danger' : null),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                static::adjustStockAction(),
            ])
            ->toolbarActions([
                static::recordDeliveryAction(),
            ])
            ->defaultSort('id');
    }

    /**
     * One-shot intake form covering every packaging item at once, since a
     * real delivery (e.g. a fresh box of bags plus a set of rarity inserts)
     * arrives as one event — mirrors how BatchGenerator deducts several of
     * these together for one batch. Blank/zero fields are skipped.
     */
    public static function recordDeliveryAction(): Action
    {
        return Action::make('recordDelivery')
            ->label('Record delivery')
            ->icon(Heroicon::OutlinedTruck)
            ->color('success')
            ->schema(
                collect(PackagingStock::LABELS)
                    ->map(fn (string $label, string $key) => Forms\Components\TextInput::make($key)
                        ->label($label)
                        ->numeric()
                        ->minValue(0)
                        ->default(0))
                    ->values()
                    ->push(
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->rows(2)
                            ->placeholder('e.g. Delivery from supplier — PO #1234')
                    )
                    ->all()
            )
            ->modalHeading('Record delivery')
            ->modalDescription('Add newly arrived bags/inserts to stock. Leave anything not received at 0.')
            ->action(function (array $data) {
                $service = app(PackagingStockService::class);
                $added = [];

                foreach (PackagingStock::LABELS as $key => $label) {
                    $quantity = (int) ($data[$key] ?? 0);
                    if ($quantity <= 0) {
                        continue;
                    }

                    $service->addStock($key, $quantity, $data['reason'], auth()->user());
                    $added[] = "{$quantity} {$label}";
                }

                if (empty($added)) {
                    Notification::make()
                        ->title('Nothing recorded')
                        ->body('Every quantity was 0 — nothing was added to stock.')
                        ->warning()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Stock updated')
                    ->body('Added: '.implode(', ', $added).'.')
                    ->success()
                    ->send();
            });
    }

    /**
     * A manual +/- correction for a single item (miscount, damaged stock,
     * etc.) — unlike recordDeliveryAction, this can also go negative.
     */
    public static function adjustStockAction(): Action
    {
        return Action::make('adjustStock')
            ->label('Adjust')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->schema([
                Forms\Components\TextInput::make('delta')
                    ->label('Change')
                    ->numeric()
                    ->required()
                    ->helperText('Positive to add, negative to remove.'),
                Forms\Components\Textarea::make('reason')
                    ->label('Reason')
                    ->required()
                    ->rows(2)
                    ->placeholder('e.g. Recount — 5 bags found damaged'),
            ])
            ->modalHeading(fn (PackagingStock $record) => "Adjust {$record->label}")
            ->action(function (array $data, PackagingStock $record) {
                $delta = (int) $data['delta'];

                if ($delta === 0) {
                    Notification::make()
                        ->title('No change')
                        ->warning()
                        ->send();

                    return;
                }

                $service = app(PackagingStockService::class);

                try {
                    if ($delta > 0) {
                        $service->addStock($record->key, $delta, $data['reason'], auth()->user());
                    } else {
                        $service->deduct($record->key, abs($delta), $data['reason'], performedBy: auth()->user());
                    }
                } catch (\RuntimeException $e) {
                    Notification::make()
                        ->title('Adjustment failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Stock adjusted')
                    ->body($record->fresh()->label.' is now '.$record->fresh()->quantity_on_hand.'.')
                    ->success()
                    ->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackagingStocks::route('/'),
        ];
    }
}
