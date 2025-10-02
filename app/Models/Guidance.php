<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guidance extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'first_name',
        'last_name',
        'phone_number',
        'address',
        'position',
        'hire_date',
        'qualifications',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'notes',
        'specialization',
        'is_active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns this guidance record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a guidance counselor
     */
    public function isGuidanceCounselor(): bool
    {
        return $this->specialization === 'guidance_counselor';
    }

    /**
     * Check if this is a head counselor
     */
    public function isHeadCounselor(): bool
    {
        return $this->specialization === 'head_counselor';
    }

    /**
     * Check if this is a career counselor
     */
    public function isCareerCounselor(): bool
    {
        return $this->specialization === 'career_counselor';
    }

    /**
     * Get the display name for specialization
     */
    public function getSpecializationDisplayAttribute(): string
    {
        return match($this->specialization) {
            'guidance_counselor' => 'Guidance Counselor',
            'head_counselor' => 'Head Counselor',
            'career_counselor' => 'Career Counselor',
            default => ucfirst(str_replace('_', ' ', $this->specialization))
        };
    }


    /**
     * Get the counseling sessions conducted by this guidance counselor.
     */
    public function counselingSessions(): HasMany
    {
        return $this->hasMany(CounselingSession::class, 'counselor_id');
    }

    /**
     * Get the case meetings scheduled by this guidance counselor.
     */
    public function caseMeetings(): HasMany
    {
        return $this->hasMany(CaseMeeting::class, 'counselor_id');
    }

    /**
     * Get the full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the display name with position.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name . ($this->position ? ' (' . $this->position . ')' : '');
    }

    /**
     * Scope to get only active guidance staff.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get guidance counselors.
     */
    public function scopeCounselors($query)
    {
        return $query->where('specialization', 'guidance_counselor');
    }

    /**
     * Scope to get head counselors.
     */
    public function scopeHeadCounselors($query)
    {
        return $query->where('specialization', 'head_counselor');
    }

    /**
     * Scope to get career counselors.
     */
    public function scopeCareerCounselors($query)
    {
        return $query->where('specialization', 'career_counselor');
    }
}
