<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Buttons;
use App\Models\Moments;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with all buttons.
     */
    public function index(Request $request)
    {
        // Get the buttons of the user.
        $buttons = Buttons::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
        
        // If there are no buttons, create the first one.
        if ( !$buttons || !count($buttons) )
        {
            $button = new Buttons;
            $button->name = 'Nachtrust';
            $button->user_id = Auth::id();
            $button->save();
            $buttons[] = $button;
        }
        
        foreach ( $buttons as $button )
        {
            $button->getDetails();
        }
        
        return view('dashboard', [
            'buttons' => $buttons,
        ]);
    }
    
    /**
     * Register the start time.
     */
    public function start($button_id)
    {
        if ( !Buttons::belongsToUser($button_id) )
        {
            redirect(route('dashboard'));
        }
        
        $moment = new Moments;
        $moment->button_id = $button_id;
        $moment->state = true;
        $moment->save();
        
        return redirect(route('dashboard'));
    }
    
    /**
     * Register the stop time.
     */
    public function stop($button_id)
    {
        if ( !Buttons::belongsToUser($button_id) )
        {
            redirect(route('dashboard'));
        }
        
        $moment = new Moments;
        $moment->button_id = $button_id;
        $moment->state = false;
        $moment->save();
        
        return redirect(route('dashboard'));
    }
    
}
