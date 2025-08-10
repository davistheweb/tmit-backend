<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\FacultyDepartmentController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\StudentResultController;
use App\Http\Controllers\Staff\StaffAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/faculties', [FacultyDepartmentController::class, 'faculties']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/student/profile', [StudentProfileController::class, 'show']);
    Route::post('/student/profile', [StudentProfileController::class, 'complete']);
    Route::get('/student/profile/pdf', [StudentProfileController::class, 'downloadPdf']);
    Route::post('/student/results/view', [StudentResultController::class, 'viewResults']);
    Route::post('/student/cgpa', [StudentResultController::class, 'viewCgpa']);

    // Route::get('/students/{student}/results', [ResultController::class, 'studentResults']);
});



Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::get('/students', [AdminStudentController::class, 'index']);
        Route::get('/students/pending', [AdminStudentController::class, 'pending']);
        Route::post('/students/{id}/approve', [AdminStudentController::class, 'approve']);
        Route::post('/students/{id}/reject', [AdminStudentController::class, 'reject']);
        Route::post('/students/pending-by-department', [AdminStudentController::class, 'pendingByDepartment']);
        Route::delete('/students/{id}', [AdminStudentController::class, 'destroy']);
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/students/by-department', [AdminDashboardController::class, 'studentsByDepartmentQuery']);
        Route::post('/faculties', [FacultyDepartmentController::class, 'createFaculty']);
        Route::post('/departments', [FacultyDepartmentController::class, 'createDepartment']);
        Route::post('/results/store', [ResultController::class, 'storeResult']);
        Route::get('results/{reg_number}', [ResultController::class, 'viewStudentResult'])
    ->where('reg_number', '.*');

        Route::get('courses/', [CourseController::class, 'index']);
        Route::get('courses/{id}', [CourseController::class, 'show']);
        Route::post('courses/', [CourseController::class, 'store']);
        Route::put('courses/{id}', [CourseController::class, 'update']);
        Route::delete('courses/{id}', [CourseController::class, 'destroy']);
        Route::get('staff/all', [StaffAuthController::class, 'listAll']);
        Route::get('staff/{id}', [StaffAuthController::class, 'view']);
        Route::get('/departments/{id}/courses', [CourseController::class, 'getCoursesByDepartment']);
        Route::get('/courses/{id}/departments', [CourseController::class, 'getDepartmentsByCourse']);
        // Route::post('/results', [ResultController::class, 'store']);
        // Route::post('/admin/results/input', [ResultController::class, 'storeResult']);
        // Route::get('/admin/results/student', [ResultController::class, 'getResultsByRegNumber']);

    });
});

Route::middleware(['auth:sanctum'])->prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::get('/{id}', [CourseController::class, 'show']);
});


Route::prefix('staff')->group(function () {
    Route::post('/register', [StaffAuthController::class, 'register']);
    Route::post('/login', [StaffAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [StaffAuthController::class, 'logout']);
        Route::get('/{id}', [StaffAuthController::class, 'view']);         // get single staff
    });
});
