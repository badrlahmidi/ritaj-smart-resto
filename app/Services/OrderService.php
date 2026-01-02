<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Décrémente le stock basé sur la recette des produits commandés
     */
    public function deductStockForOrder(Order $order)
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = $item->product;

                // 1. Direct Stock (e.g. Can of Coke)
                if ($product->has_stock) {
                    $product->deductStock(
                        $item->quantity, 
                        'sale', 
                        'Order #' . $order->local_id
                    );
                }

                // 2. Recipe Stock (e.g. Burger Meat)
                if ($product->ingredients->count() > 0) {
                    foreach ($product->ingredients as $ingredient) {
                        $qtyNeeded = $ingredient->pivot->quantity * $item->quantity;

                        // Wastage
                        if ($ingredient->pivot->wastage_percent > 0) {
                            $qtyNeeded *= (1 + ($ingredient->pivot->wastage_percent / 100));
                        }

                        $ingredient->deductStock(
                            $qtyNeeded, 
                            'sale', 
                            'Order #' . $order->local_id . ' (Product: ' . $product->name . ')'
                        );
                    }
                }
            }
        });
    }
}
