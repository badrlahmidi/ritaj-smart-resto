<?php

namespace App\Livewire\Pos;

use App\Enums\OrderType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.pos-app')]
class PosPage extends Component
{
    public $selectedCategoryId = null;
    public $orderType = 'dine_in'; 
    public $selectedTableId = null;
    public $search = '';
    public $cart = []; 

    public function mount()
    {
        $firstCategory = Category::where('is_active', true)->first();
        $this->selectedCategoryId = $firstCategory?->id;
    }

    #[Computed]
    public function categories()
    {
        return Category::where('is_active', true)->get();
    }

    #[Computed]
    public function tables()
    {
        return Table::where('status', '!=', 'occupied')->get();
    }

    #[Computed]
    public function products()
    {
        $query = Product::where('is_available', true);

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

    public function setOrderType($type)
    {
        $this->orderType = $type;
        // Reset table if switching away from dine_in
        if ($type !== OrderType::DINE_IN->value) {
            $this->selectedTableId = null;
        }
        $this->recalculatePrices();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        $price = $product->getPriceByType(OrderType::from($this->orderType));

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'image_url' => $product->image_url,
                'qty' => 1,
                'price' => $price,
            ];
        }
    }

    public function updateQuantity($productId, $change)
    {
        if (!isset($this->cart[$productId])) return;

        $this->cart[$productId]['qty'] += $change;

        if ($this->cart[$productId]['qty'] <= 0) {
            unset($this->cart[$productId]);
        }
    }

    public function recalculatePrices()
    {
        foreach ($this->cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                $this->cart[$productId]['price'] = $product->getPriceByType(OrderType::from($this->orderType));
            }
        }
    }

    public function getCartTotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
    }

    public function sendToKitchen()
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', 'Panier vide !', 'error');
            return;
        }

        if ($this->orderType === OrderType::DINE_IN->value && !$this->selectedTableId) {
            $this->dispatch('notify', 'Veuillez sélectionner une table !', 'error');
            return;
        }

        DB::transaction(function () {
            // 1. Create Order
            $order = Order::create([
                'table_id' => $this->selectedTableId,
                'user_id' => auth()->id(),
                'status' => 'sent_to_kitchen', // Triggers Observer logic (Stock deduction)
                'type' => $this->orderType,
                'total_amount' => $this->cartTotal,
            ]);

            // 2. Create Order Items
            foreach ($this->cart as $item) {
                $order->items()->create([
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['qty'],
                ]);
            }

            // 3. Update Table Status
            if ($this->selectedTableId) {
                Table::where('id', $this->selectedTableId)->update([
                    'status' => 'occupied',
                    'current_order_uuid' => $order->id // or uuid if using uuid trait
                ]);
            }
        });

        // Reset UI
        $this->cart = [];
        $this->selectedTableId = null;
        $this->dispatch('notify', 'Commande envoyée en cuisine !', 'success');
    }

    public function render()
    {
        return view('livewire.pos.pos-page');
    }
}
