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
        
        foreach ( $buttons as $button )
        {
            $button->getDetails();
        }
        
        return view('dashboard', [
            'buttons' => $buttons,
        ]);
    }
}
