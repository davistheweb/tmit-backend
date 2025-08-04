<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faculty;
use App\Models\Department;

class FacultyDepartmentController extends Controller
{
    public function createFaculty(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:faculties,name',
            'abbrev' => 'required|unique:faculties,abbrev',
        ]);

        $faculty = Faculty::create([
            'name' => $request->name,
            'abbrev' => $request->abbrev
        ]);

        return response()->json(['message' => 'Faculty created', 'data' => $faculty], 201);
    }

    public function createDepartment(Request $request)
    {
        $request->validate([
            'name'       => 'required|string',
            'code'       => 'required|unique:departments,code',
            'faculty_id' => 'required|exists:faculties,id',
        ]);

        $department = Department::create($request->only('name', 'code', 'faculty_id'));

        return response()->json(['message' => 'Department created', 'data' => $department], 201);
    }

    public function faculties()
    {
        return Faculty::with('departments')->get();
    }
}
