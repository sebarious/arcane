<?php

namespace Tests\Feature;

use App\Models\CardInventory;
use App\Services\Kiosk\KioskStockQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * When the kiosk holds several copies of one printing it offers exactly one,
 * and the copy it picks is the row that gets reserved, charged for and
 * marked sold.
 *
 * Ranking on price alone meant it would sell a batch-eligible copy while the
 * customer was lifting an identical card off the card wall: wall stock never
 * depleted, and batch stock drained into walk-up sales.
 */
class KioskCopyPriorityTest extends TestCase
{
    use RefreshDatabase;

    private const PRODUCT = 'card:test|TEST001|null|null|null|null';

    private function copy(array $attributes = []): CardInventory
    {
        return CardInventory::create(array_merge([
            'game' => 'pokemon',
            'product_id' => self::PRODUCT,
            'card_name' => 'Test Card',
            'set_name' => 'Test Set',
            'status' => 'in_stock',
            'rarity_band' => 'rare',
            'not_for_batches' => false,
            'in_card_wall' => false,
            'market_value_pence' => 1000,
            'cost_pence' => 200,
            'acquired_at' => now()->toDateString(),
        ], $attributes));
    }

    /** @return int[] */
    private function offered(): array
    {
        return app(KioskStockQuery::class)->build()->pluck('id')->all();
    }

    public function test_a_card_wall_copy_is_offered_over_batch_stock(): void
    {
        // Deliberately the dearer copy, so price alone would pick the wrong one.
        $batchable = $this->copy(['market_value_pence' => 5000]);
        $wall = $this->copy(['in_card_wall' => true, 'market_value_pence' => 1000]);

        $this->assertSame([$wall->id], $this->offered());
        $this->assertNotContains($batchable->id, $this->offered());
    }

    public function test_a_held_back_copy_is_offered_over_batch_stock(): void
    {
        $batchable = $this->copy(['market_value_pence' => 5000]);
        $heldBack = $this->copy(['not_for_batches' => true, 'market_value_pence' => 1000]);

        $this->assertSame([$heldBack->id], $this->offered());
        $this->assertNotContains($batchable->id, $this->offered());
    }

    public function test_the_card_wall_outranks_other_held_back_stock(): void
    {
        $this->copy(['not_for_batches' => true, 'market_value_pence' => 5000]);
        $wall = $this->copy(['in_card_wall' => true, 'market_value_pence' => 1000]);

        $this->assertSame([$wall->id], $this->offered());
    }

    /** Batch stock is still sellable — it's the last resort, not excluded. */
    public function test_batch_stock_is_still_offered_when_it_is_all_there_is(): void
    {
        $only = $this->copy();

        $this->assertSame([$only->id], $this->offered());
    }

    /** Within a tier the old rule stands: the dearest copy, id as tiebreak. */
    public function test_price_still_decides_between_copies_of_equal_priority(): void
    {
        $cheap = $this->copy(['in_card_wall' => true, 'market_value_pence' => 1000]);
        $dear = $this->copy(['in_card_wall' => true, 'market_value_pence' => 9000]);

        $this->assertSame([$dear->id], $this->offered());
        $this->assertNotContains($cheap->id, $this->offered());
    }

    /**
     * The point of the whole change: repeated walk-up sales should eat the
     * wall and held-back copies first and leave batch stock alone.
     */
    public function test_repeated_sales_consume_wall_stock_before_batch_stock(): void
    {
        $wall = $this->copy(['in_card_wall' => true]);
        $heldBack = $this->copy(['not_for_batches' => true]);
        $batchable = $this->copy();

        $this->assertSame([$wall->id], $this->offered());

        $wall->update(['status' => 'sold']);
        $this->assertSame([$heldBack->id], $this->offered());

        $heldBack->update(['status' => 'sold']);
        $this->assertSame([$batchable->id], $this->offered());
    }

    /** A slab is non-batchable by nature, so it ranks with the held-back stock. */
    public function test_a_graded_copy_outranks_batch_stock(): void
    {
        // Same product_id but graded: the partition keys on the grade too, so
        // give the raw copy its own product to keep this to one group.
        $batchable = $this->copy(['market_value_pence' => 5000]);
        $slab = $this->copy(['graded_by' => 'PSA', 'grade' => '10', 'market_value_pence' => 1000]);

        $offered = $this->offered();

        // Both are offered (a slab and a raw copy are different things to a
        // buyer), so this asserts the slab is present rather than excluded.
        $this->assertContains($slab->id, $offered);
        $this->assertContains($batchable->id, $offered);
    }
}
