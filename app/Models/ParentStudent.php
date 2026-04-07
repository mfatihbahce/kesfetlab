<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ParentStudent extends Pivot
{
    protected $table = 'parent_students';

    protected $fillable = [
        'parent_user_id',
        'student_id',
        'relationship',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Veli
     */
    public function parent()
    {
        return $this->belongsTo(ParentUser::class, 'parent_user_id');
    }

    /**
     * Öğrenci
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
