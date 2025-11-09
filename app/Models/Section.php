<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'section_name',
        'grade_level',
        'academic_year',
        'max_students',
        'current_students',
        'is_active',
        'description'
    ];

    /**
     * Boot the model and set up model events
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set academic year when creating a new section
        static::creating(function ($section) {
            if (empty($section->academic_year)) {
                $currentYear = date('Y');
                $section->academic_year = $currentYear . '-' . ($currentYear + 1);
            }
        });
    }

    protected $casts = [
        'is_active' => 'boolean',
        'max_students' => 'integer',
        'current_students' => 'integer'
    ];

    /**
     * Get students in this section
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'section_id');
    }

    /**
     * Get class schedules for this section
     */
    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class, 'section_id');
    }

    /**
     * Get faculty assignments for this section
     */
    public function facultyAssignments()
    {
        return $this->hasMany(FacultyAssignment::class, 'section_id');
    }

    /**
     * Check if section has available slots
     */
    public function hasAvailableSlots()
    {
        return $this->current_students < $this->max_students;
    }

    /**
     * Get available slots count
     */
    public function getAvailableSlotsAttribute()
    {
        return $this->max_students - $this->current_students;
    }

    /**
     * Increment current students count
     */
    public function addStudent($allowOverflow = false)
    {
        if ($this->hasAvailableSlots() || $allowOverflow) {
            $this->increment('current_students');
            return true;
        }
        return false;
    }

    /**
     * Decrement current students count
     */
    public function removeStudent()
    {
        if ($this->current_students > 0) {
            $this->decrement('current_students');
            return true;
        }
        return false;
    }

    /**
     * Scope for active sections
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific grade level
     */
    public function scopeForGrade($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * Scope for current academic year
     */
    public function scopeCurrentYear($query)
    {
        $currentYear = date('Y');
        $academicYear = $currentYear . '-' . ($currentYear + 1);
        return $query->where('academic_year', $academicYear);
    }
}
