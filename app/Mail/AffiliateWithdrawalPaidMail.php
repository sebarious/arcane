<?php

namespace App\Mail;

use App\Models\AffiliateWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AffiliateWithdrawalPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AffiliateWithdrawal $withdrawal,
    ) {}

    public function build(): static
    {
        return $this->subject('Your Arcane withdrawal has been paid')
            ->view('emails.affiliate-withdrawal-paid');
    }
}
