<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'group_id',
        'workshop_id',
        'status',
        'enrollment_date',
        'start_date',
        'end_date',
        'amount',
        'payment_status',
        'payment_date',
        'payment_notes',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
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

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    // Scope'lar
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // Accessor'lar
    public function getIsActiveEnrollmentAttribute()
    {
        return $this->is_active && $this->status === 'approved';
    }

    public function getPaymentStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Beklemede',
            'paid' => 'Ödendi',
            'partial' => 'Kısmi Ödeme',
            'refunded' => 'İade Edildi',
        ];

        return $statuses[$this->payment_status] ?? $this->payment_status;
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Beklemede',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            'cancelled' => 'İptal Edildi',
            'graduated' => 'Mezun',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}
