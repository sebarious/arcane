<?php

namespace Tests\Feature;

use App\Services\Kiosk\KioskDailyPin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Two faults found in production, kept honest here.
 */
class KioskCheckoutRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function unlocked(): array
    {
        return ['kiosk_unlocked_for' => app(KioskDailyPin::class)->for()];
    }

    /**
     * The second customer of the day could never pay: "Too Many Attempts.".
     *
     * Inline throttle:n,1 keys guests on domain|ip with no per-route part, so
     * every throttled route shared one counter. Three minutes of 2-second
     * status polling during one payment blew through the allowance that
     * /kiosk/checkout (capped at 10) then read.
     */
    public function test_status_polling_does_not_exhaust_the_checkout_allowance(): void
    {
        $session = $this->unlocked();

        // One payment's worth of polling.
        for ($i = 0; $i < 45; $i++) {
            $this->withSession($session)->getJson('/kiosk/orders/1/status');
        }

        $response = $this->withSession($session)->postJson('/kiosk/checkout');

        $this->assertNotSame(
            429,
            $response->status(),
            'Status polling drained the checkout rate limit — the next customer cannot pay.'
        );
    }

    /** Browsing shouldn't eat the till's payment budget either. */
    public function test_browsing_does_not_exhaust_the_checkout_allowance(): void
    {
        $session = $this->unlocked();

        for ($i = 0; $i < 40; $i++) {
            $this->withSession($session)->getJson('/kiosk/search?q=char');
        }

        $this->assertNotSame(429, $this->withSession($session)->postJson('/kiosk/checkout')->status());
    }

    /**
     * A basket of nothing but manual lines — a supplies sale, a deposit — is
     * a real sale. The server has always accepted it; the button was the
     * thing that silently refused.
     */
    public function test_a_basket_of_only_manual_lines_can_be_checked_out(): void
    {
        $session = $this->unlocked();

        $this->withSession($session)
            ->postJson('/kiosk/basket/custom', ['label' => 'Sleeves', 'amount' => 5.50])
            ->assertOk();

        $response = $this->withSession($session)->postJson('/kiosk/checkout');

        // Anything but "your basket is empty": whether Stripe is reachable in
        // the test environment is beside the point here.
        $this->assertNotSame(422, $response->status(), $response->getContent());
    }
}
