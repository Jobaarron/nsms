<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacultyAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'assigned_by',
        'grade_level',
        'section',
        'academic_year',
        'assignment_type',
        'status',
        'assigned_date',
        'effective_date',
        'end_date',
        'notes',
        'student_count',
        'weekly_hours'
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'effective_date' => 'date',
        'end_date' => 'date'
    ];

    // Relationships - interconnected with existing system
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id'); // Links to users table
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by'); // Faculty head who assigned
    }

    // Get students for this assignment
    public function students()
    {
        return Student::where('grade_level', $this->grade_level)
                     ->where('section', $this->section)
                     ->where('academic_year', $this->academic_year)
                     ->where('is_active', true)
                     ->get();
    }

    // Get class schedule for this assignment
    public function classSchedule()
    {
        return ClassSchedule::where('teacher_id', $this->teacher_id)
                           ->where('subject_id', $this->subject_id)
                           ->where('grade_level', $this->grade_level)
                           ->where('section', $this->section)
                           ->where('academic_year', $this->academic_year)
                           ->where('is_active', true)
                           ->first();
    }

    // Get grades for this assignment
    public function grades()
    {
        return Grade::where('teacher_id', $this->teacher_id)
                   ->where('subject_id', $this->subject_id)
                   ->where('academic_year', $this->academic_year)
                   ->whereIn('student_id', $this->students()->pluck('id'))
                   ->get();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    public function scopeSubjectTeachers($query)
    {
        return $query->where('assignment_type', 'subject_teacher');
    }

    public function scopeClassAdvisers($query)
    {
        return $query->where('assignment_type', 'class_adviser');
    }

    public function scopeForGradeSection($query, $gradeLevel, $section)
    {
        return $query->where('grade_level', $gradeLevel)->where('section', $section);
    }

    // Helper methods
    public function getStudentCountAttribute()
    {
        return $this->students()->count();
    }

    public function isClassAdviser()
    {
        return $this->assignment_type === 'class_adviser';
    }

    public function isSubjectTeacher()
    {
        return $this->assignment_type === 'subject_teacher';
    }

    // Get teacher's full teaching load
    public static function getTeacherLoad($teacherId, $academicYear = null)
    {
        $academicYear = $academicYear ?: (date('Y') . '-' . (date('Y') + 1));
        
        return self::active()
                  ->forTeacher($teacherId)
                  ->forAcademicYear($academicYear)
                  ->with(['subject', 'teacher'])
                  ->get();
    }

    // Check if teacher is already assigned to this class
    public static function isTeacherAssigned($teacherId, $subjectId, $gradeLevel, $section, $academicYear)
    {
        return self::where('teacher_id', $teacherId)
                  ->where('subject_id', $subjectId)
                  ->where('grade_level', $gradeLevel)
                  ->where('section', $section)
                  ->where('academic_year', $academicYear)
                  ->where('status', 'active')
                  ->exists();
    }
}
