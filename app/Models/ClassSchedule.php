<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'teacher_id',
        'grade_level',
        'section',
        'academic_year',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean'
    ];

    // Relationships - interconnected with existing system
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id'); // Links to users table like grades
    }

    // Get students for this class schedule
    public function students()
    {
        return Student::where('grade_level', $this->grade_level)
                     ->where('section', $this->section)
                     ->where('academic_year', $this->academic_year)
                     ->where('is_active', true)
                     ->get();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForGradeSection($query, $gradeLevel, $section)
    {
        return $query->where('grade_level', $gradeLevel)->where('section', $section);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    // Helper methods
    public function getTimeRangeAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    public function getStudentCountAttribute()
    {
        return $this->students()->count();
    }

    // Check for schedule conflicts
    public static function hasConflict($teacherId, $dayOfWeek, $startTime, $endTime, $excludeId = null)
    {
        $query = self::where('teacher_id', $teacherId)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_active', true)
                    ->where(function($q) use ($startTime, $endTime) {
                        $q->whereBetween('start_time', [$startTime, $endTime])
                          ->orWhereBetween('end_time', [$startTime, $endTime])
                          ->orWhere(function($subQ) use ($startTime, $endTime) {
                              $subQ->where('start_time', '<=', $startTime)
                                   ->where('end_time', '>=', $endTime);
                          });
                    });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
