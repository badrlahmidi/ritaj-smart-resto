<div class="h-screen bg-gray-900 text-white font-sans flex flex-col overflow-hidden"
     x-data="{ notification: { show: false, message: '', type: 'success' } }"
     x-on:notify.window="notification.show = true; notification.message = $event.detail[0]; notification.type = $event.detail[1] || 'success'; setTimeout(() => notification.show = false, 3000)">

    <!-- HEADER -->
    <div class="h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-6 shadow-md z-10">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold text-yellow-500 tracking-wider">KDS <span class="text-gray-400 text-sm font-normal">{{ $stationFilter ? strtoupper($stationFilter) : 'MASTER' }}</span></h1>
            <span class="bg-gray-700 text-white px-3 py-1 rounded-full text-xs font-bold animate-pulse">LIVE</span>
        </div>
        
        <div class="flex gap-4 text-sm font-bold text-gray-400">
            <span>En attente : <span class="text-white">{{ $this->pendingOrders->count() }}</span></span>
            <span>Retard (>20m) : <span class="text-red-500">0</span></span>
        </div>

        <div class="text-xs text-gray-500">{{ now()->format('H:i') }}</div>
    </div>

    <!-- KANBAN BOARD (Horizontal Scroll) -->
    <div class="flex-1 overflow-x-auto overflow-y-hidden p-6">
        <div class="flex gap-4 h-full">
            @forelse($this->pendingOrders as $order)
                <!-- TICKET CARD -->
                <div class="w-80 flex-shrink-0 bg-gray-100 rounded-xl flex flex-col shadow-2xl overflow-hidden border-t-8 
                    {{ $order->updated_at->diffInMinutes(now()) > 20 ? 'border-red-500 bg-red-50' : ($order->updated_at->diffInMinutes(now()) > 10 ? 'border-orange-500' : 'border-green-500') }}
                    animate-in slide-in-from-right-10 duration-500">
                    
                    <!-- Ticket Header -->
                    <div class="bg-white p-3 border-b flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-black text-gray-900">{{ $order->table?->name ?? $order->type->getLabel() }}</h2>
                            <p class="text-xs text-gray-500 font-bold uppercase">{{ $order->server?->name ?? 'System' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold text-gray-800">#{{ $order->local_id }}</span>
                            <div class="text-xs font-bold {{ $order->updated_at->diffInMinutes(now()) > 20 ? 'text-red-600 animate-pulse' : 'text-gray-400' }}">
                                {{ $order->updated_at->format('H:i') }}
                                ({{ $order->updated_at->diffInMinutes(now()) }}m)
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Body (Items) -->
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        @foreach($order->items as $item)
                            <div class="flex gap-3 group cursor-pointer" wire:click="markItemReady({{ $item->id }})">
                                <div class="w-8 h-8 flex items-center justify-center bg-gray-200 text-gray-900 font-black rounded text-lg group-hover:bg-green-200 transition">
                                    {{ $item->quantity }}
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-800 text-lg leading-tight group-hover:line-through decoration-2 decoration-green-500">{{ $item->product->name }}</h3>
                                    
                                    <!-- Options -->
                                    @if(!empty($item->options))
                                        <div class="text-sm text-red-600 font-bold mt-1 bg-red-100 inline-block px-2 rounded">
                                            @foreach($item->options as $opt)
                                                {{ $opt['name'] }}@if(!$loop->last), @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Note -->
                                    @if($item->notes)
                                        <div class="text-xs text-orange-600 italic bg-orange-100 p-1 rounded mt-1">
                                            ⚠️ {{ $item->notes }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Ticket Footer -->
                    <div class="p-3 bg-gray-200">
                        <button 
                            wire:click="markOrderReady('{{ $order->uuid }}')"
                            class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-3 rounded-lg shadow uppercase tracking-wide transform active:scale-95 transition"
                        >
                            Terminer
                        </button>
                    </div>
                </div>
            @empty
                <div class="w-full h-full flex flex-col items-center justify-center text-gray-600">
                    <svg class="w-24 h-24 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <h2 class="text-2xl font-bold opacity-50">Aucune commande en attente</h2>
                    <p class="opacity-30">La cuisine est calme...</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
