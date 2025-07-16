<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Teacher extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'user_id',
        'employee_id',
        'department',
        'subject_specialization',
        'employment_type',
        'teacher_level',
        'hire_date',
        'qualification',
        'years_experience',
        'subjects_taught',
        'class_assignments',
        'permissions',
        'last_login_at',
        'is_active',
    ];

    protected $casts = [
        'subjects_taught' => 'array',
        'class_assignments' => 'array',
        'permissions' => 'array',
        'hire_date' => 'date',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isDepartmentHead()
    {
        return $this->teacher_level === 'department_head';
    }

    public function isHeadTeacher()
    {
        return $this->teacher_level === 'head_teacher';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }
}
