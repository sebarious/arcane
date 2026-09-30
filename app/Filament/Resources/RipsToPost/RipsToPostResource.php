<?php

namespace App\Filament\Resources\RipsToPost;

use App\Filament\Resources\RipsToPost\Pages\ListRipsToPost;
use App\Models\Rip;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * Fulfilment queue for kept Digital Rips — every card a customer chose to
 * keep has to be physically pulled from stock and posted out. Grouped by
 * customer so a warehouse picker can process one person's parcel at a time,
 * and only ever shows what's still outstanding (dispatched_at IS NULL) — an
 * intentionally separate, narrower view from RipResource's full sales report.
 *
 * The joins below (rather than Group::make('order.user.name') /
 * TextColumn::make('order.user.name')) are deliberate: Filament's
 * relationship-based grouping/summarizing tries to auto-guess an inverse
 * relationship and throws when one doesn't cleanly exist (see RipResource's
 * own cost/profit columns for the exact same issue) — selecting real,
 * flat customer_name/customer_email columns via an explicit join sidesteps
 * that entirely.
 */
class RipsToPostResource extends Resource
{
    protected static ?string $model = Rip::class;

    protected static ?string $slug = 'rips-to-post';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|UnitEnum|null $navigationGroup = 'Digital Rips';

    protected static ?int $navigationSort = 15;

    protected static ?string $navigationLabel = 'Kept — to post';

    protected static ?string $modelLabel = 'Kept rip';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->join('rip_orders', 'rip_orders.id', '=', 'rips.rip_order_id')
            ->join('users', 'users.id', '=', 'rip_orders.user_id')
            ->where('rips.decision', 'kept')
            ->whereNull('rips.dispatched_at')
            ->select('rips.*', 'users.name as customer_name', 'users.email as customer_email')
            ->with(['order.user', 'card'])
            ->orderBy('users.name')
            ->orderBy('rips.decided_at');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                // Filament's default group-scoping does `where($column, $key)`
                // using the group's raw column name — 'customer_name' is a
                // SELECT alias, not a real column, and Postgres (unlike some
                // other engines) rejects referencing an output alias inside
                // WHERE. Scope by the real joined column instead.
                Group::make('customer_name')
                    ->label('Customer')
                    ->scopeQueryByKeyUsing(fn (Builder $query, ?string $key) => $query->where('users.name', $key)),
            ])
            ->defaultGroup('customer_name')
            ->groupingSettingsHidden()
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn (Rip $record) => $record->customer_email)
                    ->searchable(['users.name', 'users.email']),

                Tables\Columns\ImageColumn::make('card.image_url')
                    ->label('')
                    ->height(50),

                Tables\Columns\TextColumn::make('card.card_name')
                    ->label('Card')
                    ->description(fn (Rip $record) => $record->card?->set_name),

                Tables\Columns\TextColumn::make('pack_name')
                    ->label('Pack'),

                Tables\Columns\TextColumn::make('decided_at')
                    ->label('Kept on')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('viewAddress')
                    ->label('Address')
                    ->icon(Heroicon::OutlinedMapPin)
                    ->color('gray')
                    ->modalHeading(fn (Rip $record) => "Shipping address — {$record->customer_name}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->schema(fn (Rip $record) => [
                        Placeholder::make('address')
                            ->hiddenLabel()
                            ->content(
                                $record->order->user->shippingAddressLines()
                                    ? implode("\n", $record->order->user->shippingAddressLines())
                                    : 'No shipping address on file.'
                            )
                            ->extraAttributes(['style' => 'white-space: pre-line; font-size: 14px; line-height: 1.7;']),
                    ]),

                Action::make('markDispatched')
                    ->label('Mark dispatched')
                    ->icon(Heroicon::OutlinedTruck)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Rip $record) {
                        $record->update([
                            'dispatched_at' => now(),
                            'dispatched_by_user_id' => Auth::id(),
                        ]);

                        Notification::make()->title('Marked as dispatched')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('markDispatched')
                    ->label('Mark dispatched')
                    ->icon(Heroicon::OutlinedTruck)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            $record->update([
                                'dispatched_at' => now(),
                                'dispatched_by_user_id' => Auth::id(),
                            ]);
                        }

                        Notification::make()->title('Marked as dispatched')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRipsToPost::route('/'),
        ];
    }
}
