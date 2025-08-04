<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'reg_number' => 'required|unique:students',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students',
            'password' => 'required|min:6',
        ]);

        $student = StudentApplication::create([
    'reg_number' => $request->reg_number,
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
]);

        // Send email (pending approval)
        Mail::raw("Your registration is pending admin approval.", function ($msg) use ($student) {
            $msg->to($student->email)->subject('Registration Received');
        });

        return response()->json([
            'message' => 'Registration successful. Await admin approval.',
        ], 201);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $student = Student::where('email', $request->email)->first();
        
        if (!$student || !Hash::check($request->password, $student->password)) {
            throw ValidationException::withMessages([
                'email' => ['The credentials are incorrect.']
            ]);
        }

        if ($student->status !== 'active') {
            return response()->json([
                'message' => 'Your account is not yet approved.'
            ], 403);
        }

        $token = $student->createToken('student-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'student' => $student
        ]);
    }

    public function me(Request $request) {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'user not authenticated'], 401);
        }

        return $request->user();
    }

    public function logout(Request $request) {
           $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'user not authenticated'], 401);
        }
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
