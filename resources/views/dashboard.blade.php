<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @foreach ( $buttons as $button )
                	<div class="p-6 text-gray-900 dark:text-gray-100">
                		<h2 class="text-2xl font-bold mb-4">{{ $button->name }}</h2>
                	</div>
                	<div class="flex flex-col sm:flex-row gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300" width="300" height="300">
                          <defs>
                            <!-- Verloop voor de zwarte plastic buitenring / montagering -->
                            <linearGradient id="outerBezel" x1="0%" y1="0%" x2="100%" y2="100%">
                              <stop offset="0%" stop-color="#333333" />
                              <stop offset="50%" stop-color="#1A1A1A" />
                              <stop offset="100%" stop-color="#0A0A0A" />
                            </linearGradient>
                        
                            <!-- Verloop voor de opstaande binnenste rand -->
                            <linearGradient id="innerRim" x1="0%" y1="0%" x2="0%" y2="100%">
                              <stop offset="0%" stop-color="#4F4F4F" />
                              <stop offset="100%" stop-color="#121212" />
                            </linearGradient>
                        
                            <!-- Radiaal verloop voor de rode plastic bolling (Big Dome) -->
                            <radialGradient id="redDome" cx="35%" cy="35%" r="65%">
                              <stop offset="0%" stop-color="{{ $button->colors['on']['stop0'] }}" />
                              <stop offset="40%" stop-color="{{ $button->colors['on']['stop40'] }}" />
                              <stop offset="75%" stop-color="{{ $button->colors['on']['stop75'] }}" />
                              <stop offset="100%" stop-color="{{ $button->colors['on']['stop100'] }}" />
                            </radialGradient>
                        
                            <!-- Subtiele schaduw onder de buitenste rand -->
                            <filter id="dropShadow" x="-10%" y="-10%" width="120%" height="120%">
                              <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="#000000" flood-opacity="0.4" />
                            </filter>
                        
                            <!-- Diepteschaduw langs de binnenrand van de knop -->
                            <filter id="domeInnerShadow" x="-20%" y="-20%" width="140%" height="140%">
                              <feGaussianBlur in="SourceAlpha" stdDeviation="5" result="blur" />
                              <feOffset dx="0" dy="4" />
                              <feComposite in2="SourceAlpha" operator="arithmetic" k2="-1" k3="1" result="shadowDiff" />
                              <feFlood flood-color="#000000" flood-opacity="0.7" />
                              <feComposite in2="shadowDiff" operator="in" />
                              <feComposite in2="SourceGraphic" operator="over" />
                            </filter>
                        
                            <!-- Subtiele schaduw onder de tekst -->
                            <filter id="textShadow" x="-10%" y="-10%" width="120%" height="120%">
                              <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#000000" flood-opacity="0.6" />
                            </filter>
                          </defs>
                        
                          <!-- 1. Buitenste zwarte rand (Housing) met schaduw -->
                          <circle cx="150" cy="150" r="135" fill="url(#outerBezel)" filter="url(#dropShadow)" />
                        
                          <!-- 2. Groef / scheidingslijn tussen buitenring en knopbehuizing -->
                          <circle cx="150" cy="150" r="122" fill="#0D0D0D" />
                          <circle cx="150" cy="150" r="120" fill="url(#innerRim)" />
                        
                          <!-- 3. Zwarte binnenste uitsparing waar de rode knop in valt -->
                          <circle cx="150" cy="150" r="105" fill="#050505" />
                        
                          <!-- 4. De rode dome knop -->
                          <circle cx="150" cy="150" r="100" fill="url(#redDome)" filter="url(#domeInnerShadow)" />
                        
                          <!-- 5. Glans / lichtreflectie van de plastic kap (bovenkant) -->
                          <path d="M 65 150 A 85 85 0 0 1 235 150 A 82 82 0 0 0 65 150 Z" fill="#FFFFFF" opacity="0.22" />
                        
                          <!-- 6. Tekst 'start' in het midden -->
                          <text 
                            x="150" 
                            y="150" 
                            fill="#FFFFFF" 
                            font-family="Arial, Helvetica, sans-serif" 
                            font-size="36" 
                            font-weight="900" 
                            letter-spacing="3"
                            text-anchor="middle" 
                            dominant-baseline="central"
                            filter="url(#textShadow)">
                            START
                          </text>
                        </svg>
                        
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300" width="300" height="300">
                          <defs>
                            <!-- Verloop voor de zwarte plastic buitenring / montagering -->
                            <linearGradient id="outerBezelOff" x1="0%" y1="0%" x2="100%" y2="100%">
                              <stop offset="0%" stop-color="#333333" />
                              <stop offset="50%" stop-color="#1A1A1A" />
                              <stop offset="100%" stop-color="#0A0A0A" />
                            </linearGradient>
                        
                            <!-- Verloop voor de opstaande binnenste rand -->
                            <linearGradient id="innerRimOff" x1="0%" y1="0%" x2="0%" y2="100%">
                              <stop offset="0%" stop-color="#4F4F4F" />
                              <stop offset="100%" stop-color="#121212" />
                            </linearGradient>
                        
                            <!-- Radiaal verloop voor de rode plastic bolling (Big Dome) -->
                            <radialGradient id="redDomeOff" cx="35%" cy="35%" r="65%">
                              <stop offset="0%" stop-color="{{ $button->colors['off']['stop0'] }}" />
                              <stop offset="40%" stop-color="{{ $button->colors['off']['stop40'] }}" />
                              <stop offset="75%" stop-color="{{ $button->colors['off']['stop75'] }}" />
                              <stop offset="100%" stop-color="{{ $button->colors['off']['stop100'] }}" />
                            </radialGradient>
                        
                            <!-- Subtiele schaduw onder de buitenste rand -->
                            <filter id="dropShadowOff" x="-10%" y="-10%" width="120%" height="120%">
                              <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="#000000" flood-opacity="0.4" />
                            </filter>
                        
                            <!-- Diepteschaduw langs de binnenrand van de knop -->
                            <filter id="domeInnerShadowOff" x="-20%" y="-20%" width="140%" height="140%">
                              <feGaussianBlur in="SourceAlpha" stdDeviation="5" result="blur" />
                              <feOffset dx="0" dy="4" />
                              <feComposite in2="SourceAlpha" operator="arithmetic" k2="-1" k3="1" result="shadowDiff" />
                              <feFlood flood-color="#000000" flood-opacity="0.7" />
                              <feComposite in2="shadowDiff" operator="in" />
                              <feComposite in2="SourceGraphic" operator="over" />
                            </filter>
                        
                            <!-- Subtiele schaduw onder de tekst -->
                            <filter id="textShadowOff" x="-10%" y="-10%" width="120%" height="120%">
                              <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#000000" flood-opacity="0.6" />
                            </filter>
                          </defs>
                        
                          <!-- 1. Buitenste zwarte rand (Housing) met schaduw -->
                          <circle cx="150" cy="150" r="135" fill="url(#outerBezelOff)" filter="url(#dropShadowOff)" />
                        
                          <!-- 2. Groef / scheidingslijn tussen buitenring en knopbehuizing -->
                          <circle cx="150" cy="150" r="122" fill="#0D0D0D" />
                          <circle cx="150" cy="150" r="120" fill="url(#innerRimOff)" />
                        
                          <!-- 3. Zwarte binnenste uitsparing waar de rode knop in valt -->
                          <circle cx="150" cy="150" r="105" fill="#050505" />
                        
                          <!-- 4. De rode dome knop -->
                          <circle cx="150" cy="150" r="100" fill="url(#redDomeOff)" filter="url(#domeInnerShadowOff)" />
                        
                          <!-- 5. Glans / lichtreflectie van de plastic kap (bovenkant) -->
                          <path d="M 65 150 A 85 85 0 0 1 235 150 A 82 82 0 0 0 65 150 Z" fill="#FFFFFF" opacity="0.22" />
                        
                          <!-- 6. Tekst 'start' in het midden -->
                          <text 
                            x="150" 
                            y="150" 
                            fill="#FFFFFF" 
                            font-family="Arial, Helvetica, sans-serif" 
                            font-size="36" 
                            font-weight="900" 
                            letter-spacing="3"
                            text-anchor="middle" 
                            dominant-baseline="central"
                            filter="url(#textShadowOff)">
                            STOP
                          </text>
                        </svg>

                        
                    </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
