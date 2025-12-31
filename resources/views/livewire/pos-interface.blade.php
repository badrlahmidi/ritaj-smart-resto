<div class="h-[calc(100vh-4rem)] flex flex-col lg:flex-row overflow-hidden bg-gray-50 dark:bg-gray-900">
    
    <!-- Zone 1: Catégories (Sidebar Gauche) -->
    <!-- Mobile: Scroll horizontal en haut / Desktop: Sidebar verticale à gauche -->
    <div class="w-full lg:w-1/6 bg-white dark:bg-gray-800 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-700 flex lg:flex-col overflow-x-auto lg:overflow-y-auto z-10 shadow-sm">
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

    <!-- Zone 2: Grille Produits (Centre) -->
    <div class="flex-1 p-4 overflow-y-auto bg-gray-50 dark:bg-gray-900 relative">
        <!-- Barre de recherche -->
        <div class="mb-4 sticky top-0 z-10">
             <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                <input type="text" class="block w-full pl-10 pr-3 py-3 border-none rounded-xl shadow-sm ring-1 ring-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-amber-500 bg-white dark:bg-gray-800 dark:ring-gray-700 sm:text-sm" placeholder="Recherche des produits (ex: Soda, Café...)">
             </div>
        </div>

        @if($view === 'tables')
            <!-- Vue Plan de Salle (Mode initial) -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($tables as $table)
                    <button wire:click="selectTable({{ $table->id }})" 
                        class="relative group flex flex-col items-center justify-center p-6 rounded-2xl border-2 transition-all hover:shadow-md
                        {{ $table->current_order_uuid ? 'border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800' : 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800' }}">
                        
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 
                             {{ $table->current_order_uuid ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                            <span class="text-xl font-bold">{{ $table->name }}</span>
                        </div>
                        
                        <span class="text-sm font-medium {{ $table->current_order_uuid ? 'text-red-700' : 'text-green-700' }}">
                            {{ $table->current_order_uuid ? 'Occupée' : 'Libre' }}
                        </span>
                        
                        @if($table->current_order_uuid)
                             <span class="absolute top-2 right-2 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        @else
            <!-- Vue Grille Produits -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 pb-20 lg:pb-0">
                @foreach($products as $product)
                    <button wire:click="addToCart({{ $product->id }})" 
                        class="group relative flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-amber-300 transition-all overflow-hidden h-48">
                        
                        <!-- Image ou Placeholder -->
                        <div class="h-28 w-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                             @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                             @else
                                <span class="text-gray-300 text-3xl font-bold opacity-30">Ritaj</span>
                             @endif
                        </div>

                        <div class="flex-1 p-3 flex flex-col justify-between text-left">
                            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-sm line-clamp-2 leading-tight">{{ $product->name }}</h3>
                            <div class="mt-2 font-bold text-amber-600">{{ number_format($product->price, 2) }} Dhs</div>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Zone 3: Panier / Ticket (Droite) -->
    <!-- Mobile: Drawer ou Bottom Sheet / Desktop: Colonne fixe droite -->
    @if($selectedTableId)
    <div class="w-full lg:w-1/3 xl:w-1/4 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex flex-col h-full shadow-xl z-20 absolute inset-0 lg:relative {{ $view === 'order' ? 'translate-x-0' : 'translate-x-full lg:translate-x-0' }} transition-transform duration-300">
        
        <!-- Header Panier -->
        <div class="p-4 bg-blue-600 text-white flex justify-between items-center shadow-md">
            <div>
                <div class="text-xs opacity-80 uppercase tracking-wide">Commande en cours</div>
                <div class="font-bold text-lg flex items-center gap-2">
                    <span>Table {{ $selectedTableId }}</span>
                    @if($selectedTableOrderUuid)
                        <span class="bg-blue-500 text-xs px-2 py-0.5 rounded-full border border-blue-400">#{{ substr($selectedTableOrderUuid, -4) }}</span>
                    @endif
                </div>
            </div>
            <button wire:click="$set('view', 'tables')" class="p-2 hover:bg-blue-700 rounded-lg lg:hidden">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
        </div>

        <!-- Mode de commande (Tabs) -->
        <div class="grid grid-cols-3 gap-1 p-2 bg-gray-100 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <button class="bg-white dark:bg-gray-700 shadow-sm rounded py-1.5 text-xs font-bold text-blue-600">À TABLE</button>
            <button class="text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-800 rounded py-1.5 text-xs font-medium">EMPORTER</button>
            <button class="text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-800 rounded py-1.5 text-xs font-medium">LIVRAISON</button>
        </div>

        <!-- Liste Articles (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-2 space-y-1">
            @forelse($cart as $itemId => $item)
                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-750 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-700 group">
                    <div class="flex-1">
                        <div class="font-bold text-gray-800 dark:text-gray-200">{{ $item['name'] }}</div>
                        <div class="text-xs text-gray-500">{{ number_format($item['price'], 2) }} x {{ $item['quantity'] }}</div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="font-bold text-gray-900 dark:text-gray-100 w-16 text-right">
                            {{ number_format($item['price'] * $item['quantity'], 2) }}
                        </div>
                        <button wire:click="removeFromCart({{ $itemId }})" class="text-red-400 hover:text-red-600 p-1 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-gray-400 p-8 text-center opacity-60">
                    <x-heroicon-o-shopping-cart class="w-12 h-12 mb-3" />
                    <p class="text-sm">Sélectionnez des produits à gauche pour commencer</p>
                </div>
            @endforelse
        </div>

        <!-- Actions Rapides (Pavé Numérique Style) -->
        <div class="grid grid-cols-4 gap-2 p-2 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
             <button class="flex flex-col items-center justify-center p-2 bg-white rounded shadow-sm text-green-600 hover:bg-green-50">
                 <x-heroicon-o-plus class="w-5 h-5"/>
             </button>
             <button class="flex flex-col items-center justify-center p-2 bg-white rounded shadow-sm text-red-600 hover:bg-red-50">
                 <x-heroicon-o-minus class="w-5 h-5"/>
             </button>
             <button class="flex flex-col items-center justify-center p-2 bg-white rounded shadow-sm text-gray-600 hover:bg-gray-100">
                 <x-heroicon-o-chat-bubble-bottom-center-text class="w-5 h-5"/>
             </button>
             <button wire:click="resetCart" class="flex flex-col items-center justify-center p-2 bg-red-100 rounded shadow-sm text-red-600 hover:bg-red-200">
                 <x-heroicon-o-x-mark class="w-5 h-5"/>
             </button>
        </div>

        <!-- Total Footer -->
        <div class="bg-white dark:bg-gray-800 p-4 border-t border-gray-200 dark:border-gray-700 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
            <div class="flex justify-between items-end mb-4">
                <span class="text-gray-500 font-bold uppercase text-xs tracking-wider">Total à payer</span>
                <span class="text-2xl font-black text-gray-900 dark:text-white">
                    {{ number_format(collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']), 2) }} <span class="text-sm text-gray-500 font-normal">Dhs</span>
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button wire:click="sendOrder" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-blue-200 dark:shadow-none transition-transform active:scale-95 flex justify-center items-center gap-2">
                    <x-heroicon-o-printer class="w-5 h-5" />
                    ENVOYER
                </button>
                <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-green-200 dark:shadow-none transition-transform active:scale-95 flex justify-center items-center gap-2">
                    <x-heroicon-o-banknotes class="w-5 h-5" />
                    PAYER
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
