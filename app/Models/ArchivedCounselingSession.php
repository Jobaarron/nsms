<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ArchivedCounselingSession extends Model
{
    protected $fillable = [
        'original_session_id',
        'counseling_summary_report',
        'student_id',
        'counselor_id',
        'recommended_by',
        'start_date',
        'end_date',
        'frequency',
        'time_limit',
        'time',
        'session_no',
        'status',
        'referral_academic',
        'referral_academic_other',
        'referral_social',
        'referral_social_other',
        'incident_description',
        'archived_at',
        'archive_reason',
        'archive_notes',
        'archived_by',
        'student_name',
        'student_id_number',
        'counselor_name',
        'recommended_by_name',
        'original_created_at',
        'original_updated_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'time' => 'datetime:H:i',
        'referral_academic' => 'array',
        'referral_social' => 'array',
        'archived_at' => 'datetime',
        'original_created_at' => 'datetime',
        'original_updated_at' => 'datetime',
    ];

    /**
     * Get the student that this archived session belongs to
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the counselor that handled this session
     */
    public function counselor(): BelongsTo
    {
        return $this->belongsTo(Guidance::class, 'counselor_id');
    }

    /**
     * Get the user who recommended this session
     */
    public function recommender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recommended_by');
    }

    /**
     * Get the user who archived this session
     */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Archive a counseling session
     */
    public static function archiveCompletedSession(CounselingSession $session, ?string $notes = null, ?int $archivedBy = null): self
    {
        // Load relationships to get names
        $session->load(['student', 'counselor', 'recommender']);

        return self::create([
            'original_session_id' => $session->id,
            'counseling_summary_report' => $session->counseling_summary_report,
            'student_id' => $session->student_id,
            'counselor_id' => $session->counselor_id,
            'recommended_by' => $session->recommended_by,
            'start_date' => $session->start_date,
            'end_date' => $session->end_date,
            'frequency' => $session->frequency,
            'time_limit' => $session->time_limit,
            'time' => $session->time,
            'session_no' => $session->session_no,
            'status' => $session->status,
            'referral_academic' => $session->referral_academic,
            'referral_academic_other' => $session->referral_academic_other,
            'referral_social' => $session->referral_social,
            'referral_social_other' => $session->referral_social_other,
            'incident_description' => $session->incident_description,
            'archived_at' => now(),
            'archive_reason' => 'completed',
            'archive_notes' => $notes,
            'archived_by' => $archivedBy,
            'student_name' => $session->student ? $session->student->full_name ?? $session->student->name : null,
            'student_id_number' => $session->student ? $session->student->student_id : null,
            'counselor_name' => $session->counselor ? $session->counselor->name : null,
            'recommended_by_name' => $session->recommender ? $session->recommender->name : null,
            'original_created_at' => $session->created_at,
            'original_updated_at' => $session->updated_at,
        ]);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeArchivedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('archived_at', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by archive reason
     */
    public function scopeByArchiveReason($query, $reason)
    {
        return $query->where('archive_reason', $reason);
    }

    /**
     * Get formatted referral reasons
     */
    public function getFormattedReferralReasonsAttribute(): string
    {
        $reasons = [];
        
        if ($this->referral_academic) {
            $academic = array_merge($this->referral_academic, $this->referral_academic_other ? [$this->referral_academic_other] : []);
            $reasons[] = 'Academic: ' . implode(', ', array_filter($academic));
        }
        
        if ($this->referral_social) {
            $social = array_merge($this->referral_social, $this->referral_social_other ? [$this->referral_social_other] : []);
            $reasons[] = 'Social: ' . implode(', ', array_filter($social));
        }
        
        return implode(' | ', $reasons);
    }
}
