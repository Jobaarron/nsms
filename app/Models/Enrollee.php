<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class Enrollee extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'application_id',
        'lrn',
        'enrollment_status',
        'academic_year',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'enrolled_at',
        'status_reason',
        'first_viewed_at',
        'first_viewed_by',
        'evaluation_started_at',
        'evaluation_started_by',
        'evaluation_completed_at',
        'evaluation_completed_by',
        'documents_reviewed_count',
        'documents_total_count',
        'id_photo',
        'id_photo_mime_type',
        'documents',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'nationality',
        'religion',
        'contact_number',
        'email',
        'address',
        'city',
        'province',
        'zip_code',
        'grade_level_applied',
        'strand_applied',
        'track_applied',
        'student_type',
        'father_name',
        'father_occupation',
        'father_contact',
        'mother_name',
        'mother_occupation',
        'mother_contact',
        'guardian_name',
        'guardian_contact',
        'last_school_type',
        'last_school_name',
        'medical_history',
        // 'payment_mode', // Removed - now handled by payment_method in payments table
        'is_paid',
        'total_fees_due',
        'total_paid',
        'payment_completed_at',
        'enrollment_fee', // Keep for backward compatibility
        'payment_date',
        'payment_reference',
        'preferred_schedule',
        'appointment_status',
        'appointment_notes',
        'enrollment_date',
        'application_date',
        'student_id',
        'pre_registered_at',
        'is_active',
        'admin_notes',
        'processed_by',
        'password',
        'remember_token',
        'id_photo_data_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'enrolled_at' => 'datetime',
        'first_viewed_at' => 'datetime',
        'evaluation_started_at' => 'datetime',
        'evaluation_completed_at' => 'datetime',
        'payment_date' => 'datetime',
        'payment_completed_at' => 'datetime',
        'preferred_schedule' => 'date',
        'enrollment_date' => 'datetime',
        'application_date' => 'datetime',
        'pre_registered_at' => 'datetime',
        'documents' => 'array',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
        'enrollment_fee' => 'decimal:2', // Keep for backward compatibility
        'total_fees_due' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    protected $appends = [
        'id_photo_data_url',
        'full_name',
    ];

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'application_id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getAttribute($this->getAuthIdentifierName()); // Return application_id for session storage
    }

    /**
     * Get the column name for the "username".
     *
     * @return string
     */
    public function username()
    {
        return 'application_id';
    }

    // Relationships
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'enrollee_id');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function notices()
    {
        return $this->hasMany(Notice::class);
    }

    public function dataChangeRequests()
    {
        return $this->hasMany(DataChangeRequest::class);
    }

    // Evaluation tracking relationships
    public function firstViewedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'first_viewed_by');
    }

    public function evaluationStartedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'evaluation_started_by');
    }

    public function evaluationCompletedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'evaluation_completed_by');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        $name = $this->first_name;
        
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        
        $name .= ' ' . $this->last_name;
        
        if ($this->suffix) {
            $name .= ' ' . $this->suffix;
        }
        
        return $name;
    }

    public function getIdPhotoDataUrlAttribute()
    {
        if ($this->id_photo && $this->id_photo_mime_type) {
            return "data:{$this->id_photo_mime_type};base64,{$this->id_photo}";
        }
        return null;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('enrollment_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('enrollment_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('enrollment_status', 'rejected');
    }

    public function scopeEnrolled($query)
    {
        return $query->where('enrollment_status', 'enrolled');
    }

    public function scopeCurrentYear($query, $year = null)
    {
        $year = $year ?? date('Y') . '-' . (date('Y') + 1);
        return $query->where('academic_year', $year);
    }

    // Helper methods
    public function isPaid()
    {
        return $this->is_paid;
    }

    public function canBeApproved()
    {
        return $this->enrollment_status === 'pending';
    }

    public function canBeRejected()
    {
        return in_array($this->enrollment_status, ['pending', 'approved']);
    }

    public function canBeEnrolled()
    {
        return $this->enrollment_status === 'approved' && $this->is_paid;
    }

    public function calculateTotalPaid()
    {
        return $this->payments()->where('status', 'paid')->sum('amount');
    }

    public function hasIdPhoto()
    {
        return !empty($this->id_photo);
    }

    public function getStatusBadgeClass()
    {
        return match($this->enrollment_status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'enrolled' => 'primary',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    // Evaluation status helper methods
    public function hasBeenViewed()
    {
        return !is_null($this->first_viewed_at);
    }

    public function isBeingEvaluated()
    {
        return !is_null($this->evaluation_started_at) && is_null($this->evaluation_completed_at);
    }

    public function hasBeenEvaluated()
    {
        return !is_null($this->evaluation_completed_at);
    }

    public function getDocumentReviewProgress()
    {
        if ($this->documents_total_count == 0) {
            return 0;
        }
        return ($this->documents_reviewed_count / $this->documents_total_count) * 100;
    }

    public function updateDocumentCounts()
    {
        $documents = is_array($this->documents) ? $this->documents : [];
        $total = count($documents);
        $reviewed = 0;

        foreach ($documents as $document) {
            if (is_array($document) && isset($document['status'])) {
                if (in_array($document['status'], ['approved', 'rejected', 'verified'])) {
                    $reviewed++;
                }
            }
        }

        $this->update([
            'documents_total_count' => $total,
            'documents_reviewed_count' => $reviewed
        ]);

        return ['total' => $total, 'reviewed' => $reviewed];
    }

    // Get plain password for email purposes
    public function getPlainPassword()
    {
        if ($this->application_id) {
            // Application ID is already in format 25-001, so return as is
            return $this->application_id;
        }
        return null;
    }

    // Auto-generate application ID and password
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enrollee) {
            if (empty($enrollee->application_id)) {
                $year = date('Y');
                $shortYear = substr($year, -2); // Get last 2 digits of year (25 for 2025)
                
                // Find the last application for this year
                $lastApplication = static::where('application_id', 'like', "{$shortYear}-%")
                    ->orderBy('application_id', 'desc')
                    ->first();
                
                if ($lastApplication) {
                    // Extract number after the hyphen (e.g., "25-001" -> "001")
                    $lastNumber = (int) substr($lastApplication->application_id, 3);
                    $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                } else {
                    $newNumber = '001';
                }
                
                // Generate application ID in format: 25-001 (25 = year, 001 = sequence)
                $enrollee->application_id = $shortYear . '-' . $newNumber;
                
                // Generate password in format: 25-001 (easy to remember)
                $enrollee->password = Hash::make($shortYear . '-' . $newNumber);
            }
        });
    }
}
