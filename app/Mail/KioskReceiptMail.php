<?php

namespace App\Mail;

use App\Models\KioskOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * A till receipt for a kiosk sale. No PDF attachment, unlike an invoice —
 * this is a shop receipt someone asked for at the counter, and it should be
 * readable the moment it opens rather than behind a download.
 */
class KioskReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public KioskOrder $order,
    ) {}

    public function build(): static
    {
        return $this->subject("Your receipt from Arcane — {$this->order->reference}")
            ->view('emails.kiosk-receipt', [
                'order' => $this->order,
                'items' => $this->order->items()->orderBy('id')->get(),
            ]);
    }
}
