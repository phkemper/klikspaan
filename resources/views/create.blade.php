<x-app-layout>
    
    <!-- Voeg x-data toe aan de omhullende div -->
    <div class="max-w-xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700"
         x-data="{ selectedColor: '{{ old('color', '#4f46e5') }}' }">
        
        <!-- Titel -->
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">Nieuwe klikspaan aanmaken</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Voer een naam en kies een kleur voor de nieuwe klikspaan.</p>
        </div>

        <!-- Formulier -->
        <form action="{{ route('store') }}" method="POST">
            @csrf

            <!-- Naam Invoerveld -->
            <div class="mb-6">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Naam klikspaan
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name') }}"
                    placeholder="Bijv. Voordeur, Keuken, etc." 
                    required
                    class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kleurselector -->
            <div class="mb-6">
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Kies een kleur
                </label>
                
                <input type="hidden" name="color" :value="selectedColor">

                <div class="flex items-center gap-3 flex-wrap">
                    <!-- Preset Kleuren -->
                    <template x-for="hex in [
                        '#4f46e5', '#2563eb', '#0284c7', '#059669', 
                        '#16a34a', '#ca8a04', '#ea580c', '#dc2626', 
                        '#e11d48', '#9333ea', '#475569', '#111827'
                    ]" :key="hex">
                        <button 
                            type="button" 
                            @click="selectedColor = hex"
                            :style="`background-color: ${hex}`"
                            class="w-8 h-8 rounded-full relative flex items-center justify-center transition-all hover:scale-110 focus:outline-none ring-2 ring-offset-2 dark:ring-offset-gray-800"
                            :class="selectedColor === hex ? 'ring-gray-900 dark:ring-white scale-110' : 'ring-transparent opacity-80 hover:opacity-100'"
                        >
                            <svg x-show="selectedColor === hex" class="w-4 h-4 text-white drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </template>

                    <!-- Custom Color Input -->
                    <input 
                        type="color" 
                        x-model="selectedColor"
                        class="w-8 h-8 p-0.5 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 cursor-pointer shadow-sm"
                    >
                </div>

                <!-- Gekozen kleur preview -->
                <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span>Geselecteerde kleur:</span>
                    <span class="inline-block w-4 h-4 rounded-full border border-gray-300" :style="`background-color: ${selectedColor}`"></span>
                    <span class="font-mono uppercase" x-text="selectedColor"></span>
                </div>

                @error('color')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Knoppen (Annuleren & Opslaan) -->
            <div class="flex items-center justify-end gap-[4%] pt-4 border-t border-gray-100 dark:border-gray-700">
                <!-- Annuleren Knop -->
                <a 
                    href="{{ route('setting') }}" 
                    class="inline-block px-4 py-2 text-xs md:text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 active:bg-yellow-400 active:hover:bg-yellow-400 active:text-black active:hover:text-black rounded-lg shadow-sm transition-colors text-center"
                >
                    Annuleren
                </a>

                <!-- Opslaan Knop (Groen) -->
                <button 
                    type="submit" 
                    class="inline-block px-4 py-2 text-xs md:text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 active:bg-yellow-400 active:hover:bg-yellow-400 active:text-black active:hover:text-black rounded-lg shadow transition-colors text-center"
                >
                    Opslaan
                </button>
            </div>
        </form>

    </div>

</x-app-layout>