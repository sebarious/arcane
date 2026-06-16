<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use App\Filament\Widgets\Concerns\HasGameFilter;

class AttentionList extends BaseWidget
{
    use HasGameFilter;

    protected static ?string $heading = 'Draft batches awaiting generation';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->applyGameFilter(Batch::query()
                    ->where('status', 'draft')
                    ->latest())
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Ref'),
                Tables\Columns\TextColumn::make('store.name')->label('Store'),
                Tables\Columns\TextColumn::make('game')
                    ->formatStateUsing(fn($state) =>
                    $state instanceof \App\Enums\Game ? $state->label() : (string) $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn($state) =>
                    $state instanceof \App\Enums\BatchType ? $state->label() : (string) $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('pack_count')->label('Packs'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y H:i'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Open')
                    ->url(fn(Batch $record) => route('filament.admin.resources.batches.edit', $record)),
            ])
            ->paginated(false);
    }
}
