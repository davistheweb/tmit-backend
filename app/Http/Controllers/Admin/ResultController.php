<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Result;
use App\Models\Student;
use App\Models\Course;

class ResultController extends Controller
{
    public function storeResult(Request $request)
    {
        $request->validate([
            'reg_number' => 'required|exists:students,reg_number',
            'session' => 'required|string',
            'semester' => 'required|string',
            'results' => 'required|array',
            'results.*.course_code' => 'required|exists:courses,code',
            'results.*.score' => 'required|integer|min:0|max:100',
        ]);

        $student = Student::where('reg_number', $request->reg_number)->first();

        foreach ($request->results as $input) {
            $course = Course::where('code', $input['course_code'])->first();

            Result::updateOrCreate(
                [
                    'student_id'   => $student->id,
                    'course_code'  => $course->code,
                    'session'      => $request->session,
                    'semester'     => $request->semester,
                ],
                [
                    'course_title' => $course->title, // save course title directly
                    'score'        => $input['score'],
                    'grade'        => $this->getGrade($input['score']),
                ]
            );
        }

        return response()->json(['message' => 'Results stored successfully.']);
    }

    public function viewStudentResult($reg_number, Request $request)
    {
        $request->validate([
            'session' => 'required|string',
            'semester' => 'required|string',
        ]);

        $student = Student::where('reg_number', $reg_number)
            ->with('department')
            ->firstOrFail();

        $results = Result::where('student_id', $student->id)
            ->where('session', $request->session)
            ->where('semester', $request->semester)
            ->get()
            ->map(function ($result) {
                return [
                    'course_code' => $result->course_code,
                    'course_title' => $result->course_title, // use stored title instead of relation
                    'score' => $result->score,
                    'grade' => $result->grade,
                ];
            });

        return response()->json([
            'student' => [
                'name' => $student->name,
                'reg_number' => $student->reg_number,
                'department' => $student->department->name ?? null,
            ],
            'results' => $results,
        ]);
    }

    private function getGrade($score)
    {
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 45) return 'D';
        if ($score >= 40) return 'E';
        return 'F';
    }
}
