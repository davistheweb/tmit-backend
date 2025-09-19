<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\StudentResultController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\FacultyDepartmentController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\Staff\StaffAuthController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoutePermissionController;
use App\Http\Middleware\StaffPermissionMiddleware;

// -------------------- PUBLIC ROUTES --------------------
Route::post('/register', [AuthController::class, 'register'])->name('student_register');
Route::post('/login', [AuthController::class, 'login'])->name('student_login');
Route::get('/faculties', [FacultyDepartmentController::class, 'faculties'])->name('list_faculties');

// -------------------- AUTHENTICATED STUDENT ROUTES --------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me'])->name('student_me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('student_logout');

    Route::get('/student/profile', [StudentProfileController::class, 'show'])->name('student_profile_show');
    Route::post('/student/profile', [StudentProfileController::class, 'complete'])->name('student_profile_complete');
    Route::get('/student/profile/pdf', [StudentProfileController::class, 'downloadPdf'])->name('student_profile_pdf');

    Route::post('/student/results/view', [StudentResultController::class, 'viewResults'])->name('student_view_results');
    Route::post('/student/cgpa', [StudentResultController::class, 'viewCgpa'])->name('student_view_cgpa');
});

// -------------------- ADMIN ROUTES --------------------
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])->name('admin_login');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('admin_logout');
        Route::get('me', [AdminAuthController::class, 'me'])->name('admin_me');
         Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin_dashboard');

        // -------------------- ADMIN STUDENT MANAGEMENT --------------------
        
            Route::get('/students', [AdminStudentController::class, 'index'])->name('admin_students_index');
            Route::get('/students/pending', [AdminStudentController::class, 'pending'])->name('admin_students_pending');
            Route::post('/students/{id}/approve', [AdminStudentController::class, 'approve'])->name('admin_students_approve');
            Route::post('/students/{id}/reject', [AdminStudentController::class, 'reject'])->name('admin_students_reject');
            Route::post('/students/pending-by-department', [AdminStudentController::class, 'pendingByDepartment'])->name('admin_students_pending_by_department');
            Route::delete('/students/{id}', [AdminStudentController::class, 'destroy'])->name('admin_students_destroy');
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin_dashboard');
            Route::get('/students/by-department', [AdminDashboardController::class, 'studentsByDepartmentQuery'])->name('admin_students_by_department');
            Route::post('/faculties', [FacultyDepartmentController::class, 'createFaculty'])->name('admin_faculty_create');
            Route::post('/departments', [FacultyDepartmentController::class, 'createDepartment'])->name('admin_department_create');
        

        // -------------------- ROLE & PERMISSION MANAGEMENT --------------------
        Route::get('/roles', [RoleController::class, 'listRoles'])->name('admin_roles_list');
        Route::post('/roles', [RoleController::class, 'store'])->name('admin_roles_store');
        Route::post('/roles/assign', [RoleController::class, 'assign'])->name('admin_roles_assign');

        Route::get('/permissions', [PermissionController::class, 'listPermissions'])->name('admin_permissions_list');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('admin_permissions_store');
        Route::post('/permissions/assign', [PermissionController::class, 'assign'])->name('admin_permissions_assign');

        Route::post('/route-permissions/store', [RoutePermissionController::class, 'store'])->name('admin_route_permission_store');
        Route::get('/route-protected', [RoutePermissionController::class, 'list'])->name('admin_route_permission_list');
        Route::get('/route/list', function () {
            $routes = collect(Route::getRoutes())->map(function ($route) {
                return [
                    'uri' => $route->uri(),
                    'method' => $route->methods()[0],
                    'name' => $route->getName(),
                ];
            })->filter(fn($r) => $r['name']); // only routes with a name

            return response()->json($routes);
        })->name('admin_route_list');

        // -------------------- RESULTS --------------------
        
            Route::post('/results/store', [ResultController::class, 'storeResult'])->name('results_store');
            Route::get('/results/{reg_number}', [ResultController::class, 'viewStudentResult'])->where('reg_number', '.*')->name('results_view');
        

        // -------------------- COURSES --------------------
        Route::get('courses/', [CourseController::class, 'index'])->name('courses_index');
        Route::get('courses/{id}', [CourseController::class, 'show'])->name('courses_show');
        Route::post('courses/', [CourseController::class, 'store'])->name('courses_store');
        Route::put('courses/{id}', [CourseController::class, 'update'])->name('courses_update');
        Route::delete('courses/{id}', [CourseController::class, 'destroy'])->name('courses_destroy');
        Route::get('/departments/{id}/courses', [CourseController::class, 'getCoursesByDepartment'])->name('courses_by_department');
        Route::get('/courses/{id}/departments', [CourseController::class, 'getDepartmentsByCourse'])->name('courses_departments');

        // -------------------- STAFF MANAGEMENT --------------------
        Route::get('staff/all', [StaffAuthController::class, 'listAll'])->name('staff_list_all');
        Route::get('staff/{id}', [StaffAuthController::class, 'view'])->name('staff_view');
    });
});

// -------------------- STAFF ROUTES --------------------
Route::prefix('staff')->group(function () {
    Route::post('/register', [StaffAuthController::class, 'register'])->name('staff_register');
    Route::post('/login', [StaffAuthController::class, 'login'])->name('staff_login');


    Route::middleware('auth:sanctum')->group(function () {
         Route::middleware([StaffPermissionMiddleware::class])->group(function () {
            Route::get('/students', [AdminStudentController::class, 'index'])->name('staff_students_index');
            Route::get('/students/pending', [AdminStudentController::class, 'pending'])->name('staff_students_pending');
            Route::post('/students/{id}/approve', [AdminStudentController::class, 'approve'])->name('staff_students_approve');
            Route::post('/students/{id}/reject', [AdminStudentController::class, 'reject'])->name('staff_students_reject');
            Route::post('/students/pending-by-department', [AdminStudentController::class, 'pendingByDepartment'])->name('staff_students_pending_by_department');
            Route::delete('/students/{id}', [AdminStudentController::class, 'destroy'])->name('staff_students_destroy');
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('staff_dashboard');
            Route::get('/students/by-department', [AdminDashboardController::class, 'studentsByDepartmentQuery'])->name('staff_students_by_department');
            Route::post('/faculties', [FacultyDepartmentController::class, 'createFaculty'])->name('staff_faculty_create');
            Route::post('/departments', [FacultyDepartmentController::class, 'createDepartment'])->name('staff_department_create');
              Route::get('courses/', [CourseController::class, 'index'])->name('staff_list_courses_index');
        Route::get('courses/{id}', [CourseController::class, 'show'])->name('staff_list_courses_show');
        Route::post('courses/', [CourseController::class, 'store'])->name('staff_list_courses_store');
        Route::put('courses/{id}', [CourseController::class, 'update'])->name('staff_list_courses_update');
        Route::delete('courses/{id}', [CourseController::class, 'destroy'])->name('staff_list_courses_destroy');
        Route::get('/departments/{id}/courses', [CourseController::class, 'getCoursesByDepartment'])->name('staff_list_courses_by_department');
        Route::get('/courses/{id}/departments', [CourseController::class, 'getDepartmentsByCourse'])->name('staff_list_courses_departments');
         Route::middleware([StaffPermissionMiddleware::class])->group(function () {
            Route::post('/results/store', [ResultController::class, 'storeResult'])->name('staff_store_results_store');
            Route::get('/results/{reg_number}', [ResultController::class, 'viewStudentResult'])->where('reg_number', '.*')->name('staff_list_results_view');
        });

        });
        Route::get('/permission', [StaffAuthController::class, 'me']);
        Route::post('/logout', [StaffAuthController::class, 'logout'])->name('staff_logout');
        Route::get('/{id}', [StaffAuthController::class, 'view'])->name('staff_view_single');
    });
});

// -------------------- FRONTEND HELPER: LIST ROUTES --------------------
