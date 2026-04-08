<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Workshop;
use App\Models\Group;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Notification;
use App\Models\AppSetting;
use App\Models\ParentUser;
use App\Models\ParentStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Yönetici paneli ana sayfası
     */
    public function dashboard()
    {
        // İstatistikler
        $stats = [
            'total_students' => Student::count(),
            'pending_students' => Student::pending()->count(),
            'approved_students' => Student::approved()->count(),
            'total_workshops' => Workshop::count(),
            'active_workshops' => Workshop::active()->count(),
            'total_groups' => Group::count(),
            'active_groups' => Group::active()->count(),
            'total_enrollments' => Enrollment::whereHas('student', function ($q) {
                $q->where('registration_status', 'approved');
            })->count(),
            'pending_enrollments' => Enrollment::whereHas('student', function ($q) {
                $q->where('registration_status', 'approved');
            })->where('status', 'pending')->count(),
        ];

        // Son ön kayıtlar (bekleyen öğrenciler)
        $recentStudents = Student::where('registration_status', 'pending')
                                ->with(['enrollments.workshop'])
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();

        // Son kayıtlar (onaylanmış öğrenciler)
        $recentEnrollments = Enrollment::whereHas('student', function ($q) {
            $q->where('registration_status', 'approved');
        })->with(['student', 'group.workshop'])
          ->orderBy('created_at', 'desc')
          ->limit(5)
          ->get();

        return view('admin.dashboard', compact('stats', 'recentStudents', 'recentEnrollments'));
    }

    /**
     * Öğrenci yönetimi sayfası (Ön Kayıtlar)
     */
    public function students(Request $request)
    {
        $query = Student::where('registration_status', 'pending'); // Sadece bekleyen öğrenciler

        // Filtreleme
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

        $students = $query->with(['enrollments.workshop'])
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        return view('admin.students', compact('students'));
    }

    /**
     * Sınıf yönetimi sayfası
     */
    public function workshops()
    {
        $workshops = Workshop::with(['groups.instructor', 'enrollments'])
                            ->orderBy('name')
                            ->get();

        return view('admin.workshops', compact('workshops'));
    }

    /**
     * Grup yönetimi sayfası
     */
    public function groups()
    {
        $groups = Group::with(['workshop', 'instructor', 'enrollments.student'])
                      ->withCount('enrollments')
                      ->orderBy('created_at', 'desc')
                      ->get();

        $workshops = Workshop::active()->get();
        $instructors = User::instructors()->active()->get();

        return view('admin.groups', compact('groups', 'workshops', 'instructors'));
    }

    /**
     * Grup detay sayfası
     */
    public function groupDetail($id)
    {
        $group = Group::with(['workshop', 'instructor', 'enrollments.student'])
                      ->findOrFail($id);

        // Duyurular
        $announcements = Notification::byTarget('group', $group->id)
                                      ->byType('announcement')
                                      ->orderBy('created_at', 'desc')
                                      ->limit(20)
                                      ->get();

        // Takvim etkinlikleri: mevcut ay için haftalık tekrar (grubun gün/saatine göre)
        $events = [];
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $cursor = $startOfMonth->copy();
        while ($cursor->lte($endOfMonth)) {
            if (strtolower($cursor->englishDayOfWeek) === $group->day_of_week) {
                $events[] = [
                    'title' => $group->name . ' Dersi',
                    'start' => $cursor->toDateString() . 'T' . $group->start_time->format('H:i') . ':00',
                    'end'   => $cursor->toDateString() . 'T' . $group->end_time->format('H:i') . ':00',
                ];
            }
            $cursor->addDay();
        }

        return view('admin.group_detail', [
            'group' => $group,
            'announcements' => $announcements,
            'events' => $events,
        ]);
    }

        /**
     * Grup için duyuru oluştur
     */
    public function storeGroupAnnouncement(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $group = Group::findOrFail($id);

        Notification::create([
            'type' => 'announcement',
            'target_type' => 'group',
            'target_id' => $group->id,
            'title' => $request->title,
            'message' => $request->message,
            'sender_type' => 'admin',
            'sender_id' => auth()->id(),
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return redirect()->route('admin.groups.detail', $group->id)
                        ->with('success', 'Duyuru paylaşıldı.');
    }

    /**
     * Eğitmen yönetimi sayfası
     */
    public function instructors()
    {
        $instructors = User::instructors()
                           ->with(['groups.workshop', 'groups.enrollments'])
                           ->withCount('groups')
                           ->orderBy('created_at', 'desc')
                           ->get();

        return view('admin.instructors', compact('instructors'));
    }

    /**
     * Eğitmen detay sayfası
     */
    public function instructorDetail($id)
    {
        $instructor = User::instructors()
                          ->with(['groups.workshop', 'groups.enrollments.student'])
                          ->findOrFail($id);

        // Eğitmenin verdiği dersler için takvim
        $events = [];
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        foreach ($instructor->groups as $group) {
            $cursor = $startOfMonth->copy();
            while ($cursor->lte($endOfMonth)) {
                if (strtolower($cursor->englishDayOfWeek) === $group->day_of_week) {
                    $events[] = [
                        'title' => $group->name . ' - ' . $group->workshop->name,
                        'start' => $cursor->toDateString() . 'T' . $group->start_time->format('H:i') . ':00',
                        'end'   => $cursor->toDateString() . 'T' . $group->end_time->format('H:i') . ':00',
                        'groupId' => $group->id,
                    ];
                }
                $cursor->addDay();
            }
        }

        return view('admin.instructor_detail', [
            'instructor' => $instructor,
            'events' => $events,
        ]);
    }

    /**
     * Yeni eğitmen oluştur
     */
    public function storeInstructor(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'profession' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'password' => 'required|string|min:4',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'profession' => $request->profession,
            'address' => $request->address,
            'role' => 'instructor',
            'password' => bcrypt($request->password),
            'is_active' => true,
        ]);

        return redirect()->route('admin.instructors')
                        ->with('success', 'Eğitmen başarıyla oluşturuldu. Giriş: Telefon + Şifre');
    }

    /**
     * Eğitmen güncelle
     */
    public function updateInstructor(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'profession' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:4',
        ]);

        $instructor = User::instructors()->findOrFail($id);
        
        $data = $request->except(['password']);
        
        // Şifre değiştirilecekse
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        
        $instructor->update($data);

        $message = 'Eğitmen başarıyla güncellendi.';
        if ($request->filled('password')) {
            $message .= ' Yeni şifre kaydedildi.';
        }

        return redirect()->route('admin.instructors')
                        ->with('success', $message);
    }

    /**
     * Eğitmen sil
     */
    public function deleteInstructor($id)
    {
        $instructor = User::instructors()->findOrFail($id);
        
        // Eğitmenin aktif grupları varsa silmeyi engelle
        if ($instructor->groups()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bu eğitmenin aktif grupları bulunduğu için silinemez.'
            ]);
        }

        $instructor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Eğitmen başarıyla silindi.'
        ]);
    }

    /**
     * Kayıt yönetimi sayfası (Onaylanmış Öğrenciler)
     */
    public function enrollments(Request $request)
    {
        $query = Enrollment::whereHas('student', function ($q) {
            $q->where('registration_status', 'approved'); // Sadece onaylanmış öğrenciler
        });

        // Filtreleme
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $enrollments = $query->with(['student', 'group.workshop', 'group.instructor'])
                            ->orderBy('created_at', 'desc')
                            ->paginate(20);

        // Grup atama için mevcut grupları getir
        $groups = Group::with(['workshop', 'instructor'])
                      ->active()
                      ->orderBy('name')
                      ->get();

        return view('admin.enrollments', compact('enrollments', 'groups'));
    }

    /**
     * Yeni sınıf oluştur
     */
    public function storeWorkshop(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        Workshop::create($request->all());

        return redirect()->route('admin.workshops')
                        ->with('success', 'Sınıf başarıyla oluşturuldu.');
    }

    /**
     * Sınıf güncelle
     */
    public function updateWorkshop(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $workshop = Workshop::findOrFail($id);
        $workshop->update($request->all());

        return redirect()->route('admin.workshops')
                        ->with('success', 'Sınıf başarıyla güncellendi.');
    }

    /**
     * Sınıf sil
     */
    public function deleteWorkshop($id)
    {
        $workshop = Workshop::findOrFail($id);
        
        // Eğer sınıfa kayıt varsa silmeyi engelle
        if ($workshop->enrollments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bu sınıfa kayıtlı öğrenciler bulunduğu için silinemez.'
            ]);
        }

        $workshop->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sınıf başarıyla silindi.'
        ]);
    }

    /**
     * Yeni grup oluştur
     */
    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'workshop_id' => 'required|exists:workshops,id',
            'instructor_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:active,inactive,full',
            'description' => 'nullable|string',
        ]);

        Group::create($request->all());

        return redirect()->route('admin.groups')
                        ->with('success', 'Grup başarıyla oluşturuldu.');
    }

    /**
     * Grup güncelle
     */
    public function updateGroup(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'workshop_id' => 'required|exists:workshops,id',
            'instructor_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:active,inactive,full',
            'description' => 'nullable|string',
        ]);

        $group = Group::findOrFail($id);

		// Eski program bilgilerini sakla
		$oldDay = $group->day_of_week;
		$oldStart = optional($group->start_time)->format('H:i');
		$oldEnd = optional($group->end_time)->format('H:i');

		$group->update($request->all());

		// Program değişti mi? Değiştiyse duyuru/notification oluştur
		$newDay = $group->day_of_week;
		$newStart = optional($group->start_time)->format('H:i');
		$newEnd = optional($group->end_time)->format('H:i');

		$dayMap = [
			'monday' => 'Pazartesi', 'tuesday' => 'Salı', 'wednesday' => 'Çarşamba',
			'thursday' => 'Perşembe', 'friday' => 'Cuma', 'saturday' => 'Cumartesi', 'sunday' => 'Pazar'
		];

		if ($oldDay !== $newDay || $oldStart !== $newStart || $oldEnd !== $newEnd) {
			$title = 'Grup Programı Güncellendi';
			$message = sprintf(
				"%s grubunun ders programı güncellendi. Eski: %s %s-%s, Yeni: %s %s-%s",
				$group->name,
				$dayMap[$oldDay] ?? $oldDay, $oldStart, $oldEnd,
				$dayMap[$newDay] ?? $newDay, $newStart, $newEnd
			);

			// Grup hedefli bir duyuru oluştur
			Notification::createForGroup($group->id, 'announcement', $title, $message);

			// Her öğrenci için bireysel bildirim kaydı oluştur
			$enrollments = $group->enrollments()->with('student')->get();
			foreach ($enrollments as $enrollment) {
				if ($enrollment->student) {
					Notification::createForStudent(
						$enrollment->student->id,
						'announcement',
						$title,
						$message,
						['group_id' => $group->id]
					);
				}
			}
		}

		return redirect()->route('admin.groups')
						->with('success', 'Grup başarıyla güncellendi.');
    }

    /**
     * Grup sil
     */
    public function deleteGroup($id)
    {
        $group = Group::findOrFail($id);
        
        // Eğer gruba kayıt varsa silmeyi engelle
        if ($group->enrollments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bu gruba kayıtlı öğrenciler bulunduğu için silinemez.'
            ]);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grup başarıyla silindi.'
        ]);
    }

    /**
     * Kayıt durumu güncelle
     */
    public function updateEnrollmentStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,cancelled',
        ]);

        $enrollment = Enrollment::findOrFail($id);
        $enrollment->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kayıt durumu başarıyla güncellendi.'
        ]);
    }

    /**
     * Grup atama
     */
    public function assignGroup(Request $request, $id)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        $enrollment = Enrollment::findOrFail($id);
        
        // Grubun kapasitesini kontrol et
        $group = Group::findOrFail($request->group_id);
        $currentEnrollments = $group->enrollments()->count();
        
        if ($currentEnrollments >= $group->capacity) {
            return response()->json([
                'success' => false,
                'message' => 'Bu grup dolu. Başka bir grup seçin.'
            ]);
        }

        $enrollment->update([
            'group_id' => $request->group_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Grup başarıyla atandı.'
        ]);
    }

    /**
     * Öğrenci durumu güncelle
     */
    public function updateStudentStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $student = Student::findOrFail($id);
        $student->update([
            'registration_status' => $request->status
        ]);

        // Eğer öğrenci onaylandıysa
        if ($request->status === 'approved') {
            // Veli kaydı oluştur
            $this->createParentFromStudent($student);
            
            // Enrollment durumunu da güncelle
            $student->enrollments()->update([
                'status' => 'pending' // Grup ataması bekliyor
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Öğrenci durumu başarıyla güncellendi.'
        ]);
    }

    /**
     * Öğrenci bilgilerinden veli kaydı oluştur
     */
    private function createParentFromStudent($student)
    {
        $parentPhone = $student->parent_phone;
        $parentFullName = $student->parent_full_name;
        
        // Veli zaten var mı kontrol et
        $parent = ParentUser::where('phone', $parentPhone)->first();
        
        if (!$parent) {
            // Yeni veli oluştur
            $tempCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT); // 6 haneli kod
            $tcIdentity = 'P' . str_pad($parentPhone, 10, '0', STR_PAD_LEFT);
            
            $parent = ParentUser::create([
                'full_name' => $parentFullName,
                'phone' => $parentPhone,
                'email' => null,
                'password' => bcrypt($tempCode), // Geçici şifre olarak kodu kullan
                'tc_identity' => $tcIdentity,
                'address' => null,
                'status' => 'active',
                'temp_code' => $tempCode,
                'password_changed' => false,
            ]);
        }

        // Veli-öğrenci ilişkisini oluştur (eğer yoksa)
        $existingRelation = ParentStudent::where('parent_user_id', $parent->id)
                                       ->where('student_id', $student->id)
                                       ->first();

        if (!$existingRelation) {
            ParentStudent::create([
                'parent_user_id' => $parent->id,
                'student_id' => $student->id,
                'relationship' => 'parent',
                'is_primary' => true,
            ]);
        }
    }

    /**
     * Kayıt ödeme durumu güncelle
     */
    public function updateEnrollmentPayment(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,partial,refunded',
            'payment_date' => 'nullable|date',
            'payment_notes' => 'nullable|string',
        ]);

        $enrollment = Enrollment::findOrFail($id);
        $enrollment->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Ödeme durumu başarıyla güncellendi.'
        ]);
    }



    /**
     * Veli yönetimi sayfası
     */
    public function parents(Request $request)
    {
        $query = ParentUser::query();

        // Filtreleme
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('tc_identity', 'like', "%{$search}%");
            });
        }

        $parents = $query->with(['students'])
                        ->withCount('students')
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);

        return view('admin.parents', compact('parents'));
    }

    /**
     * Veli detay sayfası
     */
    public function parentDetail($id)
    {
        $parent = ParentUser::with(['students.enrollments.group.workshop'])
                           ->findOrFail($id);

        return view('admin.parent-detail', compact('parent'));
    }

    /**
     * Veli için yeni kod üret
     */
    public function generateParentCode(Request $request, $id)
    {
        $parent = ParentUser::findOrFail($id);
        
        $tempCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $parent->update([
            'temp_code' => $tempCode,
            'password' => bcrypt($tempCode),
            'password_changed' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Yeni kod başarıyla üretildi.',
            'data' => [
                'temp_code' => $tempCode
            ]
        ]);
    }

    /**
     * Veli şifre değiştirildi durumunu sıfırla
     */
    public function resetParentPasswordChanged(Request $request, $id)
    {
        $parent = ParentUser::findOrFail($id);
        $parent->update([
            'password_changed' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Şifre değiştirildi durumu başarıyla sıfırlandı. Veli bir sonraki girişinde şifresini değiştirmek zorunda kalacaktır.'
        ]);
    }

    /**
     * Veli durumu güncelle
     */
    public function updateParentStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $parent = ParentUser::findOrFail($id);
        $parent->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Veli durumu başarıyla güncellendi.'
        ]);
    }

    /**
     * Veli-öğrenci ilişkisi ekle
     */
    public function addParentStudent(Request $request, $parentId)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'relationship' => 'required|in:parent,guardian,other',
        ]);

        // İlişki zaten var mı kontrol et
        $existingRelation = ParentStudent::where('parent_user_id', $parentId)
                                       ->where('student_id', $request->student_id)
                                       ->first();

        if ($existingRelation) {
            return response()->json([
                'success' => false,
                'message' => 'Bu ilişki zaten mevcut.'
            ], 400);
        }

        ParentStudent::create([
            'parent_user_id' => $parentId,
            'student_id' => $request->student_id,
            'relationship' => $request->relationship,
            'is_primary' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Öğrenci-veli ilişkisi başarıyla eklendi.'
        ]);
    }

    /**
     * Veli-öğrenci ilişkisi kaldır
     */
    public function removeParentStudent(Request $request, $parentId, $studentId)
    {
        ParentStudent::where('parent_user_id', $parentId)
                    ->where('student_id', $studentId)
                    ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Öğrenci-veli ilişkisi başarıyla kaldırıldı.'
        ]);
    }

    /**
     * Sistem ayarlari sayfasi.
     */
    public function settings()
    {
        $settings = [
            'brand_name' => AppSetting::getValue('brand_name', 'Kesfet LAB'),
            'brand_logo_path' => AppSetting::getValue('brand_logo_path'),
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Sistem ayarlarini kaydet.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string|max:80',
            'brand_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:5120',
        ], [
            'brand_name.required' => 'Marka adi alani zorunludur.',
            'brand_name.max' => 'Marka adi en fazla 80 karakter olabilir.',
            'brand_logo.image' => 'Yuklenen dosya bir gorsel olmalidir.',
            'brand_logo.mimes' => 'Logo yalnizca jpg, jpeg, png, webp veya svg formatinda olabilir.',
            'brand_logo.max' => 'Logo boyutu en fazla 5 MB olabilir.',
        ]);

        AppSetting::setValue('brand_name', $request->brand_name);

        if ($request->hasFile('brand_logo')) {
            $uploadDir = public_path('uploads/branding');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $oldPath = AppSetting::getValue('brand_logo_path');
            if ($oldPath) {
                $oldFullPath = public_path(ltrim($oldPath, '/'));
                if (File::exists($oldFullPath)) {
                    File::delete($oldFullPath);
                }
            }

            $logoFile = $request->file('brand_logo');
            $fileName = 'brand_logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
            $logoFile->move($uploadDir, $fileName);

            AppSetting::setValue('brand_logo_path', '/uploads/branding/' . $fileName);
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Ayarlar basariyla guncellendi.');
    }
}
