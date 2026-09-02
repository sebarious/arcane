<?php

namespace App\Filament\Resources\CreditNotes\Pages;

use App\Filament\Resources\CreditNotes\CreditNoteResource;
use App\Jobs\SendCreditNoteEmailJob;
use App\Models\CreditNote;
use App\Models\Store;
use App\Services\Invoicing\CreditNoteService;
use App\Support\Money;
use Filament\Resources\Pages\CreateRecord;

class CreateCreditNote extends CreateRecord
{
    protected static string $resource = CreditNoteResource::class;

    // Goes through CreditNoteService rather than a plain model fill so the
    // number generator, defaults (status: issued), and validation stay in
    // one place regardless of what else ever creates a credit note.
    protected function handleRecordCreation(array $data): CreditNote
    {
        return app(CreditNoteService::class)->create(
            Store::findOrFail($data['store_id']),
            Money::toPence($data['amount_pounds']),
            $data['reason'],
            auth()->user(),
        );
    }

    // "The credit note should be sent to the store owner once generated" —
    // fires right after creation, queued and deferred until the transaction
    // (such as it is here) commits, same pattern as BatchGenerator's invoice email.
    protected function afterCreate(): void
    {
        SendCreditNoteEmailJob::dispatch($this->record->id)->afterCommit();
    }
}
