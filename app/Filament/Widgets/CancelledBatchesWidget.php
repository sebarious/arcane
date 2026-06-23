<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Support\Icons\Heroicon;

class CancelledBatchesWidget extends BaseWidget
{
    protected static ?string $heading = 'Cancelled batches';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Batch::query()
                    ->where('status', 'cancelled')
                    ->latest('failed_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Ref'),
                Tables\Columns\TextColumn::make('store.name')
                    ->label('Store'),
                Tables\Columns\TextColumn::make('game')
                    ->formatStateUsing(
                        fn($state) =>
                        $state instanceof \App\Enums\Game ? $state->label() : (string) $state
                    )
                    ->badge(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(
                        fn($state) =>
                        $state instanceof \App\Enums\BatchType ? $state->label() : (string) $state
                    )
                    ->badge(),
                Tables\Columns\TextColumn::make('failure_reason')
                    ->label('Reason')
                    ->limit(120)
                    ->wrap(),
                Tables\Columns\TextColumn::make('failed_at')
                    ->dateTime('d M Y H:i'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->url(fn(Batch $record) => route('filament.admin.resources.batches.edit', $record)),

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
            ])
            ->paginated([5, 10]);
    }
}
