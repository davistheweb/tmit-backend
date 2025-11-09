<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function getActiveSession()
    {
        $session = Session::where('is_active', true)->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active session found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $session
        ]);
    }

    
    public function getAllSessions()
    {
        $sessions = Session::orderBy('start_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }
}
