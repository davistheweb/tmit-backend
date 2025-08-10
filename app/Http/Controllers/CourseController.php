<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // List all courses with optional filters
    public function index(Request $request)
    {
        $query = Course::with('departments');

        if ($request->has('session')) {
            $query->where('session', $request->session);
        }

        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->has('department_id')) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }

        return response()->json($query->get());
    }

    // Show a single course with its departments
    public function show($id)
    {
        $course = Course::with('departments')->findOrFail($id);
        return response()->json($course);
    }

    // Create a new course and attach departments
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code',
            'title' => 'required|string',
            'unit' => 'required|integer|min:1|max:10',
            'level' => 'required|integer',
            'semester' => 'required|string|in:First,Second',
            'session' => 'required|string',
            'department_ids' => 'required|array',
            'department_ids.*' => 'exists:departments,id',
        ]);

        $course = Course::create([
            'code' => $validated['code'],
            'title' => $validated['title'],
            'unit' => $validated['unit'],
            'level' => $validated['level'],
            'semester' => $validated['semester'],
            'session' => $validated['session']
        ]);

        // Attach departments
        $course->departments()->attach($validated['department_ids']);

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $course->load('departments')
        ], 201);
    }

    // Update an existing course and its departments
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code,' . $id,
            'title' => 'required|string',
            'unit' => 'required|integer|min:1|max:10',
            'level' => 'required|integer',
            'semester' => 'required|string|in:First,Second',
            'session' => 'required|string',
            'department_ids' => 'required|array',
            'department_ids.*' => 'exists:departments,id',
        ]);

        $course->update([
            'code' => $validated['code'],
            'title' => $validated['title'],
            'unit' => $validated['unit'],
            'level' => $validated['level'],
            'semester' => $validated['semester'],
            'session' => $validated['session']
        ]);

        // Sync departments (remove old and attach new)
        $course->departments()->sync($validated['department_ids']);

        return response()->json([
            'message' => 'Course updated successfully.',
            'course' => $course->load('departments')
        ]);
    }

    // Delete a course
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully.']);
    }

    // Get all courses under a specific department
    public function getCoursesByDepartment($departmentId)
    {
        $department = Department::with('courses')->findOrFail($departmentId);
        return response()->json($department->courses);
    }

    // Get all departments for a specific course
    public function getDepartmentsByCourse($courseId)
    {
        $course = Course::with('departments')->findOrFail($courseId);
        return response()->json($course->departments);
    }
}
