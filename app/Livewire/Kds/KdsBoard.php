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
            $q->where('status', 'sent');
            if ($this->stationFilter) {
                $q->whereHas('product', fn($sq) => $sq->where('kitchen_station', $this->stationFilter));
            }
        })
        ->where('status', 'in_progress')
        ->with(['items' => function($q) {
            $q->where('status', 'sent');
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
        OrderItem::where('id', $itemId)->update(['status' => 'served']);
        $this->checkOrderCompletion($itemId);
    }

    public function markOrderReady($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) return;

        // Mark all filtered items as served
        foreach ($order->items as $item) {
            if ($item->status === 'sent') {
                $item->update(['status' => 'served']);
            }
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
