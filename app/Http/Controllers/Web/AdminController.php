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
use Illuminate\Support\Facades\DB;
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

    public function createGroup()
    {
        $workshops = Workshop::active()->orderBy('name')->get();
        $instructors = User::instructors()->active()->orderBy('name')->get();

        return view('admin.group_create', compact('workshops', 'instructors'));
    }

    public function calendar(Request $request)
    {
        $workshops = Workshop::orderBy('name')->get(['id', 'name']);
        $selectedWorkshopId = $request->query('workshop_id');

        return view('admin.calendar', compact('workshops', 'selectedWorkshopId'));
    }

    public function calendarEvents(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'workshop_id' => 'nullable|exists:workshops,id',
        ]);

        $rangeStart = \Carbon\Carbon::parse($request->start)->startOfDay();
        $rangeEnd = \Carbon\Carbon::parse($request->end)->endOfDay();

        $groupsQuery = Group::with(['workshop', 'instructor']);
        if ($request->filled('workshop_id')) {
            $groupsQuery->where('workshop_id', $request->workshop_id);
        }

        $groups = $groupsQuery->get();
        $events = [];
        $rawSlots = [];

        foreach ($groups as $group) {
            $selectedDays = $group->day_of_weeks;
            if (empty($selectedDays) && !empty($group->day_of_week)) {
                $selectedDays = [$group->day_of_week];
            }

            if (empty($selectedDays)) {
                continue;
            }

            $daySchedules = $group->day_schedules ?? [];
            $cursor = $rangeStart->copy();

            while ($cursor->lte($rangeEnd)) {
                $englishDay = strtolower($cursor->englishDayOfWeek);
                if (!in_array($englishDay, $selectedDays, true)) {
                    $cursor->addDay();
                    continue;
                }
                if ($group->group_start_date && $cursor->lt($group->group_start_date->copy()->startOfDay())) {
                    $cursor->addDay();
                    continue;
                }
                if ($group->group_end_date && $cursor->gt($group->group_end_date->copy()->endOfDay())) {
                    $cursor->addDay();
                    continue;
                }

                $start = data_get($daySchedules, $englishDay . '.start', optional($group->start_time)->format('H:i'));
                $end = data_get($daySchedules, $englishDay . '.end', optional($group->end_time)->format('H:i'));
                if (!$start || !$end) {
                    $cursor->addDay();
                    continue;
                }

                $event = [
                    'title' => ($group->workshop->name ?? 'Sınıf') . ' - ' . $group->name,
                    'start' => $cursor->toDateString() . 'T' . $start . ':00',
                    'end' => $cursor->toDateString() . 'T' . $end . ':00',
                    'backgroundColor' => '#1f6feb',
                    'borderColor' => '#1f6feb',
                    'extendedProps' => [
                        'group_id' => $group->id,
                        'group_name' => $group->name,
                        'workshop_id' => $group->workshop_id,
                        'workshop_name' => $group->workshop->name ?? '-',
                        'instructor_id' => $group->instructor_id,
                        'instructor_name' => $group->instructor->name ?? 'Atanmamış',
                    ],
                ];
                $events[] = $event;
                $rawSlots[] = $event;

                $cursor->addDay();
            }
        }

        for ($i = 0; $i < count($rawSlots); $i++) {
            for ($j = $i + 1; $j < count($rawSlots); $j++) {
                $a = $rawSlots[$i];
                $b = $rawSlots[$j];

                $sameDate = substr($a['start'], 0, 10) === substr($b['start'], 0, 10);
                if (!$sameDate) {
                    continue;
                }

                $overlap = strtotime($a['start']) < strtotime($b['end']) && strtotime($b['start']) < strtotime($a['end']);
                if (!$overlap) {
                    continue;
                }

                $sameInstructor = data_get($a, 'extendedProps.instructor_id') === data_get($b, 'extendedProps.instructor_id');
                $sameWorkshop = data_get($a, 'extendedProps.workshop_id') === data_get($b, 'extendedProps.workshop_id');

                if ($sameInstructor || $sameWorkshop) {
                    $events[$i]['backgroundColor'] = '#d73a49';
                    $events[$i]['borderColor'] = '#d73a49';
                    $events[$i]['extendedProps']['conflict'] = true;
                    $events[$j]['backgroundColor'] = '#d73a49';
                    $events[$j]['borderColor'] = '#d73a49';
                    $events[$j]['extendedProps']['conflict'] = true;
                }
            }
        }

        return response()->json($events);
    }

    public function editGroup($id)
    {
        $group = Group::with(['workshop', 'instructor'])->findOrFail($id);
        $workshops = Workshop::active()->orderBy('name')->get();
        $instructors = User::instructors()->active()->orderBy('name')->get();

        return view('admin.group_edit', compact('group', 'workshops', 'instructors'));
    }

    public function checkGroupConflicts(Request $request)
    {
        $request->validate([
            'workshop_id' => 'required|exists:workshops,id',
            'instructor_id' => 'required|exists:users,id',
            'day_of_weeks' => 'required|array|min:1',
            'day_of_weeks.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'day_schedules' => 'required|array',
            'group_start_date' => 'nullable|date',
            'group_end_date' => 'nullable|date|after_or_equal:group_start_date',
            'ignore_group_id' => 'nullable|exists:groups,id',
        ]);

        $schedule = $this->normalizeDaySchedules(
            $request->input('day_schedules', []),
            $request->input('day_of_weeks', []),
            null,
            null
        );

        $conflicts = $this->findGroupConflicts(
            (int) $request->workshop_id,
            (int) $request->instructor_id,
            $request->day_of_weeks,
            $schedule,
            $request->group_start_date,
            $request->group_end_date,
            $request->ignore_group_id
        );

        return response()->json([
            'has_conflict' => count($conflicts) > 0,
            'conflicts' => $conflicts,
        ]);
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
        $startOfMonth = $group->group_start_date ? $group->group_start_date->copy()->startOfMonth() : $now->copy()->startOfMonth();
        $endOfMonth = $group->group_end_date ? $group->group_end_date->copy()->endOfMonth() : $now->copy()->endOfMonth();
        $selectedDays = $group->day_of_weeks;
        if (empty($selectedDays) && !empty($group->day_of_week)) {
            $selectedDays = [$group->day_of_week];
        }

        $daySchedules = $group->day_schedules ?? [];
        $cursor = $startOfMonth->copy();
        while ($cursor->lte($endOfMonth)) {
            $englishDay = strtolower($cursor->englishDayOfWeek);
            if (in_array($englishDay, $selectedDays ?? [], true)) {
                if ($group->group_start_date && $cursor->lt($group->group_start_date)) {
                    $cursor->addDay();
                    continue;
                }
                if ($group->group_end_date && $cursor->gt($group->group_end_date)) {
                    $cursor->addDay();
                    continue;
                }
                $start = data_get($daySchedules, $englishDay . '.start', optional($group->start_time)->format('H:i'));
                $end = data_get($daySchedules, $englishDay . '.end', optional($group->end_time)->format('H:i'));
                $events[] = [
                    'title' => $group->name . ' Dersi',
                    'start' => $cursor->toDateString() . 'T' . $start . ':00',
                    'end'   => $cursor->toDateString() . 'T' . $end . ':00',
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
            $daySchedules = $group->day_schedules ?? [];
            $selectedDays = $group->day_of_weeks;
            if (empty($selectedDays) && !empty($group->day_of_week)) {
                $selectedDays = [$group->day_of_week];
            }
            $cursor = $startOfMonth->copy();
            while ($cursor->lte($endOfMonth)) {
                $englishDay = strtolower($cursor->englishDayOfWeek);
                if (in_array($englishDay, $selectedDays ?? [], true)) {
                    if ($group->group_start_date && $cursor->lt($group->group_start_date)) {
                        $cursor->addDay();
                        continue;
                    }
                    if ($group->group_end_date && $cursor->gt($group->group_end_date)) {
                        $cursor->addDay();
                        continue;
                    }
                    $start = data_get($daySchedules, $englishDay . '.start', optional($group->start_time)->format('H:i'));
                    $end = data_get($daySchedules, $englishDay . '.end', optional($group->end_time)->format('H:i'));
                    $events[] = [
                        'title' => $group->name . ' - ' . $group->workshop->name,
                        'start' => $cursor->toDateString() . 'T' . $start . ':00',
                        'end'   => $cursor->toDateString() . 'T' . $end . ':00',
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
        $this->graduateExpiredGroupEnrollments();

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
            'status' => 'required|in:active,inactive',
        ]);
        Workshop::create($request->only(['name', 'description', 'capacity', 'status']));

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
            'status' => 'required|in:active,inactive',
        ]);

        $workshop = Workshop::findOrFail($id);
        $workshop->update($request->only(['name', 'description', 'capacity', 'status']));

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
            'day_of_weeks' => 'required|array|min:1',
            'day_of_weeks.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'day_schedules' => 'nullable|array',
            'group_start_date' => 'nullable|date',
            'group_end_date' => 'nullable|date|after_or_equal:group_start_date',
            'status' => 'required|in:active,inactive,full',
            'description' => 'nullable|string',
        ]);
        $validatedDaySchedules = $this->normalizeDaySchedules(
            $request->input('day_schedules', []),
            $request->input('day_of_weeks', []),
            $request->input('start_time'),
            $request->input('end_time')
        );

        $payload = $request->only([
            'name',
            'workshop_id',
            'instructor_id',
            'capacity',
            'day_of_weeks',
            'day_schedules',
            'start_time',
            'end_time',
            'group_start_date',
            'group_end_date',
            'status',
            'description',
        ]);
        $payload['day_of_week'] = $payload['day_of_weeks'][0];
        $payload['day_schedules'] = $validatedDaySchedules;
        $firstSchedule = reset($validatedDaySchedules);
        if ($firstSchedule) {
            $payload['start_time'] = $firstSchedule['start'];
            $payload['end_time'] = $firstSchedule['end'];
        }
        Group::create($payload);

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
            'day_of_weeks' => 'required|array|min:1',
            'day_of_weeks.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'day_schedules' => 'nullable|array',
            'group_start_date' => 'nullable|date',
            'group_end_date' => 'nullable|date|after_or_equal:group_start_date',
            'status' => 'required|in:active,inactive,full',
            'description' => 'nullable|string',
        ]);
        $validatedDaySchedules = $this->normalizeDaySchedules(
            $request->input('day_schedules', []),
            $request->input('day_of_weeks', []),
            $request->input('start_time'),
            $request->input('end_time')
        );

        $group = Group::findOrFail($id);

		// Eski program bilgilerini sakla
		$oldDay = implode(',', $group->day_of_weeks ?? [$group->day_of_week]);
		$oldStart = optional($group->start_time)->format('H:i');
		$oldEnd = optional($group->end_time)->format('H:i');

		$payload = $request->only([
            'name',
            'workshop_id',
            'instructor_id',
            'capacity',
            'day_of_weeks',
            'day_schedules',
            'start_time',
            'end_time',
            'group_start_date',
            'group_end_date',
            'status',
            'description',
        ]);
        $payload['day_of_week'] = $payload['day_of_weeks'][0];
        $payload['day_schedules'] = $validatedDaySchedules;
        $firstSchedule = reset($validatedDaySchedules);
        if ($firstSchedule) {
            $payload['start_time'] = $firstSchedule['start'];
            $payload['end_time'] = $firstSchedule['end'];
        }
		$group->update($payload);

		// Program değişti mi? Değiştiyse duyuru/notification oluştur
		$newDay = implode(',', $group->day_of_weeks ?? [$group->day_of_week]);
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
				$oldDay, $oldStart, $oldEnd,
				$newDay, $newStart, $newEnd
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

    private function normalizeDaySchedules(array $daySchedules, array $selectedDays, ?string $defaultStart, ?string $defaultEnd): array
    {
        $normalized = [];

        foreach ($selectedDays as $day) {
            $start = data_get($daySchedules, $day . '.start', $defaultStart);
            $end = data_get($daySchedules, $day . '.end', $defaultEnd);

            if (empty($start) || empty($end)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'day_schedules' => ['Seçilen her gün için başlangıç ve bitiş saati girilmelidir.'],
                ]);
            }

            if (!$this->isTimeRangeValid($start, $end)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'day_schedules' => ['Bitiş saati başlangıç saatinden sonra olmalıdır.'],
                ]);
            }

            $normalized[$day] = [
                'start' => $start,
                'end' => $end,
            ];
        }

        return $normalized;
    }

    private function isTimeRangeValid(string $start, string $end): bool
    {
        return strtotime($start) < strtotime($end);
    }

    private function isDateRangeOverlapping(?string $aStart, ?string $aEnd, ?string $bStart, ?string $bEnd): bool
    {
        $aS = $aStart ? strtotime($aStart) : strtotime('1900-01-01');
        $aE = $aEnd ? strtotime($aEnd) : strtotime('2999-12-31');
        $bS = $bStart ? strtotime($bStart) : strtotime('1900-01-01');
        $bE = $bEnd ? strtotime($bEnd) : strtotime('2999-12-31');

        return $aS <= $bE && $bS <= $aE;
    }

    private function isTimeRangeOverlapping(string $aStart, string $aEnd, string $bStart, string $bEnd): bool
    {
        return strtotime($aStart) < strtotime($bEnd) && strtotime($bStart) < strtotime($aEnd);
    }

    private function findGroupConflicts(
        int $workshopId,
        int $instructorId,
        array $dayOfWeeks,
        array $daySchedules,
        ?string $groupStartDate,
        ?string $groupEndDate,
        ?int $ignoreGroupId
    ): array {
        $query = Group::query();
        if ($ignoreGroupId) {
            $query->where('id', '!=', $ignoreGroupId);
        }

        $candidates = $query->where(function ($q) use ($workshopId, $instructorId) {
            $q->where('workshop_id', $workshopId)
              ->orWhere('instructor_id', $instructorId);
        })->get();

        $conflicts = [];
        $dayLabels = [
            'monday' => 'Pazartesi',
            'tuesday' => 'Salı',
            'wednesday' => 'Çarşamba',
            'thursday' => 'Perşembe',
            'friday' => 'Cuma',
            'saturday' => 'Cumartesi',
            'sunday' => 'Pazar',
        ];

        foreach ($candidates as $candidate) {
            if (!$this->isDateRangeOverlapping(
                $groupStartDate,
                $groupEndDate,
                optional($candidate->group_start_date)->format('Y-m-d'),
                optional($candidate->group_end_date)->format('Y-m-d')
            )) {
                continue;
            }

            $candidateDays = $candidate->day_of_weeks ?? [$candidate->day_of_week];
            $commonDays = array_values(array_intersect($dayOfWeeks, $candidateDays));
            if (empty($commonDays)) {
                continue;
            }

            $candidateSchedules = $candidate->day_schedules ?? [];
            foreach ($commonDays as $day) {
                $newSlot = $daySchedules[$day] ?? null;
                $existingStart = data_get($candidateSchedules, $day . '.start', optional($candidate->start_time)->format('H:i'));
                $existingEnd = data_get($candidateSchedules, $day . '.end', optional($candidate->end_time)->format('H:i'));

                if (!$newSlot || !$existingStart || !$existingEnd) {
                    continue;
                }

                if ($this->isTimeRangeOverlapping($newSlot['start'], $newSlot['end'], $existingStart, $existingEnd)) {
                    $reason = [];
                    if ((int) $candidate->instructor_id === $instructorId) {
                        $reason[] = 'aynı eğitmen';
                    }
                    if ((int) $candidate->workshop_id === $workshopId) {
                        $reason[] = 'aynı sınıf';
                    }
                    $conflicts[] = ($dayLabels[$day] ?? $day) . " | {$candidate->name} ({$existingStart}-{$existingEnd}) [" . implode(', ', $reason) . "]";
                    break;
                }
            }
        }

        return array_values(array_unique($conflicts));
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
            'status' => 'required|in:pending,approved,rejected,cancelled,graduated',
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
            'amount' => 'nullable|numeric|min:0',
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

    public function studentDetail($id)
    {
        $this->graduateExpiredGroupEnrollments();

        $student = Student::with([
            'enrollments.workshop',
            'enrollments.group.instructor',
        ])->findOrFail($id);

        $activeEnrollments = $student->enrollments
            ->whereIn('status', ['pending', 'approved'])
            ->sortByDesc('created_at');

        $graduatedEnrollments = $student->enrollments
            ->where('status', 'graduated')
            ->sortByDesc('updated_at');

        return view('admin.student_detail', compact('student', 'activeEnrollments', 'graduatedEnrollments'));
    }

    private function graduateExpiredGroupEnrollments(): void
    {
        $expiredGroupIds = Group::whereNotNull('group_end_date')
            ->whereDate('group_end_date', '<', now()->toDateString())
            ->pluck('id');

        if ($expiredGroupIds->isEmpty()) {
            return;
        }

        Enrollment::whereIn('group_id', $expiredGroupIds)
            ->whereIn('status', ['pending', 'approved'])
            ->update([
                'status' => 'graduated',
                'is_active' => false,
                'end_date' => now()->toDateString(),
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

    /**
     * Admin ve ayarlar disindaki tum verileri sifirla.
     */
    public function resetData(Request $request)
    {
        $request->validate([
            'confirm_text' => 'required|in:SIFIRLA',
        ], [
            'confirm_text.required' => 'Onay metni zorunludur.',
            'confirm_text.in' => 'Veri sifirlama icin kutuya SIFIRLA yazmalisiniz.',
        ]);

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Egitim ve kayit akisina ait tum veriler temizlenir.
            DB::table('group_attendances')->truncate();
            DB::table('attendances')->truncate();
            DB::table('notifications')->truncate();
            DB::table('enrollments')->truncate();
            DB::table('groups')->truncate();
            DB::table('workshops')->truncate();
            DB::table('parent_students')->truncate();
            DB::table('students')->truncate();
            DB::table('parent_users')->truncate();

            // Sadece admin harici kullanicilar temizlenir.
            DB::table('users')->where('role', '!=', 'admin')->delete();

            return redirect()->route('admin.settings')
                ->with('success', 'Veriler sifirlandi. Admin kullanicilari ve sistem ayarlari korundu.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.settings')
                ->with('error', 'Veri sifirlama sirasinda hata olustu: ' . $e->getMessage());
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
