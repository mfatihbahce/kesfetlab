<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'capacity',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'capacity' => 'integer',
    ];

    // İlişkiler
    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Scope'lar
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessor'lar
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    // Mutator'lar
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucfirst(strtolower($value));
    }
}
