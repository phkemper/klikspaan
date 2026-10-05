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
        $button = Buttons::where('user_id', Auth::id())
        ->where('id', '=', $id)->first();
        
        if ( !$button )
        {
            return redirect(route('setting'));
        }
        
        $fileName = 'klikspaan-export-' . date('Y-m-d-H-i-s') . '-' . str_replace(' ','-',$button->name) . '.csv';
        
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
    
    /**
     * Create a new item.
     */
    public function create(Request $request)
    {
        return view('create');
    }
    
    /**
     * Save a new button.
     */
    public function store(Request $request)
    {
        // 1. Valideer de invoer
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
        ]);
        
        // 2. Maak het nieuwe Button/Klikspaan record aan
        Buttons::create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#4f46e5',
            'user_id' => Auth::id(),
        ]);
        
        // 3. Stuur de gebruiker terug naar de settings pagina met een succesmelding
        return redirect()
        ->route('setting');
    }
    
    /**
     * Toon het bewerkformulier
     */
    public function update($id)
    {
        // Haal de knop op (of geef een 404 als hij niet bestaat)
        $button = Buttons::where('user_id', auth()->id())
        ->findOrFail($id);
        
        return view('edit', compact('button'));
    }
    
    /**
     * Sla de wijzigingen op
     * @param Request $request
     * @param integer $id
     */
    public function patch(Request $request)
    {
        // 1. Valideer de ingevoerde gegevens
        $validated = $request->validate([
            'id' => 'required|integer',
            'name'  => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
        ]);
        
        $button = Buttons::where('user_id', Auth::id())
        ->findOrFail($request->input('id'));
        
        
        // 2. Werk het record bij
        $button->update([
            'name'  => $validated['name'],
            'color' => $validated['color'],
        ]);
        
        // 3. Stuur terug naar de instellingenpagina
        return redirect()
        ->route('setting');
    }
}
