<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AffiliateApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Affiliate $affiliate,
    ) {}

    public function build(): static
    {
        return $this->subject('Your Arcane affiliate account is approved')
            ->view('emails.affiliate-approved');
    }
}
