<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_name',
        'grade_level',
        'strand',
        'track',
        'semester',
        'category',
        'is_active',
        'academic_year'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function facultyAssignments()
    {
        return $this->hasMany(FacultyAssignment::class);
    }

    public function gradeSubmissions()
    {
        return $this->hasMany(GradeSubmission::class);
    }

    // Simple scopes
    public function scopeForGrade($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get subjects for a student based on their grade level and strand/track
    public static function getSubjectsForStudent($gradeLevel, $strand = null, $track = null)
    {
        $query = self::active()->forGrade($gradeLevel);

        // For senior high school, filter by strand and track
        if ($strand) {
            $query->where(function($q) use ($strand, $track) {
                $q->whereNull('strand') // Core subjects for all strands
                  ->orWhere('strand', $strand);
                
                if ($track) {
                    $q->where(function($subQ) use ($track) {
                        $subQ->whereNull('track')
                             ->orWhere('track', $track);
                    });
                }
            });
        }

        return $query->orderBy('subject_name')->get();
    }

    public static function getSubjectsForEnrollee($enrollee)
    {
        return self::getSubjectsForStudent(
            $enrollee->grade_level_applied,
            $enrollee->strand_applied,
            $enrollee->track_applied
        );
    }
}
