<div class="min-h-[85vh] bg-gray-100 dark:bg-gray-900 p-4 lg:p-6 overflow-y-auto font-sans" wire:poll.10s>
    
    <!-- Sound Notification -->
    <audio id="newOrderSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', ({ component, el }) => {
                // Hook logic if needed
            });
        });
    </script>
    
    <!-- Alpine Logic for Sound -->
    <div x-data="{ 
            count: {{ $this->orders->count() }},
            init() {
                $this.$watch('count', value => {
                    if (value > this.count) { // Only play on increase
                        document.getElementById('newOrderSound').play().catch(e => console.log('Audio autoplay blocked'));
                    }
                    this.count = value;
                })
            }
        }">
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-black text-gray-800 dark:text-white uppercase tracking-wider flex items-center gap-3">
            <x-heroicon-o-fire class="w-8 h-8 text-orange-500" />
            Cuisine (KDS)
        </h1>
        <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-full shadow-sm text-sm font-bold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
            {{ $orders->count() }} Commande(s) en attente
        </div>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 lg:gap-6">
        @forelse($this->orders as $order)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col transition-all duration-300 hover:shadow-xl relative group">
                
                <!-- Elapsed Time Indicator (Visual) -->
                @php
                    $elapsed = $order->created_at->diffInMinutes(now());
                    $bgClass = $elapsed > 20 ? 'bg-red-500' : ($elapsed > 10 ? 'bg-amber-500' : 'bg-blue-500');
                @endphp
                <div class="absolute top-0 left-0 w-full h-1 {{ $bgClass }}"></div>

                <!-- Card Header -->
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 flex justify-between items-start">
                    <div>
                        <h3 class="font-black text-xl text-gray-800 dark:text-white flex items-center gap-2">
                            Table {{ $order->table->name ?? '?' }}
                            @if($elapsed > 20)
                                <x-heroicon-s-exclamation-circle class="w-5 h-5 text-red-500 animate-pulse" />
                            @endif
                        </h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-600 px-2 py-0.5 rounded">#{{ $order->local_id }}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $order->server->name ?? 'Srv' }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-gray-700 dark:text-gray-200 font-mono">{{ $order->created_at->format('H:i') }}</div>
                        <div class="text-xs font-bold {{ $elapsed > 20 ? 'text-red-500' : ($elapsed > 10 ? 'text-amber-500' : 'text-blue-500') }}">
                            {{ $elapsed }} min
                        </div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="p-4 flex-1 overflow-y-auto max-h-[400px]">
                    <ul class="space-y-3">
                        @foreach($order->items as $item)
                            <li class="flex items-start gap-3 p-2 rounded-lg {{ $item->printed_kitchen ? 'opacity-60 bg-gray-50 dark:bg-gray-700/30' : 'bg-blue-50 dark:bg-blue-900/20' }}">
                                <span class="flex-shrink-0 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-900 w-8 h-8 flex items-center justify-center rounded-lg text-sm font-black shadow-sm">
                                    {{ $item->quantity }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <span class="block font-bold text-gray-800 dark:text-gray-200 leading-tight text-sm lg:text-base">
                                        {{ $item->product->name }}
                                    </span>
                                    @if($item->notes)
                                        <p class="mt-1 text-xs font-bold text-red-500 bg-red-50 dark:bg-red-900/30 p-1 rounded border border-red-100 dark:border-red-800/50 inline-block">
                                            ⚠️ {{ $item->notes }}
                                        </p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Actions -->
                <div class="p-3 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex gap-2">
                    <button 
                        wire:click="printTicket('{{ $order->uuid }}')"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 py-3 rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2"
                        title="Réimprimer"
                    >
                        <x-heroicon-o-printer class="w-5 h-5" />
                    </button>
                    <button 
                        wire:click="markAsReady('{{ $order->uuid }}')"
                        class="flex-[3] bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl text-sm font-black tracking-wide shadow-lg shadow-emerald-200 dark:shadow-none transition-all active:scale-95 flex items-center justify-center gap-2"
                    >
                        <x-heroicon-o-check class="w-5 h-5" />
                        PRÊT
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center h-[50vh] text-gray-400 dark:text-gray-600">
                <div class="bg-white dark:bg-gray-800 p-8 rounded-full shadow-sm mb-4">
                     <x-heroicon-o-check-circle class="w-24 h-24 text-green-100 dark:text-green-900" />
                </div>
                <h3 class="text-xl font-bold text-gray-500 dark:text-gray-400">Tout est calme en cuisine</h3>
                <p class="text-sm">En attente de nouvelles commandes...</p>
            </div>
        @endforelse
    </div>
</div>