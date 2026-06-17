<?php

namespace App\Mail;

use App\Models\CustomerSellSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerSellSubmissionReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CustomerSellSubmission $submission,
    ) {}

    public function build(): static
    {
        return $this->subject('We received your Arcane sell submission')
            ->view('emails.customer-sell-submission-received');
    }
}
