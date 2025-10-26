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
        'strand',
        'track',
        'academic_year',
        'assignment_type',
        'status',
        'assigned_date',
        'effective_date',
        'end_date',
        'notes',
        'student_count',
        'weekly_hours',
        'schedule_day',
        'schedule_start_time',
        'schedule_end_time',
        'room_name'
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'effective_date' => 'date',
        'end_date' => 'date',
        'schedule_start_time' => 'datetime:H:i',
        'schedule_end_time' => 'datetime:H:i'
    ];

    // Relationships - interconnected with existing system
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id'); // Links to teachers table
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

    // Get formatted schedule display
    public function getFormattedScheduleAttribute()
    {
        if (!$this->schedule_day || !$this->schedule_start_time || !$this->schedule_end_time) {
            return 'Not scheduled';
        }

        $startTime = $this->schedule_start_time ? $this->schedule_start_time->format('g:i A') : '';
        $endTime = $this->schedule_end_time ? $this->schedule_end_time->format('g:i A') : '';
        
        return "{$this->schedule_day} {$startTime} - {$endTime}";
    }

    // Get formatted schedule with room
    public function getFullScheduleAttribute()
    {
        $schedule = $this->formatted_schedule;
        if ($schedule === 'Not scheduled') {
            return $schedule;
        }
        
        $room = $this->room_name ? " | Room: {$this->room_name}" : '';
        return $schedule . $room;
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

    // Check if teacher is already assigned to this class (deprecated - now allows multiple schedules)
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

    // Simple method to get teacher assignments for a specific academic year
    public static function getTeacherAssignments($teacherId, $academicYear = null)
    {
        $academicYear = $academicYear ?: (date('Y') . '-' . (date('Y') + 1));
        
        return self::where('teacher_id', $teacherId)
                  ->where('academic_year', $academicYear)
                  ->where('status', 'active')
                  ->with(['subject', 'teacher.user'])
                  ->get();
    }

    // Model boot method for validation using Eloquent relationships
    protected static function boot()
    {
        parent::boot();
        
        // Auto-set academic year if not provided
        static::creating(function ($assignment) {
            if (!$assignment->academic_year) {
                $assignment->academic_year = date('Y') . '-' . (date('Y') + 1);
            }
            
            // Validate class adviser uniqueness (only one adviser per class)
            if ($assignment->assignment_type === 'class_adviser') {
                $existingAdviser = self::where('grade_level', $assignment->grade_level)
                                     ->where('section', $assignment->section)
                                     ->where('academic_year', $assignment->academic_year)
                                     ->where('assignment_type', 'class_adviser')
                                     ->where('status', 'active')
                                     ->with(['teacher.user'])
                                     ->first();
                
                if ($existingAdviser) {
                    throw new \Exception("Class adviser conflict: {$existingAdviser->teacher->user->name} is already assigned as adviser for {$assignment->grade_level} - {$assignment->section}.");
                }
            }
            
            // Check for duplicate subject assignments (prevent same teacher teaching same subject to same class)
            if ($assignment->assignment_type === 'subject_teacher') {
                $existingQuery = self::where('teacher_id', $assignment->teacher_id)
                                   ->where('subject_id', $assignment->subject_id)
                                   ->where('grade_level', $assignment->grade_level)
                                   ->where('section', $assignment->section)
                                   ->where('academic_year', $assignment->academic_year)
                                   ->where('status', 'active');

                // For Senior High School, also check strand and track
                if (in_array($assignment->grade_level, ['Grade 11', 'Grade 12'])) {
                    if ($assignment->strand) {
                        $existingQuery->where('strand', $assignment->strand);
                    }
                    if ($assignment->track) {
                        $existingQuery->where('track', $assignment->track);
                    }
                }

                $existingAssignment = $existingQuery->with(['teacher.user', 'subject'])->first();
                
                if ($existingAssignment) {
                    $classInfo = $assignment->grade_level . ' - ' . $assignment->section;
                    if ($assignment->strand) {
                        $classInfo .= ' (' . $assignment->strand;
                        if ($assignment->track) {
                            $classInfo .= ' - ' . $assignment->track;
                        }
                        $classInfo .= ')';
                    }
                    
                    throw new \Exception("Assignment conflict: {$existingAssignment->teacher->user->name} is already assigned to teach {$existingAssignment->subject->subject_name} for {$classInfo}.");
                }
            }
        });
        
        // Validate on update as well
        static::updating(function ($assignment) {
            // Validate class adviser uniqueness on update
            if ($assignment->assignment_type === 'class_adviser') {
                $existingAdviser = self::where('grade_level', $assignment->grade_level)
                                     ->where('section', $assignment->section)
                                     ->where('academic_year', $assignment->academic_year)
                                     ->where('assignment_type', 'class_adviser')
                                     ->where('status', 'active')
                                     ->where('id', '!=', $assignment->id) // Exclude current record
                                     ->with(['teacher.user'])
                                     ->first();
                
                if ($existingAdviser) {
                    throw new \Exception("Class adviser conflict: {$existingAdviser->teacher->user->name} is already assigned as adviser for {$assignment->grade_level} - {$assignment->section}.");
                }
            }
            
            // Validate duplicate subject assignments on update
            if ($assignment->assignment_type === 'subject_teacher') {
                $existingAssignment = self::where('teacher_id', $assignment->teacher_id)
                                        ->where('subject_id', $assignment->subject_id)
                                        ->where('grade_level', $assignment->grade_level)
                                        ->where('section', $assignment->section)
                                        ->where('academic_year', $assignment->academic_year)
                                        ->where('status', 'active')
                                        ->where('id', '!=', $assignment->id) // Exclude current record
                                        ->with(['teacher.user', 'subject'])
                                        ->first();
                
                if ($existingAssignment) {
                    throw new \Exception("Assignment conflict: {$existingAssignment->teacher->user->name} is already assigned to teach {$existingAssignment->subject->subject_name} for {$assignment->grade_level} - {$assignment->section}.");
                }
            }
        });
    }
}
