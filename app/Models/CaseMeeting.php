<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseMeeting extends Model
{
    protected $fillable = [
        'student_id',
        'counselor_id',
        'meeting_type',
        'scheduled_date',
        'scheduled_time',
        'location',
        'reason',
        'notes',
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
     * Scope to get forwarded case meetings.
     */
    public function scopeForwarded($query)
    {
        return $query->where('status', 'forwarded');
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
            'forwarded' => ['text' => 'Forwarded', 'class' => 'badge bg-warning'],
            default => ['text' => ucfirst($this->status), 'class' => 'badge bg-secondary']
        };
    }
}
