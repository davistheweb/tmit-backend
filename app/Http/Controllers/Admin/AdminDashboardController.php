<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Staff;
use App\Models\Admin;
use App\Models\StudentApplication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    /**
     * Admin Dashboard Summary Analytics
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'totals' => [
                'students'      => Student::count(),
                'approved'      => Student::where('status', 'active')->count(),
                'pending'       => StudentApplication::count(),
                'staff'         => Staff::count(),
                'admins'        => Admin::count(),
                'faculties'     => Faculty::count(),
                'departments'   => Department::count(),
            ],
            'students_per_department' => Department::withCount('students')
                ->orderByDesc('students_count')
                ->get(['id', 'name']),

            'recent_students' => Student::latest()->take(5)->get([
                'reg_number', 'name', 'email', 'status', 'created_at'
            ]),
        ]);
    }

    /**
     * View Students by Department (with optional status & pagination)
     */
    public function studentsByDepartmentQuery(Request $request): JsonResponse
    {
        $department = $request->query('name');
        $status = $request->query('status'); // optional: e.g., active, pending

        if (!$department) {
            return response()->json(['error' => 'Department name is required'], 422);
        }

        $query = Student::whereHas('department', function ($q) use ($department) {
            $q->where('name', $department);
        });

        if ($status) {
            $query->where('status', $status);
        }

        $students = $query->paginate(20); // handles ?page=1,2,3...

        return response()->json([
            'department' => $department,
            'status_filter' => $status ?? 'all',
            'students' => $students
        ]);
    }
}
