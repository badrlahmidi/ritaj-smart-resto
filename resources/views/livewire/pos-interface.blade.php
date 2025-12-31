<div>
    @if($view === 'tables')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4">
            @foreach($tables as $table)
                <button wire:click="selectTable({{ $table->id }})"
                    class="p-6 rounded-xl shadow-lg text-center transition transform active:scale-95 flex flex-col items-center justify-center h-32
                    {{ $table->current_order_uuid ? 'bg-red-100 border-2 border-red-500' : 'bg-green-100 border-2 border-green-500' }}">
                    <span class="text-xl font-bold {{ $table->current_order_uuid ? 'text-red-700' : 'text-green-700' }}">
                        {{ $table->name }}
                    </span>
                    @if($table->current_order_uuid)
                        <span class="text-xs text-red-600 mt-2">Occupé</span>
                    @else
                        <span class="text-xs text-green-600 mt-2">Libre</span>
                    @endif
                </button>
            @endforeach
        </div>
    @elseif($view === 'order')
        <div class="flex flex-col h-[calc(100vh-4rem)]">
            <!-- Header: Table Name & Back -->
            <div class="flex justify-between items-center p-2 bg-white shadow-sm border-b">
                <button wire:click="$set('view', 'tables')" class="px-4 py-2 bg-gray-200 rounded-lg font-bold">
                    ← Retour
                </button>
                <h2 class="text-lg font-bold">Table #{{ $selectedTableId }}</h2>
                <div class="w-20"></div> <!-- Spacer -->
            </div>

            <div class="flex flex-1 overflow-hidden">
                <!-- Left: Categories & Products -->
                <div class="w-2/3 flex flex-col border-r bg-gray-50">
                    <!-- Categories (Scroll Horizontal) -->
                    <div class="flex overflow-x-auto p-2 gap-2 bg-white border-b h-16 shrink-0">
                        @foreach($categories as $cat)
                            <button wire:click="selectCategory({{ $cat->id }})"
                                class="px-4 py-1 rounded-full whitespace-nowrap border text-sm font-semibold
                                {{ $activeCategoryId === $cat->id ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Products Grid -->
                    <div class="flex-1 overflow-y-auto p-2">
                        @if($activeCategoryId)
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($products as $product)
                                    <button wire:click="addToCart({{ $product->id }})" class="bg-white p-3 rounded-lg shadow border flex flex-col items-center active:bg-blue-50">
                                        @if($product->image_url)
                                            <img src="{{ Storage::url($product->image_url) }}" class="h-16 w-16 object-cover rounded mb-2">
                                        @else
                                            <div class="h-16 w-16 bg-gray-200 rounded mb-2 flex items-center justify-center">🍽️</div>
                                        @endif
                                        <span class="text-sm font-bold text-center leading-tight">{{ $product->name }}</span>
                                        <span class="text-xs text-gray-500 mt-1">{{ number_format($product->price, 2) }} MAD</span>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="h-full flex items-center justify-center text-gray-400">
                                Sélectionnez une catégorie
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right: Cart -->
                <div class="w-1/3 flex flex-col bg-white">
                    <div class="flex-1 overflow-y-auto p-2">
                        @if(count($cart) > 0)
                            @foreach($cart as $id => $item)
                                <div class="flex justify-between items-center mb-2 p-2 border-b last:border-0">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm">{{ $item['name'] }}</span>
                                        <span class="text-xs text-gray-500">{{ $item['price'] }} x {{ $item['quantity'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button wire:click="removeFromCart({{ $id }})" class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center">-</button>
                                        <span class="font-bold">{{ $item['quantity'] }}</span>
                                        <button wire:click="addToCart({{ $id }})" class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center">+</button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="p-4 text-center text-gray-400 text-sm">Panier vide</div>
                        @endif
                    </div>

                    <!-- Footer: Total & Actions -->
                    <div class="p-3 border-t bg-gray-50">
                        <div class="flex justify-between mb-3 text-lg font-bold">
                            <span>Total:</span>
                            <span>{{ number_format(collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']), 2) }} MAD</span>
                        </div>
                        <button wire:click="sendOrder" 
                            class="w-full py-3 bg-green-600 text-white rounded-lg font-bold shadow-lg active:bg-green-700 disabled:opacity-50"
                            @if(empty($cart)) disabled @endif>
                            ENVOYER CUISINE 🚀
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
