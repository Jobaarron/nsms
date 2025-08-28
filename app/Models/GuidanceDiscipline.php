<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuidanceDiscipline extends Model
{
    protected $table = 'guidance_discipline';

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
        'department',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    /**
     * Get the user that owns this guidance discipline record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full name of the staff member.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
