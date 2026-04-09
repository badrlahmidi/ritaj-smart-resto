<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Printing\ReceiptPrinterService;
use Illuminate\Support\Facades\DB;
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
            ->whereIn('status', [
                \App\Enums\OrderStatus::SentToKitchen->value,
                \App\Enums\OrderStatus::InService->value,
                \App\Enums\OrderStatus::PaymentPending->value,
            ])
            ->with(['table', 'server'])
            ->orderByDesc('updated_at')
            ->get();
    }

    public function getSelectedOrderProperty()
    {
        if (! $this->selectedOrderUuid) {
            return null;
        }

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
        if (! $this->selectedOrder) {
            return;
        }

        $order = $this->selectedOrder;

        DB::transaction(function () use ($order) {
            // 1. Update order status — the observer handles stock deduction
            $order->update([
                'status' => \App\Enums\OrderStatus::Paid->value,
                'payment_status' => 'paid',
                'payment_method' => $this->paymentMethod,
            ]);

            // 2. Create a Payment record so financial reports are accurate
            Payment::create([
                'order_uuid' => $order->uuid,
                'amount' => $order->total_amount,
                'payment_method' => $this->paymentMethod,
                'amount_tendered' => $this->paymentMethod === 'cash' ? $this->amountTendered : null,
                'change_due' => $this->paymentMethod === 'cash' ? max(0, $this->change) : null,
                'user_id' => auth()->id(),
            ]);

            // 3. Free the table
            if ($order->table) {
                $order->table->update(['current_order_uuid' => null, 'status' => 'available']);
            }
        });

        // 4. Print receipt (outside transaction — failure is non-fatal)
        app(ReceiptPrinterService::class)->printOrder($order->fresh());

        // Reset state
        $this->selectedOrderUuid = null;
        $this->amountTendered = 0;
        $this->change = 0;

        $this->dispatch('notify', 'Paiement enregistré et ticket imprimé !', 'success');
    }

    public function render()
    {
        return view('livewire.cash-register-logic');
    }
}
