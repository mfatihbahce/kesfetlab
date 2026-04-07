<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentUser;
use App\Models\Student;
use App\Models\ParentStudent;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ParentController extends Controller
{
    /**
     * Veli kayıt
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:parent_users,phone',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate a unique TC identity for the parent
        $tcIdentity = 'P' . str_pad($request->phone, 10, '0', STR_PAD_LEFT);

        $parent = ParentUser::create([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'email' => null,
            'password' => Hash::make($request->password),
            'tc_identity' => $tcIdentity,
            'address' => null,
            'status' => 'active',
        ]);

        $token = $parent->createToken('parent-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Kayıt başarılı',
            'data' => [
                'parent' => [
                    'id' => $parent->id,
                    'full_name' => $parent->full_name,
                    'phone' => $parent->phone,
                    'email' => $parent->email,
                    'tc_identity' => $parent->tc_identity,
                ],
                'token' => $token
            ]
        ]);
    }

    /**
     * Veli giriş
     */
    public function login(Request $request): JsonResponse
    {
        // Debug logging
        error_log("=== PARENT LOGIN DEBUG ===");
        error_log("Request data: " . json_encode($request->all()));
        
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            error_log("Validation failed: " . json_encode($validator->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = $request->phone;
        $password = $request->password;
        
        error_log("Looking for parent with phone: $phone");
        
        $parent = ParentUser::where('phone', $phone)
                           ->where('status', 'active')
                           ->first();

        if (!$parent) {
            error_log("Parent not found with phone: $phone");
            return response()->json([
                'success' => false,
                'message' => 'Bu telefon numarasına kayıtlı veli bulunamadı'
            ], 401);
        }

        error_log("Parent found: " . json_encode([
            'id' => $parent->id,
            'full_name' => $parent->full_name,
            'phone' => $parent->phone,
            'temp_code' => $parent->temp_code,
            'password_changed' => $parent->password_changed
        ]));

        // Şifre kontrolü - hem normal şifre hem de geçici kod kontrolü
        $passwordValid = Hash::check($password, $parent->password);
        $tempCodeValid = $parent->temp_code === $password;
        
        error_log("Password validation: passwordValid=$passwordValid, tempCodeValid=$tempCodeValid");

        if (!$passwordValid && !$tempCodeValid) {
            error_log("Both password and temp code are invalid");
            return response()->json([
                'success' => false,
                'message' => 'Telefon numarası veya şifre hatalı'
            ], 401);
        }

        $token = $parent->createToken('parent-token')->plainTextToken;
        
        error_log("Login successful, token generated");

        return response()->json([
            'success' => true,
            'message' => 'Giriş başarılı',
            'data' => [
                'parent' => [
                    'id' => $parent->id,
                    'full_name' => $parent->full_name,
                    'phone' => $parent->phone,
                    'email' => $parent->email,
                    'tc_identity' => $parent->tc_identity,
                    'password_changed' => $parent->password_changed,
                ],
                'token' => $token,
                'requires_password_change' => !$parent->password_changed
            ]
        ]);
    }

    /**
     * Veli çıkış
     */
    public function logout(Request $request): JsonResponse
    {
        $parent = $request->get('user');
        $parent->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Çıkış başarılı'
        ]);
    }

    /**
     * Veli profil bilgileri
     */
    public function profile(Request $request): JsonResponse
    {
        $parent = $request->get('user');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $parent->id,
                'full_name' => $parent->full_name,
                'phone' => $parent->phone,
                'email' => $parent->email,
                'tc_identity' => $parent->tc_identity,
                'address' => $parent->address,
                'status' => $parent->status,
                'created_at' => $parent->created_at,
            ]
        ]);
    }

    /**
     * Öğrenci tanımlama
     */
    public function addStudent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_tc_identity' => 'required|string|size:11',
            'relationship' => 'required|in:parent,guardian,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $parent = $request->get('user');

        // Öğrenciyi bul
        $student = Student::where('tc_identity', $request->student_tc_identity)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Bu TC kimlik numarasına sahip öğrenci bulunamadı'
            ], 404);
        }

        // Zaten tanımlı mı kontrol et
        $existingRelation = ParentStudent::where('parent_user_id', $parent->id)
                                       ->where('student_id', $student->id)
                                       ->first();

        if ($existingRelation) {
            return response()->json([
                'success' => false,
                'message' => 'Bu öğrenci zaten tanımlı'
            ], 400);
        }

        // İlişkiyi oluştur
        ParentStudent::create([
            'parent_user_id' => $parent->id,
            'student_id' => $student->id,
            'relationship' => $request->relationship,
            'is_primary' => !$parent->students()->exists(), // İlk öğrenciyse ana veli
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Öğrenci başarıyla tanımlandı',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'full_name' => $student->full_name,
                    'tc_identity' => $student->tc_identity,
                    'relationship' => $request->relationship,
                ]
            ]
        ]);
    }

    /**
     * Veliye ait öğrenciler
     */
    public function students(Request $request): JsonResponse
    {
        $parent = $request->get('user');

        $students = $parent->students()
                          ->with(['enrollments.group.workshop', 'enrollments.group.instructor'])
                          ->get()
                          ->map(function ($student) {
                              $activeEnrollment = $student->enrollments->where('status', 'approved')->where('is_active', true)->first();
                              
                              return [
                                  'id' => $student->id,
                                  'full_name' => $student->full_name,
                                  'tc_identity' => $student->tc_identity,
                                  'birth_date' => $student->birth_date,
                                  'relationship' => $student->pivot->relationship,
                                  'is_primary' => $student->pivot->is_primary,
                                  'enrollment' => $activeEnrollment ? [
                                      'id' => $activeEnrollment->id,
                                      'status' => $activeEnrollment->status,
                                      'enrolled_at' => $activeEnrollment->created_at,
                                      'group' => [
                                          'id' => $activeEnrollment->group->id,
                                          'name' => $activeEnrollment->group->name,
                                          'schedule' => $activeEnrollment->group->schedule,
                                          'workshop' => [
                                              'id' => $activeEnrollment->group->workshop->id,
                                              'name' => $activeEnrollment->group->workshop->name,
                                          ],
                                          'instructor' => [
                                              'id' => $activeEnrollment->group->instructor->id,
                                              'full_name' => $activeEnrollment->group->instructor->full_name,
                                          ],
                                      ]
                                  ] : null,
                              ];
                          });

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    /**
     * Öğrenci detayları
     */
    public function studentDetail(Request $request, $studentId): JsonResponse
    {
        $parent = $request->get('user');

        $student = $parent->students()
                         ->where('students.id', $studentId)
                         ->with(['enrollments.group.workshop', 'enrollments.group.instructor'])
                         ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Öğrenci bulunamadı'
            ], 404);
        }

        $activeEnrollment = $student->enrollments->where('status', 'approved')->where('is_active', true)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'tc_identity' => $student->tc_identity,
                'birth_date' => $student->birth_date,
                'parent_full_name' => $student->parent_full_name,
                'parent_phone' => $student->parent_phone,
                'relationship' => $student->pivot->relationship,
                'is_primary' => $student->pivot->is_primary,
                'enrollment' => $activeEnrollment ? [
                    'id' => $activeEnrollment->id,
                    'status' => $activeEnrollment->status,
                    'enrolled_at' => $activeEnrollment->created_at,
                    'group' => [
                        'id' => $activeEnrollment->group->id,
                        'name' => $activeEnrollment->group->name,
                        'schedule' => $activeEnrollment->group->schedule,
                        'capacity' => $activeEnrollment->group->capacity,
                        'workshop' => [
                            'id' => $activeEnrollment->group->workshop->id,
                            'name' => $activeEnrollment->group->workshop->name,
                            'description' => $activeEnrollment->group->workshop->description,
                        ],
                        'instructor' => [
                            'id' => $activeEnrollment->group->instructor->id,
                            'full_name' => $activeEnrollment->group->instructor->full_name,
                            'phone' => $activeEnrollment->group->instructor->phone,
                        ],
                    ]
                ] : null,
            ]
        ]);
    }

    /**
     * Öğrenci devamsızlık bilgileri
     */
    public function studentAttendance(Request $request, $studentId): JsonResponse
    {
        $parent = $request->get('user');

        $student = $parent->students()->where('students.id', $studentId)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Öğrenci bulunamadı'
            ], 404);
        }

        $attendanceRecords = Attendance::where('student_id', $studentId)
                                     ->with(['group.workshop'])
                                     ->orderBy('lesson_date', 'desc')
                                     ->get()
                                     ->map(function ($record) {
                                         return [
                                             'id' => $record->id,
                                             'date' => $record->lesson_date,
                                             'status' => $record->status,
                                             'group' => [
                                                 'id' => $record->group->id,
                                                 'name' => $record->group->name,
                                                 'workshop' => [
                                                     'id' => $record->group->workshop->id,
                                                     'name' => $record->group->workshop->name,
                                                 ],
                                             ],
                                         ];
                                     });

        // İstatistikler
        $totalLessons = $attendanceRecords->count();
        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $absentCount = $attendanceRecords->where('status', 'absent')->count();
        $lateCount = $attendanceRecords->where('status', 'late')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_lessons' => $totalLessons,
                    'present_count' => $presentCount,
                    'absent_count' => $absentCount,
                    'late_count' => $lateCount,
                    'attendance_rate' => $totalLessons > 0 ? round(($presentCount / $totalLessons) * 100, 2) : 0,
                ],
                'records' => $attendanceRecords
            ]
        ]);
    }

    /**
     * Duyurular
     */
    public function notifications(Request $request): JsonResponse
    {
        $parent = $request->get('user');

        $notifications = Notification::where(function($query) {
                                   $query->where('target_type', 'parent')
                                         ->orWhere('target_type', 'all')
                                         ->orWhere('target_type', 'group')
                                         ->orWhere('target_type', 'student');
                               })
                               ->whereIn('status', ['sent', 'pending'])
                               ->orderBy('created_at', 'desc')
                                   ->get()
                                   ->map(function ($notification) {
                                       return [
                                           'id' => $notification->id,
                                           'title' => $notification->title,
                                           'content' => $notification->message,
                                           'type' => $notification->target_type,
                                           'created_at' => $notification->created_at,
                                       ];
                                   });

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Şifre değiştirme
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $parent = $request->get('user');

        // Mevcut şifre kontrolü - hem normal şifre hem de geçici kod kontrolü
        $passwordValid = Hash::check($request->current_password, $parent->password);
        $tempCodeValid = $parent->temp_code === $request->current_password;

        if (!$passwordValid && !$tempCodeValid) {
            return response()->json([
                'success' => false,
                'message' => 'Mevcut şifre hatalı'
            ], 400);
        }

        $parent->update([
            'password' => Hash::make($request->new_password),
            'password_changed' => true,
            'temp_code' => null, // Geçici kodu temizle
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Şifre başarıyla değiştirildi'
        ]);
    }

    /**
     * FCM Token kaydetme
     */
    public function saveFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
            'device_type' => 'required|string|in:android,ios',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $parent = $request->get('user');

        $parent->update([
            'fcm_token' => $request->fcm_token,
            'device_type' => $request->device_type,
            'fcm_token_updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token başarıyla kaydedildi'
        ]);
    }
}
