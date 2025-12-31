<div class="flex h-full w-full bg-gray-100 font-sans" 
     x-data="{ notification: { show: false, message: '', type: 'success' } }"
     x-on:notify.window="notification.show = true; notification.message = $event.detail[0]; notification.type = $event.detail[1] || 'success'; setTimeout(() => notification.show = false, 3000)">

    <!-- NOTIFICATION TOAST -->
    <div x-show="notification.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-xl font-bold text-white flex items-center gap-2"
         :class="notification.type === 'error' ? 'bg-red-500' : 'bg-green-500'"
         style="display: none;">
         <span x-text="notification.message"></span>
    </div>
    
    <!-- LEFT: MENU & PRODUCTS (60%) -->
    <div class="flex-1 flex flex-col h-full overflow-hidden border-r border-gray-200">
        
        <!-- HEADER: CATEGORIES -->
        <div class="bg-white shadow-sm z-10 p-2">
            <div class="flex space-x-2 overflow-x-auto no-scrollbar pb-2">
                @foreach($this->categories as $category)
                    <button 
                        wire:click="selectCategory({{ $category->id }})"
                        class="px-6 py-3 rounded-xl text-sm font-bold whitespace-nowrap transition-all duration-200
                        {{ $selectedCategoryId === $category->id 
                            ? 'bg-gray-900 text-white shadow-md transform scale-105' 
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                    >
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
            <!-- Search Bar -->
            <div class="mt-2">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher un plat..." class="w-full px-4 py-2 rounded-lg bg-gray-100 border-none focus:ring-2 focus:ring-yellow-500 text-gray-800">
            </div>
        </div>

        <!-- PRODUCT GRID -->
        <div class="flex-1 overflow-y-auto p-4 bg-gray-100">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 pb-20">
                @foreach($this->products as $product)
                    <button 
                        wire:click="addToCart({{ $product->id }})"
                        class="bg-white rounded-2xl p-3 shadow-sm hover:shadow-md transition-all duration-150 active:scale-95 flex flex-col justify-between h-48 group relative overflow-hidden"
                    >
                        <!-- Image Background or Placeholder -->
                        <div class="absolute inset-0 z-0 opacity-10 group-hover:opacity-20 transition-opacity">
                             @if($product->image_url)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_url) }}" class="w-full h-full object-cover">
                             @else
                                <div class="w-full h-full bg-gray-300"></div>
                             @endif
                        </div>

                        <div class="z-10 w-full text-left">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $product->category->name }}</span>
                            <h3 class="font-bold text-gray-800 leading-tight mt-1">{{ $product->name }}</h3>
                        </div>

                        <div class="z-10 w-full flex justify-between items-end mt-2">
                            <span class="text-lg font-black text-gray-900">
                                {{ number_format($product->getPriceByType($orderType), 0) }} <span class="text-xs font-normal text-gray-500">DH</span>
                            </span>
                            <div class="bg-yellow-400 text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg group-hover:bg-yellow-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- RIGHT: CART & TABLE SELECTION (40%) -->
    <div class="w-[450px] bg-white shadow-xl flex flex-col h-full z-20 border-l border-gray-200">
        
        <!-- ORDER TYPE SELECTOR -->
        <div class="p-3 bg-gray-50 border-b border-gray-200 grid grid-cols-3 gap-1">
            @foreach(\App\Enums\OrderType::cases() as $type)
                <button 
                    wire:click="setOrderType('{{ $type->value }}')"
                    class="py-2 px-1 text-xs font-bold uppercase rounded-lg border-2 transition-colors
                    {{ $orderType === $type->value 
                        ? 'border-' . $type->getColor() . '-500 bg-' . $type->getColor() . '-50 text-' . $type->getColor() . '-700' 
                        : 'border-transparent text-gray-400 hover:bg-gray-100' }}"
                >
                    {{ $type->getLabel() }}
                </button>
            @endforeach
        </div>

        <!-- VISUAL TABLE SELECTOR (Only for Dine-in) -->
        @if($orderType === \App\Enums\OrderType::DINE_IN->value)
            <div class="flex-1 flex flex-col bg-gray-100 border-b border-gray-200 min-h-[300px] overflow-hidden">
                <!-- Areas Tabs -->
                <div class="flex overflow-x-auto bg-white border-b p-1">
                    @foreach($this->areas as $area)
                        <button 
                            wire:click="selectArea({{ $area->id }})"
                            class="px-4 py-2 text-sm font-bold whitespace-nowrap rounded-lg mr-1 transition-colors
                            {{ $selectedAreaId === $area->id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                        >
                            {{ $area->name }}
                        </button>
                    @endforeach
                </div>

                <!-- Tables Grid -->
                <div class="flex-1 p-3 overflow-y-auto bg-gray-200">
                    <div class="grid grid-cols-4 gap-3">
                        @foreach($this->tables as $table)
                            <button 
                                wire:click="selectTable({{ $table->id }})"
                                class="aspect-square rounded-xl flex flex-col items-center justify-center shadow-sm border-2 transition-all relative
                                {{ $selectedTableId === $table->id 
                                    ? 'border-blue-500 bg-blue-50 scale-105 ring-2 ring-blue-200' 
                                    : ($table->status === 'occupied' 
                                        ? 'border-red-400 bg-red-100 opacity-90' 
                                        : 'border-white bg-white hover:border-gray-300') }}"
                            >
                                <span class="text-lg font-bold {{ $selectedTableId === $table->id ? 'text-blue-700' : 'text-gray-700' }}">
                                    {{ $table->name }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $table->capacity }}p</span>
                                
                                @if($table->status === 'occupied')
                                    <div class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
                
                @if($selectedTableId)
                    <div class="bg-blue-600 text-white text-center py-1 text-xs font-bold uppercase tracking-wider">
                        Table Sélectionnée : {{ \App\Models\Table::find($selectedTableId)?->name }}
                    </div>
                @endif
            </div>
        @endif

        <!-- CART ITEMS LIST (Compact) -->
        <div class="flex-1 overflow-y-auto p-4 space-y-2 bg-white">
            <h3 class="text-xs font-bold text-gray-400 uppercase border-b pb-1 mb-2">Commande en cours</h3>
            @forelse($cart as $id => $item)
                <div class="flex items-center justify-between group">
                    <div class="flex-1">
                        <div class="font-bold text-sm text-gray-800">{{ $item['name'] }}</div>
                        <div class="text-xs text-gray-500">{{ number_format($item['price'], 2) }}</div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <button wire:click="updateQuantity({{ $id }}, -1)" class="w-6 h-6 flex items-center justify-center bg-gray-100 rounded text-red-500 font-bold hover:bg-red-50">-</button>
                        <span class="font-bold text-sm w-4 text-center">{{ $item['qty'] }}</span>
                        <button wire:click="updateQuantity({{ $id }}, 1)" class="w-6 h-6 flex items-center justify-center bg-gray-100 rounded text-green-500 font-bold hover:bg-green-50">+</button>
                    </div>

                    <div class="ml-3 font-bold text-sm w-14 text-right">
                        {{ number_format($item['price'] * $item['qty'], 0) }}
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-gray-400 text-sm italic">Aucun article</div>
            @endforelse
        </div>

        <!-- FOOTER: TOTALS -->
        <div class="bg-gray-900 p-4 text-white">
            <div class="flex justify-between items-center mb-4">
                <span class="text-gray-400">Total</span>
                <span class="text-3xl font-bold text-yellow-500">{{ number_format($this->cartTotal, 2) }} <span class="text-sm">DH</span></span>
            </div>

            <!-- ACTIONS -->
            <button 
                wire:click="sendToKitchen"
                wire:loading.attr="disabled"
                class="w-full mt-3 bg-green-600 hover:bg-green-500 text-white font-bold py-4 rounded-xl shadow-lg transform active:scale-95 transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="sendToKitchen">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    ENVOYER EN CUISINE
                </span>
                <span wire:loading wire:target="sendToKitchen">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Traitement...
                </span>
            </button>
        </div>
    </div>
</div>
