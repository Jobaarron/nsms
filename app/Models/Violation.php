<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    protected $table = 'student_violations';

    protected $fillable = [
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
        'disciplinary_action',
        'parent_notified',
        'parent_notification_date',
        'notes',
        'sanction',
        'urgency_level',
    ];

    protected $casts = [
        'violation_date' => 'date',
        'resolved_at' => 'date',
        'parent_notification_date' => 'date',
        'parent_notified' => 'boolean',
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
}