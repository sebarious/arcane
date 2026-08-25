<?php

namespace App\Mail;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerOnboardingReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Store $store,
    ) {}

    public function build(): static
    {
        return $this->subject('We\'ve received your Arcane onboarding details')
            ->view('emails.seller-onboarding-received');
    }
}
