<div class="h-screen w-full flex flex-col bg-gray-50 font-sans overflow-hidden" 
     x-data="{ notification: { show: false, message: '', type: 'success' } }"
     x-on:notify.window="notification.show = true; notification.message = $event.detail[0]; notification.type = $event.detail[1] || 'success'; setTimeout(() => notification.show = false, 3000)">
    
    <!-- NOTIFICATION TOAST -->
    <div x-show="notification.show" class="fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-xl font-bold text-white flex items-center gap-2"
         :class="notification.type === 'error' ? 'bg-red-500' : 'bg-green-500'" style="display: none;">
         <span x-text="notification.message"></span>
    </div>

    <!-- ZONE A: TOP BAR (10%) -->
    <div class="h-[8vh] bg-gray-900 text-white flex items-center justify-between px-4 shadow-md z-30">
        <!-- Left: Back & Table Info -->
        <div class="flex items-center gap-4">
            <a href="{{ route('pos') }}" class="bg-gray-800 p-2 rounded-lg hover:bg-gray-700 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-yellow-500">{{ $table->name }}</h1>
                <span class="text-xs text-gray-400">{{ $table->area?->name }} • {{ $table->capacity }} Pers.</span>
            </div>
        </div>

        <!-- Center: Search -->
        <div class="flex-1 max-w-xl mx-4">
            <div class="relative">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher (Code ou Nom)..." 
                       class="w-full bg-gray-800 border-none rounded-xl py-3 px-4 text-white placeholder-gray-500 focus:ring-2 focus:ring-yellow-500">
                <svg class="w-5 h-5 absolute right-3 top-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <!-- Right: Server Info -->
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <p class="font-bold text-sm">{{ auth()->user()->name }}</p>
                <p class="text-xs text-green-400">En ligne</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-gray-700 overflow-hidden border-2 border-green-500">
                @if(auth()->user()->avatar_url)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->avatar_url) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center font-bold">{{ substr(auth()->user()->name, 0, 1) }}</div>
                @endif
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT GRID -->
    <div class="flex-1 flex overflow-hidden h-[92vh]">
        
        <!-- ZONE B: CATALOG (65%) -->
        <div class="w-[65%] flex flex-col bg-gray-100 border-r border-gray-200">
            <!-- Categories -->
            <div class="bg-white p-2 shadow-sm z-10">
                <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1">
                    @foreach($this->categories as $category)
                        <button 
                            wire:click="selectCategory({{ $category->id }})"
                            class="px-5 py-3 rounded-xl font-bold text-sm whitespace-nowrap transition-all
                            {{ $selectedCategoryId === $category->id 
                                ? 'bg-gray-800 text-white shadow-lg scale-105' 
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                        >
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Products Grid -->
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-3 xl:grid-cols-4 gap-4 pb-20">
                    @foreach($this->products as $product)
                        <button 
                            wire:click="selectProduct({{ $product->id }})"
                            class="bg-white rounded-2xl p-3 shadow-sm hover:shadow-md transition-all active:scale-95 flex flex-col justify-between h-40 group relative overflow-hidden border border-transparent hover:border-yellow-400"
                        >
                            <div class="absolute inset-0 z-0 opacity-10 group-hover:opacity-20 transition-opacity">
                                 @if($product->image_url)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_url) }}" class="w-full h-full object-cover">
                                 @else
                                    <div class="w-full h-full bg-gray-300"></div>
                                 @endif
                            </div>

                            <div class="z-10 w-full text-left">
                                <h3 class="font-bold text-gray-800 leading-tight text-sm">{{ $product->name }}</h3>
                            </div>

                            <div class="z-10 w-full flex justify-between items-end">
                                <span class="text-lg font-black text-gray-900">
                                    {{ number_format($product->getPriceByType(\App\Enums\OrderType::from($orderType)), 0) }}
                                </span>
                                <div class="bg-yellow-100 text-yellow-700 w-8 h-8 rounded-lg flex items-center justify-center font-bold text-lg group-hover:bg-yellow-400 group-hover:text-white transition-colors">
                                    +
                                </div>
                            </div>
                            
                            @if($product->optionGroups->isNotEmpty())
                                <div class="absolute top-2 right-2 bg-blue-100 text-blue-600 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">
                                    Options
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ZONE C: CART (35%) -->
        <div class="w-[35%] bg-white flex flex-col shadow-2xl z-20">
            <!-- Cart Header -->
            <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                <div>
                    <h2 class="font-bold text-lg text-gray-800">Commande</h2>
                    <span class="text-xs text-gray-500">
                        {{ $currentOrderId ? '#'.$currentOrderId : 'Nouveau' }}
                    </span>
                </div>
                <div class="flex bg-white rounded-lg p-1 border shadow-sm">
                    @foreach(['dine_in' => 'Sur Place', 'takeaway' => 'Emporter'] as $key => $label)
                        <button 
                            wire:click="$set('orderType', '{{ $key }}')"
                            class="px-3 py-1 text-xs font-bold rounded transition-colors {{ $orderType === $key ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-100' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                
                <!-- SENT ITEMS (Read Only) -->
                @php $sentItems = collect($cart)->where('status', 'sent'); @endphp
                @if($sentItems->isNotEmpty())
                    <div class="mb-4">
                        <h3 class="text-xs font-bold text-gray-400 uppercase mb-2 flex items-center">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span> En Cuisine
                        </h3>
                        @foreach($sentItems as $item)
                            <div class="flex justify-between items-start py-2 border-b border-gray-100 opacity-75 grayscale-[50%]">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-600">{{ $item['qty'] }}x</span>
                                        <span class="text-gray-800">{{ $item['name'] }}</span>
                                    </div>
                                    @if(!empty($item['options']))
                                        <div class="text-xs text-gray-500 pl-6">
                                            @foreach($item['options'] as $opt)
                                                <div>+ {{ $opt['name'] }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <span class="font-bold text-gray-600">{{ number_format($item['price'] * $item['qty'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- PENDING ITEMS (Editable) -->
                @php $pendingItems = collect($cart)->where('status', 'pending'); @endphp
                @if($pendingItems->isNotEmpty())
                    <div>
                        <h3 class="text-xs font-bold text-blue-500 uppercase mb-2 flex items-center">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"></span> Nouvelle Commande
                        </h3>
                        @foreach($cart as $index => $item)
                            @if($item['status'] === 'pending')
                                <div class="flex justify-between items-start py-3 bg-blue-50 rounded-xl p-3 mb-2 animate-in slide-in-from-right-5 fade-in duration-300">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-blue-900">{{ $item['qty'] }}x</span>
                                            <span class="font-bold text-gray-900">{{ $item['name'] }}</span>
                                        </div>
                                        @if(!empty($item['options']))
                                            <div class="text-xs text-blue-700 pl-6 mt-1">
                                                @foreach($item['options'] as $opt)
                                                    <div>+ {{ $opt['name'] }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="font-bold text-gray-900">{{ number_format($item['price'] * $item['qty'], 2) }}</span>
                                        <button wire:click="removeItem({{ $index }})" class="text-red-400 hover:text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

            </div>

            <!-- Cart Footer -->
            <div class="p-4 bg-white border-t border-gray-200 shadow-[0_-5px_20px_rgba(0,0,0,0.05)]">
                <div class="flex justify-between items-end mb-4">
                    <span class="text-sm text-gray-500">Total</span>
                    <span class="text-3xl font-black text-gray-900">{{ number_format($this->cartTotal, 2) }} <small class="text-sm font-normal text-gray-500">DH</small></span>
                </div>
                
                <button 
                    wire:click="sendToKitchen"
                    class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-200 transform active:scale-95 transition flex items-center justify-center gap-2 text-lg"
                    @if($pendingItems->isEmpty()) disabled class="opacity-50 cursor-not-allowed bg-gray-400" @endif
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    ENVOYER EN CUISINE
                </button>
            </div>
        </div>
    </div>

    <!-- OPTIONS MODAL -->
    @if($showOptionsModal && $selectedProductForOptions)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-sm p-4 sm:p-0">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in slide-in-from-bottom-10 fade-in">
                <!-- Header -->
                <div class="bg-gray-900 p-4 flex justify-between items-center text-white">
                    <div>
                        <h3 class="font-bold text-lg">{{ $selectedProductForOptions->name }}</h3>
                        <p class="text-sm text-gray-400">Personnalisez votre commande</p>
                    </div>
                    <button wire:click="$set('showOptionsModal', false)" class="p-2 hover:bg-gray-800 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-4 max-h-[60vh] overflow-y-auto space-y-6">
                    @foreach($selectedProductForOptions->optionGroups as $group)
                        <div class="border-b border-gray-100 pb-4 last:border-0">
                            <h4 class="font-bold text-gray-800 mb-3 flex justify-between">
                                {{ $group->name }}
                                @if($group->is_required) <span class="text-xs text-red-500 bg-red-50 px-2 py-1 rounded">Obligatoire</span> @endif
                            </h4>
                            
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($group->options as $option)
                                    <label class="cursor-pointer group relative">
                                        @if($group->is_multiselect)
                                            <input type="checkbox" wire:model="selectedOptions.{{ $group->id }}" value="{{ $option->id }}" class="peer sr-only">
                                        @else
                                            <input type="radio" wire:model="selectedOptions.{{ $group->id }}" value="{{ $option->id }}" class="peer sr-only">
                                        @endif
                                        
                                        <div class="p-3 rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all text-center h-full flex flex-col items-center justify-center">
                                            <span class="font-bold text-gray-700 peer-checked:text-blue-700">{{ $option->name }}</span>
                                            @if($option->price_modifier > 0)
                                                <span class="text-xs text-gray-500">+{{ number_format($option->price_modifier, 0) }} DH</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Footer -->
                <div class="p-4 border-t bg-gray-50">
                    <button 
                        wire:click="confirmOptions"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transition"
                    >
                        AJOUTER AU PANIER
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
