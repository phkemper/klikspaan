<x-app-layout>

    <div class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
        
        <!-- Bovenste knop: Maak nieuwe klikspaan -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">Instellingen - Klikspanen</h1>
            <a href="{{ route('create') }}" class="inline-block px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 active:bg-yellow-400 active:hover:bg-yellow-400 active:text-black active:hover:text-black rounded-lg shadow transition-colors text-center">
                Maak nieuwe klikspaan
            </a>
        </div>
    
        <!-- Tabel met bestaande klikspanen -->
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-left text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Naam</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-right">Acties</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
                    @forelse($buttons as $button)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <!-- Kolom 1: Naam van de klikspaan -->
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $button->name }}
                            </td>
                            
                            <!-- Kolom 2: Knoppen (Aanpassen & Verwijderen) -->
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <!-- Groene knop: Downloaden -->
                                <a href="{{ route('download', ['id' => $button->id]) }}" class="inline-block px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 active:bg-yellow-400 active:hover:bg-yellow-400 active:text-black active:hover:text-black rounded-lg shadow transition-colors text-center">
                                    Downloaden
                                </a>
    
                                <!-- Blauwe knop: Aanpassen -->
                                <a href="{{ route('update', ['id' => $button->id]) }}" class="inline-block px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 active:bg-yellow-400 active:hover:bg-yellow-400 active:text-black active:hover:text-black rounded-lg shadow transition-colors text-center">
                                    Aanpassen
                                </a>
    
                                <!-- Rode knop: Verwijderen -->
                                <a href="{{ route('delete', ['id' => $button->id]) }}" class="inline-block px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 active:bg-yellow-400 active:hover:bg-yellow-400 active:text-black active:hover:text-black rounded-lg shadow transition-colors text-center">
                                    Verwijderen
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Geen klikspanen gevonden. Klik op de knop hierboven om er een aan te maken.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    
</div>

</x-app-layout>
