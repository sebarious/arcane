<?php

return [

    // 110% of PulseAPI market value.
    'markup_multiplier' => (float) env('KIOSK_MARKUP_MULTIPLIER', 1.10),

    // How long adding a card to a basket holds it before it's released back
    // to general stock (and to BatchGenerator's candidate pool) — see
    // App\Services\Kiosk\KioskBasketService.
    'reservation_minutes' => (int) env('KIOSK_RESERVATION_MINUTES', 15),

];
