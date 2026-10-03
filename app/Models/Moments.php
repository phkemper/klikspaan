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
        if ( $days > 0 )
        {
            $records = Moments::orderBy('created_at')
            ->where('button_id', '=', $button_id)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();
        }
        else
        {
            $records = Moments::orderBy('created_at')
            ->where('button_id', '=', $button_id)
            ->get();
        }
        dump($records);die;
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
        $startSeconds  = [];
        $stopSeconds   = [];
        $intervalTime = [];
        
        // Paarsgewijs verwerken (start -> stop)
        $lastStart = null;
        foreach ($records as $record) {
            if ($record->state === 1) {
                $lastStart = $record->created_at;
            } elseif ($record->state === 0 && $lastStart !== null) {
                $startSec = $getNormalizedSeconds($lastStart);
                $stopSec  = $getNormalizedSeconds($record->created_at);
                
                // Borg dat stoppen chronologisch na starten ligt op de schaal
                if ($stopSec <= $startSec) {
                    $stopSec += 86400;
                }
                
                $startSeconds[] = $startSec;
                $stopSeconds[]  = $stopSec;
                $intervalTime[] = $stopSec - $startSec;
                $lastStart = null;
            }
        }
        
        // Als er voldoende data is, berekenen we de statistieken en tekenen we de grafiek
        if (!empty($startSeconds) && !empty($stopSeconds)) {
            
            // Statistieken voor starten
            $minStart = min($startSeconds);
            $maxStart = max($startSeconds);
            $avgStart = array_sum($startSeconds) / count($startSeconds);
            
            // Statistieken voor stoppen
            $minStop = min($stopSeconds);
            $maxStop = max($stopSeconds);
            $avgStop = array_sum($stopSeconds) / count($stopSeconds);
            
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
            
            // A. Donkergrijs blok aan de linkerkant (Vroegste start t/m Laatste start)
            imagefilledrectangle($image, $xMinStart, $blockTop, $xMaxStart, $blockBottom, $rangeBlockColor);
            
            // B. Grijs blok in het midden (Gemiddelde start t/m Gemiddelde stop)
            imagefilledrectangle($image, $xMaxStart, $blockTop, $xMinStop, $blockBottom, $mainBlockColor);
            
            // C. Donkergrijs blok aan de rechterkant (Vroegste stop t/m Laatste stop)
            imagefilledrectangle($image, $xMinStop, $blockTop, $xMaxStop, $blockBottom, $rangeBlockColor);
            
            // D. Zwarte verticale lijn op het gemiddelde start-uur
            imagesetthickness($image, 6);
            imageline($image, $xAvgStart, $blockTop - 10, $xAvgStart, $blockBottom + 10, $avgLineColor);
            
            // E. Zwarte verticale lijn op het gemiddelde stop-uur
            imageline($image, $xAvgStop, $blockTop - 10, $xAvgStop, $blockBottom + 10, $avgLineColor);
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
        
        $times = 'Min: ' . (empty($minTime) ? '-' : date('H:m', $minTime)) .
        ' Gem: ' . (empty($avgTime) ? '-' : date('H:m', $avgTime)) .
        ' Max: ' . (empty($maxTime) ? '-' : date('H:m', $maxTime));
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
