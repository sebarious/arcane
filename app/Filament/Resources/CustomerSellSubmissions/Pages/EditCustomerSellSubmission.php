<?php

namespace App\Filament\Resources\CustomerSellSubmissions\Pages;

use App\Filament\Resources\CustomerSellSubmissions\CustomerSellSubmissionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditCustomerSellSubmission extends EditRecord
{
    protected static string $resource = CustomerSellSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addNote')
                ->label('Add note')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('gray')
                ->schema([
                    Textarea::make('note')
                        ->label('Note')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $this->record->notes()->create([
                        'user_id'     => auth()->id(),
                        'author_name' => auth()->user()?->name ?? 'System',
                        'note'        => $data['note'],
                    ]);

                    $this->refreshFormData(['notes']);

                    Notification::make()
                        ->title('Note added')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
