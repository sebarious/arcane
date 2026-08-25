<?php

namespace App\Mail;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * A standalone resend of the onboarding link (see StoreResource::
 * sendOnboardingInviteAction()) — for sellers approved before the in-app
 * onboarding page existed, or anyone who lost/never got their original
 * SellerApprovedMail. Deliberately doesn't repeat "your account has been
 * approved" — this is a nudge to finish onboarding, not a fresh approval.
 */
class SellerOnboardingInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Store $store,
        public string $resetUrl,
    ) {}

    public function build(): static
    {
        return $this->subject('Complete your Arcane storefront onboarding')
            ->view('emails.seller-onboarding-invite');
    }
}
