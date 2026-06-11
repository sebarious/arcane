<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('contact_email')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('address_line_1')
                    ->required(),
                TextInput::make('address_line_2'),
                TextInput::make('city')
                    ->required(),
                TextInput::make('postcode')
                    ->required(),
                TextInput::make('country')
                    ->required()
                    ->default('GB'),
                TextInput::make('vat_number'),
                Toggle::make('public_page_enabled')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
            ]);
    }
}
