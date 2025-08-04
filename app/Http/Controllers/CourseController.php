<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // List all courses
   public function index(Request $request)
{
    $query = Course::with('department');

    if ($request->has('session')) {
        $query->where('session', $request->session);
    }

    if ($request->has('department_id')) {
        $query->where('department_id', $request->department_id);
    }

    if ($request->has('level')) {
        $query->where('level', $request->level);
    }

    if ($request->has('semester')) {
        $query->where('semester', $request->semester);
    }

    return response()->json($query->get());
}


    // Show a single course
    public function show($id)
    {
        $course = Course::with('department')->findOrFail($id);
        return response()->json($course);
    }

    // Create a new course
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code',
            'title' => 'required|string',
            'unit' => 'required|integer|min:1|max:10',
            'level' => 'required|integer',
            'semester' => 'required|string|in:First,Second',
            'department_id' => 'required|exists:departments,id',
        ]);

        $course = Course::create($validated);

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $course
        ], 201);
    }

    // Update an existing course
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code,' . $id,
            'title' => 'required|string',
            'unit' => 'required|integer|min:1|max:10',
            'level' => 'required|integer',
            'semester' => 'required|string|in:First,Second',
            'department_id' => 'required|exists:departments,id',
        ]);

        $course->update($validated);

        return response()->json([
            'message' => 'Course updated successfully.',
            'course' => $course
        ]);
    }

    // Delete a course
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully.']);
    }
}
