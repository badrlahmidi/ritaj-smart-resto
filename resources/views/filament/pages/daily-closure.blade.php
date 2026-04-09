<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Date Filter --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <form wire:submit="$refresh">
                {{ $this->form }}
                <div class="mt-4 flex justify-end">
                    <x-filament::button type="submit" icon="heroicon-m-magnifying-glass">
                        Afficher la clôture
                    </x-filament::button>
                </div>
            </form>
        </div>

        @php $closure = $this->getClosureData(); @endphp

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Chiffre d'Affaires</p>
                <p class="text-3xl font-black text-emerald-600">{{ number_format($closure['total_revenue'], 2) }} <span class="text-sm text-gray-400">DH</span></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Tickets Vendus</p>
                <p class="text-3xl font-black text-blue-600">{{ $closure['orders_count'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Ticket Moyen</p>
                <p class="text-3xl font-black text-amber-600">{{ number_format($closure['avg_ticket'], 2) }} <span class="text-sm text-gray-400">DH</span></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Annulations</p>
                <p class="text-3xl font-black {{ $closure['cancelled_count'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $closure['cancelled_count'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Payment Methods Breakdown --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h3 class="text-sm font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <x-heroicon-o-banknotes class="w-4 h-4" /> Ventilation par Mode de Paiement
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                <x-heroicon-s-banknotes class="w-4 h-4 text-emerald-600" />
                            </div>
                            <span class="font-bold text-gray-700 dark:text-gray-300">Espèces</span>
                        </div>
                        <span class="font-black text-gray-900 dark:text-white">{{ number_format($closure['cash_total'], 2) }} DH</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <x-heroicon-s-credit-card class="w-4 h-4 text-blue-600" />
                            </div>
                            <span class="font-bold text-gray-700 dark:text-gray-300">Carte Bancaire</span>
                        </div>
                        <span class="font-black text-gray-900 dark:text-white">{{ number_format($closure['card_total'], 2) }} DH</span>
                    </div>
                    @if($closure['other_total'] > 0)
                    <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                <x-heroicon-s-ellipsis-horizontal-circle class="w-4 h-4 text-gray-500" />
                            </div>
                            <span class="font-bold text-gray-700 dark:text-gray-300">Autres</span>
                        </div>
                        <span class="font-black text-gray-900 dark:text-white">{{ number_format($closure['other_total'], 2) }} DH</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center pt-3 mt-2">
                        <span class="font-black text-gray-700 dark:text-gray-300 uppercase text-sm tracking-wide">Total</span>
                        <span class="text-xl font-black text-emerald-600">{{ number_format($closure['total_revenue'], 2) }} DH</span>
                    </div>
                </div>
            </div>

            {{-- By Order Type + Tax --}}
            <div class="space-y-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <x-heroicon-o-chart-pie class="w-4 h-4" /> Ventilation par Type de Commande
                    </h3>
                    <div class="space-y-2">
                        @forelse($closure['by_type'] as $type => $stats)
                        <div class="flex justify-between items-center py-2 border-b border-gray-50 dark:border-gray-700 last:border-0">
                            <span class="font-bold text-gray-700 dark:text-gray-300">{{ $type }}</span>
                            <div class="text-right">
                                <span class="font-black text-gray-900 dark:text-white">{{ number_format($stats['total'], 2) }} DH</span>
                                <span class="ml-2 text-xs text-gray-400">({{ $stats['count'] }} tickets)</span>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400 text-center py-4">Aucune commande ce jour</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl border border-amber-200 dark:border-amber-800/30 p-6">
                    <h3 class="text-sm font-black text-amber-700 dark:text-amber-400 uppercase tracking-widest mb-3">
                        TVA Estimée ({{ $closure['tax_rate'] }}%)
                    </h3>
                    <p class="text-2xl font-black text-amber-600">{{ number_format($closure['tax_estimate'], 2) }} DH</p>
                    <p class="text-xs text-amber-500 mt-1">Estimation indicative — consultez votre comptable</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
