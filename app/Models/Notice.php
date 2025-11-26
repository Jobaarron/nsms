<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'enrollee_id',
        'created_by',
        'is_read',
        'read_at',
        'is_global',
        'target_status',
        'target_grade_level',
        'sent_via_email',
        'email_sent_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_global' => 'boolean',
        'sent_via_email' => 'boolean',
        'read_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the enrollee that owns the notice
     */
    public function enrollee()
    {
        return $this->belongsTo(Enrollee::class);
    }

    /**
     * Get the user who created the notice (could be Admin or Registrar)
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the registrar who created the notice
     */
    public function createdByRegistrar()
    {
        return $this->belongsTo(\App\Models\Registrar::class, 'created_by');
    }

    /**
     * Get the guidance counselor who created the notice
     */
    public function createdByGuidance()
    {
        return $this->belongsTo(\App\Models\Guidance::class, 'created_by');
    }

    /**
     * Get the creator name regardless of whether it's admin, registrar, or guidance
     */
    public function getCreatorNameAttribute()
    {
        // Try guidance counselor first
        $guidance = $this->createdByGuidance;
        if ($guidance) {
            return $guidance->full_name ?? $guidance->first_name . ' ' . $guidance->last_name ?? 'Guidance Counselor';
        }
        
        // Try registrar second
        $registrar = $this->createdByRegistrar;
        if ($registrar) {
            return $registrar->name ?? $registrar->email ?? 'Registrar';
        }
        
        // Fall back to admin user
        $user = $this->createdBy;
        if ($user) {
            return $user->name ?? $user->email ?? 'Admin';
        }
        
        return 'System';
    }

    /**
     * Scope for unread notices
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notices
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope for global notices
     */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope for specific enrollee notices
     */
    public function scopeForEnrollee($query, $enrolleeId)
    {
        return $query->where(function($q) use ($enrolleeId) {
            $q->where('enrollee_id', $enrolleeId)
              ->orWhere('is_global', true);
        });
    }


    /**
     * Mark notice as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Mark notice as unread
     */
    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null
        ]);
    }


    /**
     * Get formatted created date
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    /**
     * Get time ago format
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if notice is recent (within 24 hours)
     */
    public function getIsRecentAttribute()
    {
        return $this->created_at->isAfter(Carbon::now()->subDay());
    }

    /**
     * Get truncated message for preview
     */
    public function getPreviewMessageAttribute()
    {
        return strlen($this->message) > 100 
            ? substr($this->message, 0, 100) . '...' 
            : $this->message;
    }

    /**
     * Static method to create a notice for specific enrollee
     */
    public static function createForEnrollee($enrolleeId, $title, $message, $createdBy = null)
    {
        return static::create([
            'title' => $title,
            'message' => $message,
            'enrollee_id' => $enrolleeId,
            'created_by' => $createdBy,
            'is_global' => false,
            'is_read' => false
        ]);
    }

    /**
     * Static method to create a global notice
     */
    public static function createGlobal($title, $message, $createdBy = null, $targetStatus = null, $targetGradeLevel = null)
    {
        return static::create([
            'title' => $title,
            'message' => $message,
            'created_by' => $createdBy,
            'is_global' => true,
            'target_status' => $targetStatus,
            'target_grade_level' => $targetGradeLevel,
            'is_read' => false
        ]);
    }

    /**
     * Get notices for a specific enrollee (including global notices)
     */
    public static function getForEnrollee($enrolleeId, $limit = null)
    {
        $query = static::forEnrollee($enrolleeId)
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get unread count for enrollee
     */
    public static function getUnreadCountForEnrollee($enrolleeId)
    {
        return static::forEnrollee($enrolleeId)->unread()->count();
    }

    /**
     * Mark all notices as read for enrollee
     */
    public static function markAllAsReadForEnrollee($enrolleeId)
    {
        return static::forEnrollee($enrolleeId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }

    /**
     * Static method to create a notice from guidance counselor
     */
    public static function createFromGuidance($enrolleeId, $title, $message, $guidanceId = null)
    {
        return static::create([
            'title' => $title,
            'message' => $message,
            'enrollee_id' => $enrolleeId,
            'created_by' => $guidanceId,
            'is_global' => false,
            'is_read' => false
        ]);
    }

    /**
     * Static method to create a global guidance notice
     */
    public static function createGlobalFromGuidance($title, $message, $guidanceId = null, $targetStatus = null, $targetGradeLevel = null)
    {
        return static::create([
            'title' => $title,
            'message' => $message,
            'created_by' => $guidanceId,
            'is_global' => true,
            'target_status' => $targetStatus,
            'target_grade_level' => $targetGradeLevel,
            'is_read' => false
        ]);
    }

    /**
     * Get notifications for guidance dashboard
     */
    public static function getForGuidance($limit = 10)
    {
        return static::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for guidance dashboard
     */
    public static function getUnreadCountForGuidance()
    {
        return static::unread()->count();
    }

    /**
     * Scope for guidance-created notices
     */
    public function scopeFromGuidance($query)
    {
        return $query->whereHas('createdByGuidance');
    }

    /**
     * Scope for guidance-relevant notices (case meetings, counseling sessions, teacher replies, forwarded cases, recommendations)
     * This scope is specifically for guidance dashboard notifications and does not affect other modules
     */
    public function scopeGuidanceRelevant($query)
    {
        return $query->where(function($q) {
            // Only include notifications that are specifically created for guidance or by guidance
            $q->where(function($subQ) {
                $subQ->where('title', 'LIKE', '%case meeting%')
                     ->orWhere('title', 'LIKE', '%counseling session%')
                     ->orWhere('title', 'LIKE', '%teacher reply%')
                     ->orWhere('title', 'LIKE', '%Case Meeting%')
                     ->orWhere('title', 'LIKE', '%Counseling Session%')
                     ->orWhere('title', 'LIKE', '%Teacher Reply%')
                     ->orWhere('title', 'LIKE', '%forwarded%')
                     ->orWhere('title', 'LIKE', '%Forwarded%')
                     ->orWhere('title', 'LIKE', '%recommended%')
                     ->orWhere('title', 'LIKE', '%Recommended%')
                     ->orWhere('message', 'LIKE', '%case meeting%')
                     ->orWhere('message', 'LIKE', '%counseling session%')
                     ->orWhere('message', 'LIKE', '%teacher reply%')
                     ->orWhere('message', 'LIKE', '%forwarded%')
                     ->orWhere('message', 'LIKE', '%recommended%');
            })
            // Exclude typical registrar/student/applicant notifications
            ->whereNotIn('title', [
                'Application Status Update',
                'Enrollment Confirmation',
                'Payment Reminder',
                'Document Required',
                'Schedule Update',
                'Welcome'
            ])
            ->where('title', 'NOT LIKE', '%Application%')
            ->where('title', 'NOT LIKE', '%Enrollment%')
            ->where('title', 'NOT LIKE', '%Payment%')
            ->where('title', 'NOT LIKE', '%Schedule%')
            ->where('message', 'NOT LIKE', '%application%')
            ->where('message', 'NOT LIKE', '%enrollment%')
            ->where('message', 'NOT LIKE', '%tuition%')
            ->where('message', 'NOT LIKE', '%fee%')
            ->where('message', 'NOT LIKE', '%payment%');
        });
    }

    /**
     * Get guidance-relevant notifications
     */
    public static function getGuidanceRelevant($limit = 10)
    {
        return static::guidanceRelevant()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for guidance-relevant notifications
     */
    public static function getUnreadGuidanceRelevantCount()
    {
        return static::guidanceRelevant()->unread()->count();
    }

    /**
     * Scope to exclude guidance-specific notifications from other modules
     * Use this in registrar, student, applicant, teacher modules to avoid showing guidance notifications
     */
    public function scopeExcludeGuidanceSpecific($query)
    {
        return $query->where(function($q) {
            $q->where('title', 'NOT LIKE', '%case meeting%')
              ->where('title', 'NOT LIKE', '%counseling session%')
              ->where('title', 'NOT LIKE', '%teacher reply%')
              ->where('title', 'NOT LIKE', '%Case Meeting%')
              ->where('title', 'NOT LIKE', '%Counseling Session%')
              ->where('title', 'NOT LIKE', '%Teacher Reply%')
              ->where('title', 'NOT LIKE', '%forwarded%')
              ->where('title', 'NOT LIKE', '%Forwarded%')
              ->where('title', 'NOT LIKE', '%recommended%')
              ->where('title', 'NOT LIKE', '%Recommended%')
              ->where('message', 'NOT LIKE', '%case meeting%')
              ->where('message', 'NOT LIKE', '%counseling session%')
              ->where('message', 'NOT LIKE', '%teacher reply%')
              ->where('message', 'NOT LIKE', '%forwarded%')
              ->where('message', 'NOT LIKE', '%recommended%');
        });
    }

    /**
     * Get notifications for registrar (excluding guidance-specific ones)
     */
    public static function getForRegistrar($limit = 10)
    {
        return static::excludeGuidanceSpecific()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get notifications for students/enrollees (excluding guidance-specific ones)
     */
    public static function getForStudent($enrolleeId, $limit = 10)
    {
        return static::forEnrollee($enrolleeId)
            ->excludeGuidanceSpecific()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get notifications for teachers (excluding guidance-specific ones)
     */
    public static function getForTeacher($limit = 10)
    {
        return static::excludeGuidanceSpecific()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
