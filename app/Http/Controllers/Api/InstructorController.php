<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Group;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InstructorController extends Controller
{
    /**
     * Eğitmen girişi (telefon + şifre)
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $instructor = User::where('phone', $request->phone)
                         ->where('role', 'instructor')
                         ->where('is_active', true)
                         ->first();

        if (!$instructor || !Hash::check($request->password, $instructor->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Telefon numarası veya şifre hatalı'
            ], 401);
        }

        // Basit token oluştur (Sanctum olmadan)
        $token = base64_encode($instructor->id . '|' . time() . '|' . $instructor->phone);

        // Son giriş zamanını güncelle
        $instructor->update(['last_login_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Giriş başarılı',
            'data' => [
                'token' => $token,
                'instructor' => [
                    'id' => $instructor->id,
                    'name' => $instructor->name,
                    'full_name' => $instructor->full_name,
                    'email' => $instructor->email,
                    'phone' => $instructor->phone,
                    'profession' => $instructor->profession,
                ]
            ]
        ]);
    }

    /**
     * Eğitmen çıkışı
     */
    public function logout(Request $request): JsonResponse
    {
        // Basit token sistemi için logout işlemi
        return response()->json([
            'success' => true,
            'message' => 'Çıkış başarılı'
        ]);
    }

    /**
     * Eğitmen profil bilgileri
     */
    public function profile(Request $request): JsonResponse
    {
        $instructor = $request->get('user');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $instructor->id,
                'first_name' => $instructor->first_name,
                'last_name' => $instructor->last_name,
                'full_name' => $instructor->full_name,
                'email' => $instructor->email,
                'phone' => $instructor->phone,
                'profession' => $instructor->profession,
                'address' => $instructor->address,
                'last_login_at' => $instructor->last_login_at?->format('d.m.Y H:i'),
            ]
        ]);
    }

    /**
     * Eğitmenin grupları
     */
    public function groups(Request $request): JsonResponse
    {
        $instructor = $request->get('user');

        $groups = Group::where('instructor_id', $instructor->id)
                      ->with(['workshop', 'enrollments.student'])
                      ->withCount('enrollments')
                      ->orderBy('day_of_week')
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $groups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'workshop' => [
                        'id' => $group->workshop->id,
                        'name' => $group->workshop->name,
                    ],
                    'schedule' => $group->schedule,
                    'capacity' => $group->capacity,
                    'enrollment_count' => $group->enrollments_count,
                    'status' => $group->status,
                    'students' => $group->enrollments->map(function ($enrollment) {
                        return [
                            'id' => $enrollment->student->id,
                            'full_name' => $enrollment->student->full_name,
                            'tc_identity' => $enrollment->student->tc_identity,
                            'parent_phone' => $enrollment->student->parent_phone,
                            'enrollment_status' => $enrollment->status,
                        ];
                    })
                ];
            })
        ]);
    }

    /**
     * Bugünkü dersler
     */
    public function todayClasses(Request $request): JsonResponse
    {
        $instructor = $request->get('user');
        
        // Gün adlarını hem İngilizce hem Türkçe kontrol et
        $today = now();
        $englishDay = strtolower($today->englishDayOfWeek);
        $turkishDay = strtolower($today->locale('tr')->dayName);
        
        // Olası gün formatları
        $dayVariations = [
            $englishDay,
            $turkishDay,
            ucfirst($englishDay),
            ucfirst($turkishDay),
        ];

        $groups = Group::where('instructor_id', $instructor->id)
                      ->whereIn('day_of_week', $dayVariations)
                      ->where('status', 'active')
                      ->with(['workshop', 'enrollments.student'])
                      ->withCount('enrollments')
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $groups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'workshop_name' => $group->workshop->name,
                    'start_time' => $group->start_time->format('H:i'),
                    'end_time' => $group->end_time->format('H:i'),
                    'student_count' => $group->enrollments_count,
                    'students' => $group->enrollments->map(function ($enrollment) {
                        return [
                            'id' => $enrollment->student->id,
                            'full_name' => $enrollment->student->full_name,
                            'tc_identity' => $enrollment->student->tc_identity,
                        ];
                    })
                ];
            })
        ]);
    }

    /**
     * Grup detayı
     */
    public function groupDetail(Request $request, $groupId): JsonResponse
    {
        $instructor = $request->get('user');

        $group = Group::where('id', $groupId)
                     ->where('instructor_id', $instructor->id)
                     ->with(['workshop', 'enrollments.student'])
                     ->first();

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Grup bulunamadı'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $group->id,
                'name' => $group->name,
                'workshop' => [
                    'id' => $group->workshop->id,
                    'name' => $group->workshop->name,
                    'description' => $group->workshop->description,
                ],
                'schedule' => $group->schedule,
                'capacity' => $group->capacity,
                'status' => $group->status,
                'description' => $group->description,
                'students' => $group->enrollments->map(function ($enrollment) {
                    return [
                        'id' => $enrollment->student->id,
                        'full_name' => $enrollment->student->full_name,
                        'tc_identity' => $enrollment->student->tc_identity,
                        'parent_full_name' => $enrollment->student->parent_full_name,
                        'parent_phone' => $enrollment->student->parent_phone,
                        'enrollment_date' => $enrollment->created_at->format('d.m.Y'),
                        'enrollment_status' => $enrollment->status,
                    ];
                })
            ]
        ]);
    }

    /**
     * Şifre değiştirme
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $instructor = $request->get('user');

        if (!Hash::check($request->current_password, $instructor->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mevcut şifre hatalı'
            ], 400);
        }

        $instructor->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Şifre başarıyla değiştirildi'
        ]);
    }

    /**
     * Grup öğrencileri (yoklama için)
     */
    public function getGroupStudents(Request $request, $groupId): JsonResponse
    {
        $instructor = $request->get('user');

        $group = Group::where('id', $groupId)
                     ->where('instructor_id', $instructor->id)
                     ->with(['enrollments.student'])
                     ->first();

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Grup bulunamadı'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $group->enrollments->map(function ($enrollment) {
                return [
                    'id' => $enrollment->student->id,
                    'full_name' => $enrollment->student->full_name,
                    'tc_identity' => $enrollment->student->tc_identity,
                    'parent_full_name' => $enrollment->student->parent_full_name,
                    'parent_phone' => $enrollment->student->parent_phone,
                ];
            })
        ]);
    }

    /**
     * Yoklama durumunu kontrol et
     */
    public function checkAttendanceStatus(Request $request, $groupId): JsonResponse
    {
        $instructor = $request->get('user');

        $group = Group::where('id', $groupId)
                     ->where('instructor_id', $instructor->id)
                     ->first();

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Grup bulunamadı'
            ], 404);
        }

        $today = now()->format('Y-m-d');

        // Bugün için yoklama alınmış mı kontrol et
        $existingAttendance = Attendance::where('group_id', $groupId)
                                       ->where('lesson_date', $today)
                                       ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'attendance_taken' => $existingAttendance !== null,
                'date' => $today,
                'group_id' => $groupId
            ]
        ]);
    }

    /**
     * Yoklama kaydetme
     */
    public function saveAttendance(Request $request, $groupId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'attendance' => 'required|array',
            'attendance.*' => 'in:present,absent,late',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $instructor = $request->get('user');

        $group = Group::where('id', $groupId)
                     ->where('instructor_id', $instructor->id)
                     ->with(['enrollments.student'])
                     ->first();

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Grup bulunamadı'
            ], 404);
        }

        $today = now()->format('Y-m-d');

        // Mevcut yoklamayı kontrol et
        $existingAttendance = Attendance::where('group_id', $groupId)
                                       ->where('lesson_date', $today)
                                       ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Bugün için yoklama zaten alınmış'
            ], 400);
        }

        // Her öğrenci için yoklama kaydı oluştur
        $attendanceRecords = [];
        foreach ($request->attendance as $studentId => $status) {
            $attendanceRecords[] = [
                'student_id' => $studentId,
                'group_id' => $groupId,
                'instructor_id' => $instructor->id,
                'lesson_date' => $today,
                'lesson_start_time' => $group->start_time,
                'lesson_end_time' => $group->end_time,
                'status' => $status,
                'attendance_taken_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Toplu olarak yoklama kayıtlarını oluştur
        Attendance::insert($attendanceRecords);

        $presentCount = count(array_filter($request->attendance, function($value) {
            return $value === 'present';
        }));
        
        $absentCount = count(array_filter($request->attendance, function($value) {
            return $value === 'absent';
        }));
        
        $lateCount = count(array_filter($request->attendance, function($value) {
            return $value === 'late';
        }));

        return response()->json([
            'success' => true,
            'message' => 'Yoklama başarıyla kaydedildi',
            'data' => [
                'date' => $today,
                'student_count' => count($request->attendance),
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'late_count' => $lateCount,
            ]
        ]);
    }
}
