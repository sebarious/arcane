<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * To every admin, the moment someone completes the affiliate signup form —
 * mirrors SellerOnboardingSubmittedMail's shape exactly (same fan-out
 * pattern in SignupController::store(), same "here's who, go review them"
 * purpose).
 */
class AffiliateSignupSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Affiliate $affiliate,
    ) {}

    public function build(): static
    {
        return $this->subject('New affiliate signup — approval needed')
            ->view('emails.affiliate-signup-submitted');
    }
}
