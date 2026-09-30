<?php

namespace Tests\Feature;

use App\Filament\Resources\KioskOrders\Pages\ListKioskOrders;
use App\Jobs\SendKioskReceiptJob;
use App\Mail\KioskReceiptMail;
use App\Models\KioskOrder;
use App\Models\KioskOrderItem;
use App\Models\User;
use App\Services\Kiosk\KioskDailyPin;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KioskReceiptTest extends TestCase
{
    use RefreshDatabase;

    private function paidOrder(array $attributes = []): KioskOrder
    {
        $order = KioskOrder::create(array_merge([
            'reference' => 'KIOSK-2026-0001',
            'status' => 'paid',
            'paid_at' => now(),
            'subtotal_pence' => 6075,
            'discount_type' => 'percent',
            'discount_value' => 15,
            'discount_pence' => 911,
            'total_pence' => 5164,
        ], $attributes));

        KioskOrderItem::create([
            'kiosk_order_id' => $order->id,
            'card_name' => 'Mega Gengar ex',
            'set_name' => 'Mega Evolution',
            'card_number' => '145/132',
            'unit_price_pence' => 4825,
        ]);

        return $order;
    }

    public function test_it_queues_a_receipt_for_the_sale_just_completed(): void
    {
        Bus::fake();
        $order = $this->paidOrder();

        $this->withSession(['kiosk_unlocked_for' => app(KioskDailyPin::class)->for(), 'kiosk_last_order_id' => $order->id])
            ->postJson(route('kiosk.orders.receipt', $order), ['email' => 'buyer@example.com'])
            ->assertOk()
            ->assertJsonPath('data.email', 'buyer@example.com');

        Bus::assertDispatched(SendKioskReceiptJob::class, fn ($job) => $job->orderId === $order->id
            && $job->email === 'buyer@example.com');
    }

    public function test_it_refuses_an_order_that_is_not_the_one_just_completed(): void
    {
        Bus::fake();
        $order = $this->paidOrder();

        $this->withSession(['kiosk_unlocked_for' => app(KioskDailyPin::class)->for(), 'kiosk_last_order_id' => $order->id + 99])
            ->postJson(route('kiosk.orders.receipt', $order), ['email' => 'buyer@example.com'])
            ->assertForbidden();

        Bus::assertNothingDispatched();
    }

    public function test_it_refuses_an_unpaid_order(): void
    {
        Bus::fake();
        $order = $this->paidOrder(['status' => 'pending_payment', 'paid_at' => null]);

        $this->withSession(['kiosk_unlocked_for' => app(KioskDailyPin::class)->for(), 'kiosk_last_order_id' => $order->id])
            ->postJson(route('kiosk.orders.receipt', $order), ['email' => 'buyer@example.com'])
            ->assertStatus(422);

        Bus::assertNothingDispatched();
    }

    public function test_it_rejects_a_malformed_address(): void
    {
        Bus::fake();
        $order = $this->paidOrder();

        $this->withSession(['kiosk_unlocked_for' => app(KioskDailyPin::class)->for(), 'kiosk_last_order_id' => $order->id])
            ->postJson(route('kiosk.orders.receipt', $order), ['email' => 'not-an-address'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        Bus::assertNothingDispatched();
    }

    public function test_the_job_sends_the_receipt_and_stamps_the_order(): void
    {
        Mail::fake();
        $order = $this->paidOrder();

        (new SendKioskReceiptJob($order->id, 'buyer@example.com'))->handle();

        Mail::assertSent(KioskReceiptMail::class, fn (KioskReceiptMail $mail) => $mail->hasTo('buyer@example.com')
            && $mail->order->is($order));

        $order->refresh();
        $this->assertSame('buyer@example.com', $order->customer_email);
        $this->assertNotNull($order->receipt_sent_at);
    }

    public function test_the_job_is_a_no_op_for_a_deleted_order(): void
    {
        Mail::fake();

        (new SendKioskReceiptJob(999999, 'buyer@example.com'))->handle();

        Mail::assertNothingSent();
    }

    public function test_an_admin_can_email_a_receipt_from_the_panel(): void
    {
        Bus::fake();
        $order = $this->paidOrder(['customer_email' => 'first@example.com']);

        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Livewire::test(ListKioskOrders::class)
            // Mounting evaluates fillForm/helperText, so this also proves the
            // record reaches those closures.
            ->mountAction(TestAction::make('emailReceipt')->table($order))
            ->assertSchemaStateSet(['email' => 'first@example.com'], 'mountedActionSchema0')
            ->setActionData(['email' => 'second@example.com'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        Bus::assertDispatched(SendKioskReceiptJob::class, fn ($job) => $job->orderId === $order->id
            && $job->email === 'second@example.com');
    }
}
