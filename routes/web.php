<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Web\FormController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Online Form (Public)
Route::get('/', [FormController::class, 'index'])->name('form.index');
Route::post('/form/submit', [FormController::class, 'submit'])->name('form.submit');
Route::get('/form/success', [FormController::class, 'success'])->name('form.success');

// Admin Panel
Route::prefix('admin')->group(function () {
    Route::get('/login', function () {
        return view('admin.login');
    })->name('admin.login');
    
    // Admin özel login route'u
    Route::post('/login', [LoginController::class, 'login'])->name('admin.login.submit');
    
    Route::middleware(['auth'])->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/students', [AdminController::class, 'students'])->name('admin.students');
        Route::put('/students/{id}/status', [AdminController::class, 'updateStudentStatus'])->name('admin.students.status');
        
        // Atölye yönetimi
        Route::get('/workshops', [AdminController::class, 'workshops'])->name('admin.workshops');
        Route::post('/workshops', [AdminController::class, 'storeWorkshop'])->name('admin.workshops.store');
        Route::put('/workshops/{id}', [AdminController::class, 'updateWorkshop'])->name('admin.workshops.update');
        Route::delete('/workshops/{id}', [AdminController::class, 'deleteWorkshop'])->name('admin.workshops.destroy');
        
        // Grup yönetimi
        Route::get('/groups', [AdminController::class, 'groups'])->name('admin.groups');
        Route::get('/groups/{id}', [AdminController::class, 'groupDetail'])->name('admin.groups.detail');
        Route::post('/groups/{id}/announcements', [AdminController::class, 'storeGroupAnnouncement'])->name('admin.groups.announcements.store');
        
        // Eğitmen yönetimi
        Route::get('/instructors', [AdminController::class, 'instructors'])->name('admin.instructors');
        Route::get('/instructors/{id}', [AdminController::class, 'instructorDetail'])->name('admin.instructors.detail');
        Route::post('/instructors', [AdminController::class, 'storeInstructor'])->name('admin.instructors.store');
        Route::put('/instructors/{id}', [AdminController::class, 'updateInstructor'])->name('admin.instructors.update');
        Route::delete('/instructors/{id}', [AdminController::class, 'deleteInstructor'])->name('admin.instructors.destroy');
        Route::post('/groups', [AdminController::class, 'storeGroup'])->name('admin.groups.store');
        Route::put('/groups/{id}', [AdminController::class, 'updateGroup'])->name('admin.groups.update');
        Route::delete('/groups/{id}', [AdminController::class, 'deleteGroup'])->name('admin.groups.destroy');
        // Kayıt yönetimi
        Route::get('/enrollments', [AdminController::class, 'enrollments'])->name('admin.enrollments');
        Route::put('/enrollments/{id}/status', [AdminController::class, 'updateEnrollmentStatus'])->name('admin.enrollments.status');
        Route::put('/enrollments/{id}/payment', [AdminController::class, 'updateEnrollmentPayment'])->name('admin.enrollments.payment');
        Route::put('/enrollments/{id}/assign-group', [AdminController::class, 'assignGroup'])->name('admin.enrollments.assign-group');
        
        // Veli yönetimi
        Route::get('/parents', [AdminController::class, 'parents'])->name('admin.parents');
        Route::get('/parents/{id}', [AdminController::class, 'parentDetail'])->name('admin.parents.detail');
        Route::post('/parents/{id}/generate-code', [AdminController::class, 'generateParentCode'])->name('admin.parents.generate-code');
        Route::post('/parents/{id}/reset-password-changed', [AdminController::class, 'resetParentPasswordChanged'])->name('admin.parents.reset-password-changed');
        Route::put('/parents/{id}/status', [AdminController::class, 'updateParentStatus'])->name('admin.parents.status');
        Route::post('/parents/{id}/students', [AdminController::class, 'addParentStudent'])->name('admin.parents.add-student');
        Route::delete('/parents/{parentId}/students/{studentId}', [AdminController::class, 'removeParentStudent'])->name('admin.parents.remove-student');
    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// API Routes (for XAMPP compatibility)
Route::prefix('api')->group(function () {
    // Eğitmen API'leri
    Route::prefix('instructor')->group(function () {
        Route::post('/login', [App\Http\Controllers\Api\InstructorController::class, 'login']);
        
        // Auth gerektiren route'lar
        Route::middleware(['api.auth'])->group(function () {
            Route::post('/logout', [App\Http\Controllers\Api\InstructorController::class, 'logout']);
            Route::get('/profile', [App\Http\Controllers\Api\InstructorController::class, 'profile']);
            Route::put('/change-password', [App\Http\Controllers\Api\InstructorController::class, 'changePassword']);
            Route::get('/groups', [App\Http\Controllers\Api\InstructorController::class, 'groups']);
            Route::get('/groups/{id}', [App\Http\Controllers\Api\InstructorController::class, 'groupDetail']);
            Route::get('/groups/{id}/students', [App\Http\Controllers\Api\InstructorController::class, 'getGroupStudents']);
            Route::get('/today-classes', [App\Http\Controllers\Api\InstructorController::class, 'todayClasses']);
            Route::get('/current-active-groups', [App\Http\Controllers\Api\InstructorController::class, 'getCurrentActiveGroups']);
            
            // Yoklama API'leri
            Route::get('/groups/{id}/attendance', [App\Http\Controllers\Api\InstructorController::class, 'checkAttendanceStatus']);
            Route::post('/groups/{id}/attendance', [App\Http\Controllers\Api\InstructorController::class, 'saveAttendance']);
        });
    });
    
    // Public API'leri
    Route::prefix('public')->group(function () {
        Route::post('/students', [App\Http\Controllers\Api\StudentController::class, 'store']);
        Route::get('/workshops', [App\Http\Controllers\Api\WorkshopController::class, 'index']);
        Route::get('/workshops/{id}', [App\Http\Controllers\Api\WorkshopController::class, 'show']);
    });
    
    // Parent API'leri
    Route::prefix('parent')->group(function () {
        Route::post('/login', [App\Http\Controllers\Api\ParentController::class, 'login']);
        Route::post('/register', [App\Http\Controllers\Api\ParentController::class, 'register']);
        
        // Auth gerektiren route'lar
        Route::middleware(['parent.api.auth'])->group(function () {
            Route::post('/logout', [App\Http\Controllers\Api\ParentController::class, 'logout']);
            Route::get('/profile', [App\Http\Controllers\Api\ParentController::class, 'profile']);
            Route::put('/change-password', [App\Http\Controllers\Api\ParentController::class, 'changePassword']);
            Route::get('/students', [App\Http\Controllers\Api\ParentController::class, 'students']);
            Route::get('/students/{id}', [App\Http\Controllers\Api\ParentController::class, 'studentDetail']);
            Route::get('/students/{id}/attendance', [App\Http\Controllers\Api\ParentController::class, 'studentAttendance']);
            Route::post('/students/add', [App\Http\Controllers\Api\ParentController::class, 'addStudent']);
            Route::get('/notifications', [App\Http\Controllers\Api\ParentController::class, 'notifications']);
            Route::post('/fcm-token', [App\Http\Controllers\Api\ParentController::class, 'saveFcmToken']);
        });
        
        Route::post('/students/find', [App\Http\Controllers\Api\StudentController::class, 'findByTcIdentity']);
    });
    
    // Admin API'leri
    Route::prefix('admin')->group(function () {
        Route::get('/students', [App\Http\Controllers\Api\StudentController::class, 'index']);
        Route::get('/students/{id}', [App\Http\Controllers\Api\StudentController::class, 'show']);
        Route::put('/students/{id}/status', [App\Http\Controllers\Api\StudentController::class, 'updateStatus']);
        
        Route::post('/workshops', [App\Http\Controllers\Api\WorkshopController::class, 'store']);
        Route::put('/workshops/{id}', [App\Http\Controllers\Api\WorkshopController::class, 'update']);
        Route::delete('/workshops/{id}', [App\Http\Controllers\Api\WorkshopController::class, 'destroy']);
        Route::get('/workshops/{id}/statistics', [App\Http\Controllers\Api\WorkshopController::class, 'statistics']);
        
        Route::get('/groups', [App\Http\Controllers\Api\GroupController::class, 'index']);
        Route::post('/groups', [App\Http\Controllers\Api\GroupController::class, 'store']);
        Route::get('/groups/{id}', [App\Http\Controllers\Api\GroupController::class, 'show']);
        Route::get('/groups/{id}', [App\Http\Controllers\Api\GroupController::class, 'show']);
        Route::put('/groups/{id}', [App\Http\Controllers\Api\GroupController::class, 'update']);
        Route::delete('/groups/{id}', [App\Http\Controllers\Api\GroupController::class, 'destroy']);
        
        Route::get('/enrollments', [App\Http\Controllers\Api\EnrollmentController::class, 'index']);
        Route::post('/enrollments', [App\Http\Controllers\Api\EnrollmentController::class, 'store']);
        Route::get('/enrollments/{id}', [App\Http\Controllers\Api\EnrollmentController::class, 'show']);
        Route::put('/enrollments/{id}', [App\Http\Controllers\Api\EnrollmentController::class, 'update']);
        Route::delete('/enrollments/{id}', [App\Http\Controllers\Api\EnrollmentController::class, 'destroy']);
    });
});
