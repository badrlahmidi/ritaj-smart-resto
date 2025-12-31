<div class="min-h-screen flex flex-col items-center justify-center bg-gray-900 text-white p-4">
    <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold tracking-tight text-yellow-500 mb-2">Ritaj Smart Resto</h1>
        <p class="text-gray-400">Entrez votre code PIN serveur</p>
    </div>

    <!-- PIN Display -->
    <div class="mb-8 w-full max-w-xs flex justify-center gap-4">
        @for ($i = 0; $i < 4; $i++)
            <div class="w-12 h-12 rounded-full border-2 flex items-center justify-center {{ strlen($pin) > $i ? 'bg-yellow-500 border-yellow-500' : 'border-gray-600' }}">
                @if (strlen($pin) > $i)
                    <div class="w-3 h-3 bg-gray-900 rounded-full"></div>
                @endif
            </div>
        @endfor
    </div>

    @if ($error)
        <div class="mb-6 text-red-500 font-bold animate-pulse">
            {{ $error }}
        </div>
    @endif

    <!-- Keypad -->
    <div class="grid grid-cols-3 gap-4 w-full max-w-xs">
        @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $number)
            <button 
                wire:click="append({{ $number }})"
                class="h-20 w-20 rounded-2xl bg-gray-800 text-3xl font-bold hover:bg-gray-700 active:bg-gray-600 transition shadow-lg flex items-center justify-center mx-auto"
            >
                {{ $number }}
            </button>
        @endforeach

        <!-- Bottom Row -->
        <button 
            wire:click="clear"
            class="h-20 w-20 rounded-2xl bg-gray-800 text-xl font-bold text-yellow-500 hover:bg-gray-700 active:bg-gray-600 transition shadow-lg flex items-center justify-center mx-auto"
        >
            C
        </button>

        <button 
            wire:click="append(0)"
            class="h-20 w-20 rounded-2xl bg-gray-800 text-3xl font-bold hover:bg-gray-700 active:bg-gray-600 transition shadow-lg flex items-center justify-center mx-auto"
        >
            0
        </button>

        <button 
            wire:click="backspace"
            class="h-20 w-20 rounded-2xl bg-gray-800 text-xl font-bold text-gray-400 hover:bg-gray-700 active:bg-gray-600 transition shadow-lg flex items-center justify-center mx-auto"
        >
            ⌫
        </button>
    </div>
</div>
