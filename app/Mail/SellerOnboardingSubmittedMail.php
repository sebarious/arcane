<?php

namespace App\Mail;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerOnboardingSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Store $store,
    ) {}

    public function build(): static
    {
        return $this->subject('New seller onboarding submission ready to review')
            ->view('emails.seller-onboarding-submitted');
    }
}
