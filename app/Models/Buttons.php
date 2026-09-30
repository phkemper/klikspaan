<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buttons extends Model
{
    /**
     * Convert hex to rgb.
     */
    public static function hexToRgb(string $hex): array
    {
        // Verwijder eventuele '#' aan het begin
        $hex = ltrim($hex, '#');
        
        // Ondersteun zowel 3-cijferige (bijv. "F00") als 6-cijferige (bijv. "FF0000") HEX-codes
        if (strlen($hex) === 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        
        return ['r' => $r, 'g' => $g, 'b' => $b];
    }
    
    /**
     * Helper functies voor de verloopkleur van de knop
     */
    static public function adjustColorBrightness(int $r, int $g, int $b, float $factor): string
    {
        $newR = (int) min(255, max(0, round($r * $factor)));
        $newG = (int) min(255, max(0, round($g * $factor)));
        $newB = (int) min(255, max(0, round($b * $factor)));
        
        return sprintf('#%02X%02X%02X', $newR, $newG, $newB);
    }
    
    /**
     * Bereken verloopkleuren voor de knop.
     */
    static public function generateButtonGradients($hexColor): array
    {
        $rgb = self::hexToRgb($hexColor);
        $r = $rgb['r'];
        $g = $rgb['g'];
        $b = $rgb['b'];
        
        return [
            'stop0'   => self::adjustColorBrightness($r, $g, $b, 1.25),  // Highlight (Lichter/Glans)
            'stop40'  => self::adjustColorBrightness($r, $g, $b, 1.00),  // Basis RGB-kleur
            'stop75'  => self::adjustColorBrightness($r, $g, $b, 0.80),  // Lichte schaduw
            'stop100' => self::adjustColorBrightness($r, $g, $b, 0.45),  // Diepe rand-schaduw
        ];
    }
}
