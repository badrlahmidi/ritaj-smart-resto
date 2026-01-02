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
        return Order::query()
            ->whereIn('status', ['sent_to_kitchen', 'ready', 'pending'])
            ->with(['table', 'server'])
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
        // L'observer se déclenchera ici car le statut passe à 'paid'
        // Si la commande n'a jamais été envoyée en cuisine (Vente directe), le stock sera déduit maintenant.
        // Si elle a déjà été envoyée en cuisine, le flag is_stock_deducted bloquera la double déduction.
        $order->update([
            'status' => 'paid',
            'payment_method' => $this->paymentMethod,
        ]);

        // 2. Libérer la table
        if ($order->table) {
            $order->table->update(['current_order_uuid' => null]);
        }

        // 3. Impression Ticket Caisse
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
