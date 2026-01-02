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
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.pos-app')]
class PosPage extends Component
{
    // View State
    public $view = 'ordering'; // ordering, payment
    
    // Selection State
    public $selectedCategoryId = null;
    public $orderType = 'dine_in'; 
    public $selectedTableId = null;
    public $selectedAreaId = null; 
    public $search = '';
    
    // Cart State
    public $cart = []; // ['product_id' => [...]] OR indexed for options support
    public $currentOrderUuid = null;
    
    // Modal State
    public $showOptionsModal = false;
    public $selectedProductForOptions = null;
    public $selectedOptions = []; // ['group_id' => ['option_id', ...]]
    
    // Payment State
    public $paymentMethod = 'cash';
    public $amountTendered = 0;
    public $changeDue = 0;
    public $numericInput = '';

    public $isMobile = false;

    public function mount()
    {
        $this->selectedCategoryId = Category::where('is_active', true)->first()?->id;
        $this->selectedAreaId = Area::where('is_active', true)->first()?->id;
        
        $userAgent = request()->header('User-Agent');
        $this->isMobile = $userAgent && preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|rim)|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($userAgent, 0, 4));
    }

    #[Computed]
    public function categories()
    {
        return Category::where('is_active', true)->get();
    }

    #[Computed]
    public function areas()
    {
        return Area::where('is_active', true)->with('tables')->get();
    }

    #[Computed]
    public function tables()
    {
        if (!$this->selectedAreaId) return collect();
        return Table::where('area_id', $this->selectedAreaId)->get();
    }

    #[Computed]
    public function products()
    {
        $query = Product::where('is_available', true);
        if ($this->selectedCategoryId) $query->where('category_id', $this->selectedCategoryId);
        if ($this->search) $query->where('name', 'like', '%' . $this->search . '%');
        return $query->get();
    }

    // --- NAVIGATION & SELECTION ---

    public function selectCategory($id)
    {
        $this->selectedCategoryId = $id;
        $this->search = ''; 
    }
    
    public function selectArea($id)
    {
        $this->selectedAreaId = $id;
    }

    public function selectTable($id)
    {
        if ($this->selectedTableId === $id) {
            $this->selectedTableId = null;
            $this->resetCart();
            return;
        }

        $this->selectedTableId = $id;
        $table = Table::find($id);

        if ($table && $table->status === 'occupied' && $table->current_order_uuid) {
            $this->loadExistingOrder($table->current_order_uuid);
        } else {
            $this->resetCart();
        }
    }

    public function setOrderType($type)
    {
        $this->orderType = $type;
        if ($type !== 'dine_in') {
            $this->selectedTableId = null;
            $this->currentOrderUuid = null;
        }
    }

    // --- CART LOGIC ---

    public function loadExistingOrder($orderUuid)
    {
        $order = Order::where('uuid', $orderUuid)->first();
        if (!$order) return;

        $this->currentOrderUuid = $order->uuid;
        $this->orderType = $order->type;
        $this->cart = [];

        foreach ($order->items as $item) {
            $this->cart[] = [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => (float)$item->unit_price,
                'qty' => $item->quantity,
                'options' => $item->options ?? [],
                'status' => $item->status, // 'sent', 'served'
            ];
        }
    }

    public function selectProduct($productId)
    {
        $product = Product::with('optionGroups')->find($productId);
        if (!$product) return;

        if ($product->optionGroups->count() > 0) {
            $this->openOptionsModal($product);
        } else {
            $this->addToCart($product, []);
        }
    }

    public function openOptionsModal(Product $product)
    {
        $this->selectedProductForOptions = $product;
        $this->selectedOptions = [];
        foreach ($product->optionGroups as $group) {
            $this->selectedOptions[$group->id] = $group->is_multiselect ? [] : null;
        }
        $this->showOptionsModal = true;
    }

    public function confirmOptions()
    {
        $finalOptions = [];
        $optionsPrice = 0;
        
        foreach ($this->selectedOptions as $groupId => $selection) {
            if (!$selection) continue;
            $group = OptionGroup::find($groupId);
            $options = is_array($selection) ? $selection : [$selection];
            
            foreach ($options as $optId) {
                $opt = \App\Models\Option::find($optId);
                if ($opt) {
                    $finalOptions[] = ['name' => $group->name . ': ' . $opt->name, 'price' => $opt->price_modifier];
                    $optionsPrice += $opt->price_modifier;
                }
            }
        }
        
        $this->addToCart($this->selectedProductForOptions, $finalOptions, $optionsPrice);
        $this->showOptionsModal = false;
    }

    public function addToCart(Product $product, array $options = [], $optionsPrice = 0)
    {
        $basePrice = $product->getPriceByType(OrderType::from($this->orderType));
        $finalPrice = $basePrice + $optionsPrice;

        $this->cart[] = [
            'item_id' => null,
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $finalPrice,
            'qty' => 1,
            'options' => $options,
            'status' => 'pending',
        ];
    }

    public function updateQuantity($index, $change)
    {
        if (!isset($this->cart[$index])) return;
        if ($this->cart[$index]['status'] !== 'pending') return;

        $this->cart[$index]['qty'] += $change;
        if ($this->cart[$index]['qty'] <= 0) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
        }
    }

    public function resetCart()
    {
        $this->cart = [];
        $this->currentOrderUuid = null;
    }

    public function getCartTotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
    }

    // --- ACTIONS ---

    public function sendToKitchen()
    {
        $pendingItems = collect($this->cart)->where('status', 'pending');
        if ($pendingItems->isEmpty()) return;

        if ($this->orderType === 'dine_in' && !$this->selectedTableId) {
            $this->dispatch('notify', 'Sélectionnez une table !', 'error');
            return;
        }

        DB::transaction(function () use ($pendingItems) {
            $order = Order::updateOrCreate(
                ['uuid' => $this->currentOrderUuid],
                [
                    'table_id' => $this->selectedTableId,
                    'user_id' => auth()->id(),
                    'status' => 'sent_to_kitchen',
                    'type' => $this->orderType,
                    'total_amount' => $this->cartTotal,
                ]
            );

            foreach ($pendingItems as $index => $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['qty'],
                    'options' => $item['options'],
                    'status' => 'sent',
                ]);
                
                $this->cart[$index]['status'] = 'sent';
                $this->cart[$index]['item_id'] = $orderItem->id;
            }

            if ($this->selectedTableId) {
                Table::where('id', $this->selectedTableId)->update([
                    'status' => 'occupied',
                    'current_order_uuid' => $order->uuid
                ]);
            }
            
            $this->currentOrderUuid = $order->uuid;
        });

        $this->dispatch('notify', 'Envoyé en cuisine !', 'success');
    }

    // --- PAYMENT LOGIC ---

    public function goToPayment()
    {
        if (empty($this->cart)) return;
        $this->amountTendered = 0;
        $this->changeDue = 0;
        $this->numericInput = '';
        $this->view = 'payment';
    }

    public function setPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        if ($method !== 'cash') {
            $this->amountTendered = $this->cartTotal;
            $this->calculateChange();
        }
    }

    public function appendNumber($number)
    {
        $this->numericInput .= $number;
        $this->amountTendered = (float)$this->numericInput;
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
        $this->changeDue = max(0, $this->amountTendered - $this->cartTotal);
    }

    public function processPayment()
    {
        if ($this->paymentMethod === 'cash' && $this->amountTendered < $this->cartTotal) {
            $this->dispatch('notify', 'Montant insuffisant !', 'error');
            return;
        }

        DB::transaction(function () {
            // 1. Ensure order is saved
            $order = Order::where('uuid', $this->currentOrderUuid)->first();
            if (!$order) {
                $order = Order::create([
                    'table_id' => $this->selectedTableId,
                    'user_id' => auth()->id(),
                    'status' => 'sent_to_kitchen',
                    'type' => $this->orderType,
                    'total_amount' => $this->cartTotal,
                ]);
                foreach ($this->cart as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['qty'],
                        'unit_price' => $item['price'],
                        'total_price' => $item['price'] * $item['qty'],
                        'options' => $item['options'],
                        'status' => 'sent',
                    ]);
                }
            }

            // 2. Create Payment
            Payment::create([
                'order_uuid' => $order->uuid,
                'amount' => $this->cartTotal,
                'payment_method' => $this->paymentMethod,
                'amount_tendered' => $this->paymentMethod === 'cash' ? $this->amountTendered : null,
                'change_due' => $this->paymentMethod === 'cash' ? $this->changeDue : null,
                'user_id' => auth()->id(),
            ]);

            // 3. Close Order
            $order->update(['status' => 'paid', 'payment_status' => 'paid']);

            // 4. Free Table
            if ($order->table_id) {
                Table::where('id', $order->table_id)->update(['status' => 'available', 'current_order_uuid' => null]);
            }
        });

        $this->resetCart();
        $this->selectedTableId = null;
        $this->view = 'ordering';
        $this->dispatch('notify', 'Paiement validé !', 'success');
    }

    public function render()
    {
        return view('livewire.pos.pos-page');
    }
}