<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class GuidanceCounsellor extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'user_id',
        'employee_id',
        'license_number',
        'counsellor_level',
        'specializations',
        'grade_levels_assigned',
        'office_location',
        'available_hours',
        'max_students_per_day',
        'hire_date',
        'qualification',
        'certifications',
        'permissions',
        'last_login_at',
        'is_active',
    ];

    protected $casts = [
        'specializations' => 'array',
        'grade_levels_assigned' => 'array',
        'available_hours' => 'array',
        'certifications' => 'array',
        'permissions' => 'array',
        'hire_date' => 'date',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isHeadCounsellor()
    {
        return $this->counsellor_level === 'head_counsellor';
    }

    public function hasSpecialization($specialization)
    {
        return in_array($specialization, $this->specializations ?? []);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->whereJsonContains('grade_levels_assigned', $gradeLevel);
    }
}
