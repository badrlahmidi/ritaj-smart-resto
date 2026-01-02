<?php

namespace App\Livewire\Pos;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.pos-order')] // Reusing the same clean layout
class PosPaymentPage extends Component
{
    public Order $order;
    
    public $amountToPay = 0;
    public $amountTendered = 0;
    public $changeDue = 0;
    public $paymentMethod = 'cash'; // Default
    
    // For numeric keypad input
    public $numericInput = '';

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->amountToPay = $order->total_amount;
        
        if ($order->status === 'paid') {
            session()->flash('error', 'Commande déjà payée !');
            return redirect()->route('pos');
        }
    }

    public function addCash($amount)
    {
        $this->amountTendered += $amount;
        $this->calculateChange();
    }
    
    public function updatedNumericInput()
    {
        $this->amountTendered = (float) $this->numericInput;
        $this->calculateChange();
    }

    public function appendNumber($number)
    {
        $this->numericInput .= $number;
        $this->amountTendered = (float) $this->numericInput;
        $this->calculateChange();
    }
    
    public function clearInput()
    {
        $this->numericInput = '';
        $this->amountTendered = 0;
        $this->calculateChange();
    }

    public function calculateChange()
    {
        if ($this->amountTendered >= $this->amountToPay) {
            $this->changeDue = $this->amountTendered - $this->amountToPay;
        } else {
            $this->changeDue = 0;
        }
    }

    public function setPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        if ($method !== 'cash') {
            $this->amountTendered = $this->amountToPay; // Auto-fill for card
            $this->changeDue = 0;
        } else {
            $this->amountTendered = 0;
            $this->numericInput = '';
        }
    }

    public function processPayment()
    {
        // Validation
        if ($this->paymentMethod === 'cash' && $this->amountTendered < $this->amountToPay) {
            $this->dispatch('notify', 'Montant insuffisant !', 'error');
            return;
        }

        DB::transaction(function () {
            // 1. Create Payment
            Payment::create([
                'order_uuid' => $this->order->uuid,
                'amount' => $this->amountToPay,
                'payment_method' => $this->paymentMethod,
                'amount_tendered' => $this->paymentMethod === 'cash' ? $this->amountTendered : null,
                'change_due' => $this->paymentMethod === 'cash' ? $this->changeDue : null,
                'user_id' => auth()->id(),
            ]);

            // 2. Update Order
            $this->order->update([
                'status' => 'paid',
                'payment_status' => 'paid'
            ]);

            // 3. Free Table (or set to dirty)
            if ($this->order->table) {
                $this->order->table->update([
                    'status' => 'available', // Or 'dirty' based on config
                    'current_order_uuid' => null
                ]);
            }
        });

        // 4. Print Job (Placeholder)
        // dispatch(new PrintReceiptJob($this->order));

        $this->dispatch('notify', 'Paiement Validé !', 'success');
        return redirect()->route('pos');
    }

    public function render()
    {
        return view('livewire.pos.pos-payment-page');
    }
}
