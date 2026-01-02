<?php

namespace App\Livewire\Pos;

use App\Enums\OrderType;
use App\Models\Area;
use App\Models\Category;
use App\Models\OptionGroup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.pos-app')]
class ProPos extends Component
{
    // View state: 'tables', 'ordering', 'payment', 'dashboard'
    public $view = 'dashboard'; 
    
    // Selection state
    public $selectedAreaId = null;
    public $selectedTableId = null;
    public $selectedCategoryId = null;
    public $search = '';
    public $orderType = 'dine_in';
    
    // Cart state
    public $cart = []; 
    public $currentOrderUuid = null;
    
    // Advanced state
    public $discountAmount = 0;
    public $discountType = 'fixed'; 
    public $taxRate = 10;
    public $serviceCharge = 0;
    public $globalNotes = '';
    
    // Delivery / Customer Info
    public $customerName = '';
    public $customerPhone = '';
    public $customerAddress = '';
    
    // Modals
    public $showOptionsModal = false;
    public $showDiscountModal = false;
    public $showNoteModal = false;
    public $showPaymentModal = false;
    public $showDeliveryModal = false;
    public $showPinModal = false;
    public $showCancelModal = false;
    public $showSplitModal = false;
    
    // Targeted for modals
    public $selectedProductForOptions = null;
    public $selectedOptions = [];
    public $editingLineIndex = null;
    public $lineNote = '';
    public $cancelTarget = null; // 'order' or 'item_index'
    public $cancelReason = '';
    
    // Security
    public $pinCode = '';
    public $pendingAction = null; // Closure or method name to execute after PIN
    
    // Payment state
    public $paymentMethod = 'cash';
    public $amountTendered = 0;
    public $payments = []; // List of partial payments for split
    
    // Split Logic
    public $splitType = 'full'; // 'full', 'count', 'items'
    public $splitCount = 1;
    public $selectedItemsForSplit = []; // array of indexes

    public function mount()
    {
        $settings = app(GeneralSettings::class);
        $this->taxRate = $settings->default_tax_rate ?? 10;
        $this->selectedAreaId = Area::where('is_active', true)->first()?->id;
    }

    #[Computed]
    public function areas() { return Area::where('is_active', true)->get(); }

    #[Computed]
    public function tables() {
        if (!$this->selectedAreaId) return collect();
        return Table::where('area_id', $this->selectedAreaId)->with('currentOrder')->get();
    }

    #[Computed]
    public function categories() { return Category::where('is_active', true)->get(); }

    #[Computed]
    public function products() {
        $query = Product::where('is_available', true);
        if ($this->selectedCategoryId) $query->where('category_id', $this->selectedCategoryId);
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('id', $this->search);
            });
        }
        return $query->limit(40)->get();
    }

    // --- Core Workflow Actions ---

    public function selectCategory($id) { $this->selectedCategoryId = $id; }

    public function selectArea($id) { $this->selectedAreaId = $id; $this->view = 'tables'; }

    public function selectTable($id) {
        $this->resetCart();
        
        $this->selectedTableId = $id;
        $table = Table::find($id);
        $this->orderType = \App\Enums\OrderType::DINE_IN->value;
        
        if ($table->current_order_uuid) {
            $order = Order::find($table->current_order_uuid);
            if ($order && $order->isLockedByOthers()) {
                $this->dispatch('notify', 'Cette commande est en cours de modification par ' . ($order->locker->name ?? 'un autre utilisateur'), 'error');
                $this->selectedTableId = null;
                return;
            }
            // Acquire lock
            $order->update(['locked_by' => auth()->id(), 'locked_at' => now()]);
            $this->loadOrder($table->current_order_uuid);
        }
        $this->view = 'ordering';
    }

    public function setOrderType($type) {
        $this->orderType = $type;
        if ($type === 'dine_in') { 
            $this->view = 'tables'; 
        }
        else if ($type === 'delivery') { 
            $this->resetCart();
            $this->showDeliveryModal = true; 
            $this->view = 'ordering';
        }
        else if ($type === 'takeaway') {
            $this->resetCart();
            $this->view = 'ordering';
        }
    }

    public function unlockOrder() {
        if ($this->currentOrderUuid) {
            Order::where('uuid', $this->currentOrderUuid)
                 ->where('locked_by', auth()->id())
                 ->update(['locked_by' => null, 'locked_at' => null]);
        }
        
        if ($this->view === 'ordering' || $this->view === 'tables') {
            $this->view = 'dashboard';
        } else {
            $this->view = 'dashboard';
        }
        $this->resetCart();
    }

    // --- Order Entry ---

    public function selectProduct($productId) {
        $product = Product::with('optionGroups.options')->find($productId);
        if (!$product) return;
        if ($product->optionGroups->isNotEmpty()) {
            $this->selectedProductForOptions = $product;
            $this->selectedOptions = [];
            foreach ($product->optionGroups as $group) { $this->selectedOptions[$group->id] = $group->is_multiselect ? [] : null; }
            $this->showOptionsModal = true;
        } else {
            $this->addToCart($product, []);
        }
    }

    public function addToCart(Product $product, array $options, $extraPrice = 0) {
        $basePrice = $product->getPriceByType(OrderType::from($this->orderType));
        $this->cart[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $basePrice + $extraPrice,
            'qty' => 1,
            'options' => $options,
            'status' => 'pending',
            'notes' => '',
            'item_id' => null
        ];
        $this->dispatch('play-sound', 'click');
    }

    public function sendToKitchen() {
        $pendingItems = collect($this->cart)->where('status', 'pending');
        if ($pendingItems->isEmpty()) return;

        DB::transaction(function () use ($pendingItems) {
            $orderUuid = $this->currentOrderUuid ?? (string) Str::uuid();
            $order = Order::updateOrCreate(['uuid' => $orderUuid], [
                'table_id' => $this->selectedTableId,
                'customer_name' => $this->customerName,
                'customer_phone' => $this->customerPhone,
                'customer_address' => $this->customerAddress,
                'user_id' => auth()->id(),
                'status' => \App\Enums\OrderStatus::SentToKitchen->value,
                'type' => $this->orderType,
                'total_amount' => $this->cartTotal,
                'discount_amount' => $this->discountAmount,
                'discount_type' => $this->discountType,
                'tax_amount' => $this->taxAmount,
                'notes' => $this->globalNotes,
            ]);

            foreach ($pendingItems as $index => $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['qty'],
                    'options' => $item['options'],
                    'notes' => $item['notes'],
                    'status' => \App\Enums\OrderItemStatus::Sent->value,
                ]);
            }

            if ($this->selectedTableId) {
                Table::where('id', $this->selectedTableId)->update(['status' => 'occupied', 'current_order_uuid' => $order->uuid]);
            }
            
            // Release lock after sending
            $order->update(['locked_by' => null, 'locked_at' => null]);
            
            $this->currentOrderUuid = $order->uuid;
            $this->loadOrder($order->uuid);
        });

        $this->dispatch('notify', 'Envoyé en cuisine !', 'success');
    }

    public function requestAddition()
    {
        if (!$this->currentOrderUuid) return;

        $order = Order::find($this->currentOrderUuid);
        if ($order) {
            $order->update(['status' => \App\Enums\OrderStatus::PaymentPending->value]);
            $this->dispatch('notify', 'Addition demandée !', 'info');
        }
    }

    // --- Split & Payment ---

    public function checkout() {
        if (empty($this->cart)) return;
        $this->view = 'payment';
        $this->amountTendered = $this->cartTotal;
        $this->payments = [];
        $this->splitType = 'full';
    }

    public function processPayment() {
        if ($this->cartTotal > 0 && $this->amountTendered < $this->cartTotal && empty($this->payments)) {
            $this->dispatch('notify', 'Montant insuffisant', 'error');
            return;
        }

        DB::transaction(function() {
            $order = Order::where('uuid', $this->currentOrderUuid)->first();
            
            // For Walk-ins
            if (!$order) {
                $order = Order::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => auth()->id(),
                    'table_id' => $this->selectedTableId,
                    'status' => \App\Enums\OrderStatus::Paid,
                    'payment_status' => 'paid',
                    'type' => $this->orderType,
                    'total_amount' => $this->cartTotal,
                    'discount_amount' => $this->discountAmount,
                    'discount_type' => $this->discountType,
                    'tax_amount' => $this->taxAmount,
                ]);
                foreach ($this->cart as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['qty'],
                        'unit_price' => $item['price'],
                        'total_price' => $item['price'] * $item['qty'],
                        'options' => $item['options'],
                        'status' => \App\Enums\OrderItemStatus::Sent,
                    ]);
                }
            } else {
                $order->update(['status' => \App\Enums\OrderStatus::Paid, 'payment_status' => 'paid', 'total_amount' => $this->cartTotal]);
            }

            // Create Payment Records
            Payment::create([
                'order_uuid' => $order->uuid,
                'amount' => $this->cartTotal,
                'payment_method' => $this->paymentMethod,
                'user_id' => auth()->id(),
            ]);

            if ($order->table_id) {
                Table::where('id', $order->table_id)->update(['status' => 'available', 'current_order_uuid' => null]);
            }
        });

        $this->resetCart();
        $this->view = 'tables';
        $this->dispatch('notify', 'Payé et clôturé', 'success');
    }

    // --- Security & Permissions ---

    public function requestCancelItem($index) {
        $this->cancelTarget = $index;
        if ($this->cart[$index]['status'] === 'sent') {
            $this->pendingAction = 'cancelSentItem';
            $this->showPinModal = true;
        } else {
            $this->removeItem($index);
        }
    }

    public function verifyPin() {
        // Find any user with admin/manager role that matches this PIN
        $manager = User::whereIn('role', ['admin', 'manager'])->with('pin')->get()->first(function($user) {
            return $user->checkPin($this->pinCode);
        });

        if ($manager) {
            $action = $this->pendingAction;
            $this->$action();
            $this->showPinModal = false;
            $this->pinCode = '';
        } else {
            $this->dispatch('notify', 'Code PIN incorrect ou accès refusé', 'error');
            $this->pinCode = '';
        }
    }

    public function cancelSentItem() {
        if ($this->cancelTarget !== null) {
            $item = $this->cart[$this->cancelTarget];
            if ($item['item_id']) {
                OrderItem::find($item['item_id'])->update([
                    'status' => \App\Enums\OrderItemStatus::Cancelled,
                    'cancel_reason' => 'Annulation Manager'
                ]);
            }
            unset($this->cart[$this->cancelTarget]);
            $this->cart = array_values($this->cart);
            $this->dispatch('notify', 'Article annulé', 'success');
        }
    }

    // --- Line Item Actions ---

    public function editLineNote($index) { $this->editingLineIndex = $index; $this->lineNote = $this->cart[$index]['notes'] ?? ''; $this->showNoteModal = true; }
    public function saveLineNote() { if ($this->editingLineIndex !== null) $this->cart[$this->editingLineIndex]['notes'] = $this->lineNote; $this->showNoteModal = false; }
    
    public function saveDeliveryInfo() {
        $this->showDeliveryModal = false;
        $this->dispatch('notify', 'Informations livraison enregistrées', 'success');
    }

    public function applyDiscount() {
        $this->showDiscountModal = true;
    }

    public function confirmOptions() {
        $options = []; $extraPrice = 0;
        foreach ($this->selectedOptions as $groupId => $selection) {
            if (!$selection) continue;
            $group = OptionGroup::find($groupId);
            foreach ((is_array($selection) ? $selection : [$selection]) as $optId) {
                $opt = \App\Models\Option::find($optId);
                if ($opt) { $options[] = ['id' => $opt->id, 'name' => $group->name . ': ' . $opt->name, 'price' => $opt->price_modifier]; $extraPrice += $opt->price_modifier; }
            }
        }
        $this->addToCart($this->selectedProductForOptions, $options, $extraPrice);
        $this->showOptionsModal = false;
    }

    // --- Helpers ---

    private function loadOrder($uuid) {
        $order = Order::with('items.product')->where('uuid', $uuid)->first();
        if (!$order) return;
        $this->currentOrderUuid = $order->uuid;
        $this->orderType = $order->type->value ?? $order->type;
        $this->selectedTableId = $order->table_id;
        $this->customerName = $order->customer_name;
        $this->customerPhone = $order->customer_phone;
        $this->customerAddress = $order->customer_address;
        $this->discountAmount = (float)$order->discount_amount;
        $this->discountType = $order->discount_type ?? 'fixed';
        $this->globalNotes = $order->notes;
        $this->cart = [];
        foreach ($order->items as $item) {
            if ($item->status === \App\Enums\OrderItemStatus::Cancelled) continue;
            $this->cart[] = [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => (float)$item->unit_price,
                'qty' => $item->quantity,
                'options' => $item->options ?? [],
                'status' => 'sent',
                'notes' => $item->notes,
                'item_id' => $item->id
            ];
        }
    }

    private function resetCart() {
        $this->cart = []; $this->currentOrderUuid = null; $this->discountAmount = 0; $this->selectedTableId = null;
        $this->customerName = ''; $this->customerPhone = ''; $this->customerAddress = ''; $this->globalNotes = '';
    }

    public function getSubtotalProperty() { return collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']); }
    public function getCalculatedDiscountProperty() { return $this->discountType === 'percent' ? ($this->subtotal * $this->discountAmount) / 100 : $this->discountAmount; }
    public function getTaxAmountProperty() { return (($this->subtotal - $this->calculatedDiscount) * $this->taxRate) / 100; }
    public function getCartTotalProperty() { return max(0, $this->subtotal - $this->calculatedDiscount + $this->taxAmount + $this->serviceCharge); }

    public function syncTable() { if ($this->view === 'ordering' && $this->currentOrderUuid) { $this->loadOrder($this->currentOrderUuid); } }

    public function removeItem($index) { unset($this->cart[$index]); $this->cart = array_values($this->cart); }
    public function updateQty($index, $delta) {
        if (!isset($this->cart[$index]) || $this->cart[$index]['status'] === 'sent') return;
        $this->cart[$index]['qty'] += $delta;
        if ($this->cart[$index]['qty'] <= 0) $this->removeItem($index);
    }

    public function cancelOrder() {
        $this->pendingAction = 'doCancelOrder';
        $this->showPinModal = true;
    }

    public function doCancelOrder() {
        if ($this->currentOrderUuid) {
            Order::find($this->currentOrderUuid)->update(['status' => \App\Enums\OrderStatus::Cancelled]);
            if ($this->selectedTableId) Table::find($this->selectedTableId)->update(['status' => 'available', 'current_order_uuid' => null]);
        }
        $this->resetCart(); $this->view = 'tables';
        $this->dispatch('notify', 'Commande annulée', 'error');
    }

    public function render() { return view('livewire.pos.pro-pos'); }
}