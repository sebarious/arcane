<?php

namespace App\Services\Invoicing;

use App\Models\CreditNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;

class CreditNotePdfBuilder
{
    public function build(CreditNote $creditNote): PdfDocument
    {
        $creditNote->loadMissing(['store', 'invoice']);

        return Pdf::loadView('pdf.credit-note', [
            'creditNote' => $creditNote,
        ])->setPaper('a4', 'portrait');
    }
}
