<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }


    // RELATIONSHIPS
    public function approvedStudents()
    {
        return $this->hasMany(Student::class, 'approved_by');
    }

    // public function gradesGiven()
    // {
    //     return $this->hasMany(Grade::class, 'teacher_id');
    // }

    // public function processedEnrollments()
    // {
    //     return $this->hasMany(Enrollment::class, 'processed_by');
    // }

    // public function processedPayments()
    // {
    //     return $this->hasMany(Payment::class, 'processed_by');
    // }

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
    
    // Add this relationship to your existing User model
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function isAdmin()
    {
        return $this->admin()->exists() && $this->admin->is_active;
    }

    // public function teacher()
    // {
    //     return $this->hasOne(Teacher::class);
    // }

    // public function guidanceCounsellor()
    // {
    //     return $this->hasOne(GuidanceCounsellor::class);
    // }

    // public function disciplineOfficer()
    // {
    //     return $this->hasOne(DisciplineOfficer::class);
    // }

    // public function isTeacher()
    // {
    //     return $this->teacher()->exists() && $this->teacher->is_active;
    // }

    // public function isGuidanceCounsellor()
    // {
    //     return $this->guidanceCounsellor()->exists() && $this->guidanceCounsellor->is_active;
    // }

    // public function isDisciplineOfficer()
    // {
    //     return $this->disciplineOfficer()->exists() && $this->disciplineOfficer->is_active;
    // }

    public function student()
{
    return $this->hasOne(Student::class);
}

    public function isStudent()
    {
        return $this->student()->exists() && $this->student->is_active;
    }

    public function getUserRole()
    {
        if ($this->isAdmin()) return 'admin';
        // if ($this->isTeacher()) return 'teacher';
        // if ($this->isGuidanceCounsellor()) return 'guidance_counsellor';
        // if ($this->isDisciplineOfficer()) return 'discipline_officer';
        if ($this->isStudent()) return 'student';
        return 'user';
    }

    /**
     * Check if user is guidance staff (counselor, discipline officer, or security guard)
     */
    public function isGuidanceStaff()
    {
        return $this->hasRole(['guidance_counselor', 'discipline_officer', 'security_guard']);
    }

    /**
     * Update last login timestamp (if you want to track this)
     */
    public function updateLastLogin()
    {
        // You can implement this if you add a last_login column to users table
        // For now, we'll just return without doing anything
        return;
    }

    /**
     * Get the guidance discipline record for this user
     */
    public function guidanceDiscipline()
    {
        return $this->hasOne(GuidanceDiscipline::class);
    }

}
