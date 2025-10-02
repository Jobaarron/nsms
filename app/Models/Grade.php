<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'teacher_id',
        'academic_year',
        'quarter',
        'grade',
        'remarks',
        'submitted_at',
        'is_final'
    ];

    protected $casts = [
        'grade' => 'decimal:2',
        'is_final' => 'boolean',
        'submitted_at' => 'datetime'
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Scopes for easy querying
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForQuarter($query, $quarter)
    {
        return $query->where('quarter', $quarter);
    }

    public function scopeForAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    public function scopeFinalized($query)
    {
        return $query->where('is_final', true);
    }

    // Grade calculation methods
    public function isPassing()
    {
        return $this->grade >= 75.0;
    }

    public function getPassingStatus()
    {
        if (!$this->grade) return 'No Grade';
        return $this->isPassing() ? 'Passed' : 'Failed';
    }

    // Calculate final grade for Grades 1-10 (1st, 2nd, 3rd, 4th quarters → final grade → general average)
    public static function calculateFinalGradeElementaryJHS($studentId, $subjectId, $academicYear)
    {
        $grades = self::where('student_id', $studentId)
                     ->where('subject_id', $subjectId)
                     ->where('academic_year', $academicYear)
                     ->whereIn('quarter', ['1st', '2nd', '3rd', '4th'])
                     ->whereNotNull('grade')
                     ->pluck('grade');

        if ($grades->count() === 4) {
            return round($grades->avg(), 2); // This is the final grade
        }
        return null;
    }

    // Calculate general average for Grades 1-10 (average of all subject final grades)
    public static function calculateGeneralAverageElementaryJHS($studentId, $academicYear)
    {
        // Get all subjects for the student's grade level
        $student = \App\Models\Student::find($studentId);
        if (!$student) return null;

        $subjects = \App\Models\Subject::where('grade_level', $student->grade_level)
                                      ->where('academic_year', $academicYear)
                                      ->where('is_active', true)
                                      ->get();

        $finalGrades = [];
        foreach ($subjects as $subject) {
            $finalGrade = self::calculateFinalGradeElementaryJHS($studentId, $subject->id, $academicYear);
            if ($finalGrade !== null) {
                $finalGrades[] = $finalGrade;
            }
        }

        if (count($finalGrades) > 0) {
            return round(array_sum($finalGrades) / count($finalGrades), 2);
        }
        return null;
    }

    // Calculate semester grade for Senior High School (1st & 2nd grading OR 3rd & 4th grading)
    public static function calculateSemesterGradeSHS($studentId, $subjectId, $academicYear, $semester)
    {
        $quarters = $semester === 1 ? ['1st', '2nd'] : ['3rd', '4th'];
        
        $grades = self::where('student_id', $studentId)
                     ->where('subject_id', $subjectId)
                     ->where('academic_year', $academicYear)
                     ->whereIn('quarter', $quarters)
                     ->whereNotNull('grade')
                     ->pluck('grade');

        if ($grades->count() === 2) {
            return round($grades->avg(), 2);
        }
        return null;
    }

    // Calculate final grade for Senior High School (average of 2 semesters)
    public static function calculateFinalGradeSHS($studentId, $subjectId, $academicYear)
    {
        $firstSem = self::calculateSemesterGradeSHS($studentId, $subjectId, $academicYear, 1);
        $secondSem = self::calculateSemesterGradeSHS($studentId, $subjectId, $academicYear, 2);

        if ($firstSem && $secondSem) {
            return round(($firstSem + $secondSem) / 2, 2);
        }
        return null;
    }

    // Calculate general average for Senior High School (average of all subject final grades)
    public static function calculateGeneralAverageSHS($studentId, $academicYear)
    {
        // Get all subjects for the student's grade level and strand/track
        $student = \App\Models\Student::find($studentId);
        if (!$student) return null;

        $subjects = \App\Models\Subject::where('grade_level', $student->grade_level)
                                      ->where('academic_year', $academicYear)
                                      ->where('is_active', true);

        // Filter by strand and track if applicable
        if ($student->strand_applied) {
            $subjects->where(function($query) use ($student) {
                $query->whereNull('strand') // Core subjects
                      ->orWhere('strand', $student->strand_applied);
            });
        }

        if ($student->track_applied) {
            $subjects->where(function($query) use ($student) {
                $query->whereNull('track') // Core and strand subjects
                      ->orWhere('track', $student->track_applied);
            });
        }

        $subjects = $subjects->get();
        $finalGrades = [];

        foreach ($subjects as $subject) {
            $finalGrade = self::calculateFinalGradeSHS($studentId, $subject->id, $academicYear);
            if ($finalGrade !== null) {
                $finalGrades[] = $finalGrade;
            }
        }

        if (count($finalGrades) > 0) {
            return round(array_sum($finalGrades) / count($finalGrades), 2);
        }
        return null;
    }

    // Helper method to determine grading system based on grade level
    public static function getGradingSystem($gradeLevel)
    {
        $elementaryJHS = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 
                         'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
        
        if (in_array($gradeLevel, $elementaryJHS)) {
            return 'quarterly'; // 1st, 2nd, 3rd, 4th → final → general average
        } else {
            return 'semester'; // 1st & 2nd → first sem, 3rd & 4th → second sem → final → general average
        }
    }
}
