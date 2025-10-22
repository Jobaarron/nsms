<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'department',
        'position',
        'specialization',
        'employment_status',
        'subjects',
        'hire_date',
        'phone_number',
        'address',
        'qualifications',
        'is_active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'is_active' => 'boolean',
        'subjects' => 'array',
    ];

    /**
     * Get the user that owns the teacher profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include active teachers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the teacher's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user->name;
    }

    /**
     * Get the teacher's email.
     */
    public function getEmailAttribute(): string
    {
        return $this->user->email;
    }

    /**
     * Get faculty assignments for this teacher
     */
    public function facultyAssignments()
    {
        return $this->hasMany(FacultyAssignment::class, 'teacher_id');
    }

    /**
     * Get class schedules for this teacher
     */
    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class, 'teacher_id');
    }

    /**
     * Get grade submissions for this teacher
     */
    public function gradeSubmissions()
    {
        return $this->hasMany(GradeSubmission::class, 'teacher_id');
    }

    /**
     * Get grades submitted by this teacher
     */
    public function grades()
    {
        return $this->hasMany(Grade::class, 'teacher_id');
    }
}
