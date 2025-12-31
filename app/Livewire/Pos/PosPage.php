<?php

namespace App\Livewire\Pos;

use App\Enums\OrderType;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.pos-app')]
class PosPage extends Component
{
    public $selectedCategoryId = null;
    public $orderType = 'dine_in'; // Default to string for easier frontend handling
    public $search = '';
    public $cart = []; // [product_id => ['id', 'name', 'qty', 'price', 'total']]

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
        $this->search = ''; // Reset search when changing category
    }

    public function setOrderType($type)
    {
        $this->orderType = $type;
        $this->recalculatePrices();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        // Get smart price based on current Order Type
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
        // When order type changes, update all prices in cart
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

    public function render()
    {
        return view('livewire.pos.pos-page');
    }
}
