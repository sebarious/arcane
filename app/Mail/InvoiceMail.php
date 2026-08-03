<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\Invoicing\InvoicePdfBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
    ) {}

    public function build(): static
    {
        $pdf = app(InvoicePdfBuilder::class)->build($this->invoice);

        return $this->subject("Invoice {$this->invoice->number} from Arcane")
            ->view('emails.invoice')
            ->attachData($pdf->output(), "{$this->invoice->number}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
