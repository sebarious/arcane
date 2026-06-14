<?php

namespace App\Filament\Resources\CustomerSellSubmissions;

use App\Filament\Resources\CustomerSellSubmissions\Pages\EditCustomerSellSubmission;
use App\Filament\Resources\CustomerSellSubmissions\Pages\ListCustomerSellSubmissions;
use App\Filament\Resources\CustomerSellSubmissions\Tables\CustomerSellSubmissionsTable;
use App\Models\CustomerSellSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms;
use UnitEnum;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables;

class CustomerSellSubmissionResource extends Resource
{
    protected static ?string $model = CustomerSellSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Submissions';
    protected static ?string $navigationLabel = 'Submissions';
    protected static string|UnitEnum|null $navigationGroup = 'Acquisitions';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customer')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('customer_name')->disabled(),
                    Forms\Components\TextInput::make('customer_email')->disabled(),
                    Forms\Components\TextInput::make('customer_phone')->disabled(),
                    Forms\Components\TextInput::make('customer_postcode')->disabled(),
                ]),
            Section::make('Submission')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->rows(4)
                        ->disabled(),
                    Forms\Components\ViewField::make('images')
                        ->label('Images')
                        ->view('filament.forms.components.sell-images')
                        ->dehydrated(false),
                ]),
            Section::make('Review')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'submitted'    => 'Submitted',
                            'under_review' => 'Under review',
                            'offer_made'   => 'Offer made',
                            'accepted'     => 'Accepted',
                            'declined'     => 'Declined',
                            'completed'    => 'Completed',
                            'withdrawn'    => 'Withdrawn',
                        ])
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'submitted'    => 'Submitted',
                        'under_review' => 'Under review',
                        'offer_made'   => 'Offer made',
                        'accepted'     => 'Accepted',
                        'declined'     => 'Declined',
                        'completed'    => 'Completed',
                        'withdrawn'    => 'Withdrawn',
                        default        => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerSellSubmissions::route('/'),
            'edit' => EditCustomerSellSubmission::route('/{record}/edit'),
        ];
    }
}
