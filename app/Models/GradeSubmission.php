<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GradeSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'grade_level',
        'section',
        'academic_year',
        'quarter',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'finalized_at',
        'grades_data',
        'total_students',
        'grades_entered',
        'submission_notes',
        'review_notes',
        'grades_finalized'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'finalized_at' => 'datetime',
        'grades_data' => 'array',
        'grades_finalized' => 'boolean'
    ];

    // Relationships - interconnected with existing system
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id'); // References teachers table
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by'); // Faculty head
    }

    // Get students for this submission
    public function students()
    {
        return Student::where('grade_level', $this->grade_level)
                     ->where('section', $this->section)
                     ->where('academic_year', $this->academic_year)
                     ->where('is_active', true)
                     ->get();
    }

    // Get faculty assignment for this submission
    public function facultyAssignment()
    {
        return $this->belongsTo(FacultyAssignment::class, 'teacher_id', 'teacher_id')
                    ->where('subject_id', $this->subject_id)
                    ->where('grade_level', $this->grade_level)
                    ->where('section', $this->section)
                    ->where('academic_year', $this->academic_year)
                    ->where('status', 'active');
    }

    // Get faculty assignment record (helper method)
    public function getFacultyAssignment()
    {
        return FacultyAssignment::where('teacher_id', $this->teacher_id)
                               ->where('subject_id', $this->subject_id)
                               ->where('grade_level', $this->grade_level)
                               ->where('section', $this->section)
                               ->where('academic_year', $this->academic_year)
                               ->where('status', 'active')
                               ->first();
    }

    // Scopes
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    public function scopeForQuarter($query, $quarter)
    {
        return $query->where('quarter', $quarter);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'submitted');
    }

    // Helper methods
    public function getCompletionPercentageAttribute()
    {
        if ($this->total_students == 0) return 0;
        return round(($this->grades_entered / $this->total_students) * 100, 1);
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function canEdit()
    {
        return in_array($this->status, ['draft', 'revision_requested']);
    }

    public function canSubmit()
    {
        return $this->status === 'draft' && $this->grades_entered === $this->total_students;
    }

    // Submit grades for review
    public function submit($notes = null)
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submission_notes' => $notes
        ]);
    }

    // Approve grades (does NOT finalize to grades table yet)
    public function approve($reviewerId, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes
        ]);
        
        // NOTE: Grades are NOT finalized to main table yet
        // Teacher must call finalizeByTeacher() to make grades visible to students
    }

    // Teacher finalizes approved grades (makes them visible to students)
    public function finalizeByTeacher()
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Only approved grades can be finalized');
        }

        // Copy grades to the main grades table
        $this->finalizeGrades();
        
        // Update submission status
        $this->update([
            'status' => 'finalized',
            'finalized_at' => now()
        ]);
    }

    // Reject grades
    public function reject($reviewerId, $notes)
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes
        ]);
    }

    // Request revision
    public function requestRevision($reviewerId, $notes)
    {
        $this->update([
            'status' => 'revision_requested',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes
        ]);
    }

    // Finalize grades to the main grades table
    public function finalizeGrades()
    {
        if ($this->grades_finalized) return;

        foreach ($this->grades_data as $gradeData) {
            Grade::updateOrCreate(
                [
                    'student_id' => $gradeData['student_id'],
                    'subject_id' => $this->subject_id,
                    'teacher_id' => $this->teacher_id,
                    'quarter' => $this->quarter,
                    'academic_year' => $this->academic_year
                ],
                [
                    'grade' => $gradeData['grade'],
                    'remarks' => $gradeData['remarks'] ?? null,
                    'submitted_at' => $this->submitted_at,
                    'is_final' => true
                ]
            );
        }

        $this->update(['grades_finalized' => true]);
    }

    // Get existing grades from main grades table
    public function getExistingGrades()
    {
        return Grade::where('teacher_id', $this->teacher_id)
                   ->where('subject_id', $this->subject_id)
                   ->where('quarter', $this->quarter)
                   ->where('academic_year', $this->academic_year)
                   ->whereIn('student_id', $this->students()->pluck('id'))
                   ->get()
                   ->keyBy('student_id');
    }

    public static function getPendingSubmissionsCount()
    {
        $oneDayAgo = now()->subDay();
        
        return self::where('status', 'submitted')
            ->where('created_at', '>=', $oneDayAgo)
            ->count();
    }

    public static function getDraftSubmissionsCountForTeacher($teacherId)
    {
        $oneDayAgo = now()->subDay();
        
        return self::where('teacher_id', $teacherId)
            ->where('status', 'draft')
            ->where('created_at', '>=', $oneDayAgo)
            ->count();
    }
}
