<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class ParentUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'password',
        'tc_identity',
        'address',
        'status',
        'temp_code',
        'password_changed',
        'api_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_changed' => 'boolean',
    ];

    /**
     * Veliye ait öğrenciler
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'parent_students', 'parent_user_id', 'student_id')
                    ->withPivot('relationship', 'is_primary')
                    ->withTimestamps();
    }

    /**
     * Veli-öğrenci ilişkileri
     */
    public function parentStudents()
    {
        return $this->hasMany(ParentStudent::class);
    }

    /**
     * Aktif veliler
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * API token oluştur
     */
    public function createToken($name = 'parent-token')
    {
        $token = Str::random(60);
        $this->update(['api_token' => $token]);
        return (object) ['plainTextToken' => $token];
    }

    /**
     * Token'ları temizle
     */
    public function tokens()
    {
        return new class($this) {
            private $user;
            
            public function __construct($user) {
                $this->user = $user;
            }
            
            public function delete() {
                $this->user->update(['api_token' => null]);
            }
        };
    }
}
