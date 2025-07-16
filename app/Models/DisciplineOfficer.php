<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class DisciplineOfficer extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'user_id',
        'employee_id',
        'officer_level',
        'areas_of_responsibility',
        'grade_levels_assigned',
        'office_location',
        'patrol_schedule',
        'can_suspend',
        'can_expel',
        'hire_date',
        'qualification',
        'training_certifications',
        'permissions',
        'last_login_at',
        'is_active',
    ];

    protected $casts = [
        'areas_of_responsibility' => 'array',
        'grade_levels_assigned' => 'array',
        'patrol_schedule' => 'array',
        'training_certifications' => 'array',
        'permissions' => 'array',
        'can_suspend' => 'boolean',
        'can_expel' => 'boolean',
        'hire_date' => 'date',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isHeadDiscipline()
    {
        return $this->officer_level === 'head_discipline';
    }

    public function canSuspendStudents()
    {
        return $this->can_suspend;
    }

    public function canExpelStudents()
    {
        return $this->can_expel;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithSuspensionRights($query)
    {
        return $query->where('can_suspend', true);
    }
}
