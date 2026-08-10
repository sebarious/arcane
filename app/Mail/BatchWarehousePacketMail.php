<?php

namespace App\Mail;

use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BatchWarehousePacketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Batch $batch) {}

    public function build(): static
    {
        $mail = $this->subject("Warehouse packet — {$this->batch->reference}")
            ->view('emails.batch-warehouse-packet');

        if ($this->batch->qr_sheet_pdf_path && Storage::disk('local')->exists($this->batch->qr_sheet_pdf_path)) {
            $mail->attachData(
                Storage::disk('local')->get($this->batch->qr_sheet_pdf_path),
                "{$this->batch->reference}-qr-sheet.pdf",
                ['mime' => 'application/pdf'],
            );
        }

        if ($this->batch->picking_sheet_pdf_path && Storage::disk('local')->exists($this->batch->picking_sheet_pdf_path)) {
            $mail->attachData(
                Storage::disk('local')->get($this->batch->picking_sheet_pdf_path),
                "{$this->batch->reference}-picking-sheet.pdf",
                ['mime' => 'application/pdf'],
            );
        }

        return $mail;
    }
}
