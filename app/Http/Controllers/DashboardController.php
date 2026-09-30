<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Buttons;

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
        return view('dashboard', [
            'buttons' => $buttons,
        ]);
    }
}
