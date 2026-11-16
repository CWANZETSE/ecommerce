<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\ProductVariation;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Adjust stock on a variation by a signed quantity change.
     *
     * @param  int  $quantityChange  Positive to add, negative to subtract.
     *
     * @throws InvalidArgumentException
     */
    public function adjust(
        ProductVariation $variation,
        int $quantityChange,
        StockMovementType $type,
        ?string $reason = null,
        ?Model $reference = null,
    ): StockMovement {
        if ($quantityChange === 0) {
            throw new InvalidArgumentException('Quantity change must not be zero.');
        }

        return DB::transaction(function () use ($variation, $quantityChange, $type, $reason, $reference) {
            // Lock the row so concurrent adjustments serialize
            /** @var ProductVariation $locked */
            $locked = ProductVariation::whereKey($variation->id)->lockForUpdate()->firstOrFail();

            $previous = $locked->stock_quantity;
            $new = $previous + $quantityChange;

            if ($new < 0) {
                throw new InvalidArgumentException(
                    "Cannot reduce stock below zero. Currently {$previous}, attempted change {$quantityChange}."
                );
            }

            $locked->update(['stock_quantity' => $new]);

            return StockMovement::create([
                'product_id' => $locked->product_id,
                'variation_id' => $locked->id,
                'type' => $type,
                'quantity_change' => $quantityChange,
                'previous_quantity' => $previous,
                'new_quantity' => $new,
                'reason' => $reason,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'user_id' => Auth::id(),
            ]);
        });
    }

    /**
     * Convenience methods used across the app and by Phase 6 checkout.
     */
    public function receive(ProductVariation $variation, int $qty, ?string $reason = null, ?Model $reference = null): StockMovement
    {
        return $this->adjust($variation, abs($qty), StockMovementType::Receive, $reason, $reference);
    }

    public function recordSale(ProductVariation $variation, int $qty, Model $reference): StockMovement
    {
        return $this->adjust($variation, -abs($qty), StockMovementType::Sale, null, $reference);
    }

    public function recordDamage(ProductVariation $variation, int $qty, string $reason): StockMovement
    {
        return $this->adjust($variation, -abs($qty), StockMovementType::Damage, $reason);
    }

    public function recordReturn(ProductVariation $variation, int $qty, Model $reference): StockMovement
    {
        return $this->adjust($variation, abs($qty), StockMovementType::Return, null, $reference);
    }

    public function setInitial(ProductVariation $variation, int $qty): StockMovement
    {
        return $this->adjust($variation, abs($qty), StockMovementType::Initial);
    }
}