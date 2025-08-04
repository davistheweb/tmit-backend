<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentApplication;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicationRejectedMail;

class AdminStudentController extends Controller
{
    // GET /admin/students - Approved students (optional filter)
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        $students = $query->latest()->get();

        return response()->json($students);
    }

    // GET /admin/students/pending
    public function pending()
    {
        $applications = StudentApplication::all();

        return response()->json($applications);
    }

    // POST /admin/students/{id}/approve
    public function approve($id)
    {
        $application = StudentApplication::findOrFail($id);

$department = Department::where('code', $application->department)->first(); // 'CSC' or similar

        $student = Student::create([
            'reg_number' => $application->reg_number,
            'name'       => $application->name,
            'email'      => $application->email,
            'password'   => $application->password,// Already hashed
               'department_id' => $department ? $department->id : null,
            'status'     => 'active',
        ]);

        // Send approval email
        Mail::raw("Dear {$student->name}, your application has been approved. Welcome!", function ($msg) use ($student) {
            $msg->to($student->email)->subject('Admission Approved');
        });

        // Remove from pending list
        $application->delete();

        return response()->json([
            'message' => 'Student approved and moved to main list.',
            'student' => $student,
        ]);
    }

    // POST /admin/students/{id}/reject
    public function reject($id)
    {
        $application = StudentApplication::findOrFail($id);

        // Send rejection email using Mailable
        Mail::to($application->email)->send(new ApplicationRejectedMail($application->name));

        $application->delete();

        return response()->json([
            'message' => 'Application rejected and applicant notified.',
        ]);
    }

   public function pendingByDepartment(Request $request)
    {
        $request->validate([
            'department' => 'required|string'
        ]);

        $applications = StudentApplication::where('department', 'LIKE', '%' . $request->department . '%')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($applications->isEmpty()) {
            return response()->json([
                'message' => 'No pending applications found for this department.'
            ], 404);
        }

        return response()->json([
            'pending_applications' => $applications
        ]);
    }



    // DELETE /admin/students/{id}
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json(['message' => 'Student deleted.']);
    }
}
