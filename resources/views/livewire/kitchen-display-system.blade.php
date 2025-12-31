<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4" wire:poll.5s>
    
    <!-- Son de notification -->
    <audio id="newOrderSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let previousCount = @json($this->orders->count());

            setInterval(() => {
                // Cette logique est simplifiée; idéalement Livewire devrait émettre un événement quand count change
                // Mais avec wire:poll, on peut comparer côté serveur si on veut.
                // Ici, on utilise un event dispatché depuis le composant PHP si possible, 
                // ou on laisse le poll faire le refresh visuel et on ajoute un hook JS.
            }, 5000);
            
            Livewire.hook('morph.updated', ({ component, el }) => {
                // Hook basique : Si on détecte une augmentation du nombre d'éléments, on joue le son
                // Pour simplifier, on peut jouer un son léger à chaque refresh si y'a des commandes "Urgent"
            });
        });
    </script>
    
    <!-- Notification JS logic via Alpine pour plus de simplicité -->
    <div x-data="{ 
            count: {{ $this->orders->count() }},
            init() {
                $watch('count', value => {
                    if (value > 0) {
                        document.getElementById('newOrderSound').play().catch(e => console.log('Audio autoplay blocked'));
                    }
                })
            }
        }">
    </div>

    @forelse($this->orders as $order)
        <div class="bg-white rounded-xl shadow-lg border-l-4 border-blue-500 overflow-hidden animate-pulse-once">
            <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg">Table {{ $order->table->name ?? '?' }}</h3>
                    <span class="text-xs text-gray-500">Ticket #{{ $order->local_id }}</span>
                </div>
                <div class="text-right">
                    <span class="text-sm font-semibold">{{ $order->updated_at->format('H:i') }}</span>
                    <br>
                    <span class="text-xs text-gray-500">{{ $order->waiter->name ?? 'Srv' }}</span>
                </div>
            </div>
            
            <div class="p-4">
                <ul class="space-y-2">
                    @foreach($order->items as $item)
                        <li class="flex justify-between items-start {{ $item->printed_kitchen ? 'opacity-100' : 'font-bold text-blue-600' }}">
                            <div class="flex items-start gap-2">
                                <span class="bg-gray-200 text-gray-800 px-2 py-0.5 rounded text-sm font-bold">{{ $item->quantity }}x</span>
                                <div>
                                    <span>{{ $item->product->name }}</span>
                                    @if($item->notes)
                                        <p class="text-xs text-red-500 italic">Note: {{ $item->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="p-3 bg-gray-50 border-t flex gap-2">
                <button 
                    wire:click="printTicket('{{ $order->uuid }}')"
                    class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-2 rounded-lg text-sm transition"
                >
                    🖨️ Imprimer
                </button>
                <button 
                    wire:click="markAsReady('{{ $order->uuid }}')"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-bold transition"
                >
                    ✅ Prêt
                </button>
            </div>
        </div>
    @empty
        <div class="col-span-full flex flex-col items-center justify-center h-64 text-gray-400">
            <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            <p class="text-xl">Aucune commande en attente</p>
        </div>
    @endforelse
</div>
