<x-app-layout>

    <div class="max-w-md mx-auto p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
        
        <!-- Waarschuwings-icoon & Titel -->
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">Klikspaan verwijderen</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Weet je zeker dat je de klikspaan <strong class="text-gray-900 dark:text-white font-semibold">"{{ $button->name }}"</strong> wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.
            </p>
        </div>
    
        <!-- Formulier -->
        <form action="{{ route('remove') }}" method="POST">
            @csrf
    
            <!-- Verborgen ID veld -->
            <input type="hidden" name="id" value="{{ $button->id }}">
    
            <!-- Knoppen (Annuleren & Verwijderen) -->
            <div class="flex items-center justify-end gap-[4%] pt-4 border-t border-gray-100 dark:border-gray-700">
                <!-- Annuleren Knop -->
                <a 
                    href="{{ route('setting') }}" 
                    class="inline-block w-1/2 px-4 py-2 text-xs md:text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 active:bg-yellow-400 active:hover:bg-yellow-400 active:text-black active:hover:text-black rounded-lg shadow-sm transition-colors text-center"
                >
                    Annuleren
                </a>
    
                <!-- Verwijderen Knop (Rood) -->
                <button 
                    type="submit" 
                    class="inline-block w-1/2 px-4 py-2 text-xs md:text-sm font-medium text-white bg-red-600 hover:bg-red-700 active:bg-yellow-400 active:hover:bg-yellow-400 active:text-black active:hover:text-black rounded-lg shadow transition-colors text-center"
                >
                    Verwijderen
                </button>
            </div>
        </form>
    
    </div>

</x-app-layout>
