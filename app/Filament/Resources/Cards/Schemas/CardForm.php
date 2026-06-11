<?php

namespace App\Filament\Resources\Cards\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('set_code')
                    ->required(),
                TextInput::make('set_name')
                    ->required(),
                TextInput::make('card_number')
                    ->required(),
                TextInput::make('variant'),
                TextInput::make('language')
                    ->required()
                    ->default('en'),
                TextInput::make('printed_rarity'),
                FileUpload::make('image_front')
                    ->image(),
                FileUpload::make('image_back')
                    ->image(),
                TextInput::make('external_ids')
                    ->required()
                    ->default('{}'),
                TextInput::make('scrydex_id'),
                TextInput::make('language_code')
                    ->required()
                    ->default('EN'),
            ]);
    }
}
