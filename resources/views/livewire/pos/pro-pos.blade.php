<div class="h-screen w-full bg-[#f8fafc] dark:bg-gray-950 overflow-hidden flex flex-col font-sans" 
     wire:poll.10s="syncTable"
     x-data="{
        view: @entangle('view'),
        mobileCartOpen: false,
        notification: { show: false, message: '', type: 'success' }
     }"
     x-on:notify.window="notification.show = true; notification.message = $event.detail[0]; notification.type = $event.detail[1] || 'success'; setTimeout(() => notification.show = false, 3000)">

    <!-- TOAST -->
    <div x-show="notification.show" x-transition class="fixed bottom-6 right-6 z-[100] px-6 py-4 rounded-2xl shadow-2xl font-bold text-white flex items-center gap-3" :class="notification.type === 'error' ? 'bg-red-500' : 'bg-emerald-600'" style="display: none;">
         <span x-text="notification.message"></span>
    </div>

    <!-- MAIN APP WRAPPER -->
    <div class="flex flex-1 overflow-hidden relative">
        
        <!-- PC SIDEBAR: CATEGORIES (LEFT) - HIDDEN ON MOBILE -->
        <aside class="hidden lg:flex w-24 flex-shrink-0 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex-col items-center py-6 z-40 shadow-sm overflow-y-auto no-scrollbar">
            <div class="mb-10">
                <div class="w-14 h-14 bg-amber-500 rounded-3xl flex items-center justify-center text-white shadow-xl shadow-amber-200 dark:shadow-none rotate-3">
                    <x-heroicon-s-bolt class="w-8 h-8" />
                </div>
            </div>

            <nav class="flex flex-col gap-5 w-full px-2">
                <button wire:click="selectCategory(null)" 
                        class="flex flex-col items-center justify-center p-3 rounded-3xl transition-all duration-200 {{ is_null($selectedCategoryId) ? 'bg-gray-900 dark:bg-amber-500 text-white shadow-xl scale-105' : 'text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    <x-heroicon-s-squares-2x2 class="w-6 h-6 mb-1.5" />
                    <span class="text-[10px] font-black uppercase tracking-widest text-center">Tout</span>
                </button>
                @foreach($this->categories as $category)
                    <button wire:click="selectCategory({{ $category->id }})" 
                            class="flex flex-col items-center justify-center p-3 rounded-3xl transition-all duration-200 {{ $selectedCategoryId === $category->id ? 'bg-gray-900 dark:bg-amber-500 text-white shadow-xl scale-105' : 'text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                        <x-heroicon-s-tag class="w-6 h-6 mb-1.5" />
                        <span class="text-[10px] font-black uppercase text-center leading-tight line-clamp-2 tracking-tighter">{{ $category->name }}</span>
                    </button>
                @endforeach
            </nav>
        </aside>

        <!-- CENTER PANEL: PRODUCTS / PLAN -->
        <div class="flex-1 flex flex-col min-w-0 bg-[#f8fafc] dark:bg-gray-950 relative">
            
            <!-- PC TOP BAR (HIDDEN ON MOBILE) -->
            <header class="hidden lg:flex h-24 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 items-center px-8 justify-between z-30 shadow-sm">
                <!-- Back Button -->
                <div x-show="view !== 'dashboard'" class="mr-4">
                    <button wire:click="unlockOrder" class="p-3 bg-gray-100 dark:bg-gray-800 rounded-2xl text-gray-500 hover:text-amber-500 transition-all">
                        <x-heroicon-o-arrow-left class="w-6 h-6" />
                    </button>
                </div>

                <!-- Search -->
                <div class="flex-1 max-w-lg">
                    <div class="relative group">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 absolute left-5 top-4 text-gray-400 group-focus-within:text-amber-500 transition-colors" />
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher un plat..." 
                               class="w-full pl-14 pr-6 py-4 bg-gray-50 dark:bg-gray-800 border-none rounded-2xl focus:ring-2 focus:ring-amber-500 dark:text-white transition-all text-sm font-bold shadow-inner">
                    </div>
                </div>

                <!-- Order Type Buttons -->
                <div class="flex items-center bg-gray-100 dark:bg-gray-800 p-1.5 rounded-3xl ml-8 border border-gray-200 dark:border-gray-700 shadow-inner">
                    <button wire:click="setOrderType('dine_in')" 
                            class="px-6 py-3 rounded-2xl text-xs font-black transition-all flex items-center gap-2 {{ $orderType === 'dine_in' ? 'bg-emerald-500 text-white shadow-lg' : 'text-gray-400 hover:text-emerald-600' }}">
                        <x-heroicon-s-home-modern class="w-4 h-4" />
                        <span class="uppercase tracking-widest">Sur Place</span>
                    </button>
                    <button wire:click="setOrderType('takeaway')" 
                            class="px-6 py-3 rounded-2xl text-xs font-black transition-all flex items-center gap-2 {{ $orderType === 'takeaway' ? 'bg-amber-500 text-white shadow-lg' : 'text-gray-400 hover:text-amber-600' }}">
                        <x-heroicon-s-shopping-bag class="w-4 h-4" />
                        <span class="uppercase tracking-widest">Emporter</span>
                    </button>
                    <button wire:click="setOrderType('delivery')" 
                            class="px-6 py-3 rounded-2xl text-xs font-black transition-all flex items-center gap-2 {{ $orderType === 'delivery' ? 'bg-blue-500 text-white shadow-lg' : 'text-gray-400 hover:text-blue-600' }}">
                        <x-heroicon-s-truck class="w-4 h-4" />
                        <span class="uppercase tracking-widest">Livraison</span>
                    </button>
                </div>

                <!-- Server Info -->
                <div class="flex items-center gap-4 ml-8 border-l pl-8 border-gray-100 dark:border-gray-800">
                    <div class="text-right">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-0.5">Serveur</p>
                        <p class="text-sm font-black text-gray-800 dark:text-white">{{ auth()->user()->name }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-amber-200 dark:shadow-none">
                        <x-heroicon-s-user class="w-7 h-7" />
                    </div>
                </div>
            </header>

            <!-- MOBILE TOP BAR (HIDDEN ON PC) -->
            <header class="lg:hidden h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center px-4 justify-between z-30 shadow-sm">
                <div class="flex items-center gap-3">
                    @if($view !== 'dashboard')
                        <button wire:click="unlockOrder" class="p-2 bg-gray-50 dark:bg-gray-800 rounded-xl text-gray-500">
                            <x-heroicon-o-arrow-left class="w-5 h-5" />
                        </button>
                        <h1 class="text-lg font-black dark:text-white">
                            @if($selectedTableId)
                                Table {{ $this->tables->find($selectedTableId)?->name }}
                            @else
                                @if($orderType === 'takeaway') EMPORTER @elseif($orderType === 'delivery') LIVRAISON @else SUR PLACE @endif
                            @endif
                        </h1>
                    @else
                        <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center text-white">
                            <x-heroicon-s-bolt class="w-5 h-5" />
                        </div>
                        <h1 class="text-lg font-black dark:text-white tracking-tighter">RITAJ POS</h1>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button @click="mobileCartOpen = true" class="relative p-2 bg-amber-50 dark:bg-amber-900/30 text-amber-600 rounded-xl">
                        <x-heroicon-o-shopping-cart class="w-6 h-6" />
                        @if(count($cart) > 0)
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white dark:border-gray-900">
                                {{ count($cart) }}
                            </span>
                        @endif
                    </button>
                </div>
            </header>

            <!-- PDA CATEGORIES SCROLLER (MOBILE ONLY) -->
            @if($view === 'ordering')
            <div class="lg:hidden flex-shrink-0 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 flex items-center px-4 py-3 overflow-x-auto no-scrollbar gap-2">
                <button wire:click="selectCategory(null)" 
                        class="flex-shrink-0 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ is_null($selectedCategoryId) ? 'bg-amber-500 text-white shadow-md' : 'bg-gray-50 dark:bg-gray-800 text-gray-400' }}">
                    Tout
                </button>
                @foreach($this->categories as $category)
                    <button wire:click="selectCategory({{ $category->id }})" 
                            class="flex-shrink-0 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $selectedCategoryId === $category->id ? 'bg-amber-500 text-white shadow-md' : 'bg-gray-50 dark:bg-gray-800 text-gray-400' }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
            @endif

            <!-- MAIN CONTENT AREA -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-8 scrollbar-hide">
                @if($view === 'dashboard')
                    <!-- PDA DASHBOARD (MAIN SELECTOR) -->
                    <div class="h-full flex flex-col justify-center gap-6 max-w-sm mx-auto">
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tighter text-center mb-4">Que voulez-vous faire ?</h2>
                        
                        <button wire:click="setOrderType('dine_in')" class="flex items-center gap-6 p-8 bg-white dark:bg-gray-900 rounded-[2.5rem] border-4 border-emerald-500/20 shadow-xl shadow-emerald-100/20 active:scale-95 transition-all">
                             <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center text-emerald-600">
                                <x-heroicon-s-home-modern class="w-10 h-10" />
                             </div>
                             <div class="text-left">
                                <span class="block text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Sur Place</span>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Service à table</span>
                             </div>
                        </button>

                        <button wire:click="setOrderType('takeaway')" class="flex items-center gap-6 p-8 bg-white dark:bg-gray-900 rounded-[2.5rem] border-4 border-amber-500/20 shadow-xl shadow-amber-100/20 active:scale-95 transition-all">
                             <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 rounded-2xl flex items-center justify-center text-amber-600">
                                <x-heroicon-s-shopping-bag class="w-10 h-10" />
                             </div>
                             <div class="text-left">
                                <span class="block text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Emporter</span>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Prise rapide</span>
                             </div>
                        </button>

                        <button wire:click="setOrderType('delivery')" class="flex items-center gap-6 p-8 bg-white dark:bg-gray-900 rounded-[2.5rem] border-4 border-blue-500/20 shadow-xl shadow-blue-100/20 active:scale-95 transition-all">
                             <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center text-blue-600">
                                <x-heroicon-s-truck class="w-10 h-10" />
                             </div>
                             <div class="text-left">
                                <span class="block text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Livraison</span>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">À domicile</span>
                             </div>
                        </button>
                    </div>

                @elseif($view === 'tables')
                    <!-- TABLE VIEW -->
                    <div class="lg:mb-10 flex flex-col lg:flex-row justify-between lg:items-end gap-4 mb-6">
                        <div class="flex items-center gap-4">
                            <button wire:click="$set('view', 'dashboard')" class="lg:hidden p-2 bg-gray-100 dark:bg-gray-800 rounded-xl text-gray-500">
                                <x-heroicon-o-arrow-left class="w-5 h-5" />
                            </button>
                            <div>
                                <h2 class="text-2xl lg:text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tighter leading-none">Plan de Salle</h2>
                                <p class="text-gray-400 font-bold text-[10px] lg:text-sm tracking-widest mt-1 uppercase">SÉLECTIONNEZ UNE TABLE</p>
                            </div>
                        </div>
                        <div class="flex overflow-x-auto no-scrollbar gap-2 py-1">
                            @foreach($this->areas as $area)
                                <button wire:click="selectArea({{ $area->id }})" 
                                        class="flex-shrink-0 px-4 lg:px-6 py-2 lg:py-3 rounded-xl lg:rounded-2xl text-[10px] lg:text-xs font-black tracking-widest transition-all {{ $selectedAreaId === $area->id ? 'bg-gray-900 dark:bg-amber-500 text-white shadow-lg' : 'bg-white dark:bg-gray-900 text-gray-400 border border-gray-100 dark:border-gray-800' }}">
                                    {{ strtoupper($area->name) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 2xl:grid-cols-6 gap-3 lg:gap-8">
                        @foreach($this->tables as $table)
                            @php
                                $orderStatus = $table->currentOrder?->status;
                                $bgColor = 'bg-white dark:bg-gray-800';
                                $borderColor = 'border-emerald-500/40';
                                $textColor = 'text-emerald-600';
                                $statusLabel = 'LIBRE';
                                
                                if ($table->current_order_uuid) {
                                    if ($orderStatus === \App\Enums\OrderStatus::PaymentPending->value) {
                                        $borderColor = 'border-amber-500 shadow-amber-100';
                                        $textColor = 'text-amber-600';
                                        $statusLabel = 'ADDITION';
                                    } else {
                                        $borderColor = 'border-red-500/40 shadow-red-100';
                                        $textColor = 'text-red-600';
                                        $statusLabel = 'OCCUPÉ';
                                    }
                                }
                            @endphp
                            <button wire:click="selectTable({{ $table->id }})" 
                                    class="group aspect-[4/5] lg:aspect-square rounded-[2rem] lg:rounded-[3rem] border-4 p-4 lg:p-6 flex flex-col items-center justify-center transition-all duration-300 hover:scale-105 relative {{ $bgColor }} {{ $borderColor }} shadow-sm">
                                
                                @if($table->current_order_uuid)
                                    <div class="absolute -top-3 lg:-top-4 right-2 lg:right-4 {{ $orderStatus === \App\Enums\OrderStatus::PaymentPending->value ? 'bg-amber-500' : 'bg-red-500' }} text-white text-[8px] lg:text-[10px] font-black px-3 lg:px-4 py-1 rounded-full shadow-lg z-10 {{ $orderStatus === \App\Enums\OrderStatus::PaymentPending->value ? 'animate-pulse' : '' }} tracking-widest">
                                        {{ $statusLabel }}
                                    </div>
                                @endif

                                <span class="text-3xl lg:text-5xl font-black {{ $textColor }} mb-1 lg:mb-2 tracking-tighter">{{ $table->name }}</span>
                                <span class="text-[8px] lg:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">{{ $table->capacity }} PLACES</span>
                            </button>
                        @endforeach
                    </div>

                @else
                    <!-- PRODUCTS GRID -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-3 lg:gap-6 pb-24 lg:pb-0">
                        @forelse($this->products as $product)
                            <button wire:click="selectProduct({{ $product->id }})" 
                                    class="group bg-white dark:bg-gray-900 rounded-3xl lg:rounded-[2.5rem] p-3 lg:p-4 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col border border-transparent hover:border-amber-400 relative overflow-hidden">
                                <div class="aspect-square w-full rounded-2xl lg:rounded-[2rem] bg-gray-50 dark:bg-gray-800 mb-3 lg:mb-4 overflow-hidden relative border border-gray-100 dark:border-gray-800 shadow-inner">
                                    @if($product->image_url)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_url) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-200 dark:text-gray-700">
                                            <x-heroicon-o-photo class="w-12 h-12 lg:w-20 lg:h-20 opacity-30" />
                                        </div>
                                    @endif
                                    <div class="absolute bottom-2 lg:bottom-3 left-2 lg:left-3 px-3 lg:px-4 py-1 lg:py-2 bg-black/60 backdrop-blur-xl rounded-xl lg:rounded-2xl text-white text-[10px] lg:text-xs font-black shadow-lg">
                                        {{ number_format($product->getPriceByType(\App\Enums\OrderType::from($orderType)), 0) }} DH
                                    </div>
                                </div>
                                <div class="text-left px-1 mb-2">
                                    <h3 class="font-black text-gray-800 dark:text-gray-200 text-xs lg:text-sm leading-tight line-clamp-2 uppercase tracking-tight h-8 lg:h-10">{{ $product->name }}</h3>
                                </div>
                                <div class="px-1 flex justify-between items-center mt-auto">
                                    <span class="text-[7px] lg:text-[9px] font-black text-amber-500 uppercase tracking-[0.1em] lg:tracking-[0.2em] bg-amber-50 dark:bg-amber-900/20 px-2 py-1 rounded-md">{{ $product->category?->name }}</span>
                                    <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-xl lg:rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 group-hover:bg-amber-500 group-hover:text-white transition-all shadow-sm">
                                        <x-heroicon-s-plus class="w-5 h-5 lg:w-6 lg:h-6" />
                                    </div>
                                </div>
                            </button>
                        @empty
                            <div class="col-span-full py-20 lg:py-40 flex flex-col items-center opacity-20">
                                <x-heroicon-o-magnifying-glass class="w-20 h-20 lg:w-32 lg:h-32 mb-4 dark:text-white" />
                                <p class="text-lg lg:text-2xl font-black uppercase tracking-[0.3em] lg:tracking-[0.5em] dark:text-white">Aucun résultat</p>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>

            <!-- MOBILE BOTTOM BAR (ONLY IN ORDERING VIEW) -->
            @if($view === 'ordering')
            <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 p-4 z-40 flex items-center justify-between gap-4">
                <button @click="mobileCartOpen = true" class="flex-1 flex items-center justify-between px-6 py-4 bg-gray-900 dark:bg-gray-800 text-white rounded-2xl shadow-xl">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <x-heroicon-o-shopping-cart class="w-6 h-6 text-amber-500" />
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-gray-900">{{ count($cart) }}</span>
                        </div>
                        <span class="text-sm font-black uppercase tracking-widest">Voir Panier</span>
                    </div>
                    <span class="text-lg font-black">{{ number_format($this->cartTotal, 0) }} DH</span>
                </button>
                
                <button wire:click="sendToKitchen" 
                        @if(collect($cart)->where('status', 'pending')->isEmpty()) disabled @endif
                        class="px-6 py-4 bg-emerald-500 text-white rounded-2xl shadow-xl shadow-emerald-100 dark:shadow-none disabled:opacity-50">
                    <x-heroicon-s-paper-airplane class="w-6 h-6" />
                </button>
            </div>
            @endif
        </div>

        <!-- RIGHT PANEL: CART & ACTIONS (DRAWER ON MOBILE) -->
        <aside 
            x-show="window.innerWidth >= 1024 || mobileCartOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-0 lg:relative lg:translate-x-0 lg:flex w-full lg:w-[480px] flex-shrink-0 bg-white dark:bg-gray-900 border-l border-gray-200 dark:border-gray-800 flex flex-col h-full z-50 shadow-2xl"
            style="display: none;"
            x-cloak>
            
            <!-- CART HEADER -->
            <div class="h-20 lg:h-24 flex-shrink-0 flex items-center justify-between px-6 lg:px-8 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                <div class="flex items-center gap-3 lg:gap-4">
                    <div class="w-10 h-10 lg:w-12 lg:h-12 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded-xl lg:rounded-2xl flex items-center justify-center border border-amber-200 dark:border-amber-800/50">
                        <x-heroicon-s-shopping-cart class="w-6 h-6 lg:w-7 lg:h-7" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <h2 class="font-black text-lg lg:text-xl text-gray-900 dark:text-white uppercase tracking-tighter">COMMANDE</h2>
                            @if($selectedTableId)
                                <span class="bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 text-[9px] lg:text-[10px] font-black px-2 py-0.5 lg:px-2.5 lg:py-1 rounded-lg uppercase tracking-widest">Table {{ $this->tables->find($selectedTableId)?->name }}</span>
                            @endif
                        </div>
                        <span class="text-[8px] lg:text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em]">
                            {{ $currentOrderUuid ? 'ID: '.substr($currentOrderUuid,0,8) : 'Nouveau Ticket' }}
                        </span>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <button wire:click="resetCart" wire:confirm="Réinitialiser ?" class="p-2 lg:p-3 bg-white dark:bg-gray-800 rounded-xl text-gray-300 hover:text-red-500 transition-all border border-gray-100 dark:border-gray-700">
                        <x-heroicon-o-trash class="w-5 h-5 lg:w-6 lg:h-6" />
                    </button>
                    <button @click="mobileCartOpen = false" class="lg:hidden p-2 bg-white dark:bg-gray-800 rounded-xl text-gray-500 border border-gray-100 dark:border-gray-700">
                        <x-heroicon-o-x-mark class="w-5 h-5 lg:w-6 lg:h-6" />
                    </button>
                </div>
            </div>

            <!-- CART ITEMS LIST -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-6 space-y-3 lg:space-y-4 scrollbar-hide">
                @forelse($cart as $index => $item)
                    <div 
                        x-data="{ 
                            startX: 0, currentX: 0, isSwiping: false, offset: 0
                        }"
                        @touchstart="startX = $event.touches[0].clientX; isSwiping = true"
                        @touchmove="currentX = $event.touches[0].clientX; offset = Math.min(0, currentX - startX)"
                        @touchend="
                            if (offset < -70) { $wire.requestCancelItem({{ $index }}); }
                            isSwiping = false; startX = currentX = 0; offset = 0;
                        "
                        :style="`transform: translateX(${offset}px)`"
                        class="group relative flex flex-col rounded-2xl lg:rounded-[2rem] p-4 lg:p-5 transition-all duration-300 {{ $item['status'] === 'sent' ? 'bg-gray-50 dark:bg-gray-800/40' : 'bg-white dark:bg-gray-800 border-2 border-amber-500/20 shadow-lg' }}">
                        
                        <div x-show="offset < -20" class="absolute inset-y-0 right-0 w-16 bg-red-500 rounded-r-2xl lg:rounded-r-[2rem] flex items-center justify-center text-white font-black text-[10px] transition-opacity">
                            SUPR.
                        </div>

                        <div class="flex justify-between items-start mb-2 lg:mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 lg:gap-3">
                                    <span class="flex items-center justify-center w-6 h-6 lg:w-8 lg:h-8 rounded-lg lg:rounded-xl bg-gray-900 dark:bg-amber-500 text-white font-black text-[10px] lg:text-xs">{{ $item['qty'] }}</span>
                                    <h4 class="font-black text-gray-900 dark:text-white text-sm lg:text-base uppercase tracking-tight line-clamp-1">{{ $item['name'] }}</h4>
                                </div>
                                @if(!empty($item['options']))
                                    <div class="mt-1.5 flex flex-wrap gap-1 lg:gap-1.5 pl-8 lg:pl-11">
                                        @foreach($item['options'] as $opt)
                                            <span class="text-[8px] lg:text-[9px] font-black bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full text-gray-500 uppercase tracking-tighter">{{ $opt['name'] }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($item['notes']))
                                    <div class="mt-1.5 pl-8 lg:pl-11 text-[9px] lg:text-[10px] font-bold text-red-500 italic bg-red-50 dark:bg-red-900/20 p-2 rounded-lg border border-red-100 dark:border-red-900/40">
                                        "{{ $item['notes'] }}"
                                    </div>
                                @endif
                            </div>
                            <span class="font-black text-gray-900 dark:text-white text-sm lg:text-lg ml-2">{{ number_format($item['price'] * $item['qty'], 0) }}<small class="text-[10px] font-bold text-gray-400 ml-0.5">DH</small></span>
                        </div>

                        <div class="flex justify-between items-center mt-3 lg:mt-4 pt-3 lg:pt-4 border-t border-gray-100 dark:border-gray-700">
                             <div class="flex gap-1.5 lg:gap-2">
                                <button wire:click="editLineNote({{ $index }})" class="w-8 h-8 lg:w-10 lg:h-10 flex items-center justify-center rounded-lg lg:rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-blue-500 transition-all">
                                    <x-heroicon-s-pencil-square class="w-4 h-4 lg:w-5 lg:h-5" />
                                </button>
                                <button wire:click="requestCancelItem({{ $index }})" class="w-8 h-8 lg:w-10 lg:h-10 flex items-center justify-center rounded-lg lg:rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-red-500 transition-all">
                                    <x-heroicon-s-trash class="w-4 h-4 lg:w-5 lg:h-5" />
                                </button>
                             </div>

                             <div class="flex items-center bg-gray-50 dark:bg-gray-700 rounded-xl lg:rounded-2xl p-1 lg:p-1.5 shadow-inner">
                                @if($item['status'] === 'pending')
                                    <button wire:click="updateQty({{ $index }}, -1)" class="w-7 h-7 lg:w-9 lg:h-9 flex items-center justify-center rounded-lg lg:rounded-xl bg-white dark:bg-gray-600 text-gray-500 font-black">-</button>
                                    <span class="w-8 lg:w-12 text-center font-black text-sm lg:text-base dark:text-white">{{ $item['qty'] }}</span>
                                    <button wire:click="updateQty({{ $index }}, 1)" class="w-7 h-7 lg:w-9 lg:h-9 flex items-center justify-center rounded-lg lg:rounded-xl bg-white dark:bg-gray-600 text-gray-500 font-black">+</button>
                                @else
                                    <div class="px-3 lg:px-4 flex items-center gap-1.5 lg:gap-2">
                                        <div class="w-1.5 h-1.5 lg:w-2 lg:h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                        <span class="text-[8px] lg:text-[10px] font-black text-emerald-500 uppercase tracking-widest">CUISINE</span>
                                    </div>
                                @endif
                             </div>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center opacity-30 py-20 text-center">
                        <div class="w-20 h-20 lg:w-32 lg:h-32 bg-gray-100 dark:bg-gray-800 rounded-[2rem] lg:rounded-[3rem] flex items-center justify-center mb-4">
                            <x-heroicon-o-shopping-cart class="w-10 h-10 lg:w-16 lg:h-16 dark:text-white" />
                        </div>
                        <p class="font-black text-gray-500 uppercase tracking-[0.3em] text-[10px]">Vide</p>
                    </div>
                @endforelse
            </div>

            <!-- TOTALS & MAIN ACTIONS -->
            <div class="bg-gray-50 dark:bg-gray-950 border-t-2 border-gray-100 dark:border-gray-800 p-6 lg:p-8 shadow-[0_-20px_40px_rgba(0,0,0,0.05)]">
                <div class="space-y-3 lg:space-y-4 mb-6 lg:mb-8">
                    <div class="flex justify-between items-center text-[8px] lg:text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        <span>Sous-total</span>
                        <span class="text-gray-800 dark:text-gray-300 text-xs lg:text-sm">{{ number_format($this->subtotal, 2) }} DH</span>
                    </div>
                    @if($discountAmount > 0)
                    <div class="flex justify-between items-center text-[8px] lg:text-[10px] font-black text-red-500 uppercase tracking-widest bg-red-50 dark:bg-red-900/20 px-3 lg:px-4 py-2 rounded-xl">
                        <span>Remise ({{ $discountType === 'percent' ? $discountAmount.'%' : 'FIXE' }})</span>
                        <span class="text-xs lg:text-sm font-black">-{{ number_format($this->calculatedDiscount, 2) }} DH</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-end pt-4 lg:pt-6 border-t-4 border-double border-gray-200 dark:border-gray-800">
                        <span class="text-xl lg:text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">Total Net</span>
                        <span class="text-4xl lg:text-6xl font-black text-gray-900 dark:text-white tracking-tighter">{{ number_format($this->cartTotal, 0) }}<small class="text-sm lg:text-lg font-black text-amber-500 ml-1 uppercase">DH</small></span>
                    </div>
                </div>

                <!-- Primary Action Buttons -->
                <div class="flex flex-col gap-3 lg:gap-4">
                    <div class="grid grid-cols-2 gap-3 lg:gap-4">
                        <button wire:click="sendToKitchen" 
                                @if(collect($cart)->where('status', 'pending')->isEmpty()) disabled @endif
                                class="py-4 lg:py-6 bg-gray-900 dark:bg-gray-800 text-white rounded-2xl lg:rounded-[2.5rem] font-black text-sm lg:text-base uppercase tracking-widest flex items-center justify-center gap-2 lg:gap-4 active:scale-95 transition-all disabled:opacity-50">
                            <x-heroicon-o-printer class="w-5 h-5 lg:w-7 lg:h-7 text-amber-500" />
                            Cuisine
                        </button>
                        
                        <button wire:click="requestAddition" 
                                @if(empty($cart) || $currentOrderUuid === null) disabled @endif
                                class="py-4 lg:py-6 bg-amber-500 text-white rounded-2xl lg:rounded-[2.5rem] font-black text-sm lg:text-base uppercase tracking-widest flex items-center justify-center gap-2 lg:gap-4 active:scale-95 transition-all disabled:opacity-50">
                            <x-heroicon-o-document-text class="w-5 h-5 lg:w-7 lg:h-7" />
                            Addition
                        </button>
                    </div>

                    <!-- Cashier Only Actions (Hidden on small screens or simplified) -->
                    <div class="grid grid-cols-4 gap-2 lg:gap-3">
                        <button wire:click="applyDiscount" class="p-3 lg:p-4 bg-white dark:bg-gray-800 rounded-xl lg:rounded-3xl border border-gray-200 dark:border-gray-700 hover:border-amber-400 transition-all shadow-sm">
                            <x-heroicon-o-gift class="w-5 h-5 lg:w-6 lg:h-6 text-amber-500 mx-auto" />
                        </button>
                        <button wire:click="$set('showNoteModal', true)" class="p-3 lg:p-4 bg-white dark:bg-gray-800 rounded-xl lg:rounded-3xl border border-gray-200 dark:border-gray-700 hover:border-blue-400 transition-all shadow-sm">
                            <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 lg:w-6 lg:h-6 text-blue-500 mx-auto" />
                        </button>
                        <button wire:click="{{ $orderType === 'delivery' ? 'saveDeliveryInfo' : '$set(\'showDeliveryModal\', true)' }}" class="p-3 lg:p-4 bg-white dark:bg-gray-800 rounded-xl lg:rounded-3xl border border-gray-200 dark:border-gray-700 hover:border-emerald-400 transition-all shadow-sm">
                            <x-heroicon-o-user-plus class="w-5 h-5 lg:w-6 lg:h-6 text-emerald-500 mx-auto" />
                        </button>
                        <button wire:click="cancelOrder" class="p-3 lg:p-4 bg-white dark:bg-gray-800 rounded-xl lg:rounded-3xl border border-gray-200 dark:border-gray-700 hover:border-red-400 transition-all shadow-sm">
                            <x-heroicon-o-x-circle class="w-5 h-5 lg:w-6 lg:h-6 text-red-500 mx-auto" />
                        </button>
                    </div>

                    <button wire:click="checkout" 
                            @if(empty($cart)) disabled @endif
                            class="py-4 lg:py-6 bg-emerald-500 text-white rounded-2xl lg:rounded-[2.5rem] font-black text-base lg:text-lg uppercase tracking-widest shadow-xl active:scale-95 transition-all disabled:opacity-50">
                        ENCAISSER ({{ number_format($this->cartTotal, 0) }} DH)
                    </button>
                </div>
            </div>
        </aside>
    </div>

    <!-- SECURITY PIN MODAL -->
    @if($showPinModal)
    <div class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/95 backdrop-blur-xl p-4">
        <div class="bg-white dark:bg-gray-800 w-full max-w-sm rounded-[2.5rem] lg:rounded-[3.5rem] shadow-2xl p-6 lg:p-10 animate-in zoom-in-95 text-center">
            <div class="w-16 h-16 lg:w-20 lg:h-20 bg-red-100 dark:bg-red-900/30 text-red-600 rounded-2xl lg:rounded-[2rem] flex items-center justify-center mx-auto mb-4 lg:mb-6">
                <x-heroicon-s-lock-closed class="w-8 h-8 lg:w-10 lg:h-10" />
            </div>
            <h3 class="text-xl lg:text-2xl font-black text-gray-900 dark:text-white mb-2 uppercase tracking-tighter">Autorisation</h3>
            <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-6 lg:mb-8">Code PIN Manager requis</p>
            
            <div class="grid grid-cols-3 gap-3 lg:gap-4 mb-6 lg:mb-8">
                @for($i=1; $i<=9; $i++)
                    <button @click="$wire.pinCode += '{{ $i }}'" class="h-14 lg:h-16 rounded-xl lg:rounded-2xl bg-gray-50 dark:bg-gray-700 font-black text-xl text-gray-800 dark:text-white hover:bg-amber-500 hover:text-white transition-all">{{ $i }}</button>
                @endfor
                <button @click="$wire.pinCode = ''" class="h-14 lg:h-16 rounded-xl lg:rounded-2xl bg-red-50 text-red-500 font-black text-[10px] uppercase tracking-widest">Effacer</button>
                <button @click="$wire.pinCode += '0'" class="h-14 lg:h-16 rounded-xl lg:rounded-2xl bg-gray-50 dark:bg-gray-700 font-black text-xl text-gray-800 dark:text-white">0</button>
                <button wire:click="verifyPin" class="h-14 lg:h-16 rounded-xl lg:rounded-2xl bg-emerald-500 text-white font-black text-xl flex items-center justify-center shadow-lg">OK</button>
            </div>

            <div class="flex justify-center gap-2 mb-6 lg:mb-8">
                <template x-for="i in 4">
                    <div class="w-3 h-3 lg:w-4 lg:h-4 rounded-full border-2 border-gray-200" :class="$wire.pinCode.length >= i ? 'bg-gray-900 border-gray-900 dark:bg-amber-500 dark:border-amber-500' : ''"></div>
                </template>
            </div>

            <button wire:click="$set('showPinModal', false)" class="text-gray-400 font-black text-[10px] uppercase tracking-widest">Annuler</button>
        </div>
    </div>
    @endif

    <!-- OPTIONS MODAL -->
    @if($showOptionsModal && $selectedProductForOptions)
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-[2.5rem] lg:rounded-[3rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
            <div class="p-6 lg:p-8 pb-4 flex justify-between items-start">
                <div>
                    <h3 class="text-xl lg:text-2xl font-black text-gray-900 dark:text-white leading-tight uppercase tracking-tighter">{{ $selectedProductForOptions->name }}</h3>
                    <p class="text-gray-400 text-[10px] lg:text-sm font-bold uppercase tracking-widest">Personnalisation</p>
                </div>
                <button wire:click="$set('showOptionsModal', false)" class="p-2 lg:p-3 bg-gray-100 dark:bg-gray-700 rounded-full text-gray-400">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
            <div class="p-6 lg:p-8 pt-2 max-h-[50vh] lg:max-h-[60vh] overflow-y-auto space-y-6 lg:space-y-8 no-scrollbar">
                @foreach($selectedProductForOptions->optionGroups as $group)
                    <div>
                        <div class="flex justify-between items-center mb-3 lg:mb-4">
                            <h4 class="font-black text-gray-800 dark:text-white uppercase tracking-widest text-[10px] lg:text-xs">{{ $group->name }}</h4>
                            @if($group->is_required) <span class="text-[8px] lg:text-[10px] bg-red-50 text-red-500 px-2 py-1 rounded-lg font-black uppercase">Obligatoire</span> @endif
                        </div>
                        <div class="grid grid-cols-2 gap-3 lg:gap-4">
                            @foreach($group->options as $option)
                                <label class="relative cursor-pointer">
                                    @if($group->is_multiselect)
                                        <input type="checkbox" wire:model="selectedOptions.{{ $group->id }}" value="{{ $option->id }}" class="peer sr-only">
                                    @else
                                        <input type="radio" wire:model="selectedOptions.{{ $group->id }}" value="{{ $option->id }}" class="peer sr-only">
                                    @endif
                                    <div class="p-4 lg:p-5 rounded-2xl lg:rounded-[1.5rem] border-4 border-gray-100 dark:border-gray-700 peer-checked:border-amber-500 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-900/20 transition-all text-center group">
                                        <div class="font-black text-gray-700 dark:text-gray-300 peer-checked:text-amber-900 dark:peer-checked:text-amber-400 uppercase tracking-tighter text-[10px] lg:text-sm">{{ $option->name }}</div>
                                        @if($option->price_modifier > 0)
                                            <div class="text-[8px] lg:text-[10px] text-gray-400 mt-0.5 lg:mt-1 font-black">+{{ number_format($option->price_modifier, 0) }} DH</div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900">
                <button wire:click="confirmOptions" class="w-full bg-amber-500 text-white py-4 lg:py-6 rounded-2xl lg:rounded-[2rem] font-black text-base lg:text-lg shadow-xl shadow-amber-200 dark:shadow-none transition-all active:scale-95 uppercase tracking-widest">Confirmer</button>
            </div>
        </div>
    </div>
    @endif

    <!-- DISCOUNT MODAL -->
    @if($showDiscountModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 w-full max-w-sm rounded-[2.5rem] lg:rounded-[3.5rem] shadow-2xl p-6 lg:p-10 animate-in zoom-in-95">
            <h3 class="text-2xl lg:text-3xl font-black text-gray-900 dark:text-white mb-2 uppercase tracking-tighter text-center">Remise</h3>
            <p class="text-gray-400 text-[10px] font-black uppercase tracking-[0.3em] text-center mb-6 lg:mb-10">Application de réduction</p>
            
            <div class="flex bg-gray-100 dark:bg-gray-900 p-1.5 rounded-2xl lg:rounded-3xl mb-6 lg:mb-10 shadow-inner">
                <button wire:click="$set('discountType', 'fixed')" class="flex-1 py-3 lg:py-4 rounded-xl lg:rounded-2xl font-black text-[10px] lg:text-xs transition-all {{ $discountType === 'fixed' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-lg' : 'text-gray-400' }}">FIXE (DH)</button>
                <button wire:click="$set('discountType', 'percent')" class="flex-1 py-3 lg:py-4 rounded-xl lg:rounded-2xl font-black text-[10px] lg:text-xs transition-all {{ $discountType === 'percent' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-lg' : 'text-gray-400' }}">POURCENT (%)</button>
            </div>

            <div class="relative mb-6 lg:mb-10">
                <input type="number" wire:model="discountAmount" class="w-full text-center py-6 lg:py-8 text-4xl lg:text-6xl font-black rounded-2xl lg:rounded-[2.5rem] border-none bg-gray-50 dark:bg-gray-900 dark:text-white focus:ring-4 focus:ring-amber-500/20 shadow-inner">
                <div class="absolute right-4 lg:right-8 top-1/2 -translate-y-1/2 text-gray-300 font-black text-xl lg:text-2xl uppercase">{{ $discountType === 'percent' ? '%' : 'DH' }}</div>
            </div>

            <div class="flex flex-col gap-3 lg:gap-4">
                <button wire:click="$set('showDiscountModal', false)" class="w-full bg-emerald-500 text-white py-4 lg:py-6 rounded-2xl lg:rounded-3xl font-black uppercase tracking-widest shadow-xl transition-all active:scale-95">Appliquer</button>
                <button wire:click="$set('showDiscountModal', false)" class="text-gray-400 font-black text-[10px] uppercase tracking-widest py-2">Fermer</button>
            </div>
        </div>
    </div>
    @endif

    <!-- NOTE MODAL -->
    @if($showNoteModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 w-full max-w-md rounded-[2.5rem] lg:rounded-[3.5rem] shadow-2xl p-6 lg:p-10 animate-in zoom-in-95">
            <h3 class="text-2xl lg:text-3xl font-black text-gray-900 dark:text-white mb-2 uppercase tracking-tighter">
                {{ $editingLineIndex !== null ? 'Note Article' : 'Note Cuisine' }}
            </h3>
            <p class="text-gray-400 text-[10px] font-black uppercase tracking-[0.3em] mb-6 lg:mb-10">Instructions spéciales</p>
            
            <textarea 
                wire:model="{{ $editingLineIndex !== null ? 'lineNote' : 'globalNotes' }}" 
                rows="4" lg:rows="5"
                class="w-full p-6 lg:p-8 rounded-2xl lg:rounded-[2.5rem] bg-gray-50 dark:bg-gray-900 border-none dark:text-white focus:ring-4 focus:ring-blue-500/20 mb-6 lg:mb-8 placeholder-gray-300 font-bold text-base lg:text-lg shadow-inner"
                placeholder="Instructions..."></textarea>
            
            <button wire:click="{{ $editingLineIndex !== null ? 'saveLineNote' : '$set(\'showNoteModal\', false)' }}" class="w-full bg-blue-600 text-white py-4 lg:py-6 rounded-2xl lg:rounded-[2.5rem] font-black uppercase tracking-widest shadow-xl active:scale-95 transition-all">Enregistrer</button>
        </div>
    </div>
    @endif

    <!-- DELIVERY MODAL -->
    @if($showDeliveryModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-[2.5rem] lg:rounded-[3.5rem] shadow-2xl p-6 lg:p-12 animate-in zoom-in-95">
            <h3 class="text-3xl lg:text-4xl font-black text-gray-900 dark:text-white mb-2 uppercase tracking-tighter text-center">LIVRAISON</h3>
            <p class="text-gray-400 text-[10px] font-black uppercase tracking-[0.4em] text-center mb-6 lg:mb-10">Informations client</p>

            <div class="space-y-4 lg:space-y-5 mb-6 lg:mb-10">
                <div class="group">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 lg:mb-2 block ml-2 lg:ml-4">Nom</label>
                    <input type="text" wire:model="customerName" placeholder="Nom..." class="w-full px-6 lg:px-8 py-4 lg:py-5 bg-gray-50 dark:bg-gray-900 border-none rounded-xl lg:rounded-2xl dark:text-white focus:ring-4 focus:ring-amber-500/20 font-bold text-base lg:text-lg shadow-inner">
                </div>
                <div class="group">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 lg:mb-2 block ml-2 lg:ml-4">Téléphone</label>
                    <input type="text" wire:model="customerPhone" placeholder="06..." class="w-full px-6 lg:px-8 py-4 lg:py-5 bg-gray-50 dark:bg-gray-900 border-none rounded-xl lg:rounded-2xl dark:text-white focus:ring-4 focus:ring-amber-500/20 font-bold text-base lg:text-lg shadow-inner">
                </div>
                <div class="group">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 lg:mb-2 block ml-2 lg:ml-4">Adresse</label>
                    <textarea wire:model="customerAddress" placeholder="Adresse..." rows="2" lg:rows="3" class="w-full px-6 lg:px-8 py-4 lg:py-5 bg-gray-50 dark:bg-gray-900 border-none rounded-xl lg:rounded-2xl dark:text-white focus:ring-4 focus:ring-amber-500/20 font-bold text-base lg:text-lg shadow-inner"></textarea>
                </div>
            </div>

            <button wire:click="saveDeliveryInfo" class="w-full bg-gray-900 dark:bg-amber-600 text-white py-4 lg:py-6 rounded-2xl lg:rounded-[2rem] font-black uppercase tracking-widest shadow-2xl active:scale-95 transition-all">Valider</button>
            <button wire:click="$set('showDeliveryModal', false)" class="w-full mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Annuler</button>
        </div>
    </div>
    @endif

</div>