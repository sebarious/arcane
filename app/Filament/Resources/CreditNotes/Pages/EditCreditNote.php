<?php

namespace App\Filament\Resources\CreditNotes\Pages;

use App\Filament\Resources\CreditNotes\CreditNoteResource;
use App\Support\Money;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCreditNote extends EditRecord
{
    protected static string $resource = CreditNoteResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['amount_pounds'])) {
            $data['amount_pence'] = Money::toPence($data['amount_pounds']);
            unset($data['amount_pounds']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreditNoteResource::applyToInvoiceAction(),
            CreditNoteResource::markPaidAction(),
            CreditNoteResource::resendEmailAction(),
            // Safe to delete only while nothing has been settled yet — once
            // applied/paid, the row is the audit trail for real money moved.
            DeleteAction::make()
                ->visible(fn () => $this->record->status === 'issued'),
        ];
    }
}
