<?php

namespace App\Mail;

use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BatchShippedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Batch $batch,
    ) {}

    public function build(): static
    {
        return $this->subject("Your batch {$this->batch->reference} has shipped")
            ->view('emails.batch-shipped');
    }
}
