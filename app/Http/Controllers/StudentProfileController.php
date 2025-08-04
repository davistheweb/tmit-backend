<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
    public function complete(Request $request)
    {
        $request->validate([
            'bio' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'certifications' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'department' => 'required|string|max:255',
            'year' => 'required|string|max:255',
        ]);

        $student = Auth::user();

        // Check approval
        if ($student->status !== 'active') {
            return response()->json(['message' => 'You must be approved to complete your profile.'], 403);
        }

        $profileData = [
            'bio' => $request->bio,
            'department' => $request->department,
            'year' => $request->year,
        ];

        if ($request->hasFile('image')) {
            $profileData['image_path'] = $request->file('image')->store('profile_images', 'public');
        }

        if ($request->hasFile('certifications')) {
            $profileData['certifications_path'] = $request->file('certifications')->store('certificates', 'public');
        }

        $profile = StudentProfile::updateOrCreate(
            ['student_id' => $student->id],
            $profileData
        );

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => $profile
        ]);
    }

    public function show(Request $request)
    {
        $student = Auth::user();
        $profile = $student->profile;

        if (!$profile) {
            return response()->json(['message' => 'Profile not completed.'], 404);
        }

        return response()->json([
            'student' => $student,
            'profile' => $profile,
        ]);
    }
}
