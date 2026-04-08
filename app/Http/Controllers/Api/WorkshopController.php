<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WorkshopController extends Controller
{
    /**
     * Aktif sınıfları listele (online form için)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Workshop::query();

        // Sadece aktif sınıfları getir
        if ($request->has('active_only') && $request->active_only) {
            $query->active();
        }

        $workshops = $query->with(['groups' => function ($query) {
            $query->available();
        }])
        ->orderBy('name')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $workshops
        ]);
    }

    /**
     * Sınıf detayı
     */
    public function show($id): JsonResponse
    {
        $workshop = Workshop::with(['groups.instructor', 'groups.students'])
                           ->find($id);

        if (!$workshop) {
            return response()->json([
                'success' => false,
                'message' => 'Sınıf bulunamadı'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $workshop
        ]);
    }

    /**
     * Yeni sınıf oluştur (yönetici paneli için)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $workshop = Workshop::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Sınıf başarıyla oluşturuldu',
                'data' => $workshop
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sınıf oluşturulurken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sınıf güncelle (yönetici paneli için)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $workshop = Workshop::find($id);

        if (!$workshop) {
            return response()->json([
                'success' => false,
                'message' => 'Sınıf bulunamadı'
            ], 404);
        }

        try {
            $workshop->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Sınıf başarıyla güncellendi',
                'data' => $workshop
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sınıf güncellenirken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sınıf sil (yönetici paneli için)
     */
    public function destroy($id): JsonResponse
    {
        $workshop = Workshop::find($id);

        if (!$workshop) {
            return response()->json([
                'success' => false,
                'message' => 'Sınıf bulunamadı'
            ], 404);
        }

        // Sınıfye kayıtlı öğrenci var mı kontrol et
        if ($workshop->enrollments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bu sınıfa kayıtlı öğrenciler bulunduğu için silinemez'
            ], 400);
        }

        try {
            $workshop->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sınıf başarıyla silindi'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sınıf silinirken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sınıf istatistikleri (yönetici paneli için)
     */
    public function statistics($id): JsonResponse
    {
        $workshop = Workshop::with(['groups.enrollments', 'enrollments'])
                           ->find($id);

        if (!$workshop) {
            return response()->json([
                'success' => false,
                'message' => 'Sınıf bulunamadı'
            ], 404);
        }

        $totalEnrollments = $workshop->enrollments()->count();
        $activeEnrollments = $workshop->enrollments()->where('status', 'approved')->count();
        $totalGroups = $workshop->groups()->count();
        $activeGroups = $workshop->groups()->where('status', 'active')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'workshop' => $workshop,
                'statistics' => [
                    'total_enrollments' => $totalEnrollments,
                    'active_enrollments' => $activeEnrollments,
                    'total_groups' => $totalGroups,
                    'active_groups' => $activeGroups,
                    'utilization_rate' => $totalGroups > 0 ? round(($activeGroups / $totalGroups) * 100, 2) : 0
                ]
            ]
        ]);
    }
}
