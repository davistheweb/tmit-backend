<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentProfileController extends Controller
{
    public function complete(Request $request)
    {
        $student = Auth::user();
        
    $request->validate([
        // 'regnumber' => 'required|string|exists:students,reg_number',
        'surname' => 'required|string',
        'middle_name' => 'nullable|string',
        'last_name' => 'required|string',
        'gender' => 'required',
        'dob' => 'required|date',
        'country' => 'required|string',
        'state' => 'required|string',
        'lga' => 'required|string',
        'home_town' => 'required|string',
        'phone' => 'required|string',
        'nin' => 'required|string',
        'contact_address' => 'required|string',
        'blood_group' => 'required',
        'genotype' => 'required',
        'religion' => 'required|string',
        'bio' => 'nullable|string',
        // 'department' => 'required|string',
        'year' => 'required|string',
        'image' => 'nullable|image|max:2048',
        'certifications' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
    ]);

    $profileData = $request->except(['image', 'certifications']);
    $profileData['student_id'] = $student->id;
    $profileData['reg_number'] = $student->reg_number;
    $profileData['email'] = $student->email;
    $profileData['department'] = $student->department;

    // Handle Image Upload
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images', 'public');
        $profileData['image_path'] = $imagePath;
    }

    // Handle Certifications Upload
    if ($request->hasFile('certifications')) {
        $certPath = $request->file('certifications')->store('certifications', 'public');
        $profileData['certifications_path'] = $certPath;
    }

    // Create or update
    $profile = StudentProfile::updateOrCreate(
        ['student_id' => $student->id],
        $profileData
    );

    return response()->json([
        'message' => 'Profile completed successfully',
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
            'image_url' => $profile->image_path ? asset('storage/' . $profile->image_path) : null,
            'certifications_url' => $profile->certifications_path ? asset('storage/' . $profile->certifications_path) : null,
        ]);
    }

    

public function downloadPdf()
{
    $student = Auth::user();
    $profile = $student->profile; // assuming one-to-one relation

    if (!$profile) {
        return response()->json(['message' => 'Profile not completed'], 404);
    }

       $pdf = Pdf::loadView('pdf.student-profile', compact('profile'))
              ->setPaper('A4', 'portrait');
    return $pdf->download('student_profile.pdf');
}

}
