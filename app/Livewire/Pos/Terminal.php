<?php

namespace App\Livewire\Pos;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Services\Printing\ReceiptPrinterService;
use App\Settings\GeneralSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.pos')]
class Terminal extends Component
{
    // Data
    public $categories;
    public $currentCategoryId;
    public $products;
    
    // State
    public $cart = []; // [product_id => ['qty' => 1, 'price' => 10, 'name' => 'Pizza']]
    public $selectedTableId = null;
    public $orderType = 'dine_in'; // dine_in, takeaway
    
    // UI
    public $search = '';

    public function mount()
    {
        $this->categories = Category::where('is_active', true)->get();
        if ($this->categories->isNotEmpty()) {
            $this->currentCategoryId = $this->categories->first()->id;
        }
        $this->loadProducts();
    }

    public function loadProducts()
    {
        $query = Product::where('is_available', true);
        
        if ($this->currentCategoryId) {
            $query->where('category_id', $this->currentCategoryId);
        }
        
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $this->products = $query->get();
    }

    public function selectCategory($id)
    {
        $this->currentCategoryId = $id;
        $this->search = '';
        $this->loadProducts();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
            $this->cart[$productId]['total'] = $this->cart[$productId]['quantity'] * $this->cart[$productId]['price'];
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'total' => $product->price,
                'image' => $product->image_url,
            ];
        }
    }

    public function removeFromCart($productId)
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']--;
            if ($this->cart[$productId]['quantity'] <= 0) {
                unset($this->cart[$productId]);
            } else {
                $this->cart[$productId]['total'] = $this->cart[$productId]['quantity'] * $this->cart[$productId]['price'];
            }
        }
    }

    public function updatedSearch()
    {
        $this->currentCategoryId = null; // Search globally
        $this->loadProducts();
    }

    public function getTotalProperty()
    {
        return collect($this->cart)->sum('total');
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Le panier est vide !', type: 'error');
            return;
        }

        // Ensure we have a valid user ID
        $userId = auth()->id();
        if (!$userId) {
            // Fallback: Get the first available server or admin
            $userId = \App\Models\User::whereIn('role', ['server', 'admin'])->value('id');
            if (!$userId) {
                $this->dispatch('notify', message: 'Erreur: Aucun utilisateur trouvé pour attribuer la commande.', type: 'error');
                return;
            }
        }

        // Create Order
        $order = Order::create([
            'uuid' => Str::uuid(),
            'user_id' => $userId,
            'table_id' => $this->selectedTableId ?: null, // Ensure empty string becomes null
            'status' => 'paid',
            'payment_status' => 'paid',
            'type' => $this->orderType,
            'total_amount' => $this->getTotalProperty(),
            'created_at' => now(),
        ]);

        // Create Items
        foreach ($this->cart as $item) {
            $order->items()->create([
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price' => $item['total'],
                'status' => 'sent',
                'printed_kitchen' => false,
            ]);
            
             // Stock Logic
             $product = Product::find($item['id']);
             if($product && $product->has_stock) {
                 $product->deductStock($item['quantity'], 'sale', 'POS #' . $order->local_id);
             }
        }

        // DIRECT PRINTING (V2)
        try {
            $printerService = new ReceiptPrinterService();
            $printerService->printOrder($order);
            $this->dispatch('notify', message: 'Commande enregistrée et imprimée !', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Erreur impression: ' . $e->getMessage(), type: 'warning');
        }
        
        $this->reset(['cart', 'selectedTableId']);
        
        $this->dispatch('order-created', orderId: $order->local_id);
    }

    public function render()
    {
        return view('livewire.pos.terminal', [
            'tables' => Table::all(),
        ]);
    }
}