<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataChangeRequest extends Model
{
    protected $fillable = [
        'enrollee_id',
        'field_name',
        'old_value',
        'new_value',
        'reason',
        'status',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    /**
     * Get the enrollee that owns the data change request.
     */
    public function enrollee(): BelongsTo
    {
        return $this->belongsTo(Enrollee::class);
    }

    /**
     * Get the user who processed the request.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }

    /**
     * Get the human-readable field name.
     */
    public function getHumanFieldNameAttribute(): string
    {
        return match($this->field_name) {
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'suffix' => 'Suffix',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'nationality' => 'Nationality',
            'religion' => 'Religion',
            'email' => 'Email Address',
            'contact_number' => 'Contact Number',
            'address' => 'Address',
            'city' => 'City',
            'province' => 'Province',
            'zip_code' => 'ZIP Code',
            'grade_level_applied' => 'Grade Level Applied',
            'strand_applied' => 'Strand Applied',
            'track_applied' => 'Track Applied',
            'student_type' => 'Student Type',
            'father_name' => 'Father\'s Name',
            'father_occupation' => 'Father\'s Occupation',
            'father_contact' => 'Father\'s Contact',
            'mother_name' => 'Mother\'s Name',
            'mother_occupation' => 'Mother\'s Occupation',
            'mother_contact' => 'Mother\'s Contact',
            'guardian_name' => 'Guardian Name',
            'guardian_contact' => 'Guardian Contact',
            'last_school_name' => 'Last School Name',
            'last_school_type' => 'Last School Type',
            'medical_history' => 'Medical History',
            default => ucwords(str_replace('_', ' ', $this->field_name)),
        };
    }
}
