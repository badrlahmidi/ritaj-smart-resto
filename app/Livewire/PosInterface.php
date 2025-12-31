<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use App\Services\PrinterService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class PosInterface extends Component
{
    // State: 'tables' or 'order'
    public string $view = 'tables';
    
    // Selection
    public ?int $selectedTableId = null;
    public ?string $selectedTableOrderUuid = null;
    public ?int $activeCategoryId = null;

    // Cart
    public array $cart = [];

    public function render()
    {
        return view('livewire.pos-interface', [
            'tables' => Table::all(),
            'categories' => Category::where('is_active', true)->get(),
            'products' => $this->activeCategoryId 
                ? Product::where('category_id', $this->activeCategoryId)->where('is_available', true)->get()
                : collect(), // Show nothing or favorites if no category selected
        ]);
    }

    public function selectTable($tableId)
    {
        $this->selectedTableId = $tableId;
        $table = Table::find($tableId);
        
        // Load existing order if any
        if ($table->current_order_uuid) {
            $this->loadOrder($table->current_order_uuid);
        } else {
            $this->resetCart();
        }

        $this->view = 'order';
    }

    public function selectCategory($categoryId)
    {
        $this->activeCategoryId = $categoryId;
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'notes' => ''
            ];
        }
    }

    public function removeFromCart($productId)
    {
        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['quantity'] > 1) {
                $this->cart[$productId]['quantity']--;
            } else {
                unset($this->cart[$productId]);
            }
        }
    }

    public function sendOrder(PrinterService $printer)
    {
        if (empty($this->cart)) return;

        // On capture l'UUID pour l'utiliser hors de la transaction
        $orderUuid = null;

        DB::transaction(function () use (&$orderUuid) {
            $table = Table::find($this->selectedTableId);
            
            // Create or Get Order
            if (!$table->current_order_uuid) {
                $order = Order::create([
                    'uuid' => Str::uuid(),
                    'table_id' => $table->id,
                    'waiter_id' => Auth::id(), // Use logged in filament user
                    'status' => 'sent_to_kitchen',
                    'sync_status' => false
                ]);
                
                $table->update(['current_order_uuid' => $order->uuid]);
                $this->selectedTableOrderUuid = $order->uuid;
            } else {
                $order = Order::find($table->current_order_uuid);
                // Si une commande existait, on s'assure qu'elle repasse en "sent_to_kitchen" si elle était pending
                if ($order->status === 'pending') {
                    $order->update(['status' => 'sent_to_kitchen']);
                }
            }
            
            $orderUuid = $order->uuid;

            // Add Items
            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_uuid' => $order->uuid,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'printed_kitchen' => false
                ]);
            }

            // Update Total
            $order->total_amount += collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']);
            $order->save();
        });

        // Trigger PrinterService Logic
        if ($orderUuid) {
            $order = Order::find($orderUuid);
            $printed = $printer->printKitchenTicket($order);
            
            if (!$printed) {
                 session()->flash('warning', 'Commande envoyée mais erreur impression (Vérifiez les logs)');
            } else {
                 session()->flash('success', 'Commande envoyée et imprimée !');
            }
        }
        
        $this->resetCart();
        $this->view = 'tables'; // Go back to table view
        $this->dispatch('$refresh'); // Force Livewire to reload table statuses
    }

    private function loadOrder($uuid)
    {
        $this->selectedTableOrderUuid = $uuid;
        // In a real app we might load existing items to show them, 
        // but for now we just handle new items in cart.
        $this->resetCart(); 
    }

    private function resetCart()
    {
        $this->cart = [];
        $this->activeCategoryId = null;
    }
}
