<?php

namespace App\Filament\Resources\CardInventories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CardInventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('card_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('condition')
                    ->searchable(),
                TextColumn::make('cost_pence')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('acquired_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('acquired_from')
                    ->searchable(),
                TextColumn::make('acquisition_lot')
                    ->searchable(),
                TextColumn::make('market_value_pence')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('market_value_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('rarity_band')
                    ->searchable(),
                TextColumn::make('pack_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('allocated_sale_price_pence')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('margin_pence')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delisted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delisted_by_user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
