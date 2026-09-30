<?php

namespace App\Jobs;

use App\Mail\KioskReceiptMail;
use App\Models\KioskOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendKioskReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId,
        public string $email,
    ) {}

    public function handle(): void
    {
        $order = KioskOrder::with('items')->find($this->orderId);

        if (! $order) {
            return;
        }

        Mail::to($this->email)->send(new KioskReceiptMail($order));

        // Stamped only once it has actually gone out, so the admin column
        // reflects a sent receipt rather than an attempted one.
        $order->forceFill([
            'customer_email' => $this->email,
            'receipt_sent_at' => now(),
        ])->save();
    }
}
