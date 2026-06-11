<?php

namespace App\Filament\Resources\Cards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('set_code')
                    ->searchable(),
                TextColumn::make('set_name')
                    ->searchable(),
                TextColumn::make('card_number')
                    ->searchable(),
                TextColumn::make('variant')
                    ->searchable(),
                TextColumn::make('language')
                    ->searchable(),
                TextColumn::make('printed_rarity')
                    ->searchable(),
                ImageColumn::make('image_front'),
                ImageColumn::make('image_back'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('scrydex_id')
                    ->searchable(),
                TextColumn::make('language_code')
                    ->searchable(),
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
