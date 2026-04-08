<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'workshop_id',
        'instructor_id',
        'capacity',
        'current_enrollment',
        'day_of_week',
        'day_of_weeks',
        'day_schedules',
        'start_time',
        'end_time',
        'group_start_date',
        'group_end_date',
        'status',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'day_of_weeks' => 'array',
        'day_schedules' => 'array',
        'group_start_date' => 'date',
        'group_end_date' => 'date',
        'capacity' => 'integer',
        'current_enrollment' => 'integer',
    ];

    // İlişkiler
    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments');
    }

    // Scope'lar
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
                    ->whereRaw('current_enrollment < capacity');
    }

    public function scopeByDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    // Accessor'lar
    public function getIsFullAttribute()
    {
        return $this->current_enrollment >= $this->capacity;
    }

    public function getAvailableSlotsAttribute()
    {
        return $this->capacity - $this->current_enrollment;
    }

    public function getScheduleAttribute()
    {
        $days = [
            'monday' => 'Pazartesi',
            'tuesday' => 'Salı',
            'wednesday' => 'Çarşamba',
            'thursday' => 'Perşembe',
            'friday' => 'Cuma',
            'saturday' => 'Cumartesi',
            'sunday' => 'Pazar',
        ];
        $selectedDays = $this->day_of_weeks;
        if (empty($selectedDays) && !empty($this->day_of_week)) {
            $selectedDays = [$this->day_of_week];
        }

        $scheduleMap = $this->day_schedules ?? [];
        $timePart = collect($selectedDays ?? [])
            ->map(function ($day) use ($days, $scheduleMap) {
                $label = $days[$day] ?? $day;
                $slot = $scheduleMap[$day] ?? null;
                if (is_array($slot) && !empty($slot['start']) && !empty($slot['end'])) {
                    return $label . ' ' . $slot['start'] . '-' . $slot['end'];
                }
                return $label;
            })
            ->implode(', ');

        if ($this->group_start_date && $this->group_end_date) {
            return $timePart . ' | ' . $this->group_start_date->format('d.m.Y') . ' - ' . $this->group_end_date->format('d.m.Y');
        }

        return $timePart;
    }

    // Metodlar
    public function incrementEnrollment()
    {
        $this->increment('current_enrollment');
        $this->refresh();
        
        if ($this->isFull) {
            $this->update(['status' => 'full']);
        }
    }

    public function decrementEnrollment()
    {
        $this->decrement('current_enrollment');
        $this->refresh();
        
        if ($this->status === 'full' && !$this->isFull) {
            $this->update(['status' => 'active']);
        }
    }
}
