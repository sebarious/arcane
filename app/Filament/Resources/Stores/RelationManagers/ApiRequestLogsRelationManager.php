<?php

namespace App\Filament\Resources\Stores\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ApiRequestLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'apiRequestLogs';

    protected static ?string $title = 'API logs';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('path')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->badge()
                    ->color(fn (string $state) => $state === 'GET' ? 'info' : 'warning'),
                Tables\Columns\TextColumn::make('path')
                    ->wrap(),
                Tables\Columns\TextColumn::make('status_code')
                    ->label('Status')
                    ->badge()
                    ->color(fn (int $state) => match (true) {
                        $state >= 500 => 'danger',
                        $state >= 400 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('duration_ms')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => "{$state} ms")
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('response_summary')
                    ->label('Response')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state) : '—')
                    ->limit(80)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
