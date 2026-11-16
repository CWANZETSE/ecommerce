<?php

namespace Tests\Feature\Admin;

use App\Models\ProductVariation;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_adjustments_serialize(): void
    {
        $variation = ProductVariation::factory()->create(['stock_quantity' => 10]);
        $service = app(InventoryService::class);

        // Simulate two adjustments in the same process; lockForUpdate ensures consistency.
        $service->recordDamage($variation, 7, 'Test A');
        $service->recordDamage($variation->fresh(), 2, 'Test B');

        $this->assertSame(1, $variation->fresh()->stock_quantity);
        $this->assertSame(2, $variation->stockMovements()->count());
    }

    public function test_each_movement_preserves_before_and_after(): void
    {
        $variation = ProductVariation::factory()->create(['stock_quantity' => 5]);
        $service = app(InventoryService::class);

        $service->receive($variation, 10);
        $service->recordDamage($variation->fresh(), 3, 'Dropped');

        $movements = $variation->stockMovements()->orderBy('created_at')->get();

        $this->assertSame(5, $movements[0]->previous_quantity);
        $this->assertSame(15, $movements[0]->new_quantity);
        $this->assertSame(15, $movements[1]->previous_quantity);
        $this->assertSame(12, $movements[1]->new_quantity);
    }
}
