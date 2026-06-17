<?php

namespace App\Filament\Resources\SellerApplications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SellerApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('business_name')
                    ->required(),
                TextInput::make('contact_name')
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
                Textarea::make('about')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                Textarea::make('admin_notes')
                    ->columnSpanFull(),
                TextInput::make('reviewed_by_user_id')
                    ->numeric(),
                DateTimePicker::make('reviewed_at'),
            ]);
    }
}
