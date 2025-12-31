<?php

namespace App\Livewire\Pos;

use App\Enums\OrderType;
use App\Models\Category;
use App\Models\OptionGroup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.pos-order')]
class PosOrderPage extends Component
{
    public Table $table;
    public $orderType = 'dine_in';
    public $selectedCategoryId = null;
    public $search = '';
    
    // Cart State
    public $cart = []; // ['id' => 1, 'name' => 'Burger', 'price' => 50, 'qty' => 1, 'options' => [], 'status' => 'pending']
    public $currentOrderId = null;
    
    // Modal State
    public $showOptionsModal = false;
    public $selectedProductForOptions = null;
    public $selectedOptions = []; // ['group_id' => ['option_id', ...]]
    
    public function mount(Table $table)
    {
        $this->table = $table;
        $this->selectedCategoryId = Category::where('is_active', true)->first()?->id;
        
        // Load existing order if table is occupied
        if ($table->status === 'occupied' && $table->current_order_uuid) {
            $this->loadExistingOrder($table->current_order_uuid);
        }
    }
    
    public function loadExistingOrder($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) return;
        
        $this->currentOrderId = $order->id;
        $this->orderType = $order->type;
        
        foreach ($order->items as $item) {
            $this->cart[] = [
                'item_id' => $item->id, // Existing DB ID
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->price,
                'qty' => $item->quantity,
                'options' => $item->options ?? [],
                'status' => $item->status, // 'sent', 'served'
            ];
        }
    }

    #[Computed]
    public function categories()
    {
        return Category::where('is_active', true)->get();
    }

    #[Computed]
    public function products()
    {
        $query = Product::where('is_available', true)
            ->with(['optionGroups.options']); // Eager load options

        if ($this->selectedCategoryId) {
            $query->where('category_id', $this->selectedCategoryId);
        }

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return $query->get();
    }
    
    public function selectCategory($id)
    {
        $this->selectedCategoryId = $id;
        $this->search = '';
    }

    public function selectProduct($productId)
    {
        $product = Product::with('optionGroups')->find($productId);
        if (!$product) return;

        // Check if product has required options
        if ($product->optionGroups->where('is_required', true)->count() > 0 || $product->optionGroups->count() > 0) {
            $this->openOptionsModal($product);
        } else {
            $this->addToCart($product, []);
        }
    }

    public function openOptionsModal(Product $product)
    {
        $this->selectedProductForOptions = $product;
        $this->selectedOptions = [];
        // Pre-fill defaults or empty arrays for multiselect
        foreach ($product->optionGroups as $group) {
            if ($group->is_multiselect) {
                $this->selectedOptions[$group->id] = [];
            } else {
                $this->selectedOptions[$group->id] = null;
            }
        }
        $this->showOptionsModal = true;
    }

    public function confirmOptions()
    {
        // Validation logic could go here
        $finalOptions = [];
        $totalOptionsPrice = 0;
        
        foreach ($this->selectedOptions as $groupId => $selection) {
            $group = OptionGroup::find($groupId);
            if (!$selection) continue;
            
            $options = is_array($selection) ? $selection : [$selection];
            
            foreach ($options as $optId) {
                $opt = \App\Models\Option::find($optId);
                if ($opt) {
                    $finalOptions[] = [
                        'name' => $group->name . ': ' . $opt->name,
                        'price' => $opt->price_modifier
                    ];
                    $totalOptionsPrice += $opt->price_modifier;
                }
            }
        }
        
        $this->addToCart($this->selectedProductForOptions, $finalOptions, $totalOptionsPrice);
        $this->showOptionsModal = false;
        $this->selectedProductForOptions = null;
    }

    public function addToCart(Product $product, array $options = [], $optionsPrice = 0)
    {
        $basePrice = $product->getPriceByType(OrderType::from($this->orderType));
        $finalPrice = $basePrice + $optionsPrice;

        $this->cart[] = [
            'item_id' => null, // New item
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $finalPrice,
            'qty' => 1,
            'options' => $options,
            'status' => 'pending',
        ];
    }
    
    public function removeItem($index)
    {
        // Only remove pending items or handle void logic for sent items
        if ($this->cart[$index]['status'] === 'pending') {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart); // Re-index
        }
    }

    public function sendToKitchen()
    {
        $pendingItems = collect($this->cart)->where('status', 'pending');
        if ($pendingItems->isEmpty()) return;

        DB::transaction(function () use ($pendingItems) {
            // 1. Create or Update Order
            $order = Order::updateOrCreate(
                ['id' => $this->currentOrderId],
                [
                    'table_id' => $this->table->id,
                    'user_id' => auth()->id(),
                    'status' => 'in_progress',
                    'type' => $this->orderType,
                    'total_amount' => collect($this->cart)->sum(fn($i) => $i['price'] * $i['qty']),
                ]
            );
            $this->currentOrderId = $order->id;

            // 2. Save Items
            foreach ($pendingItems as $index => $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'], // Unit price including options
                    'total' => $item['price'] * $item['qty'],
                    'options' => $item['options'],
                    'status' => 'sent',
                ]);
                
                // Update local cart to reflect sent status
                $this->cart[$index]['status'] = 'sent';
                $this->cart[$index]['item_id'] = $orderItem->id;
            }

            // 3. Update Table
            $this->table->update([
                'status' => 'occupied',
                'current_order_uuid' => $order->id
            ]);
        });

        $this->dispatch('notify', 'Envoyé en cuisine !', 'success');
        // Redirect back to map
        return redirect()->route('pos'); 
    }

    public function getCartTotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
    }

    public function render()
    {
        return view('livewire.pos.pos-order-page');
    }
}
