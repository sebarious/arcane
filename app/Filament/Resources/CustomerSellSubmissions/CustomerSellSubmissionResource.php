<?php

namespace App\Filament\Resources\CustomerSellSubmissions;

use App\Filament\Resources\CustomerSellSubmissions\Pages\EditCustomerSellSubmission;
use App\Filament\Resources\CustomerSellSubmissions\Pages\ListCustomerSellSubmissions;
use App\Filament\Resources\CustomerSellSubmissions\Tables\CustomerSellSubmissionsTable;
use App\Models\CustomerSellSubmission;
use App\Support\Money;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
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
            Section::make('Cards submitted')
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('estimated_value_display')
                        ->label('Indicative total offer')
                        ->state(fn (?CustomerSellSubmission $record) => Money::format($record?->estimated_value_pence)),
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->label('')
                        ->schema([
                            Forms\Components\TextInput::make('card_name')->label('Card')->disabled(),
                            Forms\Components\TextInput::make('set_name')->label('Set')->disabled(),
                            Forms\Components\TextInput::make('card_number')->label('Number')->disabled(),
                            Forms\Components\TextInput::make('quantity')->label('Qty')->disabled(),
                            Forms\Components\TextInput::make('band')->label('Band')->disabled(),
                            Forms\Components\TextInput::make('total_offer_pence')
                                ->label('Offer')
                                ->disabled()
                                ->formatStateUsing(fn ($state) => Money::format($state)),
                        ])
                        ->columns(6)
                        ->disabled()
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columnSpanFull(),
                ]),
            Section::make('Additional notes')
                ->columnSpanFull()
                ->visible(fn (?CustomerSellSubmission $record) => filled($record?->description))
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->label('')
                        ->rows(4)
                        ->disabled(),
                ]),
            Section::make('Photos')
                ->columnSpanFull()
                ->visible(fn (?CustomerSellSubmission $record) => filled($record?->images))
                ->schema([
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
            Section::make('Notes history')
                ->columnSpanFull()
                ->description('How this submission has been handled over time. Use "Add note" above to log an update.')
                ->schema([
                    Forms\Components\Repeater::make('notes')
                        ->relationship()
                        ->label('')
                        ->schema([
                            Forms\Components\TextInput::make('author_name')->label('By')->disabled(),
                            Forms\Components\TextInput::make('created_at')->label('When')->disabled(),
                            Forms\Components\Textarea::make('note')->label('Note')->disabled()->rows(2)->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->disabled()
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Cards')
                    ->getStateUsing(fn (CustomerSellSubmission $record) => $record->items()->count())
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('estimated_value_pence')
                    ->label('Indicative offer')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd(),
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
