<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // RELATIONSHIPS
    public function approvedStudents()
    {
        return $this->hasMany(Student::class, 'approved_by');
    }

    public function gradesGiven()
    {
        return $this->hasMany(Grade::class, 'teacher_id');
    }

    public function processedEnrollments()
    {
        return $this->hasMany(Enrollment::class, 'processed_by');
    }

    public function processedPayments()
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    // ACCESSORS
    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('name')->toArray();
    }

    public function getIsAdminAttribute()
    {
        return $this->hasRole('admin');
    }

    public function getIsTeacherAttribute()
    {
        return $this->hasRole('teacher');
    }
}
