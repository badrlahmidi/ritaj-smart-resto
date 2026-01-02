<div class="h-screen w-full font-sans antialiased" x-data="{ view: 'ordering', cartOpen: false }">
    <!-- Main container -->
    <div class="flex h-full">

        <!-- ======================================================= -->
        <!-- DESKTOP LAYOUT                                          -->
        <!-- ======================================================= -->
        <div class="hidden md:flex flex-1 h-full">
            <!-- Left: Menu -->
            <div class="flex-1 flex flex-col h-full overflow-hidden border-r border-gray-200 bg-gray-50">
                <!-- Header -->
                <div class="bg-white shadow-sm z-10 p-3">
                    <div class="flex space-x-2 overflow-x-auto no-scrollbar pb-2">
                        @foreach($this->categories as $category)
                            <button wire:click="selectCategory({{ $category->id }})" class="px-5 py-2.5 rounded-lg text-xs font-bold whitespace-nowrap transition-all {{ $selectedCategoryId === $category->id ? 'bg-gray-900 text-white shadow' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        @foreach($this->products as $product)
                            <button wire:click="selectProduct({{ $product->id }})" class="bg-white rounded-lg p-3 shadow-sm hover:shadow-lg transition-all active:scale-95 flex flex-col justify-between h-36">
                                <div class="w-full text-left">
                                    <h3 class="font-bold text-gray-800 text-sm leading-tight">{{ $product->name }}</h3>
                                </div>
                                <div class="w-full flex justify-between items-end">
                                    <span class="text-md font-black text-gray-900">{{ number_format($product->getPriceByType(\App\Enums\OrderType::from($orderType)), 0) }}<span class="text-xs">DH</span></span>
                                    <div class="bg-yellow-400 text-white w-6 h-6 rounded-md flex items-center justify-center shadow-lg">+</div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right: Cart -->
            <div class="w-[380px] bg-white shadow-lg flex flex-col h-full z-10 border-l">
                @if($view === 'ordering')
                    @include('livewire.pos.partials.cart-view')
                @elseif($view === 'payment')
                    @include('livewire.pos.partials.payment-view')
                @endif
            </div>
        </div>

        <!-- ======================================================= -->
        <!-- MOBILE LAYOUT                                           -->
        <!-- ======================================================= -->
        <div class="flex md:hidden flex-1 flex-col h-full w-full">
            <!-- Top Content -->
            <div class="flex-1 overflow-y-auto">
                <div class="p-3">
                    <div class="flex space-x-2 overflow-x-auto no-scrollbar pb-2">
                        @foreach($this->categories as $category)
                            <button wire:click="selectCategory({{ $category->id }})" class="px-4 py-2 rounded-lg text-xs font-bold whitespace-nowrap {{ $selectedCategoryId === $category->id ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-600' }}">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 p-3">
                    @foreach($this->products as $product)
                        <button wire:click="selectProduct({{ $product->id }})" class="bg-white rounded-lg p-3 shadow-sm h-32 flex flex-col justify-between">
                             <h3 class="font-bold text-gray-800 text-xs leading-tight">{{ $product->name }}</h3>
                             <span class="text-md font-black text-gray-900">{{ number_format($product->getPriceByType(\App\Enums\OrderType::from($orderType)), 0) }}<span class="text-xs">DH</span></span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Floating Cart Button -->
            <div class="fixed bottom-4 right-4 z-20">
                <button @click="cartOpen = true" class="bg-gray-900 text-white rounded-full shadow-xl h-16 w-16 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span class="absolute -top-1 -right-1 bg-yellow-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">{{ count($cart) }}</span>
                </button>
            </div>

            <!-- Mobile Cart Panel -->
            <div x-show="cartOpen" @click.away="cartOpen = false" class="fixed inset-0 bg-black/50 z-30" style="display:none;">
                <div @click.away="cartOpen = false" class="absolute inset-x-0 bottom-0 bg-white rounded-t-2xl max-h-[80vh] flex flex-col">
                    @if($view === 'ordering')
                        @include('livewire.pos.partials.cart-view')
                    @elseif($view === 'payment')
                        @include('livewire.pos.partials.payment-view')
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Modals (shared between layouts) -->
    @if($showOptionsModal)
        @include('livewire.pos.partials.options-modal')
    @endif
</div>
