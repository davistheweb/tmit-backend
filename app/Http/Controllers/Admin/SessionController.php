<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function index()
    {
        $sessions = Session::orderBy('start_date', 'desc')->paginate(20);
        return response()->json($sessions);
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'is_active' => 'boolean'
    ]);

    $session = DB::transaction(function () use ($validated) {
        // Check if a session with this name already exists
        $existing = Session::where('name', $validated['name'])->first();

        if ($validated['is_active'] ?? false) {
            // Deactivate other active sessions
            Session::where('is_active', true)->update(['is_active' => false]);
        }

        if ($existing) {
            // Update the existing one instead of creating a new one
            $existing->update($validated);
            return $existing;
        }

        // Otherwise, create a new session
        return Session::create($validated);
    });

    return response()->json([
        'message' => $session->wasRecentlyCreated
            ? 'Session created successfully'
            : 'Existing session updated successfully',
        'data' => $session
    ], $session->wasRecentlyCreated ? 201 : 200);
}

    public function show(Session $session)
    {
        return response()->json($session);
    }

    public function update(Request $request, Session $session)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'is_active' => 'boolean'
        ]);

        // Use transaction to prevent race conditions
        DB::transaction(function () use ($validated, $session) {
            // Deactivate other sessions if this one is being activated
            if (($validated['is_active'] ?? false) && !$session->is_active) {
                Session::where('is_active', true)
                    ->where('id', '!=', $session->id)
                    ->update(['is_active' => false]);
            }

            $session->update($validated);
        });

        // Refresh to get updated data
        $session->refresh();

        return response()->json([
            'message' => 'Session updated successfully',
            'data' => $session
        ]);
    }

    public function destroy(Session $session)
    {
        // Prevent deletion of active session (optional safeguard)
        if ($session->is_active) {
            return response()->json([
                'message' => 'Cannot delete an active session. Please deactivate it first.'
            ], 422);
        }

        $session->delete();
        return response()->json(['message' => 'Session deleted successfully']);
    }

    public function getActive()
    {
        $session = Session::where('is_active', true)->first();
        
        if (!$session) {
            return response()->json([
                'message' => 'No active session found'
            ], 404);
        }

        return response()->json($session);
    }

    /**
     * Toggle a session's active status
     * Makes it more explicit for frontend usage
     */
   public function toggleActive(Session $session)
{
    $message = '';

    DB::transaction(function () use ($session, &$message) {
        if (!$session->is_active) {
            // Activating this session - deactivate others
            Session::where('is_active', true)->update(['is_active' => false]);
            $session->update(['is_active' => true]);
            $message = 'Session activated successfully';
        } else {
            // Deactivating this session
            $session->update(['is_active' => false]);
            $message = 'Session deactivated successfully';
        }
    });

    $session->refresh();

    return response()->json([
        'message' => $message,
        'data' => $session
    ]);
}

}