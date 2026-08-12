<?php

namespace App\Filament\Resources\KioskOrders\RelationManagers;

use App\Support\Money;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * The actual picking reference for staff — lot + position, computed once at
 * payment-confirmation time by the same chaos-storage math batch picking
 * sheets use (PickingSheetGenerator::pickTargets()) and never recomputed.
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Cards to pick';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('card_name')
            ->columns([
                Tables\Columns\TextColumn::make('lot')
                    ->label('Lot')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->weight('bold')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('card_name')
                    ->label('Card')
                    ->description(fn ($record) => trim(($record->set_name ?? '').' · '.($record->card_number ?? ''), ' ·')),
                Tables\Columns\TextColumn::make('rarity')
                    ->badge()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('unit_price_pence')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd(),
            ])
            ->defaultSort('lot')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
