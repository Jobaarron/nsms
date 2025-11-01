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
                // Map case meeting status to violation status
                $violationStatus = self::mapStatusToViolationStatus($newStatus);
                if ($violationStatus) {
                    foreach ($caseMeeting->violations as $violation) {
                        $violation->update(['status' => $violationStatus]);
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
        'violation_id', // <-- added
        'counselor_id',
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
            'completed' => 'completed',
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
            // Load sanctions with related violations
            $this->load('sanctions.violation');

            foreach ($this->sanctions as $sanction) {
                $violation = $sanction->violation;
                if ($violation) {
                    $violation->status = $newStatus;
                    if ($newStatus === 'completed') {
                        $violation->resolved_at = now();
                        // Note: resolved_by might need to be set if there's a current user, but in model events, auth might not be available
                    }
                    $violation->save();
                }
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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($caseMeeting) {
            // Check if status was changed
            if ($caseMeeting->wasChanged('status')) {
                $newStatus = $caseMeeting->status;
                $violationStatus = self::mapStatusToViolationStatus($newStatus);
                if ($violationStatus) {
                    $caseMeeting->updateRelatedViolationStatuses($violationStatus);
                }
            }
        });
    }

}
