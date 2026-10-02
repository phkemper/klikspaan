<x-app-layout>

<div class="w-full max-w-xl mx-auto px-2 sm:px-4 py-4 sm:py-6 flex flex-col items-center justify-center min-h-screen">
    <!-- Header weggelaten -->

    <div class="w-full flex flex-col items-center gap-3 sm:gap-4">
        <div class="flex flex-row justify-center gap-3 sm:gap-4 w-full">
            <div class="w-1/4 sm:w-1/4 flex flex-col items-center">
            	<a href="{{ $days == 1 ? '#' : '/graph?days=1&id='.$id }}" 
                   @if($days==1) tabindex="-1" aria-disabled="true" @endif
                   class="w-full inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 
                          {{ $days==1 
                              ? 'bg-gray-200 text-gray-800 cursor-not-allowed pointer-events-none opacity-60' 
                              : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md cursor-pointer' }}">
                    1 dag
                </a>
            </div>
            <div class="w-1/4 sm:w-1/4 flex flex-col items-center">
            	<a href="{{ $days == 7 ? '#' : '/graph?days=7&id='.$id }}" 
                   @if($days==7) tabindex="-1" aria-disabled="true" @endif
                   class="w-full inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 
                          {{ $days==7 
                              ? 'bg-gray-200 text-gray-800 cursor-not-allowed pointer-events-none opacity-60' 
                              : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md cursor-pointer' }}">
                    7 dagen
                </a>
            </div>
            <div class="w-1/4 sm:w-1/4 flex flex-col items-center">
            	<a href="{{ $days == 28 ? '#' : '/graph?days=28&id='.$id }}" 
                   @if($days==28) tabindex="-1" aria-disabled="true" @endif
                   class="w-full inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 
                          {{ $days==28 
                              ? 'bg-gray-200 text-gray-800 cursor-not-allowed pointer-events-none opacity-60' 
                              : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md cursor-pointer' }}">
                    28 dagen
                </a>
            </div>
            <div class="w-1/4 sm:w-1/4 flex flex-col items-center">
            	<a href="{{ $days == 0 ? '#' : '/graph?days=0&id='.$id }}" 
                   @if($days==0) tabindex="-1" aria-disabled="true" @endif
                   class="w-full inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 
                          {{ $days==0 
                              ? 'bg-gray-200 text-gray-800 cursor-not-allowed pointer-events-none opacity-60' 
                              : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md cursor-pointer' }}">
                    alles
                </a>
            </div>
        </div>

        <div class="flex justify-center w-full">
            <img class="w-full h-full object-contain" src="{{ $graph }}"/>
        </div>
    </div>
</div>

</x-app-layout>