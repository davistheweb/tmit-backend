<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;

class PasswordResetController extends Controller
{
     public function requestReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:students,email',
        ]);

        $student = Student::where('email', $request->email)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        // Generate token
        $token = Str::random(60);

        // Delete old tokens
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Create new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Send email (implement your email service)
        // Mail::to($student->email)->send(new PasswordResetMail($token));

        // Generate reset link
$resetUrl = url("/password/reset?email={$request->email}&token={$token}");

// Send reset email
Mail::to($student->email)->send(new PasswordResetMail($resetUrl));


        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent to your email',
            'token' => $token // Remove in production, only for testing
        ]);
    }

    // Verify reset token
    public function verifyToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 400);
        }

        // Check if token is expired (24 hours)
        $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
        if ($createdAt->addHours(24)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired'
            ], 400);
        }

        // Verify token
        if (!Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token is valid'
        ]);
    }

    // Reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:students,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 400);
        }

        // Check if token is expired (24 hours)
        $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
        if ($createdAt->addHours(24)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Token has expired'
            ], 400);
        }

        // Verify token
        if (!Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 400);
        }

        // Update password
        $student = Student::where('email', $request->email)->first();
        $student->password = Hash::make($request->password);
        $student->save();

        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    }
}

