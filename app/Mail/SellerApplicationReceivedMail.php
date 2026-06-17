<?php

namespace App\Mail;

use App\Models\SellerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SellerApplication $application,
    ) {}

    public function build(): static
    {
        return $this->subject('We received your Arcane seller application')
            ->view('emails.seller-application-received');
    }
}
