<?php

return [

    // Flip this on once the business crosses the UK VAT registration threshold.
    // Until then, invoices show no VAT at all — nothing to charge, itemize, or
    // note. We still compute/store the theoretical margin-scheme VAT internally
    // either way (see Batch::margin_scheme_vat_pence, Invoice::internal_margin_vat_pence)
    // for our own accounting — this toggle only controls what's shown to stores.
    'registered' => (bool) env('VAT_REGISTERED', false),

];
