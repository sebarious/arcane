<?php

namespace App\Jobs;

use App\Models\CreditNote;
use App\Services\Invoicing\CreditNoteMailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCreditNoteEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $creditNoteId,
    ) {}

    public function handle(CreditNoteMailSender $sender): void
    {
        $creditNote = CreditNote::find($this->creditNoteId);
        if (! $creditNote) {
            return;
        }

        $sender->send($creditNote);
    }
}
