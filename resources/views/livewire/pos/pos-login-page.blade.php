<div class="min-h-screen flex flex-col bg-gray-900 text-white font-sans">
    <!-- Header -->
    <div class="p-8 text-center">
        <h1 class="text-4xl font-bold text-yellow-500 mb-2">Ritaj Smart Resto</h1>
        <p class="text-gray-400">Qui êtes-vous ?</p>
    </div>

    <!-- Users Grid -->
    <div class="flex-1 overflow-y-auto p-4 flex justify-center items-start">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 max-w-6xl w-full">
            @foreach($this->users as $user)
                <button 
                    wire:click="selectUser({{ $user->id }})"
                    class="bg-gray-800 rounded-2xl p-6 flex flex-col items-center justify-center gap-4 hover:bg-gray-700 hover:scale-105 transition-all shadow-lg group border-2 border-transparent hover:border-yellow-500"
                >
                    <div class="w-24 h-24 rounded-full overflow-hidden bg-gray-600 border-4 border-gray-700 group-hover:border-yellow-500 transition-colors">
                        @if($user->avatar_url)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar_url) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-2xl font-bold bg-gray-700 text-gray-400">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-white group-hover:text-yellow-400">{{ $user->name }}</h3>
                        <span class="text-xs uppercase font-bold text-gray-500 bg-gray-900 px-2 py-1 rounded mt-1 inline-block border border-gray-700">{{ $user->role }}</span>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    <!-- PIN Modal Overlay -->
    @if($showPinPad && $selectedUser)
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             x-data
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
        >
            <div class="bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl border border-gray-700 relative"
                 @click.away="$wire.closePinPad()">
                
                <button wire:click="closePinPad" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <div class="text-center mb-8">
                    <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-600 border-2 border-yellow-500 mx-auto mb-3">
                        @if($selectedUser->avatar_url)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($selectedUser->avatar_url) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xl font-bold bg-gray-700 text-gray-400">
                                {{ substr($selectedUser->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <h2 class="text-2xl font-bold text-white">Bonjour, {{ $selectedUser->name }}</h2>
                    <p class="text-gray-400 text-sm">Entrez votre code PIN</p>
                </div>

                <!-- PIN Dots -->
                <div class="mb-8 flex justify-center gap-4">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="w-4 h-4 rounded-full border-2 transition-all duration-150 {{ strlen($pin) > $i ? 'bg-yellow-500 border-yellow-500 scale-110' : 'border-gray-600 bg-gray-900' }}"></div>
                    @endfor
                </div>

                @if ($error)
                    <div class="mb-6 text-center text-red-500 font-bold animate-pulse">
                        {{ $error }}
                    </div>
                @endif

                <!-- Keypad -->
                <div class="grid grid-cols-3 gap-4">
                    @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $number)
                        <button 
                            wire:click="append({{ $number }})"
                            class="h-16 rounded-xl bg-gray-700 text-2xl font-bold hover:bg-gray-600 active:bg-gray-500 transition shadow-lg text-white"
                        >
                            {{ $number }}
                        </button>
                    @endforeach

                    <button 
                        wire:click="clear"
                        class="h-16 rounded-xl bg-gray-700 text-lg font-bold text-yellow-500 hover:bg-gray-600 active:bg-gray-500 transition shadow-lg"
                    >
                        C
                    </button>

                    <button 
                        wire:click="append(0)"
                        class="h-16 rounded-xl bg-gray-700 text-2xl font-bold hover:bg-gray-600 active:bg-gray-500 transition shadow-lg text-white"
                    >
                        0
                    </button>

                    <button 
                        wire:click="backspace"
                        class="h-16 rounded-xl bg-gray-700 text-lg font-bold text-gray-400 hover:bg-gray-600 active:bg-gray-500 transition shadow-lg flex items-center justify-center"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
