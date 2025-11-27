<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseMeeting extends Model
{
    protected static function booted()
    {
        static::updated(function (CaseMeeting $caseMeeting) {
            // Only sync if status was changed
            if ($caseMeeting->isDirty('status')) {
                $newStatus = $caseMeeting->status;
                $violationStatus = self::mapStatusToViolationStatus($newStatus);
                if ($violationStatus) {
                    try {
                        // Batch update direct violations to avoid N+1 queries
                        $caseMeeting->violations()->update(['status' => $violationStatus]);
                        
                        // Update violations via sanctions - get all at once
                        $caseMeeting->load('sanctions.violation');
                        $violationIds = $caseMeeting->sanctions
                            ->pluck('violation')
                            ->filter()
                            ->where('status', '!=', $violationStatus)
                            ->pluck('id');
                        
                        if ($violationIds->isNotEmpty()) {
                            Violation::whereIn('id', $violationIds)->update(['status' => $violationStatus]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('CaseMeeting booted status sync error', [
                            'case_meeting_id' => $caseMeeting->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        });
    }
    /**
     * Get the violations related to this case meeting.
     */
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'case_meeting_id');
    }

    // Removed automatic status syncing between CaseMeeting and related violations
    protected $fillable = [
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
    ];
    /**
     * Get the violation associated with this case meeting.
     */
    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class, 'violation_id');
    }

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i',
        'follow_up_date' => 'date',
        'follow_up_required' => 'boolean',
        'forwarded_to_president' => 'boolean',
        'forwarded_at' => 'datetime',
        'completed_at' => 'datetime',
        // Agreed Actions/Interventions casts
        'written_reflection' => 'boolean',
        'written_reflection_due' => 'date',
        'mentorship_counseling' => 'boolean',
        'mentor_name' => 'string',
        'parent_teacher_communication' => 'boolean',
        'parent_teacher_date' => 'date',
        'restorative_justice_activity' => 'boolean',
        'restorative_justice_date' => 'date',
        'follow_up_meeting' => 'boolean',
        'follow_up_meeting_date' => 'date',
        'community_service' => 'boolean',
        'community_service_date' => 'date',
        'community_service_area' => 'string',
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
     * Get the student that this case meeting is for.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the counselor who scheduled this case meeting.
     */
    public function counselor(): BelongsTo
    {
        return $this->belongsTo(Guidance::class, 'counselor_id');
    }

    /**
     * Get the summary report for this case meeting.
     */
    public function summaryReport(): HasOne
    {
        return $this->hasOne(SummaryReport::class);
    }

    /**
     * Get the sanctions for this case meeting (can be multiple).
     */
    public function sanctions(): HasMany
    {
        return $this->hasMany(Sanction::class);
    }


    /**
     * Check if the case meeting is ready to be forwarded.
     */
    public function isReadyForForwarding(): bool
    {
        // Ready if summary exists and status is pre_completed
        return !empty($this->summary) && $this->status === 'pre_completed';
    }

    /**
     * Scope to get scheduled case meetings.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope to get completed case meetings.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get submitted case meetings (previously forwarded).
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope to get house visits.
     */
    public function scopeHouseVisits($query)
    {
        return $query->where('meeting_type', 'house_visit');
    }

    /**
     * Get the display name for the meeting type.
     */
    public function getMeetingTypeDisplayAttribute(): string
    {
        return match($this->meeting_type) {
            'case_meeting' => 'Case Meeting',
            'house_visit' => 'House Visit',
            default => ucfirst(str_replace('_', ' ', $this->meeting_type))
        };
    }

    /**
     * Get the status display with color.
     */
    public function getStatusDisplayAttribute(): array
    {
        return match($this->status) {
            'scheduled' => ['text' => 'Scheduled', 'class' => 'badge bg-primary'],
            'in_progress' => ['text' => 'In Progress', 'class' => 'badge bg-info'],
            'pre_completed' => ['text' => 'Pre-Completed', 'class' => 'badge bg-warning'],
            'completed' => ['text' => 'Completed', 'class' => 'badge bg-success'],
            'cancelled' => ['text' => 'Cancelled', 'class' => 'badge bg-danger'],
            'submitted' => ['text' => 'Submitted', 'class' => 'badge bg-warning'],
            default => ['text' => ucfirst($this->status), 'class' => 'badge bg-secondary']
        };
    }

    /**
     * Map case meeting status to violation status.
     *
     * @param string $caseMeetingStatus
     * @return string|null
     */
    public static function mapStatusToViolationStatus(string $caseMeetingStatus): ?string
    {
        return match($caseMeetingStatus) {
            'scheduled' => 'scheduled',
            'in_progress' => 'in_progress',
            'pre_completed' => 'pre_completed',
            'completed' => 'case_closed',
            'submitted' => 'submitted',
            'cancelled' => 'pending', // Return to pending if cancelled
            default => null
        };
    }

    /**
     * Update related violation statuses for this case meeting.
     *
     * @param string $newStatus
     * @return void
     */
    public function updateRelatedViolationStatuses(string $newStatus): void
    {
        try {
            // Batch update violations directly related to this case meeting
            $this->violations()->update(['status' => $newStatus]);
            
            // Batch update violations via sanctions
            $this->load('sanctions.violation');
            $violationIds = $this->sanctions
                ->pluck('violation')
                ->filter()
                ->pluck('id');
            
            if ($violationIds->isNotEmpty()) {
                $updateData = ['status' => $newStatus];
                if ($newStatus === 'case_closed') {
                    $updateData['resolved_at'] = now();
                    // Note: resolved_by might need to be set if there's a current user
                }
                Violation::whereIn('id', $violationIds)->update($updateData);
            }
        } catch (\Exception $e) {
            \Log::error('Update Related Violation Statuses: Exception', [
                'case_meeting_id' => $this->id,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw the exception to prevent breaking the case meeting creation/update
        }
    }
    /**
     * Get the admin associated with this case meeting.
     * Assumes there is an admin_id column referencing users table.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Accessor: Get the teacher associated via the violation relationship.
     * Usage: $caseMeeting->teacher
     * For eager loading, use with(['violation.teacher'])
     */
    public function getTeacherAttribute()
    {
        return $this->violation ? $this->violation->teacher : null;
    }

      /**
     * Get the adviser (class adviser) for this case meeting.
     * Assumes there is an adviser_id column referencing users table.
     */
    public function adviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    /**
     * Archive this case meeting when it's completed or closed.
     */
    public function archiveCase($archivedBy = null, $reason = 'case_closed')
    {
        // Create archived meeting record
        $archivedMeeting = ArchivedMeeting::create([
            'original_case_meeting_id' => $this->id,
            'student_id' => $this->student_id,
            'violation_id' => $this->violation_id,
            'counselor_id' => $this->counselor_id,
            'adviser_id' => $this->adviser_id,
            'meeting_type' => $this->meeting_type,
            'scheduled_date' => $this->scheduled_date,
            'scheduled_time' => $this->scheduled_time,
            'location' => $this->location,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'teacher_statement' => $this->teacher_statement,
            'action_plan' => $this->action_plan,
            'status' => $this->status,
            'summary' => $this->summary,
            'recommendations' => $this->recommendations,
            'follow_up_required' => $this->follow_up_required,
            'follow_up_date' => $this->follow_up_date,
            'sanction_recommendation' => $this->sanction_recommendation,
            'urgency_level' => $this->urgency_level,
            'president_notes' => $this->president_notes,
            'forwarded_to_president' => $this->forwarded_to_president,
            'forwarded_at' => $this->forwarded_at,
            'completed_at' => $this->completed_at,
            // Agreed Actions/Interventions fields
            'written_reflection' => $this->written_reflection,
            'written_reflection_due' => $this->written_reflection_due,
            'mentor_name' => $this->mentor_name,
            'mentorship_counseling' => $this->mentorship_counseling,
            'parent_teacher_communication' => $this->parent_teacher_communication,
            'parent_teacher_date' => $this->parent_teacher_date,
            'restorative_justice_activity' => $this->restorative_justice_activity,
            'restorative_justice_date' => $this->restorative_justice_date,
            'follow_up_meeting' => $this->follow_up_meeting,
            'follow_up_meeting_date' => $this->follow_up_meeting_date,
            'community_service' => $this->community_service,
            'community_service_date' => $this->community_service_date,
            'community_service_area' => $this->community_service_area,
            'suspension' => $this->suspension,
            'suspension_3days' => $this->suspension_3days,
            'suspension_5days' => $this->suspension_5days,
            'suspension_other_days' => $this->suspension_other_days,
            'suspension_start' => $this->suspension_start,
            'suspension_end' => $this->suspension_end,
            'suspension_return' => $this->suspension_return,
            'expulsion' => $this->expulsion,
            'expulsion_date' => $this->expulsion_date,
            // Student reply fields
            'student_statement' => $this->student_statement ?? null,
            'incident_feelings' => $this->incident_feelings ?? null,
            'student_reply_incident_date' => $this->student_reply_incident_date ?? null,
            'student_reply_location' => $this->student_reply_location ?? null,
            'student_reply_people_involved' => $this->student_reply_people_involved ?? null,
            'student_reply_what_happened' => $this->student_reply_what_happened ?? null,
            'student_reply_feelings' => $this->student_reply_feelings ?? null,
            'student_reply_why_happened' => $this->student_reply_why_happened ?? null,
            'student_reply_what_learned' => $this->student_reply_what_learned ?? null,
            'student_reply_prevent_future' => $this->student_reply_prevent_future ?? null,
            'student_reply_additional_comments' => $this->student_reply_additional_comments ?? null,
            // Archive metadata
            'archived_at' => now(),
            'archived_by' => $archivedBy,
            'archive_reason' => $reason,
        ]);

        return $archivedMeeting;
    }

    /**
     * Check if this case meeting should be archived.
     */
    public function shouldBeArchived()
    {
        return in_array($this->status, ['case_closed']);
    }

    /**
     * Get archived meetings relationship.
     */
    public function archivedMeeting()
    {
        return $this->hasOne(ArchivedMeeting::class, 'original_case_meeting_id');
    }

    /**
     * Get count of unreplied observation reports for a teacher
     * Returns observation reports where teacher hasn't replied yet (no teacher_statement)
     */
    public static function getUnrepliedObservationReportsCountForTeacher($teacherId)
    {
        $oneDayAgo = now()->subDay();
        
        return self::where('adviser_id', $teacherId)
            ->whereNull('teacher_statement')
            ->where('created_at', '>=', $oneDayAgo)
            ->count();
    }

}
