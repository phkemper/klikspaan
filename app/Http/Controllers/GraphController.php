<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Moments;

class GraphController extends Controller
{
    /**
     * Show a graph.
     */
    public function index(Request $request)
    {
        $allowedDays = [0, 1, 7, 28];
        $days = $request->input('days', 1);
        if ( !in_array($days, $allowedDays) ) $days == 1;
        
        $graph = Moments::createGraph($days);
        
        if ( !$graph )
        {
            redirect(route('dashboard'));
        }
        
        return view('graph', ['days' => $days, 'graph' => $graph,]);
    }
}
