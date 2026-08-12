<?php

namespace App\Filament\Resources\KioskOrders;

use App\Filament\Resources\KioskOrders\RelationManagers\ItemsRelationManager;
use App\Models\KioskOrder;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * The kiosk fulfilment queue — a completed order is money already taken, so
 * this resource is deliberately read-only (no create/edit) beyond the one
 * "Mark fulfilled" workflow action. Each order's items carry the lot/position
 * computed at payment-confirmation time by the same chaos-storage math as
 * batch picking sheets (PickingSheetGenerator::pickTargets()) — see
 * RelationManagers\ItemsRelationManager.
 */
class KioskOrderResource extends Resource
{
    protected static ?string $model = KioskOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'Kiosk';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Order')
                ->columns(2)
                ->schema([
                    TextEntry::make('reference'),
                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'pending_payment' => 'Awaiting payment',
                            'paid' => 'Paid',
                            'expired' => 'Expired',
                            'cancelled' => 'Cancelled',
                            default => ucfirst($state),
                        })
                        ->color(fn (string $state) => match ($state) {
                            'pending_payment' => 'warning',
                            'paid' => 'success',
                            'expired', 'cancelled' => 'gray',
                            default => 'gray',
                        }),
                    TextEntry::make('total_pence')
                        ->label('Total')
                        ->formatStateUsing(fn ($state) => Money::format($state)),
                    TextEntry::make('stripe_payment_intent_id')
                        ->label('Stripe PaymentIntent')
                        ->copyable()
                        ->placeholder('—'),
                    TextEntry::make('paid_at')
                        ->dateTime('d M Y H:i')
                        ->placeholder('—'),
                    TextEntry::make('fulfilled_at')
                        ->label('Picked up')
                        ->dateTime('d M Y H:i')
                        ->placeholder('Not yet picked up'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending_payment' => 'Awaiting payment',
                        'paid' => 'Paid',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state) => match ($state) {
                        'pending_payment' => 'warning',
                        'paid' => 'success',
                        'expired', 'cancelled' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Cards'),
                Tables\Columns\TextColumn::make('total_pence')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd(),
                Tables\Columns\IconColumn::make('fulfilled_at')
                    ->label('Picked up')
                    ->boolean()
                    ->getStateUsing(fn (KioskOrder $record) => filled($record->fulfilled_at)),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_payment' => 'Awaiting payment',
                        'paid' => 'Paid',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\Filter::make('needs_picking')
                    ->label('Needs picking')
                    ->query(fn ($query) => $query->where('status', 'paid')->whereNull('fulfilled_at'))
                    ->default(),
            ])
            ->recordActions([
                static::markFulfilledAction(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markFulfilled')
                        ->label('Mark fulfilled')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->whereNull('fulfilled_at')->each->update(['fulfilled_at' => now()]);
                            Notification::make()->title('Marked fulfilled')->success()->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** Staff check-off once the cards have actually been picked and handed over. */
    public static function markFulfilledAction(): Action
    {
        return Action::make('markFulfilled')
            ->label('Mark fulfilled')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (KioskOrder $record) => $record->status === 'paid' && blank($record->fulfilled_at))
            ->action(function (KioskOrder $record) {
                $record->update(['fulfilled_at' => now()]);
                Notification::make()->title('Order marked fulfilled')->success()->send();
            });
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKioskOrders::route('/'),
            'view' => Pages\ViewKioskOrder::route('/{record}'),
        ];
    }
}
