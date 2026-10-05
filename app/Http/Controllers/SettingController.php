<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Buttons;

class SettingController extends Controller
{
    /**
     * Show the settings.
     */
    public function index(Request $request)
    {
        // Get the buttons of the user.
        $buttons = Buttons::where('user_id', Auth::id())
        ->orderBy('name')
        ->get();
        
        return view('settings',['buttons' => $buttons,]);
    }
    
    /**
     * Start download for a button.
     */
    public function download(Request $request, $id)
    {
        $fileName = 'klikspaan-export-' . date('Y-m-d-H-i-s') . '-button-' . $id . '.csv';
        
        // Haal de data op
        $records = Buttons::getData($id);
        
        // Stream de CSV direct naar de browser
        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            
            // Optioneel: voeg een UTF-8 BOM toe voor correcte weergave in Excel
            fputs($handle, "\xEF\xBB\xBF");
            
            // CSV Kolomkopteksten
            fputcsv($handle, ['Start', 'Stop', 'Lengte',], ';');
            
            // Data rijen toevoegen
            foreach ($records as $record) {
                fputcsv($handle, [
                    $record['start'],
                    $record['stop'],
                    $record['lengte'],
                ], ';');
            }
            
            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
