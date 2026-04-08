<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\WorkshopController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\InstructorController;
use App\Http\Controllers\Api\ParentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Online Form Routes (Public)
Route::prefix('public')->group(function () {
    // Öğrenci kayıt formu
    Route::post('/students', [StudentController::class, 'store']);
    
    // Aktif sınıfları listele
    Route::get('/workshops', [WorkshopController::class, 'index']);
    Route::get('/workshops/{id}', [WorkshopController::class, 'show']);
});

// Veli Uygulaması Routes
Route::prefix('parent')->group(function () {
    // Giriş (public)
    Route::post('/register', [ParentController::class, 'register']);
    Route::post('/login', [ParentController::class, 'login']);
    
    // Giriş yapmış veliler için
    Route::middleware(['simple.token.auth'])->group(function () {
        // Çıkış
        Route::post('/logout', [ParentController::class, 'logout']);
        
        // Profil
        Route::get('/profile', [ParentController::class, 'profile']);
        Route::put('/change-password', [ParentController::class, 'changePassword']);
        
        // Öğrenci işlemleri
        Route::post('/add-student', [ParentController::class, 'addStudent']);
        Route::get('/students', [ParentController::class, 'students']);
        Route::get('/students/{studentId}', [ParentController::class, 'studentDetail']);
        Route::get('/students/{studentId}/attendance', [ParentController::class, 'studentAttendance']);
        
        // Duyurular
        Route::get('/notifications', [ParentController::class, 'notifications']);
    });
});

// Yönetici Panel Routes (Admin only)
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Öğrenci yönetimi
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}', [StudentController::class, 'show']);
    Route::put('/students/{id}/status', [StudentController::class, 'updateStatus']);
    
    // Sınıf yönetimi
    Route::post('/workshops', [WorkshopController::class, 'store']);
    Route::put('/workshops/{id}', [WorkshopController::class, 'update']);
    Route::delete('/workshops/{id}', [WorkshopController::class, 'destroy']);
    Route::get('/workshops/{id}/statistics', [WorkshopController::class, 'statistics']);
    
    // Grup yönetimi
    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::get('/groups/{id}', [GroupController::class, 'show']);
    Route::put('/groups/{id}', [GroupController::class, 'update']);
    Route::delete('/groups/{id}', [GroupController::class, 'destroy']);
    
    // Kayıt yönetimi
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::post('/enrollments', [EnrollmentController::class, 'store']);
    Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);
    Route::put('/enrollments/{id}', [EnrollmentController::class, 'update']);
    Route::delete('/enrollments/{id}', [EnrollmentController::class, 'destroy']);
});

// Eğitmen Uygulaması Routes
Route::prefix('instructor')->group(function () {
    // Giriş (public)
    Route::post('/login', [InstructorController::class, 'login']);
    
    // Giriş yapmış eğitmenler için
    Route::middleware(['simple.token.auth'])->group(function () {
        // Çıkış
        Route::post('/logout', [InstructorController::class, 'logout']);
        
        // Profil
        Route::get('/profile', [InstructorController::class, 'profile']);
        Route::put('/change-password', [InstructorController::class, 'changePassword']);
        
        // Gruplar
        Route::get('/groups', [InstructorController::class, 'groups']);
        Route::get('/groups/{id}', [InstructorController::class, 'groupDetail']);
        Route::get('/today-classes', [InstructorController::class, 'todayClasses']);
        
        // Yoklama işlemleri
        Route::get('/groups/{groupId}/students', [InstructorController::class, 'getGroupStudents']);
        Route::get('/groups/{groupId}/attendance-status', [InstructorController::class, 'checkAttendanceStatus']);
        Route::post('/groups/{groupId}/attendance', [InstructorController::class, 'saveAttendance']);
    });
});
