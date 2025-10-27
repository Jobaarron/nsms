<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselingSession extends Model
{
    protected $fillable = [
        'student_id',
        'counselor_id',
        'recommended_by',
        'start_date',
        'end_date',
        'time_limit',
        'time',
        'session_no',
        'status',
        'referral_academic',
        'referral_academic_other',
        'referral_social',
        'referral_social_other',
        'incident_description',
        'counseling_summary_report',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'time' => 'datetime:H:i',
        'time_limit' => 'integer',
        'session_no' => 'integer',
        'referral_academic' => 'array',
        'referral_social' => 'array',
    ];

    /**
     * Get the student that this counseling session is for.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the counselor who scheduled this counseling session.
     */
    public function counselor(): BelongsTo
    {
        return $this->belongsTo(Guidance::class, 'counselor_id');
    }

    /**
     * Get the user who recommended this counseling session.
     */
    public function recommender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recommended_by');
    }

    /**
     * Scope to get scheduled counseling sessions.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope to get completed counseling sessions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get individual sessions.
     */
    public function scopeIndividual($query)
    {
        return $query->where('session_type', 'individual');
    }

    /**
     * Scope to get group sessions.
     */
    public function scopeGroup($query)
    {
        return $query->where('session_type', 'group');
    }

    /**
     * Scope to get family sessions.
     */
    public function scopeFamily($query)
    {
        return $query->where('session_type', 'family');
    }

    /**
     * Scope to get career counseling sessions.
     */
    public function scopeCareer($query)
    {
        return $query->where('session_type', 'career');
    }

    /**
     * Get the display name for the session type.
     */
    public function getSessionTypeDisplayAttribute(): array
    {
        return match($this->session_type) {
            'individual' => [
                'text' => 'Individual Counseling',
                'class' => 'badge bg-primary',
                'icon' => 'ri-user-heart-line'
            ],
            'group' => [
                'text' => 'Group Counseling',
                'class' => 'badge bg-info',
                'icon' => 'ri-group-line'
            ],
            'family' => [
                'text' => 'Family Counseling',
                'class' => 'badge bg-warning',
                'icon' => 'ri-home-heart-line'
            ],
            'career' => [
                'text' => 'Career Counseling',
                'class' => 'badge bg-success',
                'icon' => 'ri-briefcase-line'
            ],
            default => [
                'text' => ucfirst(str_replace('_', ' ', $this->session_type)),
                'class' => 'badge bg-secondary',
                'icon' => 'ri-question-line'
            ]
        };
    }

    /**
     * Get the status display with color.
     */
    public function getStatusDisplayAttribute(): array
    {
        return match($this->status) {
            'scheduled' => ['text' => 'Scheduled', 'class' => 'badge bg-primary'],
            'completed' => ['text' => 'Completed', 'class' => 'badge bg-success'],
            'cancelled' => ['text' => 'Cancelled', 'class' => 'badge bg-danger'],
            'rescheduled' => ['text' => 'Rescheduled', 'class' => 'badge bg-warning'],
            'recommended' => ['text' => 'Recommended', 'class' => 'badge bg-info'],
            default => ['text' => ucfirst($this->status), 'class' => 'badge bg-secondary']
        };
    }

    /**
     * Get the duration in human readable format.
     */
    public function getDurationDisplayAttribute(): string
    {
        $hours = intval($this->duration / 60);
        $minutes = $this->duration % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Get summary attribute (alias for session_summary)
     */
    public function getSummaryAttribute()
    {
        return $this->session_summary;
    }
}
