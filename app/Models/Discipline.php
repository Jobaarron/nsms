<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discipline extends Model
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
     * Get the user that owns this discipline record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a discipline head
     */
    public function isDisciplineHead(): bool
    {
        return $this->specialization === 'discipline_head';
    }

    /**
     * Check if this is a discipline officer
     */
    public function isDisciplineOfficer(): bool
    {
        return $this->specialization === 'discipline_officer';
    }

    /**
     * Check if this is a security guard
     */
    public function isSecurityGuard(): bool
    {
        return $this->specialization === 'security_guard';
    }

    /**
     * Get the display name for specialization
     */
    public function getSpecializationDisplayAttribute(): string
    {
        return match($this->specialization) {
            'discipline_head' => 'Discipline Head',
            'discipline_officer' => 'Discipline Officer',
            'security_guard' => 'Security Guard',
            default => ucfirst(str_replace('_', ' ', $this->specialization))
        };
    }


    /**
     * Get the violations reported by this discipline staff.
     */
    public function reportedViolations(): HasMany
    {
        return $this->hasMany(Violation::class, 'reported_by');
    }

    /**
     * Get the violations resolved by this discipline staff.
     */
    public function resolvedViolations(): HasMany
    {
        return $this->hasMany(Violation::class, 'resolved_by');
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
     * Scope to get only active discipline staff.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get discipline heads.
     */
    public function scopeHeads($query)
    {
        return $query->where('specialization', 'discipline_head');
    }

    /**
     * Scope to get discipline officers.
     */
    public function scopeOfficers($query)
    {
        return $query->where('specialization', 'discipline_officer');
    }

    /**
     * Scope to get security guards.
     */
    public function scopeSecurityGuards($query)
    {
        return $query->where('specialization', 'security_guard');
    }
}
