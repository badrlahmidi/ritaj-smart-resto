<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Liste des commandes actives -->
    <div class="lg:col-span-1 bg-white rounded-xl shadow p-4 h-fit">
        <h2 class="text-xl font-bold mb-4">Commandes à encaisser</h2>
        <div class="space-y-3">
            @forelse($this->activeOrders as $order)
                <div 
                    wire:click="selectOrder('{{ $order->uuid }}')"
                    class="cursor-pointer border rounded-lg p-3 transition hover:bg-blue-50 {{ $selectedOrderUuid === $order->uuid ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-200' }}"
                >
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-bold text-lg">Table {{ $order->table->name ?? '?' }}</span>
                            <div class="text-sm text-gray-500">
                                Ticket #{{ $order->local_id }} <br>
                                {{ $order->waiter->name ?? 'Serveur Inconnu' }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-xl text-green-600">{{ number_format($order->total_amount, 2) }}</span> <span class="text-xs">DH</span>
                            <br>
                            <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">{{ $order->status }}</span>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-400">
                        {{ $order->updated_at->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    Aucune commande en attente
                </div>
            @endforelse
        </div>
    </div>

    <!-- Détail Commande & Paiement -->
    <div class="lg:col-span-2 space-y-6">
        @if($this->selectedOrder)
            <!-- Détails -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold">Ticket #{{ $this->selectedOrder->local_id }}</h2>
                    <span class="text-gray-500">Table: {{ $this->selectedOrder->table->name }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-600 uppercase">
                            <tr>
                                <th class="px-4 py-3">Produit</th>
                                <th class="px-4 py-3 text-center">Qté</th>
                                <th class="px-4 py-3 text-right">Prix U.</th>
                                <th class="px-4 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($this->selectedOrder->items as $item)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $item->product->name }}</td>
                                    <td class="px-4 py-3 text-center">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold">{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-gray-200">
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-right font-bold text-xl">TOTAL À PAYER</td>
                                <td class="px-4 py-4 text-right font-bold text-2xl text-blue-600">
                                    {{ number_format($this->selectedOrder->total_amount, 2) }} DH
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Interface Paiement -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-bold mb-4">Paiement</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mode de Paiement</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button 
                                wire:click="$set('paymentMethod', 'cash')"
                                class="flex items-center justify-center gap-2 p-3 rounded-lg border-2 {{ $paymentMethod === 'cash' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 hover:border-gray-300' }}"
                            >
                                💵 Espèces
                            </button>
                            <button 
                                wire:click="$set('paymentMethod', 'card')"
                                class="flex items-center justify-center gap-2 p-3 rounded-lg border-2 {{ $paymentMethod === 'card' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300' }}"
                            >
                                💳 Carte Bancaire
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Montant Reçu</label>
                        <div class="relative rounded-md shadow-sm">
                            <input 
                                type="number" 
                                wire:model.live="amountTendered"
                                class="block w-full rounded-md border-0 py-3 pl-4 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-xl sm:leading-6" 
                                placeholder="0.00"
                            >
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-gray-500 sm:text-sm">DH</span>
                            </div>
                        </div>
                        @if($paymentMethod === 'cash' && $change > 0)
                            <div class="mt-2 text-right">
                                <span class="text-sm text-gray-500">Monnaie à rendre :</span>
                                <span class="text-xl font-bold text-green-600 ml-2">{{ number_format($change, 2) }} DH</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t flex justify-end gap-4">
                    <button 
                        wire:click="$set('selectedOrderUuid', null)"
                        class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        wire:click="processPayment"
                        class="px-8 py-3 rounded-lg bg-green-600 text-white font-bold text-lg hover:bg-green-700 shadow-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ ($amountTendered < $this->selectedOrder->total_amount && $paymentMethod === 'cash') ? 'disabled' : '' }}
                    >
                        <span>✅ Valider & Imprimer</span>
                    </button>
                </div>
            </div>
        @else
            <div class="h-full flex flex-col items-center justify-center text-gray-400 border-2 border-dashed border-gray-300 rounded-xl p-12">
                <svg class="w-24 h-24 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <p class="text-xl font-medium">Sélectionnez une commande pour encaisser</p>
            </div>
        @endif
    </div>
</div>
