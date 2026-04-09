<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\StockMovement;
use App\Services\OrderService;

class OrderObserver
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // 1. Trigger Deduction when sent to kitchen or paid (if not already deducted)
        if (
            ! $order->is_stock_deducted &&
            in_array($order->status, [OrderStatus::SentToKitchen, OrderStatus::Paid])
        ) {
            $this->orderService->deductStockForOrder($order);

            // Mark as deducted to prevent double counting
            $order->updateQuietly(['is_stock_deducted' => true]);
        }

        // 2. Handle Cancellation AFTER preparation (Waste Management)
        // If order was already deducted (cooked) and is now cancelled -> Mark movements as 'waste'
        // wasChanged() is used (not isDirty()) because in the 'updated' event the model is already
        // persisted, so isDirty() always returns false.
        if (
            $order->is_stock_deducted &&
            $order->status === OrderStatus::Cancelled &&
            $order->wasChanged('status')
        ) {
            // Update related stock movements type from 'sale' to 'waste'
            StockMovement::where('reference', 'Order #'.$order->local_id)
                ->where('type', 'sale')
                ->update(['type' => 'waste']);
        }
    }
}
