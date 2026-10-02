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
            $button->lastOn = '';
            $button->lastOff = '';
            // Get the current state of the buttons.
            $lastState = Moments::where('button_id', $button->id)
            ->orderBy('created_at', 'desc')
            ->first();
            $button->state = $lastState ? $lastState->state : false;
            // Get the colors for the button
            if ( $button->state )
            {
                $button->colors = [
                    'off' => Buttons::generateButtonGradients($button->color),
                    'on' => Buttons::generateButtonGradients('#808080'),
                ];
                $prevState = Moments::where('button_id', $button->id)
                ->limit(1,1)
                ->orderBy('created_at', 'desc')
                ->first();
                if ( $prevState )
                {
                    $button->lastOn = date('m-d H:i', strtotime($prevState->created_at));
                }
            }
            else
            {
                $button->colors = [
                    'off' => Buttons::generateButtonGradients('#808080'),
                    'on' => Buttons::generateButtonGradients($button->color),
                ];
                if ( $lastState )
                {
                    $button->lastOff = date('m-d H:i', strtotime($lastState));
                }
                $prevState = Moments::where('button_id', $button->id)
                ->limit(1,1)
                ->orderBy('created_at', 'desc')
                ->first();
                if ( $prevState )
                {
                    $button->lastOff = date('m-d H:i', strtotime($prevState->created_at));
                }
            }
        }
        return view('dashboard', [
            'buttons' => $buttons,
        ]);
    }
}
