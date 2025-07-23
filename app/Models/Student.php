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
        'lrn',
        'student_type',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'date_of_birth',
        'gender',
        'religion',
        'email',
        'contact_number',
        'address',
        'city',
        'province',
        'zip_code',
        'grade_level',
        'strand',
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
        'preferred_schedule',
        'id_photo',
        'documents',
        'password',
        'is_paid',
        'enrollment_status',
        'academic_year',
        'approved_by',
        'approved_at',
        'enrolled_at',
        // Additional
        'rejected_by',
        'rejected_at',
        'status_updated_at',
        'status_updated_by',
        'status_reason',
        
    ];


    protected $casts = [
        'birth_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_paid' => 'boolean'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $guard_name = 'student';
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'preferred_schedule' => 'date',
            'documents' => 'array', // This will automatically convert JSON to array when retrieved
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
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

    public function scopeEnrolled($query)
    {
        return $query->where('enrollment_status', 'enrolled');
    }

    public function scopePending($query)
    {
        return $query->where('enrollment_status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('enrollment_status', 'rejected');
    }

    public function scopeCurrentYear($query)
    {
        return $query->where('academic_year', date('Y'));
    }

    // Payment-related methods
    public function isPaid()
    {
        return $this->is_paid;
    }

    public function canAccessFeatures()
    {
        return $this->is_paid && $this->enrollment_status === 'enrolled';
    }

    public function getPaymentStatusAttribute()
    {
        return $this->is_paid ? 'Paid' : 'Unpaid';
    }

    public function getPaymentStatusBadgeAttribute()
    {
        return $this->is_paid ? 'success' : 'danger';
    }

    /**
     * Get violations for this student
     */
    public function violations()
    {
        return $this->hasMany(Violation::class);
    }
}