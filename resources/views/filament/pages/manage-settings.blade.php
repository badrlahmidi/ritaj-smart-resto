<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex justify-end mt-4">
            <x-filament::button type="submit">
                Sauvegarder les Paramètres
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
