<div class="h-screen w-full flex flex-col lg:flex-row overflow-hidden bg-gray-100 dark:bg-gray-900 font-sans">
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    
    <!-- LEFT SIDEBAR: CATEGORIES -->
    <aside class="w-full lg:w-24 flex-shrink-0 bg-white dark:bg-gray-800 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-700 flex lg:flex-col overflow-x-auto lg:overflow-y-auto z-20 shadow-md scrollbar-hide">
        <div class="p-2 lg:p-4 flex flex-col gap-2 items-center justify-center">
            <button wire:click="selectCategory(null)" 
                class="w-16 h-16 lg:w-full lg:h-auto lg:aspect-square flex flex-col items-center justify-center p-2 rounded-xl transition-all duration-200 border-2 
                {{ is_null($activeCategoryId) 
                    ? 'bg-amber-500 text-white border-amber-600 shadow-lg scale-105' 
                    : 'bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-transparent hover:bg-amber-50 dark:hover:bg-gray-600 hover:text-amber-600' 
                }}">
                <x-heroicon-o-squares-2x2 class="w-6 h-6 lg:w-8 lg:h-8 mb-1" />
                <span class="text-[10px] lg:text-xs font-bold uppercase tracking-wider text-center leading-none">Tout</span>
            </button>

            @foreach($categories as $category)
                <button wire:click="selectCategory({{ $category->id }})" 
                    class="w-16 h-16 lg:w-full lg:h-auto lg:aspect-square flex flex-col items-center justify-center p-2 rounded-xl transition-all duration-200 border-2 group
                    {{ $activeCategoryId === $category->id 
                        ? 'bg-amber-500 text-white border-amber-600 shadow-lg scale-105' 
                        : 'bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-transparent hover:bg-amber-50 dark:hover:bg-gray-600 hover:text-amber-600' 
                    }}">
                    <!-- Icon Placeholder (could be dynamic) -->
                    <x-heroicon-o-tag class="w-5 h-5 lg:w-6 lg:h-6 mb-1 opacity-70 group-hover:opacity-100" />
                    <span class="text-[10px] lg:text-xs font-bold uppercase tracking-wider text-center leading-tight line-clamp-2">
                        {{ $category->name }}
                    </span>
                </button>
            @endforeach
        </div>
    </aside>

    <!-- MIDDLE AREA: CONTENT (TABLES OR PRODUCTS) -->
    <main class="flex-1 flex flex-col min-w-0 relative bg-gray-100 dark:bg-gray-900">
        
        <!-- Header / Search -->
        <header class="h-16 flex items-center justify-between px-4 lg:px-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm z-10">
            <div class="flex items-center gap-4">
                @if($view === 'order')
                    <button wire:click="$set('view', 'tables')" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-colors" title="Retour aux tables">
                        <x-heroicon-o-arrow-left class="w-6 h-6" />
                    </button>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white leading-none">Table {{ $selectedTableId }}</h2>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Commande en cours</span>
                    </div>
                @else
                    <h2 class="text-xl font-black text-gray-800 dark:text-white tracking-tight">RITAJ POS</h2>
                @endif
            </div>

            <!-- Search Bar -->
            <div class="flex-1 max-w-md mx-4">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400 group-focus-within:text-amber-500 transition-colors" />
                    </div>
                    <input type="text" 
                        class="block w-full pl-10 pr-4 py-2 bg-gray-100 dark:bg-gray-700 border-transparent rounded-full text-sm focus:border-amber-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-0 transition-all placeholder-gray-400 dark:text-white" 
                        placeholder="Rechercher un article...">
                </div>
            </div>

            <div class="flex items-center gap-2">
                 <!-- Status Indicators or User Profile could go here -->
                 <div class="h-8 w-8 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-200 dark:border-amber-700">
                    {{ substr(Auth::user()->name ?? 'U', 0, 2) }}
                 </div>
            </div>
        </header>

        <!-- Scrollable Grid -->
        <div class="flex-1 overflow-y-auto p-4 lg:p-6">
            
            @if($view === 'tables')
                <!-- TABLE VIEW -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 lg:gap-6">
                    @foreach($tables as $table)
                        <button wire:click="selectTable({{ $table->id }})" 
                            class="group relative aspect-square flex flex-col items-center justify-center p-4 rounded-3xl border-2 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300
                            {{ $table->current_order_uuid 
                                ? 'bg-white dark:bg-gray-800 border-red-500/50' 
                                : 'bg-white dark:bg-gray-800 border-emerald-500/50' 
                            }}">
                            
                            <!-- Status Badge -->
                            <div class="absolute top-4 right-4 h-3 w-3 rounded-full {{ $table->current_order_uuid ? 'bg-red-500 animate-pulse' : 'bg-emerald-500' }}"></div>

                            <div class="w-20 h-20 rounded-full flex items-center justify-center mb-3 shadow-inner 
                                {{ $table->current_order_uuid 
                                    ? 'bg-red-50 text-red-600 dark:bg-red-900/20' 
                                    : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20' 
                                }}">
                                <span class="text-3xl font-black">{{ $table->name }}</span>
                            </div>
                            
                            <span class="text-sm font-bold uppercase tracking-wide {{ $table->current_order_uuid ? 'text-red-500' : 'text-emerald-500' }}">
                                {{ $table->current_order_uuid ? 'Occupée' : 'Libre' }}
                            </span>
                        </button>
                    @endforeach
                </div>

            @else
                <!-- PRODUCT GRID -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 lg:gap-4 pb-20 lg:pb-0">
                    @forelse($products as $product)
                        <button wire:click="addToCart({{ $product->id }})" 
                            class="group relative flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-lg hover:border-amber-400 dark:hover:border-amber-500 transition-all duration-200 overflow-hidden h-48 lg:h-56">
                            
                            <!-- Image Area -->
                            <div class="h-28 lg:h-36 w-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center overflow-hidden relative">
                                 @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                 @else
                                    <x-heroicon-o-photo class="w-12 h-12 text-gray-300 dark:text-gray-600" />
                                 @endif
                                 
                                 <!-- Price Tag Overlay -->
                                 <div class="absolute bottom-2 right-2 bg-black/70 backdrop-blur-sm text-white px-2 py-1 rounded-lg text-xs font-bold shadow-sm">
                                    {{ number_format($product->price, 2) }} <span class="text-[10px] font-normal">Dhs</span>
                                 </div>
                            </div>

                            <!-- Content Area -->
                            <div class="flex-1 p-3 flex flex-col justify-between text-left w-full">
                                <h3 class="font-bold text-gray-800 dark:text-gray-100 text-sm leading-tight line-clamp-2 group-hover:text-amber-600 transition-colors">
                                    {{ $product->name }}
                                </h3>
                            </div>
                        </button>
                    @empty
                        <div class="col-span-full flex flex-col items-center justify-center h-64 text-gray-400">
                            <x-heroicon-o-face-frown class="w-16 h-16 mb-4 opacity-50" />
                            <p class="text-lg font-medium">Aucun produit trouvé</p>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </main>

    <!-- RIGHT SIDEBAR: CART (DRAWER ON MOBILE) -->
    @if($selectedTableId)
    <aside class="w-full lg:w-[400px] flex-shrink-0 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex flex-col h-full shadow-2xl lg:shadow-none z-30 absolute inset-0 lg:relative {{ $view === 'order' ? 'translate-x-0' : 'translate-x-full lg:translate-x-0' }} transition-transform duration-300">
        
        <!-- Cart Header -->
        <div class="p-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center shadow-sm z-10">
            <h3 class="font-black text-gray-800 dark:text-white text-lg uppercase tracking-wide flex items-center gap-2">
                <x-heroicon-o-shopping-cart class="w-5 h-5 text-amber-500" />
                Panier
            </h3>
            
            <div class="flex items-center gap-2">
                <!-- Close Button (Mobile Only) -->
                <button wire:click="$set('view', 'tables')" class="lg:hidden p-2 text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
        </div>

        <!-- Order Tabs -->
        <div class="grid grid-cols-2 p-1 bg-gray-100 dark:bg-gray-900 m-4 rounded-lg">
             <button class="py-2 px-4 rounded-md text-sm font-bold shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                Nouveaux
             </button>
             <button class="py-2 px-4 rounded-md text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-all">
                Envoyés
             </button>
        </div>

        <!-- Cart Items List -->
        <div class="flex-1 overflow-y-auto px-4 py-2 space-y-3">
            @forelse($cart as $itemId => $item)
                <div class="flex flex-col bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 border border-transparent hover:border-gray-200 dark:hover:border-gray-600 transition-colors group">
                    <div class="flex justify-between items-start mb-2">
                        <span class="font-bold text-gray-800 dark:text-gray-100 text-sm leading-tight">{{ $item['name'] }}</span>
                        <span class="font-bold text-gray-900 dark:text-white text-sm">{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                            {{ number_format($item['price'], 2) }} / u
                        </div>

                        <div class="flex items-center bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 overflow-hidden">
                            <button wire:click="removeFromCart({{ $itemId }})" class="p-1.5 hover:bg-red-50 hover:text-red-500 text-gray-500 transition-colors">
                                <x-heroicon-o-minus class="w-4 h-4" />
                            </button>
                            <span class="w-8 text-center font-bold text-sm text-gray-800 dark:text-gray-200">{{ $item['quantity'] }}</span>
                            <button wire:click="addToCart({{ $item['id'] }})" class="p-1.5 hover:bg-green-50 hover:text-green-500 text-gray-500 transition-colors">
                                <x-heroicon-o-plus class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-60">
                    <x-heroicon-o-shopping-bag class="w-16 h-16 mb-2" />
                    <p class="text-sm font-medium">Panier vide</p>
                    <p class="text-xs text-gray-300">Sélectionnez des produits à gauche</p>
                </div>
            @endforelse
        </div>

        <!-- Footer / Totals -->
        <div class="bg-white dark:bg-gray-800 p-4 border-t border-gray-200 dark:border-gray-700 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-20">
            <div class="space-y-2 mb-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Sous-total</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ number_format(collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']), 2) }}</span>
                </div>
                 <!-- Tax or Discount could go here -->
                <div class="flex justify-between items-end pt-2 border-t border-dashed border-gray-200 dark:border-gray-700">
                    <span class="text-gray-800 dark:text-white font-bold text-lg">Total</span>
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-500">
                        {{ number_format(collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']), 2) }} <span class="text-sm text-gray-500 font-normal">Dhs</span>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button wire:click="sendOrder" 
                    class="flex flex-col items-center justify-center py-3 px-4 bg-gray-900 hover:bg-gray-800 text-white rounded-xl shadow-lg shadow-gray-200 dark:shadow-none transition-all hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ empty($cart) ? 'disabled' : '' }}>
                    <div class="flex items-center gap-2">
                         <x-heroicon-o-printer class="w-5 h-5" />
                         <span class="font-bold uppercase tracking-wider text-sm">Cuisine</span>
                    </div>
                </button>

                <button 
                    class="flex flex-col items-center justify-center py-3 px-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-lg shadow-emerald-200 dark:shadow-none transition-all hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                     {{ empty($cart) ? 'disabled' : '' }}>
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                        <span class="font-bold uppercase tracking-wider text-sm">Payer</span>
                    </div>
                </button>
            </div>
        </div>
    </aside>
    @endif
</div>