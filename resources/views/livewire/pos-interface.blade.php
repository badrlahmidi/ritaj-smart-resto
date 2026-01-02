<div class="h-full w-full flex flex-col lg:flex-row overflow-hidden bg-gray-50 dark:bg-gray-900">
    
    <!-- Zone 1: Catégories (Sidebar Gauche) -->
    <div class="w-full lg:w-64 bg-white dark:bg-gray-800 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-700 flex lg:flex-col overflow-x-auto lg:overflow-y-auto z-10 shadow-sm flex-shrink-0">
        <div class="p-4 font-bold text-gray-500 uppercase text-xs hidden lg:block tracking-wider">Menu</div>
        
        <button wire:click="selectCategory(null)" 
            class="flex-shrink-0 lg:w-full p-4 text-left hover:bg-amber-50 dark:hover:bg-gray-700 transition-colors border-l-4 {{ is_null($activeCategoryId) ? 'border-amber-500 bg-amber-50 dark:bg-gray-700 text-amber-700 dark:text-amber-400 font-bold' : 'border-transparent' }}">
            <span class="block truncate">TOUT</span>
        </button>

        @foreach($categories as $category)
            <button wire:click="selectCategory({{ $category->id }})" 
                class="flex-shrink-0 lg:w-full p-4 text-left hover:bg-amber-50 dark:hover:bg-gray-700 transition-colors border-l-4 {{ $activeCategoryId === $category->id ? 'border-amber-500 bg-amber-50 dark:bg-gray-700 text-amber-700 dark:text-amber-400 font-bold' : 'border-transparent' }}">
                <span class="block truncate">{{ $category->name }}</span>
            </button>
        @endforeach
    </div>

    <!-- Zone 2: Grille Produits (Centre - Extensible) -->
    <div class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Barre de recherche -->
        <div class="p-4 bg-gray-50 dark:bg-gray-900 z-10">
             <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                <input type="text" class="block w-full pl-10 pr-3 py-3 border-none rounded-xl shadow-sm ring-1 ring-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-amber-500 bg-white dark:bg-gray-800 dark:ring-gray-700 sm:text-sm" placeholder="Recherche...">
             </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 pt-0">
            @if($view === 'tables')
                <!-- Vue Plan de Salle -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach($tables as $table)
                        <button wire:click="selectTable({{ $table->id }})" 
                            class="aspect-square relative group flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all hover:shadow-lg
                            {{ $table->current_order_uuid ? 'border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800' : 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800' }}">
                            
                            <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-full flex items-center justify-center mb-2 
                                 {{ $table->current_order_uuid ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                <span class="text-lg lg:text-xl font-bold">{{ $table->name }}</span>
                            </div>
                            
                            <span class="text-xs lg:text-sm font-medium {{ $table->current_order_uuid ? 'text-red-700' : 'text-green-700' }}">
                                {{ $table->current_order_uuid ? 'Occupée' : 'Libre' }}
                            </span>
                        </button>
                    @endforeach
                </div>
            @else
                <!-- Vue Grille Produits -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 pb-20 lg:pb-0">
                    @foreach($products as $product)
                        <button wire:click="addToCart({{ $product->id }})" 
                            class="group relative flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-amber-300 transition-all overflow-hidden h-40 lg:h-48">
                            
                            <div class="h-20 lg:h-28 w-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                                 @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                 @else
                                    <span class="text-gray-300 text-lg font-bold opacity-30">IMG</span>
                                 @endif
                            </div>

                            <div class="flex-1 p-2 flex flex-col justify-between text-left">
                                <h3 class="font-bold text-gray-800 dark:text-gray-100 text-xs lg:text-sm line-clamp-2 leading-tight">{{ $product->name }}</h3>
                                <div class="mt-1 font-bold text-amber-600 text-xs lg:text-sm">{{ number_format($product->price, 2) }} Dhs</div>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Zone 3: Panier (Droite - Fixe Desktop / Drawer Mobile) -->
    @if($selectedTableId)
    <div class="w-full lg:w-96 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex flex-col h-full shadow-xl z-20 absolute inset-0 lg:relative {{ $view === 'order' ? 'translate-x-0' : 'translate-x-full lg:translate-x-0' }} transition-transform duration-300">
        
        <!-- Header Panier -->
        <div class="p-3 bg-blue-600 text-white flex justify-between items-center shadow-sm">
            <div>
                <div class="font-bold text-base flex items-center gap-2">
                    <span>Table {{ $selectedTableId }}</span>
                </div>
            </div>
            <button wire:click="$set('view', 'tables')" class="p-1 hover:bg-blue-700 rounded lg:hidden">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
        </div>

        <!-- Mode de commande -->
        <div class="grid grid-cols-3 gap-1 p-2 bg-gray-100 dark:bg-gray-900 border-b border-gray-200">
            <button class="bg-white shadow-sm rounded py-1 text-xs font-bold text-blue-600">À TABLE</button>
            <button class="text-gray-500 rounded py-1 text-xs">EMPORTER</button>
            <button class="text-gray-500 rounded py-1 text-xs">LIVRAISON</button>
        </div>

        <!-- Liste Articles -->
        <div class="flex-1 overflow-y-auto p-2 space-y-1 bg-gray-50/50">
            @forelse($cart as $itemId => $item)
                <div class="flex items-center justify-between p-2 bg-white rounded border border-gray-200 shadow-sm">
                    <div class="flex-1">
                        <div class="font-bold text-sm text-gray-800">{{ $item['name'] }}</div>
                        <div class="text-xs text-gray-500">{{ number_format($item['price'], 2) }} x {{ $item['quantity'] }}</div>
                    </div>
                    <div class="font-bold text-sm text-gray-900">{{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                    <button wire:click="removeFromCart({{ $itemId }})" class="ml-2 text-red-400 hover:text-red-600">
                        <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-60">
                    <p class="text-xs">Panier vide</p>
                </div>
            @endforelse
        </div>

        <!-- Actions -->
        <div class="bg-white p-3 border-t border-gray-200 shadow-lg z-30">
            <div class="flex justify-between items-end mb-3">
                <span class="text-gray-500 font-bold text-xs uppercase">Total</span>
                <span class="text-2xl font-black text-gray-900">
                    {{ number_format(collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']), 2) }} <span class="text-sm">Dhs</span>
                </span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <button wire:click="sendOrder" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-sm flex justify-center gap-2">
                    <x-heroicon-o-printer class="w-4 h-4" /> ENVOYER
                </button>
                <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg text-sm flex justify-center gap-2">
                    <x-heroicon-o-banknotes class="w-4 h-4" /> PAYER
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
