<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sanction extends Model
{
    protected $fillable = [
        'violation_id',
        'severity',
        'category',
        'sanction',
        'deportment_grade_action',
        'suspension',
        'notes',
        'is_automatic',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_automatic' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the violation that owns this sanction.
     */
    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }

    /**
     * Get the staff member who approved this sanction.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'approved_by');
    }

    /**
     * Get the sanction color for display.
     */
    public function getSanctionColorAttribute(): string
    {
        if ($this->is_approved) {
            return 'success';
        } elseif ($this->is_automatic) {
            return 'warning';
        } else {
            return 'secondary';
        }
    }

    /**
     * Get the sanction status text.
     */
    public function getSanctionStatusAttribute(): string
    {
        if ($this->is_approved) {
            return 'Approved';
        } elseif ($this->is_automatic) {
            return 'Pending Approval';
        } else {
            return 'Draft';
        }
    }
}
