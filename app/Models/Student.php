<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;



class Student extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'user_id',
        'enrollee_id',
        'student_id',
        'lrn',
        'password',
        'id_photo',
        'id_photo_mime_type',
        'id_photo_data_url',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'full_name',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'nationality',
        'religion',
        'email',
        'contact_number',
        'address',
        'city',
        'province',
        'zip_code',
        'grade_level',
        'strand',
        'track',
        'section',
        'student_type',
        'enrollment_status',
        'academic_year',
        'documents',
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
        'payment_mode',
        'is_paid',
        'total_fees_due',
        'total_paid',
        'payment_completed_at',
        'pre_registered_at',
        'is_active',
        'remarks',

    ];


    protected $casts = [
        'date_of_birth' => 'date',
        'pre_registered_at' => 'datetime',
        'payment_completed_at' => 'datetime',
        'is_active' => 'boolean',
        'is_paid' => 'boolean',
        'documents' => 'array',
        'total_fees_due' => 'decimal:2',
        'total_paid' => 'decimal:2'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'id_photo_data_url',
    ];
    
    protected $guard_name = 'student';
    
    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'student_id';
    }
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to enrollee record (if student was created from enrollment)
    public function enrollee()
    {
        return $this->belongsTo(Enrollee::class);
    }

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

    // public function getAgeAttribute()
    // {
    //     return $this->birth_date ? $this->birth_date->age : null;
    // }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade_level', $grade);
    }

    public function scopeBySection($query, $section)
    {
        return $query->where('section', $section);
    }

    // Helper methods
    public function canAccessFeatures()
    {
        return $this->is_active;
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? \Carbon\Carbon::parse($this->date_of_birth)->age : null;
    }

    /**
     * Get violations for this student
     */
    public function violations()
    {
        return $this->hasMany(Violation::class);
    }

    /**
     * Get face registrations for this student
     */
    public function faceRegistrations()
    {
        return $this->hasMany(FaceRegistration::class);
    }

    /**
     * Get the active face registration for this student
     */
    public function activeFaceRegistration()
    {
        return $this->hasOne(FaceRegistration::class)->where('is_active', true)->latest();
    }

    /**
     * Check if student has face registered
     */
    public function hasFaceRegistered()
    {
        return $this->activeFaceRegistration()->exists();
    }

    /**
     * Get face registration status for filtering
     */
    public function getFaceRegistrationStatusAttribute()
    {
        return $this->hasFaceRegistered() ? 'registered' : 'not_registered';
    }

    /**
     * Get ID photo as base64 data URL for display
     */
    public function getIdPhotoDataUrlAttribute()
    {
        if ($this->id_photo && $this->id_photo_mime_type) {
            return "data:{$this->id_photo_mime_type};base64,{$this->id_photo}";
        }
        return null;
    }

    /**
     * Check if student has ID photo
     */
    public function hasIdPhoto()
    {
        return !empty($this->id_photo);
    }
}