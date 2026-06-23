<?php

namespace App\Mail;

use App\Models\Batch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BatchRequestSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Batch $batch,
        public User $requestingUser,
    ) {}

    public function build(): static
    {
        return $this->subject('New Arcane batch request')
            ->view('emails.batch-request-submitted')
            ->with([
                'batch' => $this->batch,
                'requestingUser' => $this->requestingUser,
            ]);
    }
}
