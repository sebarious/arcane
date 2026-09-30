<?php

namespace App\Services\Kiosk;

use Illuminate\Support\Carbon;

/**
 * The code staff type to unlock a kiosk tablet for the day.
 *
 * Derived from the app key and the date rather than stored anywhere: it needs
 * no table, no cron to rotate it, and no way to leak from the database. The
 * same PIN is shown to every admin all day and a new one takes over at
 * midnight on its own.
 *
 * It is not a password — it's a short number shown openly in the admin
 * topbar, and its only job is to stop a customer holding the tablet from
 * discounting their own basket. The kiosk is a shop-floor device, so the
 * realistic threat is the person standing in front of it, not someone
 * brute-forcing six digits from the internet (see EnsureKioskUnlocked for the
 * rate limiting that covers the rest).
 */
class KioskDailyPin
{
    public function for(?Carbon $date = null): string
    {
        $date ??= Carbon::now();

        $digest = hash_hmac('sha256', 'kiosk-pin:'.$date->toDateString(), (string) config('app.key'));

        // Six digits, zero-padded — long enough not to be guessed on the spot,
        // short enough to read off a screen and thumb into a tablet.
        return str_pad((string) (hexdec(substr($digest, 0, 10)) % 1_000_000), 6, '0', STR_PAD_LEFT);
    }

    public function matches(string $candidate): bool
    {
        return hash_equals($this->for(), trim($candidate));
    }

    /** When today's PIN stops working, so the UI can say how long it's good for. */
    public function expiresAt(): Carbon
    {
        return Carbon::now()->endOfDay();
    }
}
