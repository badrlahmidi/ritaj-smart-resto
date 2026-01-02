<?php

namespace App\Livewire\Kds;

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kds')]
class KdsBoard extends Component
{
    public $stationFilter = null; // 'kitchen', 'pizza', 'bar'

    public function mount($station = null)
    {
        $this->stationFilter = $station;
    }

    #[Computed]
    public function pendingOrders()
    {
        // Get orders that have items with 'sent' status
        $query = Order::whereHas('items', function ($q) {
            $q->where('status', \App\Enums\OrderItemStatus::Sent->value);
            if ($this->stationFilter) {
                $q->whereHas('product', fn($sq) => $sq->where('kitchen_station', $this->stationFilter));
            }
        })
        ->whereIn('status', [\App\Enums\OrderStatus::SentToKitchen->value, 'in_progress']) // Support both legacy and new status
        ->with(['items' => function($q) {
            $q->where('status', \App\Enums\OrderItemStatus::Sent->value);
            if ($this->stationFilter) {
                $q->whereHas('product', fn($sq) => $sq->where('kitchen_station', $this->stationFilter));
            }
        }, 'table', 'server'])
        ->orderBy('updated_at', 'asc') // Oldest first
        ->get();

        return $query;
    }

    public function markItemReady($itemId)
    {
        OrderItem::where('id', $itemId)->update(['status' => \App\Enums\OrderItemStatus::Served->value]);
        // Check order completion logic
    }

    public function markOrderReady($orderUuid)
    {
        $order = Order::where('uuid', $orderUuid)->first();
        if (!$order) return;

        // Mark all filtered items as served
        foreach ($order->items as $item) {
             // Only mark items relevant to this station or all if no station
             if ($item->status === \App\Enums\OrderItemStatus::Sent) {
                if ($this->stationFilter && $item->product->kitchen_station !== $this->stationFilter) {
                    continue; 
                }
                $item->update(['status' => \App\Enums\OrderItemStatus::Served]);
             }
        }
        
        // If all items in order are served, update order status
        if ($order->items()->where('status', '!=', \App\Enums\OrderItemStatus::Served)->count() === 0) {
            $order->update(['status' => \App\Enums\OrderStatus::InService]);
        }

        $this->dispatch('notify', 'Commande terminée !', 'success');
    }

    protected function checkOrderCompletion($itemId)
    {
        // Logic to check if whole order is done could go here
    }

    // Listen for events from Reverb (WebSockets)
    public function getListeners()
    {
        return [
            "echo:kitchen,NewOrderForKitchen" => '$refresh',
            "echo:kitchen,OrderVoided" => '$refresh',
        ];
    }

    public function render()
    {
        return view('livewire.kds.kds-board');
    }
}
