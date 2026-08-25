<?php

namespace App\Mail;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerOnboardingApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Store $store,
    ) {}

    public function build(): static
    {
        return $this->subject('Your Arcane storefront is live')
            ->view('emails.seller-onboarding-approved');
    }
}
