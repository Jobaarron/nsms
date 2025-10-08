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
        'type',
        'priority',
        'enrollee_id',
        'created_by',
        'is_read',
        'read_at',
        'is_global',
        'target_status',
        'target_grade_level'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_global' => 'boolean',
        'read_at' => 'datetime',
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
     * Get the creator name regardless of whether it's admin or registrar
     */
    public function getCreatorNameAttribute()
    {
        // Try registrar first (check if notice was created by a registrar)
        $registrar = $this->createdByRegistrar;
        if ($registrar) {
            return $registrar->name ?? $registrar->email ?? 'Registrar';
        }
        
        // Fall back to admin user
        $user = $this->createdBy;
        if ($user) {
            return $user->name ?? $user->email ?? 'Administrator';
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
     * Scope for priority notices
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for urgent notices
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope for high priority notices
     */
    public function scopeHigh($query)
    {
        return $query->where('priority', 'high');
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
     * Get priority badge class
     */
    public function getPriorityBadgeAttribute()
    {
        $classes = [
            'normal' => 'bg-secondary',
            'high' => 'bg-warning',
            'urgent' => 'bg-danger'
        ];

        return $classes[$this->priority] ?? 'bg-secondary';
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
    public static function createForEnrollee($enrolleeId, $title, $message, $priority = 'normal', $createdBy = null)
    {
        return static::create([
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'enrollee_id' => $enrolleeId,
            'created_by' => $createdBy,
            'is_global' => false,
            'is_read' => false
        ]);
    }

    /**
     * Static method to create a global notice
     */
    public static function createGlobal($title, $message, $priority = 'normal', $createdBy = null, $targetStatus = null, $targetGradeLevel = null)
    {
        return static::create([
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
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
            ->orderBy('priority', 'desc')
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
}
