<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\Invoicing\InvoiceMailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $invoiceId,
    ) {}

    public function handle(InvoiceMailSender $sender): void
    {
        $invoice = Invoice::find($this->invoiceId);
        if (! $invoice) return;

        $sender->send($invoice);
    }
}
