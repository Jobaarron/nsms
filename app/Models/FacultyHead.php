<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class FacultyHead extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'user_id',
        'employee_id',
        'department',
        'position',
        'appointed_date',
        'employment_status',
        'phone_number',
        'address',
        'qualifications',
        'permissions',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'appointed_date' => 'date',
        'permissions' => 'array',
        'is_active' => 'boolean'
    ];

    protected $guard_name = 'web';

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Faculty assignments made by this faculty head
    public function assignmentsMade()
    {
        return $this->hasMany(FacultyAssignment::class, 'assigned_by', 'user_id');
    }

    // Grade submissions reviewed by this faculty head
    public function reviewedSubmissions()
    {
        return $this->hasMany(GradeSubmission::class, 'reviewed_by', 'user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('employment_status', 'active');
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    // Helper methods
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    public function canManageDepartment($department)
    {
        return $this->department === $department || $this->department === 'All Departments';
    }

    public function hasPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    // Get statistics for dashboard
    public function getStatistics($academicYear = null)
    {
        $academicYear = $academicYear ?: (date('Y') . '-' . (date('Y') + 1));
        
        return [
            'total_assignments' => $this->assignmentsMade()
                                       ->where('academic_year', $academicYear)
                                       ->count(),
            'pending_reviews' => $this->reviewedSubmissions()
                                     ->where('status', 'submitted')
                                     ->count(),
            'approved_submissions' => $this->reviewedSubmissions()
                                          ->where('status', 'approved')
                                          ->where('academic_year', $academicYear)
                                          ->count(),
            'rejected_submissions' => $this->reviewedSubmissions()
                                          ->where('status', 'rejected')
                                          ->where('academic_year', $academicYear)
                                          ->count()
        ];
    }

    // Get recent activities
    public function getRecentActivities($limit = 10)
    {
        $assignments = $this->assignmentsMade()
                           ->with(['teacher', 'subject'])
                           ->latest()
                           ->limit($limit)
                           ->get()
                           ->map(function($assignment) {
                               return [
                                   'type' => 'assignment',
                                   'description' => "Assigned {$assignment->teacher->name} to {$assignment->subject->subject_name}",
                                   'date' => $assignment->created_at,
                                   'data' => $assignment
                               ];
                           });

        $reviews = $this->reviewedSubmissions()
                       ->with(['teacher', 'subject'])
                       ->whereNotNull('reviewed_at')
                       ->latest('reviewed_at')
                       ->limit($limit)
                       ->get()
                       ->map(function($submission) {
                           return [
                               'type' => 'review',
                               'description' => "Reviewed grades from {$submission->teacher->name} for {$submission->subject->subject_name}",
                               'date' => $submission->reviewed_at,
                               'data' => $submission
                           ];
                       });

        return $assignments->concat($reviews)
                          ->sortByDesc('date')
                          ->take($limit)
                          ->values();
    }
}
