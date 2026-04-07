<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'target_type',
        'target_id',
        'title',
        'message',
        'data',
        'sender_type',
        'sender_id',
        'status',
        'sent_at',
        'read_at',
        'send_sms',
        'send_email',
        'send_push',
    ];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'send_sms' => 'boolean',
        'send_email' => 'boolean',
        'send_push' => 'boolean',
    ];

    // İlişkiler
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function target()
    {
        switch ($this->target_type) {
            case 'student':
                return $this->belongsTo(Student::class, 'target_id');
            case 'group':
                return $this->belongsTo(Group::class, 'target_id');
            case 'instructor':
                return $this->belongsTo(User::class, 'target_id');
            default:
                return null;
        }
    }

    // Scope'lar
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByTarget($query, $targetType, $targetId = null)
    {
        $query->where('target_type', $targetType);
        
        if ($targetId) {
            $query->where('target_id', $targetId);
        }
        
        return $query;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // Accessor'lar
    public function getTypeTextAttribute()
    {
        $types = [
            'lesson_cancelled' => 'Ders İptali',
            'lesson_postponed' => 'Ders Ertelenmesi',
            'attendance_update' => 'Yoklama Güncellemesi',
            'announcement' => 'Duyuru',
            'payment_reminder' => 'Ödeme Hatırlatması',
        ];

        return $types[$this->type] ?? $this->type;
    }

    public function getTargetTypeTextAttribute()
    {
        $types = [
            'student' => 'Öğrenci',
            'parent' => 'Veli',
            'instructor' => 'Eğitmen',
            'group' => 'Grup',
            'all' => 'Tümü',
        ];

        return $types[$this->target_type] ?? $this->target_type;
    }

    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    public function getIsSentAttribute()
    {
        return $this->status === 'sent';
    }

    // Metodlar
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    // Static metodlar
    public static function createForGroup($groupId, $type, $title, $message, $data = [])
    {
        return self::create([
            'type' => $type,
            'target_type' => 'group',
            'target_id' => $groupId,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'sender_type' => 'system',
        ]);
    }

    public static function createForStudent($studentId, $type, $title, $message, $data = [])
    {
        return self::create([
            'type' => $type,
            'target_type' => 'student',
            'target_id' => $studentId,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'sender_type' => 'system',
        ]);
    }
}
