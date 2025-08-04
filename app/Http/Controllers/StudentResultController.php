<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Result;
use App\Models\Student;
use App\Models\Course;

class StudentResultController extends Controller
{
   public function viewResults(Request $request)
{
    $request->validate([
        'reg_number' => 'required|exists:students,reg_number',
        'session' => 'required|string',
        'semester' => 'required|string',
    ]);

    $student = Student::where('reg_number', $request->reg_number)->firstOrFail();

    $results = Result::with('course')
        ->where('student_id', $student->id)
        ->where('session', $request->session)
        ->where('semester', $request->semester)
        ->get();

    // GPA Calculation
    $totalCredits = 0;
    $totalWeightedPoints = 0;

    $gradePoints = [
        'A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 0
    ];

    $formattedResults = $results->map(function ($result) use (&$totalCredits, &$totalWeightedPoints, $gradePoints) {
        $course = $result->course;
        $creditUnit = $course->credit_unit;
        $gradePoint = $gradePoints[$result->grade] ?? 0;

        $totalCredits += $creditUnit;
        $totalWeightedPoints += $gradePoint * $creditUnit;

        return [
            'course_code' => $course->code,
            'course_title' => $course->title,
            'credit_unit' => $creditUnit,
            'score' => $result->score,
            'grade' => $result->grade,
        ];
    });

    $gpa = $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0;

    return response()->json([
        'student' => [
            'name' => $student->name,
            'reg_number' => $student->reg_number,
        ],
        'session' => $request->session,
        'semester' => $request->semester,
        'gpa' => $gpa,
        'results' => $formattedResults,
    ]);
}

public function viewCgpa(Request $request)
{
    $request->validate([
        'reg_number' => 'required|exists:students,reg_number',
    ]);

    $student = Student::where('reg_number', $request->reg_number)->firstOrFail();
    $results = Result::with('course')->where('student_id', $student->id)->get();

    $totalCredits = 0;
    $totalWeightedPoints = 0;

    $gradePoints = [
        'A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 0
    ];

    foreach ($results as $result) {
        $creditUnit = $result->course->credit_unit ?? 0;
        $gradePoint = $gradePoints[$result->grade] ?? 0;

        $totalCredits += $creditUnit;
        $totalWeightedPoints += $gradePoint * $creditUnit;
    }

    $cgpa = $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0;

    return response()->json([
        'student' => [
            'name' => $student->name,
            'reg_number' => $student->reg_number,
        ],
        'cgpa' => $cgpa,
    ]);
}

}
