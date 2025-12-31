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
        // On ne décrémente que si ce n'est pas déjà fait
        // Pour être sûr, on pourrait ajouter un flag 'stock_deducted' sur la commande
        // Mais ici, on va assumer que cette méthode est appelée une seule fois au paiement ou en cuisine
        
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = $item->product;

                // 1. Cas simple : Produit fini (ex: Canette)
                if ($product->track_stock) {
                    $product->decrement('stock_quantity', $item->quantity);
                    // Pas de log movement pour produit fini dans cette version v2, 
                    // sauf si on unifie produit et ingrédient, ce qui est le mieux à terme.
                }

                // 2. Cas Recette : Ingrédients
                if ($product->ingredients->count() > 0) {
                    foreach ($product->ingredients as $ingredient) {
                        // Quantité nécessaire = Qté recette * Qté commandée
                        // ex: 0.150kg viande * 2 burgers = 0.300kg
                        $qtyNeeded = $ingredient->pivot->quantity * $item->quantity;

                        // Gestion perte (Wastage)
                        if ($ingredient->pivot->wastage_percent > 0) {
                            $qtyNeeded *= (1 + ($ingredient->pivot->wastage_percent / 100));
                        }

                        // Décrémentation
                        $ingredient->decrement('stock_quantity', $qtyNeeded);

                        // Traceability
                        StockMovement::create([
                            'ingredient_id' => $ingredient->id,
                            'type' => 'sale',
                            'quantity' => -$qtyNeeded,
                            'cost' => $ingredient->cost_per_unit, // Snapshot du coût
                            'reference' => 'Order #' . $order->local_id,
                            'user_id' => auth()->id() ?? null,
                        ]);
                    }
                }
            }
        });
    }
}
