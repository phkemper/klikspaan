<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Moments extends Model
{
    /**
     * Log the sleep time.
     */
    public static function sleep()
    {
        $moment = new Moments;
        $moment->userid = Auth::id();
        $moment->type = 'sleep';
        $moment->save();
    }
    
    /**
     * Log the wakeup time.
     */
    public static function wakeup()
    {
        $moment = new Moments;
        $moment->userid = Auth::id();
        $moment->type = 'wakeup';
        $moment->save();
    }
    
    /**
     * Return last times buttons where pushed.
     */
    public static function getLastButtonTimes()
    {
        $userTimezone = auth()->user()->timezone ?? 'Europe/Amsterdam';
        $now = Carbon::now($userTimezone);
        $offsetInSeconds = $now->utcOffset() * 60;
        
        $lastSleep = Moments::where('type', '=', 'sleep')
        ->where('userid', '=', Auth::id())
        ->orderBy('created_at', 'desc')
        ->first();
        if ( $lastSleep)
        {
            $lastSleepTime = date('d-m H:i', strtotime($lastSleep->created_at) + $offsetInSeconds);
        }
        else
        {
            $lastSleepTime = '';
        }
        
        $lastWakeup = Moments::where('type', '=', 'wakeup')
        ->where('userid', '=', Auth::id())
        ->orderBy('created_at', 'desc')
        ->first();
        if ( $lastWakeup )
        {
            $lastWakeupTime = date('d-m H:i', strtotime($lastWakeup->created_at) + $offsetInSeconds);
        }
        else
        {
            $lastWakeupTime = '';
        }
        
        $lastState = Moments::orderBy('created_at', 'desc')->first();
        $lastState = $lastState ? $lastState->type : '';
        
        return (object)[
            'lastWakeup' => $lastWakeupTime,
            'lastSleep' => $lastSleepTime,
            'type' => $lastState,
        ];
    }
    
    /**
     * Create a PNG graph based on the number of days requested.
     */
    public static function createGraph($days)
    {
        // Get the data, ordered by create date/time.
        if ( $days > 0 )
        {
            $records = Moments::orderBy('created_at')
            ->where('userid', '=', Auth::id())
            ->where('created_at', '>=', now()->subDays($days))
            ->get();
        }
        else
        {
            $records = Moments::orderBy('created_at')
            ->where('userid', '=', Auth::id())
            ->get();
        }
        
        // Convert the create date/time to local timezone.
        $userTimezone = auth()->user()->timezone ?? 'Europe/Amsterdam';
        $now = Carbon::now($userTimezone);
        $offsetInSeconds = $now->utcOffset() * 60;
        
        foreach ( $records as $record )
        {
            $record->created_at = date('Y-m-d H:i:s', strtotime($record->created_at) + $offsetInSeconds);
        }
        
        // 1. Canvas en Afmetingen
        $width  = 1920;
        $height = 1080;
        $image  = imagecreatetruecolor($width, $height);
        
        // 2. Kleuren
        $bgColor         = imagecolorallocate($image, 245, 247, 250); // Neutrale achtergrond
        $mainBlockColor  = imagecolorallocate($image, 200, 205, 212); // Grijs (gemiddeld blok)
        $rangeBlockColor = imagecolorallocate($image, 110, 120, 135); // Donkergrijs (min-max bereik)
        $avgLineColor    = imagecolorallocate($image, 0, 0, 0);        // Zwart (gemiddelde lijn)
        $axisColor       = imagecolorallocate($image, 80, 80, 80);     // Askleur
        $textColor       = imagecolorallocate($image, 40, 40, 40);     // Tekstkleur
        
        imagefill($image, 0, 0, $bgColor);
        
        // 3. Layout instellingen
        $paddingLeft  = 120;
        $paddingRight = 120;
        $graphWidth   = $width - $paddingLeft - $paddingRight;
        
        $blockTop     = 350;
        $blockBottom  = 650;
        $axisY        = 670;
        
        // Helper: Zet seconden vanaf 12:00 's middags om naar een X-coördinaat op de as
        // 0 sec = 12:00 (start), 86400 sec = 12:00 volgende dag (eind)
        $secondsToX = function (float $seconds) use ($graphWidth, $paddingLeft): int {
            $fraction = max(0, min(1, $seconds / 86400));
            return (int)($paddingLeft + ($fraction * $graphWidth));
        };
        
        // Helper: Reken een Carbon/DateTime timestamp om naar seconden t.o.v. de meest nabije 12:00 's middags (vóór het event)
        $getNormalizedSeconds = function ($dateTimeStr): float {
            $dt = new \DateTime($dateTimeStr);
            $hour = (int)$dt->format('H');
            
            // Als de tijd vóór 12:00 is, hoort het bij het venster dat gisteren om 12:00 begon
            if ($hour < 12) {
                $base12 = (clone $dt)->modify('yesterday')->setTime(12, 0, 0);
            } else {
                $base12 = (clone $dt)->setTime(12, 0, 0);
            }
            
            return (float)($dt->getTimestamp() - $base12->getTimestamp());
        };
        
        // 4. Gegevens groeperen in Slaap- en Ontwaaktijden (in seconden vanaf 12:00)
        $sleepSeconds  = [];
        $wakeSeconds   = [];
        $sleepTime = [];
        
        // Paarsgewijs verwerken (sleep -> wakeup)
        $lastSleep = null;
        foreach ($records as $record) {
            if ($record->type === 'sleep') {
                $lastSleep = $record->created_at;
            } elseif ($record->type === 'wakeup' && $lastSleep !== null) {
                $sleepSec = $getNormalizedSeconds($lastSleep);
                $wakeSec  = $getNormalizedSeconds($record->created_at);
                
                // Borg dat ontwaken chronologisch na slapen ligt op de schaal
                if ($wakeSec <= $sleepSec) {
                    $wakeSec += 86400;
                }
                
                $sleepSeconds[] = $sleepSec;
                $wakeSeconds[]  = $wakeSec;
                $sleepTime[] = $wakeSec - $sleepSec;
                $lastSleep = null;
            }
        }
        
        // Als er voldoende data is, berekenen we de statistieken en tekenen we de grafiek
        if (!empty($sleepSeconds) && !empty($wakeSeconds)) {
            
            // Statistieken voor Slapen
            $minSleep = min($sleepSeconds);
            $maxSleep = max($sleepSeconds);
            $avgSleep = array_sum($sleepSeconds) / count($sleepSeconds);
            
            // Statistieken voor Ontwaken
            $minWake = min($wakeSeconds);
            $maxWake = max($wakeSeconds);
            $avgWake = array_sum($wakeSeconds) / count($wakeSeconds);
            
            $minTime = min($sleepTime);
            $maxTime = max($sleepTime);
            $avgTime = array_sum($sleepTime) / count($sleepTime);
            
            // Omzetten naar X-coördinaten
            $xMinSleep = $secondsToX($minSleep);
            $xMaxSleep = $secondsToX($maxSleep);
            $xAvgSleep = $secondsToX($avgSleep);
            
            $xMinWake  = $secondsToX($minWake);
            $xMaxWake  = $secondsToX($maxWake);
            $xAvgWake  = $secondsToX($avgWake);
            
            // --- TEKENEN VAN DE BLOKKEN ---
            
            // A. Donkergrijs blok aan de linkerkant (Vroegste sleep t/m Laatste sleep)
            imagefilledrectangle($image, $xMinSleep, $blockTop, $xMaxSleep, $blockBottom, $rangeBlockColor);
            
            // B. Grijs blok in het midden (Gemiddelde sleep t/m Gemiddelde wakeup)
            imagefilledrectangle($image, $xMaxSleep, $blockTop, $xMinWake, $blockBottom, $mainBlockColor);
            
            // C. Donkergrijs blok aan de rechterkant (Vroegste wakeup t/m Laatste wakeup)
            imagefilledrectangle($image, $xMinWake, $blockTop, $xMaxWake, $blockBottom, $rangeBlockColor);
            
            // D. Zwarte verticale lijn op het gemiddelde Slaap-uur
            imagesetthickness($image, 6);
            imageline($image, $xAvgSleep, $blockTop - 10, $xAvgSleep, $blockBottom + 10, $avgLineColor);
            
            // E. Zwarte verticale lijn op het gemiddelde Ontwaak-uur
            imageline($image, $xAvgWake, $blockTop - 10, $xAvgWake, $blockBottom + 10, $avgLineColor);
        }
        
        // 5. Horizontale as en uurmarkeringen (12:00 t/m 12:00)
        imagesetthickness($image, 3);
        imageline($image, $paddingLeft, $axisY, $width - $paddingRight, $axisY, $axisColor);
        
        $fontPath = public_path('fonts/ARIALUNI.TTF');
        
        $fontSize = 36; // Grootte in punten (pt)
        $angle    = 0;  // Rotatie in graden
        
        for ($i = 0; $i <= 24; $i++) {
            $sec = $i * 3600;
            $x   = $secondsToX($sec);
            
            // Tick mark
            imageline($image, $x, $axisY, $x, $axisY + 15, $axisColor);
            
            // Uuraanduiding (12:00, 13:00, ..., 00:00, ..., 12:00)
            $hourVal  = (12 + $i) % 24;
            $hourText = sprintf('%02d', $hourVal);
            
            // Teken tekst met exacte fontgrootte
            imagettftext($image, $fontSize, $angle, $x - 25, $axisY + $fontSize + 15, $textColor, $fontPath, $hourText);
        }
        
        $times = 'Min: ' . date('H:m', $minTime) .
        ' Gem: ' . date('H:m', $avgTime) .
        ' Max: ' . date('H:m', $maxTime);
        $bbox = imagettfbbox($fontSize, $angle, $fontPath, $times);
        $boxWidth = abs($bbox[2] - $bbox[0]);
        imagettftext($image, $fontSize, $angle, $width / 2 - $boxWidth / 2, 10 + $fontSize, $textColor, $fontPath, $times);
        
        // 6. Export the graph.
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        // Encode graph as Base64.
        $graphData = 'data:image/png;base64,' . base64_encode($imageData);
        
        return $graphData;
    }
    
}
