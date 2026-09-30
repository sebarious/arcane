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

    // The landing view (no search, no letter, no filter) shows a shuffled
    // sample drawn from this many of the most recently released sets, so
    // there's something worth looking at before anyone types. Raise it if
    // stock in the newest sets gets thin.
    'featured_recent_sets' => (int) env('KIOSK_FEATURED_RECENT_SETS', 8),

    // Whether a tablet has to be unlocked with the day's PIN before the kiosk
    // will open (the PIN is shown in the admin topbar — see KioskDailyPin).
    // On by default: the kiosk can take payments and apply discounts, and it
    // has no login of its own, so without this anyone holding it can discount
    // their own basket. Only turn it off if the tablet lives behind the
    // counter and is never handed to a customer.
    'require_pin' => (bool) env('KIOSK_REQUIRE_PIN', true),

    // How long adding a card to a basket holds it before it's released back
    // to general stock (and to BatchGenerator's candidate pool) — see
    // App\Services\Kiosk\KioskBasketService.
    'reservation_minutes' => (int) env('KIOSK_RESERVATION_MINUTES', 15),

];
