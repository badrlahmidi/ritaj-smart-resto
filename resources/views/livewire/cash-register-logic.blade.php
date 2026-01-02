<div class="min-h-[85vh] bg-gray-50 dark:bg-gray-900 p-4 lg:p-6 font-sans">
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full">
        
        <!-- LEFT COLUMN: ACTIVE ORDERS LIST -->
        <div class="lg:col-span-4 flex flex-col h-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h2 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-wide flex items-center gap-2">
                    <x-heroicon-o-queue-list class="w-5 h-5 text-blue-500" />
                    À Encaisser
                </h2>
            </div>
            
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                @forelse($this->activeOrders as $order)
                    <div 
                        wire:click="selectOrder('{{ $order->uuid }}')"
                        class="cursor-pointer group relative bg-white dark:bg-gray-700 p-4 rounded-xl border-2 transition-all duration-200 hover:shadow-md
                        {{ $selectedOrderUuid === $order->uuid 
                            ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 shadow-md ring-1 ring-blue-500' 
                            : 'border-gray-100 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-400' 
                        }}"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="block font-black text-lg text-gray-800 dark:text-white">Table {{ $order->table->name ?? '?' }}</span>
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ticket #{{ $order->local_id }}</span>
                            </div>
                            <div class="text-right">
                                <span class="block font-black text-lg text-blue-600 dark:text-blue-400">{{ number_format($order->total_amount, 2) }} <span class="text-xs text-gray-500">DH</span></span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-end mt-2">
                             <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <x-heroicon-o-user class="w-3 h-3" />
                                {{ $order->waiter->name ?? 'Serveur' }}
                             </div>
                             <span class="text-[10px] font-medium text-gray-400 bg-gray-100 dark:bg-gray-600 px-2 py-1 rounded-full">
                                {{ $order->updated_at->format('H:i') }}
                             </span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-64 text-gray-400 dark:text-gray-500 opacity-60">
                        <x-heroicon-o-check-badge class="w-16 h-16 mb-2" />
                        <p class="text-sm font-medium">Aucune commande en attente</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- RIGHT COLUMN: DETAILS & PAYMENT -->
        <div class="lg:col-span-8 flex flex-col h-full gap-6 overflow-y-auto">
            @if($this->selectedOrder)
                <!-- Order Details Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col flex-1 min-h-0">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50 rounded-t-2xl">
                        <div>
                            <h2 class="text-2xl font-black text-gray-800 dark:text-white">Ticket #{{ $this->selectedOrder->local_id }}</h2>
                            <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">Table {{ $this->selectedOrder->table->name }}</span>
                        </div>
                        <div class="text-right">
                             <span class="block text-xs text-gray-500 uppercase font-bold">Total à Payer</span>
                             <span class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ number_format($this->selectedOrder->total_amount, 2) }} DH</span>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-0">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 uppercase text-xs font-bold sticky top-0 backdrop-blur-sm">
                                <tr>
                                    <th class="px-6 py-4">Produit</th>
                                    <th class="px-6 py-4 text-center">Qté</th>
                                    <th class="px-6 py-4 text-right">Prix U.</th>
                                    <th class="px-6 py-4 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($this->selectedOrder->items as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200">{{ $item->product->name }}</td>
                                        <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-400">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 text-right font-black text-gray-800 dark:text-white">{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Interface Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 flex-shrink-0">
                    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">Finaliser le paiement</h3>
                    
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                        <!-- Left: Methods -->
                        <div class="space-y-4">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Mode de Paiement</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button 
                                    wire:click="$set('paymentMethod', 'cash')"
                                    class="relative overflow-hidden group flex flex-col items-center justify-center gap-2 p-4 rounded-xl border-2 transition-all duration-200
                                    {{ $paymentMethod === 'cash' 
                                        ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 shadow-sm' 
                                        : 'border-gray-200 dark:border-gray-700 hover:border-emerald-300 dark:hover:border-emerald-700 text-gray-600 dark:text-gray-300' 
                                    }}"
                                >
                                    <x-heroicon-o-banknotes class="w-8 h-8" />
                                    <span class="font-bold">Espèces</span>
                                    @if($paymentMethod === 'cash')
                                        <div class="absolute inset-0 border-2 border-emerald-500 rounded-xl pointer-events-none"></div>
                                    @endif
                                </button>

                                <button 
                                    wire:click="$set('paymentMethod', 'card')"
                                    class="relative overflow-hidden group flex flex-col items-center justify-center gap-2 p-4 rounded-xl border-2 transition-all duration-200
                                    {{ $paymentMethod === 'card' 
                                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 shadow-sm' 
                                        : 'border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700 text-gray-600 dark:text-gray-300' 
                                    }}"
                                >
                                    <x-heroicon-o-credit-card class="w-8 h-8" />
                                    <span class="font-bold">Carte Bancaire</span>
                                    @if($paymentMethod === 'card')
                                        <div class="absolute inset-0 border-2 border-blue-500 rounded-xl pointer-events-none"></div>
                                    @endif
                                </button>
                            </div>
                        </div>

                        <!-- Right: Amount -->
                        <div class="space-y-4">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Montant Reçu</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-400 font-bold">DH</span>
                                </div>
                                <input 
                                    type="number" 
                                    wire:model.live="amountTendered"
                                    class="block w-full pl-12 pr-4 py-4 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-2xl font-black tracking-widest focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner"
                                    placeholder="0.00"
                                >
                            </div>
                            
                            @if($paymentMethod === 'cash')
                                <div class="flex justify-between items-center p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800/30">
                                    <span class="text-sm font-bold text-emerald-800 dark:text-emerald-400">À Rendre</span>
                                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($change > 0 ? $change : 0, 2) }} DH</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-4">
                        <button 
                            wire:click="$set('selectedOrderUuid', null)"
                            class="px-6 py-4 rounded-xl text-gray-500 dark:text-gray-400 font-bold hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        >
                            Annuler
                        </button>
                        <button 
                            wire:click="processPayment"
                            class="flex-1 lg:flex-none px-12 py-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-lg shadow-lg shadow-blue-200 dark:shadow-none transition-all hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3"
                            {{ ($amountTendered < $this->selectedOrder->total_amount && $paymentMethod === 'cash') ? 'disabled' : '' }}
                        >
                            <x-heroicon-o-check class="w-6 h-6" />
                            <span>Valider le Paiement</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-300 dark:text-gray-600 border-4 border-dashed border-gray-200 dark:border-gray-700 rounded-3xl m-4 bg-gray-50/50 dark:bg-gray-800/30">
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-full shadow-sm mb-6">
                        <x-heroicon-o-currency-dollar class="w-24 h-24 text-gray-200 dark:text-gray-700" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-400 dark:text-gray-500">Aucune commande sélectionnée</h3>
                    <p class="text-sm font-medium mt-2">Cliquez sur une commande à gauche pour l'encaisser</p>
                </div>
            @endif
        </div>
    </div>
</div>