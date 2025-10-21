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

    public function getIsFacultyHeadAttribute()
    {
        return $this->hasRole('faculty_head');
    }
    
    // Add this relationship to your existing User model
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function isAdmin()
    {
        $admin = $this->admin;
        return $admin && $admin->is_active;
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

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
        $student = $this->student;
        return $student && $student->is_active;
    }

    public function getUserRole()
    {
        if ($this->isAdmin()) return 'admin';
        if ($this->getIsFacultyHeadAttribute()) return 'faculty_head';
        //add disciplinestaffhere
        if ($this->isDisciplineStaff()) return 'discipline_officer';
        if ($this->isGuidanceStaff()) return 'guidance_counselor';
        if ($this->getIsTeacherAttribute()) return 'teacher';
        // if ($this->isGuidanceCounsellor()) return 'guidance_counsellor';
        // if ($this->isDisciplineOfficer()) return 'discipline_officer';
        if ($this->isStudent()) return 'student';
        return 'user';
    }

    /**
     * Check if user is guidance staff
     */
    public function isGuidanceStaff()
    {
        $guidance = $this->guidance;
        return ($guidance && $guidance->is_active);
    }

    /**
     * Check if user is discipline staff
     */
    public function isDisciplineStaff()
    {
    $discipline = $this->discipline;
    // Defensive: check for null and property
    return ($discipline && (property_exists($discipline, 'is_active') ? $discipline->is_active : true));
    }

    /**
     * Update last login timestamp 
     */
    public function updateLastLogin()
    {
        // Update last login logic can be implemented here
        return;
    }

    // Legacy guidanceDiscipline relationship removed - now using separate guidance() and discipline() relationships

    /**
     * Get the discipline record for this user (new system)
     */
    public function discipline()
    {
        return $this->hasOne(Discipline::class);
    }

    /**
     * Get the guidance record for this user (new system)
     */
    public function guidance()
    {
        return $this->hasOne(Guidance::class);
    }

    /**
     * Get faculty assignments for this teacher
     */
    public function facultyAssignments()
    {
        return $this->hasMany(FacultyAssignment::class, 'teacher_id');
    }

    /**
     * Get class schedules for this teacher
     */
    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class, 'teacher_id');
    }

    /**
     * Get grade submissions for this teacher
     */
    public function gradeSubmissions()
    {
        return $this->hasMany(GradeSubmission::class, 'teacher_id');
    }

    /**
     * Get grade submissions reviewed by this faculty head
     */
    public function reviewedGradeSubmissions()
    {
        return $this->hasMany(GradeSubmission::class, 'reviewed_by');
    }

    /**
     * Get faculty assignments made by this faculty head
     */
    public function assignmentsMade()
    {
        return $this->hasMany(FacultyAssignment::class, 'assigned_by');
    }

    /**
     * Get teacher's current teaching load
     */
    public function getCurrentTeachingLoad($academicYear = null)
    {
        $academicYear = $academicYear ?: (date('Y') . '-' . (date('Y') + 1));
        
        return $this->facultyAssignments()
                   ->where('academic_year', $academicYear)
                   ->where('status', 'active')
                   ->with(['subject', 'assignedBy'])
                   ->get();
    }

    /**
     * Get teacher's weekly schedule
     */
    public function getWeeklySchedule($academicYear = null)
    {
        $academicYear = $academicYear ?: (date('Y') . '-' . (date('Y') + 1));
        
        $schedules = $this->classSchedules()
                         ->where('academic_year', $academicYear)
                         ->where('is_active', true)
                         ->with(['subject'])
                         ->get();
        
        $weeklySchedule = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        foreach ($days as $day) {
            $weeklySchedule[$day] = $schedules->where('day_of_week', $day)
                                            ->sortBy('start_time')
                                            ->values();
        }
        
        return $weeklySchedule;
    }

    /**
     * Check if user can submit grades for a specific class
     */
    public function canSubmitGradesFor($subjectId, $gradeLevel, $section, $academicYear = null)
    {
        $academicYear = $academicYear ?: (date('Y') . '-' . (date('Y') + 1));
        
        return $this->facultyAssignments()
                   ->where('subject_id', $subjectId)
                   ->where('grade_level', $gradeLevel)
                   ->where('section', $section)
                   ->where('academic_year', $academicYear)
                   ->where('status', 'active')
                   ->exists();
    }

}
