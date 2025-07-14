<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Student extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'user_id', 'student_id', 'lrn', 'enrollment_status', 'academic_year',
        'approved_by', 'approved_at', 'enrolled_at', 'id_photo', 'documents',
        'first_name', 'middle_name', 'last_name', 'suffix', 'date_of_birth',
        'place_of_birth', 'gender', 'civil_status', 'nationality', 'religion',
        'contact_number', 'email', 'address', 'barangay', 'city', 'province', 'zip_code',
        'grade_level', 'strand', 'track', 'section', 'student_type',
        'father_name', 'father_occupation', 'father_contact',
        'mother_name', 'mother_occupation', 'mother_contact',
        'guardian_name', 'guardian_relationship', 'guardian_contact', 'guardian_email', 'guardian_address',
        'last_school_type', 'last_school_name', 'last_school_address',
        'last_grade_completed', 'year_graduated', 'general_average',
        'medical_history', 'allergies', 'medications',
        'emergency_contact_name', 'emergency_contact_number', 'emergency_contact_relationship',
        'payment_mode', 'is_scholar', 'scholarship_type', 'scholarship_amount',
        'is_pwd', 'is_indigenous', 'preferred_schedule', 'enrollment_date',
        'is_active', 'remarks'
    ];

    protected $casts = [
        'documents' => 'array',
        'allergies' => 'array',
        'medications' => 'array',
        'date_of_birth' => 'date',
        'approved_at' => 'datetime',
        'enrolled_at' => 'datetime',
        'enrollment_date' => 'datetime',
        'preferred_schedule' => 'date',
        'is_scholar' => 'boolean',
        'is_pwd' => 'boolean',
        'is_indigenous' => 'boolean',
        'is_active' => 'boolean',
        'scholarship_amount' => 'decimal:2',
        'general_average' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEnrolled($query)
    {
        return $query->where('enrollment_status', 'enrolled');
    }

    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    // Helper methods
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name . ' ' . $this->suffix);
    }

    public function isEnrolled()
    {
        return $this->enrollment_status === 'enrolled';
    }
}
