<?php

namespace App\Mail;

use App\Models\CustomerSellSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerSellSubmissionAdminAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CustomerSellSubmission $submission,
    ) {}

    public function build(): static
    {
        return $this->subject('New Arcane customer sell submission')
            ->view('emails.customer-sell-submission-admin-alert');
    }
}
