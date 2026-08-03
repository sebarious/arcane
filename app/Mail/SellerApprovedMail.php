<?php

namespace App\Mail;

use App\Models\Store;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Store $store,
        public string $resetUrl,
    ) {}

    public function build(): static
    {
        return $this->subject('Your Arcane seller account has been approved')
            ->view('emails.seller-approved')
            ->attach(resource_path('documents/arcane-seller-onboarding.pdf'), [
                'as'   => 'Arcane Seller Onboarding.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
