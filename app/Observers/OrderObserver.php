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
        // Eloquent's `updated` event fires after the record has been persisted, so `isDirty()`
        // always returns false at this point. `wasChanged()` correctly inspects the diff between
        // the previous and current DB values, making it the right tool here.
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
