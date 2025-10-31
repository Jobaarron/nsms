<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'student_violations';
    /**
     * Get the case meeting related to this violation.
     */
    public function caseMeeting(): BelongsTo
    {
        return $this->belongsTo(CaseMeeting::class, 'case_meeting_id');
    }

    protected $fillable = [
        'case_meeting_id',
        'student_id',
        'reported_by',
        'violation_type',
        'title',
        'description',
        'severity',
        'major_category',
        'violation_date',
        'violation_time',
        'location',
        'witnesses',
        'evidence',
        'attachments',
        'status',
        'resolution',
        'resolved_by',
        'resolved_at',
        'student_statement',
         'incident_feelings',
        'action_plan',
        'disciplinary_action',
        'parent_notified',
        'parent_notification_date',
        'notes',
        'sanction',
        'urgency_level',
    ];

    protected $attributes = [
        'urgency_level' => 'medium',
    ];

    protected $casts = [
        'violation_date' => 'date',
        'resolved_at' => 'date',
        'parent_notification_date' => 'date',
        'parent_notified' => 'boolean',
        'incident_feelings' => 'string',
        'action_plan' => 'string',
    ];

    /**
     * Get the student that owns this violation.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the staff member who reported this violation.
     */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'reported_by');
    }

    /**
     * Get the staff member who resolved this violation.
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'resolved_by');
    }

    /**
     * Get the sanctions related to this violation.
     */
    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }

    /**
     * Get the severity color for display.
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'minor' => 'success',
            'major' => 'warning',
            'severe' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'investigating' => 'info',
            'in_progress' => 'info',
            'resolved' => 'success',
            'dismissed' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get the name of the staff member who reported this violation.
     */
    public function getReportedByNameAttribute(): string
    {
        return $this->reportedBy ? $this->reportedBy->full_name : 'Unknown';
    }

    /**
     * Get the name of the staff member who handled this violation.
     */
    public function getHandledByNameAttribute(): string
    {
        return $this->resolvedBy ? $this->resolvedBy->full_name : 'Pending';
    }

    /**
     * Get the action taken for this violation.
     */
    public function getActionTakenAttribute(): string
    {
        return $this->disciplinary_action ?: $this->resolution ?: 'No action recorded';
    }
        /**
         * Get the teacher associated with this violation.
         */
        public function teacher()
        {
            // Assumes there is a teacher_id field in the violations table, or you want to use another field
            // If you want to use a different field, adjust the second and third arguments accordingly
            // If you want to use the teacher's id, and the violations table has a field like 'reported_by' or similar, use that
            // Example: return $this->belongsTo(Teacher::class, 'reported_by', 'user_id');
            // But if you want to use the teacher's id, you need a teacher_id field in violations table
            // For now, fallback to reported_by as a demonstration (if reported_by is a teacher's id)
            return $this->belongsTo(Teacher::class, 'reported_by', 'id');
        }
        /**
     * Get the teacher associated with this violation (by users_id).
     */
    // public function teacher()
    // {
    //     return $this->belongsTo(\App\Models\Teacher::class, 'users_id', 'users_id');
    // }
}