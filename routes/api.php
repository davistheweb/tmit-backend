<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;





Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/student/profile', [StudentProfileController::class, 'show']);
    Route::post('/student/profile', [StudentProfileController::class, 'complete']);
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
        Route::delete('/students/{id}', [AdminStudentController::class, 'destroy']);
    });
});

