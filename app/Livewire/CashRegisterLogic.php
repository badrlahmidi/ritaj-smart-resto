<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\PrinterService;
use Livewire\Component;

class CashRegisterLogic extends Component
{
    public $selectedOrderUuid = null;
    public $paymentMethod = 'cash';
    public $amountTendered = 0;
    public $change = 0;

    protected $listeners = ['$refresh'];

    public function getActiveOrdersProperty()
    {
        // On affiche les commandes "ready" ou "sent_to_kitchen" non payées
        return Order::query()
            ->whereIn('status', ['sent_to_kitchen', 'ready', 'pending'])
            ->with(['table', 'waiter'])
            ->orderByDesc('updated_at')
            ->get();
    }

    public function getSelectedOrderProperty()
    {
        if (!$this->selectedOrderUuid) return null;
        return Order::with(['items.product', 'table'])->find($this->selectedOrderUuid);
    }

    public function selectOrder($uuid)
    {
        $this->selectedOrderUuid = $uuid;
        $this->amountTendered = $this->selectedOrder->total_amount ?? 0;
        $this->calculateChange();
    }

    public function updatedAmountTendered()
    {
        $this->calculateChange();
    }

    public function calculateChange()
    {
        if ($this->selectedOrder) {
            $this->change = max(0, $this->amountTendered - $this->selectedOrder->total_amount);
        }
    }

    public function processPayment()
    {
        if (!$this->selectedOrder) return;

        $order = $this->selectedOrder;

        // Mise à jour de la commande
        $order->update([
            'status' => 'paid',
            'payment_method' => $this->paymentMethod,
        ]);

        // Libérer la table
        if ($order->table) {
            $order->table->update(['current_order_uuid' => null]);
        }

        // Impression Ticket Caisse
        app(PrinterService::class)->printBill($order);

        // Reset
        $this->selectedOrderUuid = null;
        $this->amountTendered = 0;
        $this->change = 0;
        
        session()->flash('success', 'Paiement enregistré et ticket imprimé !');
        $this->dispatch('notify', 'Paiement OK');
    }

    public function render()
    {
        return view('livewire.cash-register-logic');
    }
}
