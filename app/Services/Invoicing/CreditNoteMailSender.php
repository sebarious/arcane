<?php

namespace App\Services\Invoicing;

use App\Mail\CreditNoteMail;
use App\Models\CreditNote;
use Illuminate\Support\Facades\Mail;

class CreditNoteMailSender
{
    public function send(CreditNote $creditNote): void
    {
        $creditNote->loadMissing('store');

        Mail::to($creditNote->store->contact_email)->send(new CreditNoteMail($creditNote));

        $creditNote->update(['last_emailed_at' => now()]);
    }
}
