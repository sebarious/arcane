<?php

namespace App\Mail;

use App\Models\CreditNote;
use App\Services\Invoicing\CreditNotePdfBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreditNoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CreditNote $creditNote,
    ) {}

    public function build(): static
    {
        $pdf = app(CreditNotePdfBuilder::class)->build($this->creditNote);

        return $this->subject("Credit Note {$this->creditNote->number} from Arcane")
            ->view('emails.credit-note')
            ->attachData($pdf->output(), "{$this->creditNote->number}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
