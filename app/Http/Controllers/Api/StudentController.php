<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Online form üzerinden öğrenci kaydı
     */
    public function store(Request $request): JsonResponse
    {
        // Validation
        $validator = Validator::make($request->all(), Student::rules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Öğrenci kaydını oluştur
            $student = Student::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Öğrenci kaydı başarıyla oluşturuldu',
                'data' => [
                    'student_id' => $student->id,
                    'tc_identity' => $student->tc_identity,
                    'full_name' => $student->full_name,
                    'registration_status' => $student->registration_status,
                    'registration_date' => $student->created_at->format('d.m.Y H:i')
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kayıt oluşturulurken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * T.C. kimlik numarası ile öğrenci sorgulama (veli uygulaması için)
     */
    public function findByTcIdentity(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tc_identity' => 'required|string|size:11'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $student = Student::where('tc_identity', $request->tc_identity)
                         ->with(['enrollments.group.workshop', 'enrollments.group.instructor'])
                         ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Bu T.C. kimlik numarası ile kayıtlı öğrenci bulunamadı'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'full_name' => $student->full_name,
                    'tc_identity' => $student->tc_identity,
                    'birth_date' => $student->birth_date->format('d.m.Y'),
                    'age' => $student->age,
                    'parent_full_name' => $student->parent_full_name,
                    'parent_phone' => $student->parent_phone,
                    'registration_status' => $student->registration_status
                ],
                'enrollments' => $student->enrollments->map(function ($enrollment) {
                    return [
                        'id' => $enrollment->id,
                        'workshop_name' => $enrollment->workshop->name,
                        'group_name' => $enrollment->group->name,
                        'instructor_name' => $enrollment->group->instructor->full_name,
                        'schedule' => $enrollment->group->schedule,
                        'status' => $enrollment->status,
                        'payment_status' => $enrollment->payment_status,
                        'start_date' => $enrollment->start_date->format('d.m.Y'),
                        'amount' => $enrollment->amount
                    ];
                })
            ]
        ]);
    }

    /**
     * Öğrenci listesi (yönetici paneli için)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::query();

        // Filtreleme
        if ($request->has('status')) {
            $query->where('registration_status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('tc_identity', 'like', "%{$search}%")
                  ->orWhere('parent_first_name', 'like', "%{$search}%")
                  ->orWhere('parent_last_name', 'like', "%{$search}%");
            });
        }

        $students = $query->with(['enrollments.group.workshop'])
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    /**
     * Öğrenci detayı (yönetici paneli için)
     */
    public function show($id): JsonResponse
    {
        $student = Student::with(['enrollments.group.workshop', 'enrollments.group.instructor'])
                         ->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Öğrenci bulunamadı'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $student
        ]);
    }

    /**
     * Öğrenci kayıt durumunu güncelle (yönetici paneli için)
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'registration_status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Öğrenci bulunamadı'
            ], 404);
        }

        $student->update($request->only(['registration_status', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Öğrenci durumu güncellendi',
            'data' => $student
        ]);
    }
}
