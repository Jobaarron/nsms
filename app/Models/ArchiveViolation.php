<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchiveViolation extends Model
{
    protected $table = 'archive_violations';

    protected $fillable = [
        'student_id',
        'reported_by',
        'violation_type',
        'title',
        'description',
        'severity',
        'major_category',
        'sanction',
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
        'archived_at',
        'archive_reason',
    ];

    protected $casts = [
        'violation_date' => 'date',
        'resolved_at' => 'date',
        'parent_notification_date' => 'date',
        'parent_notified' => 'boolean',
        'witnesses' => 'array',
        'attachments' => 'array',
        'archived_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'reported_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'resolved_by');
    }
}
