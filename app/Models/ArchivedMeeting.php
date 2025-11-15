<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivedMeeting extends Model
{
    protected $fillable = [
        'original_case_meeting_id',
        'student_id',
        'violation_id',
        'counselor_id',
        'adviser_id',
        'meeting_type',
        'scheduled_date',
        'scheduled_time',
        'location',
        'reason',
        'notes',
        'teacher_statement',
        'action_plan',
        'status',
        'summary',
        'recommendations',
        'follow_up_required',
        'follow_up_date',
        'sanction_recommendation',
        'urgency_level',
        'president_notes',
        'forwarded_to_president',
        'forwarded_at',
        'completed_at',
        // Agreed Actions/Interventions fields
        'written_reflection',
        'written_reflection_due',
        'mentor_name',
        'mentorship_counseling',
        'parent_teacher_communication',
        'parent_teacher_date',
        'restorative_justice_activity',
        'restorative_justice_date',
        'follow_up_meeting',
        'follow_up_meeting_date',
        'community_service',
        'community_service_date',
        'community_service_area',
        'suspension',
        'suspension_3days',
        'suspension_5days',
        'suspension_other_days',
        'suspension_start',
        'suspension_end',
        'suspension_return',
        'expulsion',
        'expulsion_date',
        // Student reply fields
        'student_statement',
        'incident_feelings',
        'student_reply_incident_date',
        'student_reply_location',
        'student_reply_people_involved',
        'student_reply_what_happened',
        'student_reply_feelings',
        'student_reply_why_happened',
        'student_reply_what_learned',
        'student_reply_prevent_future',
        'student_reply_additional_comments',
        // Archive metadata
        'archived_at',
        'archived_by',
        'archive_reason',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i',
        'follow_up_date' => 'date',
        'follow_up_required' => 'boolean',
        'forwarded_to_president' => 'boolean',
        'forwarded_at' => 'datetime',
        'completed_at' => 'datetime',
        'archived_at' => 'datetime',
        // Agreed Actions/Interventions casts
        'written_reflection' => 'boolean',
        'written_reflection_due' => 'date',
        'mentorship_counseling' => 'boolean',
        'parent_teacher_communication' => 'boolean',
        'parent_teacher_date' => 'date',
        'restorative_justice_activity' => 'boolean',
        'restorative_justice_date' => 'date',
        'follow_up_meeting' => 'boolean',
        'follow_up_meeting_date' => 'date',
        'community_service' => 'boolean',
        'community_service_date' => 'date',
        'suspension' => 'boolean',
        'suspension_3days' => 'boolean',
        'suspension_5days' => 'boolean',
        'suspension_other_days' => 'integer',
        'suspension_start' => 'date',
        'suspension_end' => 'date',
        'suspension_return' => 'date',
        'expulsion' => 'boolean',
        'expulsion_date' => 'date',
    ];

    /**
     * Get the student associated with this archived meeting.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the violation associated with this archived meeting.
     */
    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class, 'violation_id');
    }

    /**
     * Get the original case meeting that was archived.
     */
    public function originalCaseMeeting(): BelongsTo
    {
        return $this->belongsTo(CaseMeeting::class, 'original_case_meeting_id');
    }

    /**
     * Scope to get archived meetings for a specific student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get archived meetings by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get recently archived meetings.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('archived_at', '>=', now()->subDays($days));
    }
}
