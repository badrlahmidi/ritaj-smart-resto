<?php

namespace App\Traits;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasStock
{
    /**
     * Get the stock movements for this item.
     */
    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'stockable');
    }

    /**
     * Decrement stock and log the movement.
     */
    public function deductStock(float $quantity, string $reason, string $reference = null, ?float $cost = null)
    {
        // 1. Update actual stock column
        // Assuming column is 'stock_quantity' for both
        $this->decrement('stock_quantity', $quantity);

        // 2. Create Traceability Log
        $this->stockMovements()->create([
            'type' => 'sale', // or $reason
            'quantity' => -$quantity, // Negative for deduction
            'cost' => $cost ?? $this->getCostPrice(),
            'reference' => $reference,
            'user_id' => auth()->id() ?? null,
        ]);
    }

    /**
     * Helper to get cost price, can be overridden by model.
     */
    public function getCostPrice(): float
    {
        // Default implementation, override in Ingredient/Product if different
        return $this->cost_per_unit ?? 0;
    }
}
