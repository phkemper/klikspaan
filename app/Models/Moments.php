<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Moments extends Model
{
    /**
     * Log the start time.
     * @param int $id - Button ID.
     */
    public static function start($id)
    {
        $moment = new Moments;
        $moment->button_id = $id;
        $moment->state = 1;
        $moment->save();
    }
    
    /**
     * Log the stop time.
     * @param int $id - Button ID.
     */
    public static function wakeup($id)
    {
        $moment = new Moments;
        $moment->button_id = $id;
        $moment->state = 0;
        $moment->save();
    }
    
    /**
     * Return last times buttons where pushed.
     * @param int $id - Button ID.
     */
    public static function getLastButtonTimes($id)
    {
        $userTimezone = auth()->user()->timezone ?? 'Europe/Amsterdam';
        $now = Carbon::now($userTimezone);
        $offsetInSeconds = $now->utcOffset() * 60;
        
        $lastStart = Moments::where('state', '=', 1)
        ->where('button_id', '=', $id)
        ->orderBy('created_at', 'desc')
        ->first();
        if ( $lastStart)
        {
            $lastStartTime = date('d-m H:i', strtotime($lastStart->created_at) + $offsetInSeconds);
        }
        else
        {
            $lastStartTime = '';
        }
        
        $lastStop = Moments::where('state', '=', 0)
        ->where('button_id', '=', $id)
        ->orderBy('created_at', 'desc')
        ->first();
        if ( $lastStop )
        {
            $lastStopTime = date('d-m H:i', strtotime($lastStop->created_at) + $offsetInSeconds);
        }
        else
        {
            $lastStopTime = '';
        }
        
        $lastState = Moments::orderBy('created_at', 'desc')->first();
        $lastState = $lastState ? $lastState->state : false;
        
        return (object)[
            'lastWakeup' => $lastWakeupTime,
            'lastSleep' => $lastSleepTime,
            'state' => $lastState,
        ];
    }
    
    /**
     * Create a PNG graph based on the number of days requested.
     */
    public static function createGraph($button_id, $days)
    {
        // Get the data, ordered by create date/time.
        if ($days > 0) {
            $records = Moments::orderBy('created_at')
            ->where('button_id', '=', $button_id)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();
        } else {
            $records = Moments::orderBy('created_at')
            ->where('button_id', '=', $button_id)
            ->get();
        }
        
        // Convert the create date/time to local timezone.
        $userTimezone = auth()->user()->timezone ?? 'Europe/Amsterdam';
        $now = Carbon::now($userTimezone);
        $offsetInSeconds = $now->utcOffset() * 60;
        
        foreach ($records as $record) {
            $record->created_at = date('Y-m-d H:i:s', strtotime($record->created_at) + $offsetInSeconds);
        }
        
        // Check if the last time is a stop time. If not, remove the last one.
        $lastIndex = count($records) - 1;
        if ( $records[$lastIndex]->state == 1 )
        {
            unset($records[$lastIndex]);
        }
         
        // 1. Canvas en Afmetingen
        $width  = 1920;
        $height = 1080;
        $image  = imagecreatetruecolor($width, $height);
        
        // 2. Kleuren
        $bgColor         = imagecolorallocate($image, 245, 247, 250);
        $mainBlockColor  = imagecolorallocate($image, 200, 205, 212);
        $rangeBlockColor = imagecolorallocatealpha($image, 110, 120, 135, 64);
        $avgLineColor    = imagecolorallocate($image, 0, 0, 0);
        $axisColor       = imagecolorallocate($image, 80, 80, 80);
        $textColor       = imagecolorallocate($image, 40, 40, 40);
        
        imagefill($image, 0, 0, $bgColor);
        
        // 3. Layout instellingen
        $paddingLeft  = 120;
        $paddingRight = 120;
        $graphWidth   = $width - $paddingLeft - $paddingRight;
        
        $blockTop     = 350;
        $blockBottom  = 650;
        $axisY        = 670;
        
        // Tijdelijke omrekening van timestamp naar seconden sinds middernacht van die dag
        $getAbsoluteSeconds = function ($dateTimeStr): float {
            $dt = new \DateTime($dateTimeStr);
            $hours   = (int)$dt->format('H');
            $minutes = (int)$dt->format('i');
            $seconds = (int)$dt->format('s');
            return (float)($hours * 3600 + $minutes * 60 + $seconds);
        };
        
        // 4. Gegevens verwerken tot start- en stoptijden (relatief t.o.v. eerste start)
        $startSeconds = [];
        $stopSeconds  = [];
        $intervalTime = [];
        
        $lastStart = null;
        foreach ($records as $record) {
            if ($record->state === 1) {
                $lastStart = $record->created_at;
            } elseif ($record->state === 0 && $lastStart !== null) {
                $startSec = $getAbsoluteSeconds($lastStart);
                $stopSec  = $getAbsoluteSeconds($record->created_at);
                
                // Als de stoptijd op de klok 'vroeger' is dan de starttijd, ging hij over middernacht heen
                if ($stopSec <= $startSec) {
                    $stopSec += 86400;
                }
                
                $startSeconds[] = $startSec;
                $stopSeconds[]  = $stopSec;
                $intervalTime[] = $stopSec - $startSec;
                $lastStart = null;
            }
        }
        
        // Standaard startuur als er geen data is (bijv. 12:00)
        $timelineStartHour = 12;
        
        if (!empty($startSeconds) && !empty($stopSeconds)) {
            
            $minStart = min($startSeconds);
            $maxStop  = max($stopSeconds);
            
            // Bepaal het midden van alle getekende data
            $centerDataSeconds = ($minStart + $maxStop) / 2;
            
            // De tijdlijn moet gecentreerd zijn: starttijd is het midden min 12 uur (43200 seconden)
            $idealStartSeconds = $centerDataSeconds - 43200;
            
            // Rond af naar het dichtstbijzijnde hele uur voor een schone as-indeling
            $timelineStartHour = (int)round($idealStartSeconds / 3600);
            
            // Zorg dat het startuur altijd binnen 0-23 valt
            $timelineStartHour = ($timelineStartHour % 24 + 24) % 24;
        }

        // Tijdlijn start in absolute seconden van de dag
        $timelineStartSeconds = $timelineStartHour * 3600;
        
        // Helper: Zet seconden (t.o.v. de dynamische starttijd) om naar X-coördinaat
        $secondsToX = function (float $seconds) use ($graphWidth, $paddingLeft): int {
            $fraction = max(0, min(1, $seconds / 86400));
            return (int)($paddingLeft + ($fraction * $graphWidth));
        };
        
        // Helper: Normaliseer absolute seconden van de dag t.o.v. het gekozen startuur
        $normalizeToTimeline = function (float $absoluteSeconds) use ($timelineStartSeconds): float {
            $rel = $absoluteSeconds - $timelineStartSeconds;
            if ($rel < 0) {
                $rel += 86400;
            }
            return $rel;
        };
        
        if (!empty($startSeconds) && !empty($stopSeconds)) {
            // Normaliseer alle tijden naar de nieuwe gecentreerde tijdlijn
            $normStarts = array_map($normalizeToTimeline, $startSeconds);
            $normStops  = array_map($normalizeToTimeline, $stopSeconds);
            
            // Statistieken voor starten
            $minStart = min($normStarts);
            $maxStart = max($normStarts);
            $avgStart = array_sum($normStarts) / count($normStarts);

            // Statistieken voor stoppen
            $minStop = min($normStops);
            $maxStop = max($normStops);
            $avgStop = array_sum($normStops) / count($normStops);

            $minTime = min($intervalTime);
            $maxTime = max($intervalTime);
            $avgTime = array_sum($intervalTime) / count($intervalTime);
            
            // Omzetten naar X-coördinaten
            $xMinStart = $secondsToX($minStart);
            $xMaxStart = $secondsToX($maxStart);
            $xAvgStart = $secondsToX($avgStart);
            
            $xMinStop  = $secondsToX($minStop);
            $xMaxStop  = $secondsToX($maxStop);
            $xAvgStop  = $secondsToX($avgStop);
            
            // --- TEKENEN VAN DE BLOKKEN ---
            
            // A. Donkergrijs blok links (Vroegste start t/m Laatste start)
            imagefilledrectangle($image, $xMinStart, $blockTop, $xMaxStart, $blockBottom, $rangeBlockColor);
            
            // B. Grijs blok midden (Gemiddelde start t/m Gemiddelde stop)
            imagefilledrectangle($image, $xMaxStart, $blockTop, $xMinStop, $blockBottom, $mainBlockColor);
            
            // C. Donkergrijs blok rechts (Vroegste stop t/m Laatste stop)
            imagefilledrectangle($image, $xMinStop, $blockTop, $xMaxStop, $blockBottom, $rangeBlockColor);
            
            // D. Zwarte verticale lijn op gemiddelde start
            imagesetthickness($image, 6);
            imageline($image, $xAvgStart, $blockTop - 10, $xAvgStart, $blockBottom + 10, $avgLineColor);
            
            // E. Zwarte verticale lijn op gemiddelde stop
            imageline($image, $xAvgStop, $blockTop - 10, $xAvgStop, $blockBottom + 10, $avgLineColor);
        }
        
        // 5. Horizontale as en uurmarkeringen (dynamisch vanaf $timelineStartHour)
        imagesetthickness($image, 3);
        imageline($image, $paddingLeft, $axisY, $width - $paddingRight, $axisY, $axisColor);
        
        $fontPath = public_path('fonts/ARIALUNI.TTF');
        $fontSize = 36;
        $angle    = 0;
        
        for ($i = 0; $i <= 24; $i++) {
            $sec = $i * 3600;
            $x   = $secondsToX($sec);
            
            // Tick mark
            imageline($image, $x, $axisY, $x, $axisY + 15, $axisColor);
            
            // Bepaal de uurwaarde op de as
            $hourVal  = ($timelineStartHour + $i) % 24;
            $hourText = sprintf('%02d', $hourVal);
            
            $bbox = imagettfbbox($fontSize, $angle, $fontPath, $hourText);
            
            // Teken uuraanduiding
            imagettftext($image, $fontSize, $angle, $x - (abs($bbox[2] - $bbox[0]))/2, $axisY + $fontSize + 15, $textColor, $fontPath, $hourText);
        }
        
        // Formatteer seconden naar uren:minuten (gmdate is hier geschikt voor)
        $formatDuration = function ($seconds) {
            if (empty($seconds)) return '-';
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return sprintf('%02d:%02d', $hours, $minutes);
        };
        
        $times = 'Min: ' . $formatDuration($minTime ?? null) .
        ' Gem: ' . $formatDuration($avgTime ?? null) .
        ' Max: ' . $formatDuration($maxTime ?? null);
        
        $bbox = imagettfbbox($fontSize, $angle, $fontPath, $times);
        $boxWidth = abs($bbox[2] - $bbox[0]);
        imagettftext($image, $fontSize, $angle, $width / 2 - $boxWidth / 2, 10 + $fontSize, $textColor, $fontPath, $times);
        
        // 6. Exporteer de afbeelding
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
}
