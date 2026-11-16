<?php

namespace Tests\Feature\Inventory;

use App\Enums\StockMovementType;
use App\Models\ProductVariation;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_oversell_under_repeated_adjustments(): void
    {
        $variation = ProductVariation::factory()->create(['stock_quantity' => 5]);
        $service = app(InventoryService::class);

        // Simulate 5 successful 1-unit deductions
        for ($i = 0; $i < 5; $i++) {
            $service->adjust($variation->fresh(), -1, StockMovementType::Sale);
        }

        // 6th must throw
        $this->expectException(\InvalidArgumentException::class);
        $service->adjust($variation->fresh(), -1, StockMovementType::Sale);
    }

    public function test_each_adjustment_writes_a_movement(): void
    {
        $variation = ProductVariation::factory()->create(['stock_quantity' => 10]);
        $service = app(InventoryService::class);

        $service->adjust($variation, -3, StockMovementType::Sale);
        $service->adjust($variation->fresh(), 5, StockMovementType::Receive);

        $this->assertSame(2, $variation->stockMovements()->count());
        $this->assertSame(12, $variation->fresh()->stock_quantity);
    }
}