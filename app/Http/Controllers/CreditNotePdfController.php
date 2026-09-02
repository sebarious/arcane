<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Services\Invoicing\CreditNotePdfBuilder;
use Illuminate\Http\Request;

class CreditNotePdfController extends Controller
{
    public function __invoke(Request $request, CreditNote $creditNote, CreditNotePdfBuilder $pdfBuilder)
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        // Admins can view any; sellers only their own store's credit notes
        if (! $user->hasRole('admin')) {
            if (! $user->stores()->where('id', $creditNote->store_id)->exists()) {
                abort(403);
            }
        }

        return $pdfBuilder->build($creditNote)->download("{$creditNote->number}.pdf");
    }
}
