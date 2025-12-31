<div class="h-screen flex bg-gray-100 font-sans overflow-hidden" 
     x-data="{ notification: { show: false, message: '', type: 'success' } }"
     x-on:notify.window="notification.show = true; notification.message = $event.detail[0]; notification.type = $event.detail[1] || 'success'; setTimeout(() => notification.show = false, 3000)">

    <!-- NOTIFICATION TOAST -->
    <div x-show="notification.show" class="fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-xl font-bold text-white flex items-center gap-2"
         :class="notification.type === 'error' ? 'bg-red-500' : 'bg-green-500'" style="display: none;">
         <span x-text="notification.message"></span>
    </div>

    <!-- LEFT COL: ORDER SUMMARY (40%) -->
    <div class="w-2/5 bg-white border-r flex flex-col h-full shadow-lg z-10">
        <div class="p-6 bg-gray-50 border-b">
            <h1 class="text-2xl font-black text-gray-800 mb-1">Encaissement</h1>
            <p class="text-gray-500">Commande #{{ $order->id }} • {{ $order->table ? $order->table->name : 'À emporter' }}</p>
        </div>
        
        <div class="flex-1 overflow-y-auto p-6 space-y-4">
            @foreach($order->items as $item)
                <div class="flex justify-between items-start py-3 border-b border-gray-100 last:border-0">
                    <div>
                        <div class="font-bold text-gray-800">{{ $item->quantity }}x {{ $item->product->name }}</div>
                        @if(!empty($item->options))
                            <div class="text-sm text-gray-500">
                                @foreach($item->options as $opt)
                                    {{ $opt['name'] }} 
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="font-bold text-gray-900">{{ number_format($item->total, 2) }} DH</div>
                </div>
            @endforeach
        </div>

        <div class="p-6 bg-gray-900 text-white mt-auto">
            <div class="flex justify-between items-end">
                <span class="text-gray-400">Total à Payer</span>
                <span class="text-4xl font-black text-yellow-400">{{ number_format($amountToPay, 2) }} <small class="text-lg text-gray-400">DH</small></span>
            </div>
        </div>
    </div>

    <!-- RIGHT COL: PAYMENT INTERFACE (60%) -->
    <div class="w-3/5 bg-gray-100 flex flex-col h-full p-6">
        
        <!-- Payment Methods -->
        <div class="flex gap-4 mb-6">
            @foreach(['cash' => '💵 Espèces', 'card' => '💳 Carte Bancaire', 'free' => '🎁 Offert'] as $key => $label)
                <button 
                    wire:click="setPaymentMethod('{{ $key }}')"
                    class="flex-1 py-4 rounded-xl font-bold text-lg shadow-sm transition-all transform active:scale-95
                    {{ $paymentMethod === $key ? 'bg-blue-600 text-white ring-4 ring-blue-200' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if($paymentMethod === 'cash')
            <!-- Display Amounts -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Reçu (Client)</span>
                    <div class="flex justify-between items-center">
                        <span class="text-3xl font-black text-blue-600">{{ number_format($amountTendered, 2) }}</span>
                        <button wire:click="clearInput" class="text-gray-400 hover:text-red-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">A Rendre</span>
                    <div class="text-3xl font-black {{ $changeDue > 0 ? 'text-green-500' : 'text-gray-300' }}">
                        {{ number_format($changeDue, 2) }}
                    </div>
                </div>
            </div>

            <!-- SMART CASH GRID (Moroccan Currency) -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <!-- Bills -->
                <button wire:click="addCash(200)" class="h-24 rounded-lg bg-blue-100 border-2 border-blue-300 hover:bg-blue-200 active:scale-95 transition flex flex-col items-center justify-center shadow-sm">
                    <span class="font-black text-blue-800 text-2xl">200</span>
                    <span class="text-xs text-blue-600 font-bold">DH</span>
                </button>
                <button wire:click="addCash(100)" class="h-24 rounded-lg bg-yellow-100 border-2 border-yellow-300 hover:bg-yellow-200 active:scale-95 transition flex flex-col items-center justify-center shadow-sm">
                    <span class="font-black text-yellow-800 text-2xl">100</span>
                    <span class="text-xs text-yellow-600 font-bold">DH</span>
                </button>
                <button wire:click="addCash(50)" class="h-24 rounded-lg bg-green-100 border-2 border-green-300 hover:bg-green-200 active:scale-95 transition flex flex-col items-center justify-center shadow-sm">
                    <span class="font-black text-green-800 text-2xl">50</span>
                    <span class="text-xs text-green-600 font-bold">DH</span>
                </button>
                <button wire:click="addCash(20)" class="h-24 rounded-lg bg-purple-100 border-2 border-purple-300 hover:bg-purple-200 active:scale-95 transition flex flex-col items-center justify-center shadow-sm">
                    <span class="font-black text-purple-800 text-2xl">20</span>
                    <span class="text-xs text-purple-600 font-bold">DH</span>
                </button>

                <!-- Coins -->
                <button wire:click="addCash(10)" class="h-16 rounded-full bg-yellow-50 border-2 border-yellow-400 hover:bg-yellow-100 active:scale-95 transition flex items-center justify-center shadow-sm mx-auto w-16">
                    <span class="font-bold text-yellow-700">10</span>
                </button>
                <button wire:click="addCash(5)" class="h-16 rounded-full bg-gray-100 border-2 border-gray-400 hover:bg-gray-200 active:scale-95 transition flex items-center justify-center shadow-sm mx-auto w-16">
                    <span class="font-bold text-gray-600">5</span>
                </button>
                <button wire:click="addCash(2)" class="h-16 rounded-full bg-gray-100 border-2 border-gray-400 hover:bg-gray-200 active:scale-95 transition flex items-center justify-center shadow-sm mx-auto w-16">
                    <span class="font-bold text-gray-600">2</span>
                </button>
                <button wire:click="addCash(1)" class="h-16 rounded-full bg-gray-100 border-2 border-gray-400 hover:bg-gray-200 active:scale-95 transition flex items-center justify-center shadow-sm mx-auto w-16">
                    <span class="font-bold text-gray-600">1</span>
                </button>
            </div>
            
            <!-- Numpad Fallback (Optional, simplified) -->
            <div class="grid grid-cols-3 gap-2 max-w-xs mx-auto opacity-50 hover:opacity-100 transition-opacity">
               @foreach([1,2,3,4,5,6,7,8,9] as $num)
                   <button wire:click="appendNumber({{ $num }})" class="bg-white p-2 rounded shadow-sm hover:bg-gray-50 font-bold text-gray-600">{{ $num }}</button>
               @endforeach
               <button wire:click="appendNumber(0)" class="col-span-3 bg-white p-2 rounded shadow-sm hover:bg-gray-50 font-bold text-gray-600">0</button>
            </div>

        @else
            <!-- Card Payment View -->
            <div class="flex-1 flex flex-col items-center justify-center text-center opacity-75">
                <svg class="w-32 h-32 text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <h3 class="text-2xl font-bold text-gray-800">Paiement par Carte</h3>
                <p class="text-gray-500 max-w-md">Veuillez effectuer la transaction sur le TPE puis valider ci-dessous.</p>
            </div>
        @endif

        <!-- Footer Actions -->
        <div class="mt-auto pt-6">
            <button 
                wire:click="processPayment"
                class="w-full bg-green-600 hover:bg-green-500 text-white text-xl font-bold py-5 rounded-2xl shadow-xl shadow-green-200 transform active:scale-95 transition flex items-center justify-center gap-3"
                @if($paymentMethod === 'cash' && $amountTendered < $amountToPay) disabled class="opacity-50 cursor-not-allowed bg-gray-400" @endif
            >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                VALIDER LE PAIEMENT
            </button>
        </div>
    </div>
</div>
