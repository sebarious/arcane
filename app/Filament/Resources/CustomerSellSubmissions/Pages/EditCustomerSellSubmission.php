<?php

namespace App\Filament\Resources\CustomerSellSubmissions\Pages;

use App\Filament\Resources\CustomerSellSubmissions\CustomerSellSubmissionResource;
use App\Services\Stores\StoreCreditService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
            Action::make('addStoreCredit')
                ->label('Credit store')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success')
                ->visible(fn () => filled($this->record->affiliate_store_id))
                ->fillForm(fn () => [
                    'amount' => $this->record->affiliate_bonus_pence
                        ? number_format($this->record->affiliate_bonus_pence / 100, 2, '.', '')
                        : null,
                    'reason' => "Appraised cards submitted via affiliate code — {$this->record->reference}",
                ])
                ->schema([
                    TextInput::make('amount')
                        ->label('Amount (£)')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0.01)
                        ->prefix('£')
                        ->required(),
                    Textarea::make('reason')
                        ->label('Reason')
                        ->required()
                        ->rows(2),
                ])
                ->modalHeading(fn () => "Credit {$this->record->affiliateStore?->name}")
                ->modalDescription('Added to the store\'s wallet immediately and automatically deducted from its next invoice(s).')
                ->action(function (array $data) {
                    app(StoreCreditService::class)->addCredit(
                        $this->record->affiliateStore,
                        Money::toPence($data['amount']),
                        $data['reason'],
                        submission: $this->record,
                        addedBy: auth()->user(),
                    );

                    Notification::make()
                        ->title('Credit added')
                        ->body("{$this->record->affiliateStore?->name}'s wallet has been credited.")
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
