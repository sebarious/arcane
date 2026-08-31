<?php

namespace App\Mail;

use App\Models\Rip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to every admin the moment a customer chooses to keep a Digital Rip —
 * the physical card now needs pulling and posting out. Same
 * Mail::to($admin->email)->send() + database-Notification pattern as
 * CustomerSellSubmissionAdminAlertMail, see SubmissionStoreController.
 */
class RipKeptAdminAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Rip $rip,
    ) {}

    public function build(): static
    {
        return $this->subject('Digital Rip needs posting')
            ->view('emails.rip-kept-admin-alert');
    }
}
