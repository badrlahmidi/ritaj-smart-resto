<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\PrinterService;
use Livewire\Component;

class KitchenDisplaySystem extends Component
{
    public function getOrdersProperty()
    {
        return Order::query()
            ->where('status', 'sent_to_kitchen')
            ->with(['items.product', 'table', 'waiter'])
            ->orderBy('updated_at', 'asc')
            ->get();
    }

    public function markAsReady(string $orderUuid)
    {
        $order = Order::find($orderUuid);
        if ($order) {
            $order->update(['status' => 'ready']);
            // Optionnel : Notification au serveur via Reverb
        }
    }

    public function printTicket(string $orderUuid)
    {
        $order = Order::find($orderUuid);
        if ($order) {
            app(PrinterService::class)->printKitchenTicket($order);
            $this->dispatch('notify', 'Ticket imprimé');
        }
    }

    public function render()
    {
        return view('livewire.kitchen-display-system');
    }
}
