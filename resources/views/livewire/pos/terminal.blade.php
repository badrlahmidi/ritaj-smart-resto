<div class="flex h-screen overflow-hidden bg-gray-100">
    
    <!-- LEFT SIDE: MENU & PRODUCTS -->
    <div class="flex-1 flex flex-col h-full">
        <!-- Header / Search -->
        <div class="bg-white p-4 shadow-sm z-10 flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-800">Ritaj POS</h1>
            <div class="w-1/3">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." 
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div class="flex gap-2">
                <button wire:click="$set('orderType', 'dine_in')" 
                        class="px-4 py-2 rounded-lg font-bold {{ $orderType === 'dine_in' ? 'bg-amber-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                    Sur Place
                </button>
                <button wire:click="$set('orderType', 'takeaway')" 
                        class="px-4 py-2 rounded-lg font-bold {{ $orderType === 'takeaway' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                    Emporter
                </button>
            </div>
        </div>

        <!-- Categories Tabs -->
        <div class="bg-white border-b border-gray-200 px-4 py-2 flex gap-3 overflow-x-auto scrollbar-hide whitespace-nowrap">
            <button wire:click="$set('currentCategoryId', null)"
                    class="px-6 py-3 rounded-xl font-bold transition-all shadow-sm
                           {{ is_null($currentCategoryId) && empty($search) ? 'bg-gray-800 text-white scale-105' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Tout
            </button>
            @foreach($categories as $category)
                <button wire:click="selectCategory({{ $category->id }})"
                        class="px-6 py-3 rounded-xl font-bold transition-all shadow-sm flex items-center gap-2
                               {{ $currentCategoryId === $category->id ? 'bg-amber-500 text-white scale-105' : 'bg-white text-gray-600 border hover:bg-gray-50' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        <!-- Products Grid -->
        <div class="flex-1 overflow-y-auto p-4 bg-gray-100">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($products as $product)
                    <button wire:click="addToCart({{ $product->id }})" 
                            class="bg-white rounded-2xl p-3 shadow-sm hover:shadow-md transition-all active:scale-95 flex flex-col h-full border border-gray-100">
                        <div class="aspect-square w-full rounded-xl bg-gray-50 mb-3 overflow-hidden relative">
                            @if($product->image_url)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_url) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            <div class="absolute bottom-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded-lg text-xs font-bold shadow-sm">
                                {{ number_format($product->price, 0) }} DH
                            </div>
                        </div>
                        <div class="text-left">
                            <h3 class="font-bold text-gray-800 leading-tight line-clamp-2">{{ $product->name }}</h3>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- RIGHT SIDE: CART -->
    <div class="w-1/3 min-w-[350px] bg-white border-l border-gray-200 flex flex-col h-full shadow-xl z-20">
        <!-- User Info -->
        <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white font-bold">
                    {{ substr(auth()->user()->name ?? 'Guest', 0, 1) }}
                </div>
                <span class="font-bold text-gray-700">{{ auth()->user()->name ?? 'Guest' }}</span>
            </div>
            <div>
                <select wire:model.live="selectedTableId" class="text-sm rounded-lg border-gray-300 py-1">
                    <option value="">-- Table --</option>
                    @foreach($tables as $table)
                        <option value="{{ $table->id }}">{{ $table->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            @if(empty($cart))
                <div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-50">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <p>Panier vide</p>
                    <p class="text-xs">Sélectionnez des produits à gauche</p>
                </div>
            @else
                @foreach($cart as $item)
                    <div class="flex items-center justify-between bg-white rounded-xl p-2 border border-gray-100 shadow-sm animate-pulse-once">
                        <div class="flex items-center gap-3">
                            <button wire:click="removeFromCart({{ $item['id'] }})" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center font-bold text-lg">-</button>
                            <span class="font-bold w-6 text-center">{{ $item['quantity'] }}</span>
                            <button wire:click="addToCart({{ $item['id'] }})" class="w-8 h-8 rounded-lg bg-green-50 text-green-500 hover:bg-green-100 flex items-center justify-center font-bold text-lg">+</button>
                            
                            <div class="ml-2">
                                <div class="font-bold text-gray-800 text-sm">{{ $item['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ number_format($item['price'], 2) }} DH/u</div>
                            </div>
                        </div>
                        <div class="font-bold text-amber-600">
                            {{ number_format($item['total'], 2) }}
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Totals & Actions -->
        <div class="bg-gray-50 p-6 border-t border-gray-200">
            <div class="flex justify-between items-end mb-6">
                <span class="text-gray-500 text-lg">Total à payer</span>
                <span class="text-4xl font-extrabold text-gray-900">{{ number_format($this->total, 2) }} <span class="text-xl text-gray-500">DH</span></span>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <button wire:click="$set('cart', [])" 
                        class="py-4 rounded-xl font-bold text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 shadow-sm">
                    Annuler
                </button>
                <button wire:click="checkout" 
                        class="py-4 rounded-xl font-bold text-white bg-green-600 hover:bg-green-700 shadow-lg shadow-green-200 active:scale-95 transition-transform flex items-center justify-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    ENCAISSER
                </button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('order-created', (event) => {
                new Audio('/sounds/success.mp3').play().catch(e => {}); // Optional sound
                // alert('Commande #' + event.orderId + ' enregistrée !'); 
                // Could trigger print here via JS bridging if needed
            });
        });
    </script>
</div>