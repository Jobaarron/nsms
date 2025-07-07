<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Student extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'id_photo',
        'documents',
        'first_name',
        'middle_name',
        'last_name',
        'dob',
        'religion',
        'email',
        'password',
        'address',
        'grade_applied',
        'strand',
        'guardian_name',
        'guardian_contact',
        'last_school_type',
        'last_school_name',
        'medical_history',
        'payment_mode',
        'is_paid',
        'preferred_schedule',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'documents' => 'array',
        'dob' => 'date',
        'preferred_schedule' => 'date',
        'is_paid' => 'boolean',
        'password' => 'hashed',
    ];

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }
}
