<?php

namespace App\Filament\Resources\CardInventories\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CardInventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('card_id')
                    ->required()
                    ->numeric(),
                TextInput::make('condition')
                    ->required()
                    ->default('NM'),
                TextInput::make('cost_pence')
                    ->required()
                    ->numeric(),
                DatePicker::make('acquired_at')
                    ->required(),
                TextInput::make('acquired_from'),
                TextInput::make('acquisition_lot'),
                TextInput::make('market_value_pence')
                    ->numeric(),
                DateTimePicker::make('market_value_updated_at'),
                TextInput::make('rarity_band'),
                TextInput::make('pack_id')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('in_stock'),
                TextInput::make('allocated_sale_price_pence')
                    ->numeric(),
                TextInput::make('margin_pence')
                    ->numeric(),
                DateTimePicker::make('delisted_at'),
                TextInput::make('delisted_by_user_id')
                    ->numeric(),
            ]);
    }
}
