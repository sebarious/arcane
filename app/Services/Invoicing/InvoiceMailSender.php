<?php

namespace App\Services\Invoicing;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;

class InvoiceMailSender
{
    public function send(Invoice $invoice): void
    {
        $invoice->loadMissing('store');

        Mail::to($invoice->store->contact_email)->send(new InvoiceMail($invoice));

        $invoice->update(['last_emailed_at' => now()]);
    }
}
