<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'group_id',
        'instructor_id',
        'lesson_date',
        'lesson_start_time',
        'lesson_end_time',
        'status',
        'excuse_note',
        'excuse_submitted_date',
        'makeup_lesson_date',
        'makeup_lesson_time',
        'makeup_lesson_attended',
        'attendance_taken_at',
        'notes',
    ];

    protected $casts = [
        'lesson_date' => 'date',
        'lesson_start_time' => 'datetime:H:i',
        'lesson_end_time' => 'datetime:H:i',
        'excuse_submitted_date' => 'date',
        'makeup_lesson_date' => 'date',
        'makeup_lesson_time' => 'datetime:H:i',
        'makeup_lesson_attended' => 'boolean',
        'attendance_taken_at' => 'datetime',
    ];

    // İlişkiler
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    // Scope'lar
    public function scopeByDate($query, $date)
    {
        return $query->where('lesson_date', $date);
    }

    public function scopeByGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->whereIn('status', ['absent', 'late']);
    }

    public function scopeExcused($query)
    {
        return $query->where('status', 'excused');
    }

    // Accessor'lar
    public function getStatusTextAttribute()
    {
        $statuses = [
            'present' => 'Geldi',
            'absent' => 'Gelmedi',
            'late' => 'Geç Geldi',
            'excused' => 'Mazeretli',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getHasExcuseAttribute()
    {
        return !empty($this->excuse_note);
    }

    public function getHasMakeupLessonAttribute()
    {
        return !empty($this->makeup_lesson_date);
    }

    // Metodlar
    public function markAsPresent()
    {
        $this->update([
            'status' => 'present',
            'attendance_taken_at' => now(),
        ]);
    }

    public function markAsAbsent()
    {
        $this->update([
            'status' => 'absent',
            'attendance_taken_at' => now(),
        ]);
    }

    public function markAsLate()
    {
        $this->update([
            'status' => 'late',
            'attendance_taken_at' => now(),
        ]);
    }

    public function markAsExcused($note = null)
    {
        $this->update([
            'status' => 'excused',
            'excuse_note' => $note,
            'attendance_taken_at' => now(),
        ]);
    }
}
