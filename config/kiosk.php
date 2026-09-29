<?php

return [

    // 110% of PulseAPI market value.
    'markup_multiplier' => (float) env('KIOSK_MARKUP_MULTIPLIER', 1.10),

    // Marked-up prices are rounded UP to a whole multiple of this, so the
    // kiosk quotes £9.50 rather than £9.49. Always up, never to nearest —
    // rounding down would sell under the markup the price is built on.
    // Applies to what's displayed and what's charged alike, since both come
    // from KioskCheckoutService::priceFor().
    'price_rounding_pence' => (int) env('KIOSK_PRICE_ROUNDING_PENCE', 25),

    // How long adding a card to a basket holds it before it's released back
    // to general stock (and to BatchGenerator's candidate pool) — see
    // App\Services\Kiosk\KioskBasketService.
    'reservation_minutes' => (int) env('KIOSK_RESERVATION_MINUTES', 15),

];
