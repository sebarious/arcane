<?php

namespace App\Filament\Resources\CustomerSellSubmissions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CustomerSellSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->required(),
                TextInput::make('customer_name'),
                TextInput::make('customer_email')
                    ->email(),
                TextInput::make('customer_phone')
                    ->tel(),
                TextInput::make('customer_postcode'),
                TextInput::make('images')
                    ->required()
                    ->default('[]'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('submitted'),
                TextInput::make('estimated_value_pence')
                    ->numeric(),
                TextInput::make('offered_value_pence')
                    ->numeric(),
                Textarea::make('admin_notes')
                    ->columnSpanFull(),
                Textarea::make('decline_reason')
                    ->columnSpanFull(),
                TextInput::make('reviewed_by_user_id')
                    ->numeric(),
                DateTimePicker::make('reviewed_at'),
                DateTimePicker::make('offered_at'),
                DateTimePicker::make('responded_at'),
                DatePicker::make('offer_expires_on'),
            ]);
    }
}
