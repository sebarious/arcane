<?php

namespace App\Services\Invoicing;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;

class InvoicePdfBuilder
{
    public function build(Invoice $invoice): PdfDocument
    {
        $invoice->loadMissing(['store', 'batch']);

        return Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ])->setPaper('a4', 'portrait');
    }
}
