<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'read_at',
        'admin_notes'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Scope for read messages
     */
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update([
            'status' => 'read',
            'read_at' => now()
        ]);
    }

    /**
     * Mark message as replied
     */
    public function markAsReplied()
    {
        $this->update([
            'status' => 'replied'
        ]);
    }

    /**
     * Get subject display name
     */
    public function getSubjectDisplayAttribute()
    {
        $subjects = [
            'enrollment' => 'Enrollment Inquiry',
            'academic' => 'Academic Information',
            'admission' => 'Admission Requirements',
            'facilities' => 'School Facilities',
            'other' => 'Other'
        ];

        return $subjects[$this->subject] ?? 'Unknown';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'unread' => 'badge bg-warning',
            'read' => 'badge bg-info',
            'replied' => 'badge bg-success',
            default => 'badge bg-secondary'
        };
    }

    /**
     * Get count of unread contact messages
     */
    public static function getUnreadMessagesCount()
    {
        return self::where('status', 'unread')->count();
    }
}
