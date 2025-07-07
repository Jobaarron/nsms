<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'id_photo',
        'documents',
        'first_name',
        'middle_name',
        'last_name',
        'dob',
        'religion',
        'email',
        'address',
        'grade_applied',
        'strand',
        'guardian_name',
        'guardian_contact',
        'last_school_type',
        'last_school_name',
        'medical_history',
        'payment_mode',
        'preferred_schedule',
        'is_paid',
        'password',
    ];

    protected $casts = [
        'documents'          => 'array',
        'dob'                => 'date',
        'preferred_schedule' => 'date',
        'is_paid'    => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];
}
