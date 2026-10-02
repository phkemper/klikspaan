<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buttons;
use App\Models\Moments;

class GraphController extends Controller
{
    /**
     * Show a graph.
     * Request GET parameters:
     * @param int id - Button ID.
     * @param int days - days for the graph.
     */
    public function index(Request $request)
    {
        if ( !Buttons::belongsToUser($request->input('id',0)) )
        {
            redirect(route('dashboard'));
        }
        
        $allowedDays = [0, 1, 7, 28];
        $days = $request->input('days', 1);
        if ( !in_array($days, $allowedDays) ) $days == 1;
        
        $graph = Moments::createGraph($request->input('id'), $days);
        
        if ( !$graph )
        {
            redirect(route('dashboard'));
        }
        
        return view('graph', ['days' => $days, 'graph' => $graph,]);
    }
}
