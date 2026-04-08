<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'tc_identity',
        'birth_date',
        'address',
        'school_name',
        'health_condition',
        'parent_first_name',
        'parent_last_name',
        'parent_phone',
        'parent_email',
        'parent_profession',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'registration_status',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    // İlişkiler
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'enrollments');
    }

    public function workshops()
    {
        return $this->belongsToMany(Workshop::class, 'enrollments');
    }

    // Scope'lar
    public function scopePending($query)
    {
        return $query->where('registration_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('registration_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('registration_status', 'rejected');
    }

    // Accessor'lar
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getParentFullNameAttribute()
    {
        return $this->parent_first_name . ' ' . $this->parent_last_name;
    }

    public function getAgeAttribute()
    {
        return $this->birth_date->age;
    }

    public function getRegistrationStatusTextAttribute()
    {
        return match($this->registration_status) {
            'pending' => 'Beklemede',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            default => 'Bilinmiyor'
        };
    }

    // Validation kuralları
    public static function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'tc_identity' => 'required|string|size:11|unique:students,tc_identity',
            'birth_date' => 'required|date|before:today',
            'address' => 'required|string',
            'school_name' => 'required|string|max:255',
            'parent_first_name' => 'required|string|max:255',
            'parent_last_name' => 'required|string|max:255',
            'parent_phone' => ['required', 'regex:/^0\d{10}$/'],
            'parent_email' => 'nullable|email|max:255',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => ['required', 'regex:/^0\d{10}$/'],
        ];
    }
}
