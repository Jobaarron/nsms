<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollee_id',
        'reason',
        'documents',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'submitted_at',
    ];

    protected $casts = [
        'documents' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the enrollee that owns the appeal.
     */
    public function enrollee(): BelongsTo
    {
        return $this->belongsTo(Enrollee::class);
    }

    /**
     * Get the registrar who reviewed the appeal.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Registrar::class, 'reviewed_by');
    }

    /**
     * Get appeals by status.
     */
    public static function getByStatus(string $status)
    {
        return static::where('status', $status)
            ->with(['enrollee', 'reviewer'])
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Get pending appeals count.
     */
    public static function getPendingCount(): int
    {
        return static::where('status', 'pending')->count();
    }

    /**
     * Check if appeal can be submitted for an enrollee.
     */
    public static function canSubmitAppeal(int $enrolleeId): bool
    {
        $enrollee = Enrollee::find($enrolleeId);
        
        // Can only appeal if status is rejected
        if ($enrollee->enrollment_status !== 'rejected') {
            return false;
        }

        // Check if there's already a pending appeal
        $existingAppeal = static::where('enrollee_id', $enrolleeId)
            ->where('status', 'pending')
            ->exists();

        return !$existingAppeal;
    }

    /**
     * Get the status badge class for UI.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'under_review' => 'bg-info',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get formatted submitted date.
     */
    public function getFormattedSubmittedDateAttribute(): string
    {
        return $this->submitted_at->format('M d, Y g:i A');
    }

    /**
     * Get time ago for submitted date.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->submitted_at->diffForHumans();
    }
}