<x-filament-panels::page>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
        <form wire:submit="updateReport">
            {{ $this->form }}
            
            <div class="mt-4 flex justify-end">
                <x-filament::button type="submit">
                    Actualiser le Rapport
                </x-filament::button>
            </div>
        </form>
    </div>

    <div class="mt-8">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
