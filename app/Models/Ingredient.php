<?php

namespace App\Models;

use App\Traits\HasStock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory, HasStock;

    protected $fillable = [
        'name',
        'unit', // kg, l, piece
        'stock_quantity',
        'cost_per_unit', // PUMP
        'min_stock_alert',
        'supplier_id',
    ];

    /**
     * Update Weighted Average Cost (PUMP)
     * Nouveau PUMP = ((Ancien Stock * Ancien Prix) + (Nouvelle Qté * Nouveau Prix)) / (Ancien Stock + Nouvelle Qté)
     */
    public function updateCostPrice($newQty, $newUnitCost)
    {
        $oldValue = $this->stock_quantity * $this->cost_per_unit;
        $newValue = $newQty * $newUnitCost;
        $totalQty = $this->stock_quantity + $newQty;

        if ($totalQty > 0) {
            $this->cost_per_unit = ($oldValue + $newValue) / $totalQty;
        } else {
            // If stock was negative or zero and we just add, cost is the new cost
             $this->cost_per_unit = $newUnitCost;
        }

        $this->stock_quantity += $newQty;
        $this->save();
    }
}
