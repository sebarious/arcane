<?php

namespace Tests\Feature;

use App\Models\CardInventory;
use App\Models\KioskOrder;
use App\Services\Kiosk\KioskCheckoutService;
use App\Services\Kiosk\KioskDailyPin;
use App\Services\Kiosk\KioskOpenOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Open orders: a list a customer builds on the browse-only catalogue tablet
 * and pays for at the counter. The thing that matters most here is that
 * creating one takes no stock out of circulation — an unattended, unlocked
 * tablet must not be able to deny stock to the till.
 */
class KioskOpenOrderTest extends TestCase
{
    use RefreshDatabase;

    private function card(array $attributes = []): CardInventory
    {
        return CardInventory::create(array_merge([
            'game' => 'pokemon',
            'card_name' => 'Test Card',
            'status' => 'in_stock',
            'rarity_band' => 'rare',
            'not_for_batches' => false,
            'market_value_pence' => 1000,
            'cost_pence' => 200,
            'acquired_at' => now()->toDateString(),
        ], $attributes));
    }

    private function unlocked(): array
    {
        return ['kiosk_unlocked_for' => app(KioskDailyPin::class)->for()];
    }

    // ------------------------------------------------- the catalogue tablet

    public function test_the_catalogue_tablet_can_create_an_open_order_without_a_pin(): void
    {
        $a = $this->card(['card_name' => 'Pikachu']);
        $b = $this->card(['card_name' => 'Charizard', 'market_value_pence' => 5000]);

        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJsonPath('data.item_count', 2)
            ->assertJsonStructure(['data' => ['reference', 'short_reference', 'total_pence', 'item_count']]);

        // Stored reference keeps the full scheme; only the display is short.
        $this->assertSame('KIOSK-'.now()->format('Y').'-0001', KioskOrder::sole()->reference);
        $this->assertSame('0001', KioskOrder::sole()->shortReference());

        $order = KioskOrder::sole();
        $this->assertSame(KioskOpenOrderService::STATUS_OPEN, $order->status);
        $this->assertNull($order->paid_at);
        $this->assertNull($order->stripe_payment_intent_id);
    }

    /** The whole safety argument for leaving that endpoint unauthenticated. */
    public function test_creating_an_open_order_reserves_nothing(): void
    {
        $card = $this->card();

        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$card->id]])->assertOk();

        $card->refresh();
        $this->assertNull($card->reserved_until);
        $this->assertNull($card->reserved_by);
        $this->assertSame('in_stock', $card->status);
        $this->assertTrue(
            CardInventory::available()->whereKey($card->id)->exists(),
            'An open order must leave the card available to the till and to batches.'
        );
    }

    public function test_it_refuses_an_order_of_nothing_available(): void
    {
        $sold = $this->card(['status' => 'sold']);

        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$sold->id]])
            ->assertStatus(422);

        $this->assertSame(0, KioskOrder::count());
    }

    public function test_it_prices_items_the_same_way_the_till_does(): void
    {
        // £10.00 market, with the kiosk markup, rounded up to the next 25p.
        $card = $this->card(['market_value_pence' => 1000]);

        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$card->id]])->assertOk();

        $expected = app(KioskCheckoutService::class)->priceFor($card);
        $this->assertSame($expected, KioskOrder::sole()->items()->sole()->unit_price_pence);
    }

    // --------------------------------------------------------------- the till

    public function test_the_till_lists_only_open_orders(): void
    {
        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$this->card()->id]])->assertOk();
        KioskOrder::create(['reference' => 'KIOSK-2026-9999', 'status' => 'paid', 'total_pence' => 100, 'subtotal_pence' => 100, 'discount_pence' => 0]);

        $this->withSession($this->unlocked())
            ->getJson('/kiosk/open-orders')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_the_till_requires_the_pin_to_see_open_orders(): void
    {
        $this->getJson('/kiosk/open-orders')->assertStatus(423);
    }

    public function test_checking_out_an_open_order_loads_and_reserves_it(): void
    {
        $card = $this->card(['card_name' => 'Pikachu']);
        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$card->id]])->assertOk();
        $order = KioskOrder::sole();

        $response = $this->withSession($this->unlocked())
            ->postJson("/kiosk/open-orders/{$order->id}/checkout")
            ->assertOk()
            // The short form: what the customer quotes at the counter.
            ->assertJsonPath('reference', $order->shortReference())
            ->assertJsonPath('unavailable', []);

        $this->assertSame([$card->id], $response->json('data.*.id'));

        // Now it IS held — collecting is the moment stock is committed.
        $card->refresh();
        $this->assertNotNull($card->reserved_until);

        // And it leaves the queue without losing the record of what was asked.
        $this->assertSame(KioskOpenOrderService::STATUS_COLLECTED, $order->fresh()->status);
        $this->withSession($this->unlocked())->getJson('/kiosk/open-orders')->assertJsonCount(0, 'data');
    }

    /** The cost of holding nothing: staff must be told what went, by name. */
    public function test_checkout_reports_items_that_sold_while_the_order_waited(): void
    {
        $staying = $this->card(['card_name' => 'Pikachu']);
        $going = $this->card(['card_name' => 'Charizard']);
        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$staying->id, $going->id]])->assertOk();

        $going->update(['status' => 'sold']);

        $this->withSession($this->unlocked())
            ->postJson('/kiosk/open-orders/'.KioskOrder::sole()->id.'/checkout')
            ->assertOk()
            ->assertJsonPath('unavailable', ['Charizard']);

        $this->assertNotNull($staying->fresh()->reserved_until);
    }

    public function test_an_order_can_only_be_collected_once(): void
    {
        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$this->card()->id]])->assertOk();
        $id = KioskOrder::sole()->id;

        $this->withSession($this->unlocked())->postJson("/kiosk/open-orders/{$id}/checkout")->assertOk();
        $this->withSession($this->unlocked())->postJson("/kiosk/open-orders/{$id}/checkout")->assertNotFound();
    }

    public function test_staff_can_delete_an_open_order(): void
    {
        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$this->card()->id]])->assertOk();
        $id = KioskOrder::sole()->id;

        $this->withSession($this->unlocked())->deleteJson("/kiosk/open-orders/{$id}")->assertOk();

        $this->assertSame(0, KioskOrder::count());
        $this->assertDatabaseCount('kiosk_order_items', 0);
    }

    // ----------------------------------------------------- the nightly purge

    public function test_the_nightly_purge_clears_yesterdays_uncollected_orders_only(): void
    {
        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$this->card()->id]])->assertOk();
        $yesterday = KioskOrder::sole();
        $yesterday->forceFill(['created_at' => now()->subDay()])->save();

        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$this->card()->id]])->assertOk();
        $collected = KioskOrder::where('id', '!=', $yesterday->id)->sole();
        $collected->forceFill(['status' => KioskOpenOrderService::STATUS_COLLECTED, 'created_at' => now()->subDay()])->save();

        $this->postJson('/catalogue/kiosk/order', ['card_inventory_ids' => [$this->card()->id]])->assertOk();

        $this->artisan('arcane:purge-open-kiosk-orders')->assertSuccessful();

        $this->assertNull($yesterday->fresh(), 'Yesterday\'s uncollected order should be gone.');
        $this->assertNotNull($collected->fresh(), 'A collected order is an audit record and must survive.');
        $this->assertSame(1, KioskOrder::where('status', KioskOpenOrderService::STATUS_OPEN)->count());
    }
}
