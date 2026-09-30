<?php

namespace Tests\Feature;

use App\Enums\RipGradedPolicy;
use App\Filament\Resources\RipPacks\Pages\CreateRipPack;
use App\Models\CardInventory;
use App\Models\Rip;
use App\Models\RipOrder;
use App\Models\RipPack;
use App\Models\User;
use App\Services\Rips\RipCheckoutService;
use App\Services\Rips\RipDrawer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * A rip draws from the same quality pool as a sealed batch — never anything
 * held back as not_for_batches — with graded slabs allowed in or out per
 * pack. These cover the pool rules rather than the seeded-draw maths.
 */
class RipCardEligibilityTest extends TestCase
{
    use RefreshDatabase;

    private function card(array $attributes = []): CardInventory
    {
        return CardInventory::create(array_merge([
            'game' => 'pokemon',
            'name' => 'Test Card',
            'status' => 'in_stock',
            'rarity_band' => 'common',
            'not_for_batches' => false,
            'market_value_pence' => 500,
            'cost_pence' => 100,
            'acquired_at' => now()->toDateString(),
        ], $attributes));
    }

    private function gradedCard(array $attributes = []): CardInventory
    {
        // not_for_batches is deliberately ON: that is what the non-batch
        // Inventory form (the only way a slab enters stock) actually writes,
        // and a slab must stay rip-eligible in spite of it.
        return $this->card(array_merge([
            'graded_by' => 'PSA',
            'grade' => '10',
            'not_for_batches' => true,
        ], $attributes));
    }

    private function pack(RipGradedPolicy $policy): RipPack
    {
        return RipPack::create([
            'name' => 'Test Pack',
            'slug' => 'test-pack-'.$policy->value,
            'price_pence' => 1000,
            'games' => ['pokemon'],
            'band_odds' => ['common' => 1.0],
            'buy_back_percentage' => 0.7,
            'graded_policy' => $policy,
            'status' => 'active',
        ]);
    }

    private function ripFor(RipPack $pack): Rip
    {
        $user = User::factory()->create();

        $order = RipOrder::create([
            'user_id' => $user->id,
            'reference' => 'RIP-TEST-'.$pack->id,
            'total_pence' => $pack->price_pence,
            'status' => 'paid',
        ]);

        return Rip::create([
            'rip_order_id' => $order->id,
            'rip_pack_id' => $pack->id,
            'pack_name' => $pack->name,
            'price_pence' => $pack->price_pence,
            'buy_back_percentage' => $pack->buy_back_percentage,
        ]);
    }

    // ------------------------------------------------------------ the pool

    public function test_exclude_policy_matches_the_batch_pool_exactly(): void
    {
        $raw = $this->card();
        $this->gradedCard();
        $this->card(['not_for_batches' => true]);

        $ids = CardInventory::available()->ripEligible(RipGradedPolicy::Exclude)->pluck('id');

        $this->assertEquals([$raw->id], $ids->all());
        $this->assertEquals(
            CardInventory::batchEligible()->pluck('id')->all(),
            $ids->all(),
            'Exclude should be the batch pool by construction.'
        );
    }

    public function test_allow_policy_pools_raw_and_graded_together(): void
    {
        $raw = $this->card();
        $graded = $this->gradedCard();
        $this->card(['not_for_batches' => true]);

        $ids = CardInventory::available()->ripEligible(RipGradedPolicy::Allow)->pluck('id')->sort()->values();

        $this->assertEquals(collect([$raw->id, $graded->id])->sort()->values()->all(), $ids->all());
    }

    public function test_only_policy_draws_graded_alone(): void
    {
        $this->card();
        $graded = $this->gradedCard();

        $ids = CardInventory::available()->ripEligible(RipGradedPolicy::Only)->pluck('id');

        $this->assertEquals([$graded->id], $ids->all());
    }

    /**
     * The whole point of the feature: condition-rejected RAW stock is out of
     * rips no matter which policy the pack carries.
     */
    public function test_condition_rejected_raw_stock_is_excluded_under_every_policy(): void
    {
        $this->card(['not_for_batches' => true]);

        foreach (RipGradedPolicy::cases() as $policy) {
            $this->assertSame(
                0,
                CardInventory::available()->ripEligible($policy)->count(),
                "not_for_batches raw stock leaked into the {$policy->value} pool."
            );
        }
    }

    /**
     * Regression: every slab carries not_for_batches, because the form that
     * creates it defaults the toggle on. Reading that flag as a condition
     * signal for slabs excluded all of them and made graded packs undrawable.
     */
    public function test_a_slab_is_eligible_despite_carrying_not_for_batches(): void
    {
        $slab = $this->gradedCard();
        $this->assertTrue($slab->fresh()->not_for_batches, 'Fixture should mirror the real form default.');

        foreach ([RipGradedPolicy::Allow, RipGradedPolicy::Only] as $policy) {
            $this->assertEquals(
                [$slab->id],
                CardInventory::available()->ripEligible($policy)->pluck('id')->all(),
                "A slab was excluded from the {$policy->value} pool by its not_for_batches flag."
            );
        }

        $this->assertSame(0, CardInventory::available()->ripEligible(RipGradedPolicy::Exclude)->count());
    }

    public function test_a_card_already_in_a_pack_or_rip_is_never_eligible(): void
    {
        $this->card(['status' => 'allocated']);

        foreach (RipGradedPolicy::cases() as $policy) {
            $this->assertSame(0, CardInventory::available()->ripEligible($policy)->count());
        }
    }

    // ----------------------------------------------------------- the drawer

    public function test_the_drawer_will_not_pull_a_graded_card_into_an_exclude_pack(): void
    {
        Storage::fake('local');

        $raw = $this->card();
        $this->gradedCard();

        $pack = $this->pack(RipGradedPolicy::Exclude);
        $card = app(RipDrawer::class)->draw($this->ripFor($pack));

        $this->assertSame($raw->id, $card->id);
    }

    public function test_the_drawer_only_pulls_graded_cards_into_an_only_pack(): void
    {
        Storage::fake('local');

        $this->card();
        $graded = $this->gradedCard();

        $pack = $this->pack(RipGradedPolicy::Only);
        $card = app(RipDrawer::class)->draw($this->ripFor($pack));

        $this->assertSame($graded->id, $card->id);
        $this->assertTrue($card->isGraded());
    }

    public function test_the_snapshot_records_the_policy_that_shaped_the_pool(): void
    {
        Storage::fake('local');

        $this->gradedCard();
        $pack = $this->pack(RipGradedPolicy::Only);
        $rip = $this->ripFor($pack);

        app(RipDrawer::class)->draw($rip);

        $snapshot = json_decode(Storage::disk('local')->get($rip->fresh()->verification_snapshot_path), true);
        $this->assertSame('only', $snapshot['graded_policy']);
    }

    // ---------------------------------------------------- fulfilability gate

    public function test_a_pack_is_unfulfillable_when_only_ineligible_stock_exists(): void
    {
        // Graded stock on the shelf, but this pack refuses graded — checkout
        // must block rather than take money for a draw that would throw.
        $this->gradedCard();

        $this->assertFalse(app(RipDrawer::class)->isFulfillable($this->pack(RipGradedPolicy::Exclude)));
        $this->assertTrue(app(RipDrawer::class)->isFulfillable($this->pack(RipGradedPolicy::Only)));
    }

    public function test_an_only_graded_pack_is_unfulfillable_without_graded_stock(): void
    {
        $this->card();

        $this->assertFalse(app(RipDrawer::class)->isFulfillable($this->pack(RipGradedPolicy::Only)));
    }

    // ------------------------------------------------------------- the admin

    /**
     * The pool placeholder runs a live query inside a Filament closure, which
     * is the kind of thing that only fails once someone opens the page.
     */
    public function test_the_admin_form_mounts_and_reports_the_drawable_pool(): void
    {
        $this->card();

        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Livewire::test(CreateRipPack::class)
            ->assertOk()
            ->assertSchemaStateSet(['graded_policy' => 'exclude'])
            ->fillForm(['games' => ['pokemon'], 'graded_policy' => 'only'])
            ->assertOk();
    }

    // -------------------------------------------------------- the buy-back

    /**
     * A slab sold back must come back as a slab, with its serial and its own
     * photo intact — otherwise it re-enters stock looking like a raw card and
     * the catalogue falls back to stock art of a card that isn't this one.
     */
    public function test_selling_back_a_graded_card_preserves_the_slab_identity(): void
    {
        Storage::fake('local');

        $graded = $this->gradedCard([
            'grade_serial' => '12345678',
            'custom_image_path' => 'card-photos/slab.jpg',
            'image_url' => 'https://pulseapi.example/original.png',
        ]);

        $pack = $this->pack(RipGradedPolicy::Only);
        $rip = $this->ripFor($pack);
        app(RipDrawer::class)->draw($rip);
        $rip->update(['opened_at' => now()]);

        app(RipCheckoutService::class)->decide($rip->fresh(), 'sold_back');

        $new = CardInventory::where('acquisition_lot', 'Digital Rip buy-back')->sole();

        $this->assertSame('PSA', $new->graded_by);
        $this->assertSame('10', $new->grade);
        $this->assertSame('12345678', $new->grade_serial, 'Grade serial was dropped on buy-back.');
        $this->assertSame('card-photos/slab.jpg', $new->custom_image_path, 'The slab photo was dropped on buy-back.');
        $this->assertSame(
            'https://pulseapi.example/original.png',
            $new->getRawOriginal('image_url'),
            'image_url should keep the PulseAPI original, not the rendered custom-photo URL.'
        );

        // Still a slab, so still out of batches.
        $this->assertTrue($new->isGraded());
        $this->assertSame(0, CardInventory::batchEligible()->whereKey($new->id)->count());
    }
}
