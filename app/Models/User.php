<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'first_name',
        'last_name',
        'address',
        'profession',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // İlişkiler
    public function groups()
    {
        return $this->hasMany(Group::class, 'instructor_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'instructor_id');
    }

    // Role kontrol metodları
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isInstructor()
    {
        return $this->role === 'instructor';
    }

    public function isParent()
    {
        return $this->role === 'parent';
    }

    // Accessor'lar
    public function getFullNameAttribute()
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        return $this->name;
    }

    // Mutator'lar
    public function setNameAttribute($value)
    {
        if ($this->first_name && $this->last_name) {
            $this->attributes['name'] = $this->first_name . ' ' . $this->last_name;
        } else {
            $this->attributes['name'] = $value;
        }
    }

    // Scope'lar
    public function scopeInstructors($query)
    {
        return $query->where('role', 'instructor');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
