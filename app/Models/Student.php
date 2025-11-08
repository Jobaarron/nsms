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
        'user_id',
        'enrollee_id',
        'student_id',
        'lrn',
        'password',
        'id_photo',
        'id_photo_mime_type',
        'id_photo_data_url',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'full_name',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'nationality',
        'religion',
        'email',
        'contact_number',
        'address',
        'city',
        'province',
        'zip_code',
        'grade_level',
        'strand',
        'track',
        'section',
        'section_id',
        'student_type',
        'enrollment_status',
        'academic_year',
        'documents',
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
        // 'payment_mode', // Removed - now handled by payment_method in payments table
        'is_paid',
        'total_fees_due',
        'total_paid',
        'payment_completed_at',
        'pre_registered_at',
        'is_active',
        'remarks',

    ];


    protected $casts = [
        'date_of_birth' => 'date',
        'pre_registered_at' => 'datetime',
        'payment_completed_at' => 'datetime',
        'is_active' => 'boolean',
        'is_paid' => 'boolean',
        'documents' => 'array',
        'total_fees_due' => 'decimal:2',
        'total_paid' => 'decimal:2'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'id_photo_data_url',
    ];
    
    protected $guard_name = 'student';

    /**
     * Boot the model and set up model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-assign section when creating a new student
        static::creating(function ($student) {
            // Only auto-assign if section is not already set
            if (empty($student->section) && !empty($student->grade_level) && !empty($student->academic_year)) {
                $sectionData = self::autoAssignSection(
                    $student->grade_level,
                    $student->academic_year,
                    $student->strand,
                    $student->track
                );
                
                $student->section = $sectionData['section'];
                $student->section_id = $sectionData['section_id'];
            }
        });

        // Update section count when student is deleted
        static::deleting(function ($student) {
            if ($student->section_id) {
                $section = Section::find($student->section_id);
                if ($section) {
                    $section->removeStudent();
                }
            }
        });
    }
    
    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'student_id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getAttribute($this->getAuthIdentifierName());
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to enrollee record (if student was created from enrollment)
    public function enrollee()
    {
        return $this->belongsTo(Enrollee::class, 'enrollee_id');
    }

    /**
     * Get the section this student belongs to
     */
    public function sectionModel()
    {
        return $this->belongsTo(Section::class, 'section_id');
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade_level', $grade);
    }

    public function scopeBySection($query, $section)
    {
        return $query->where('section', $section);
    }

    // Helper methods
    public function canAccessFeatures()
    {
        return $this->is_active;
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? \Carbon\Carbon::parse($this->date_of_birth)->age : null;
    }

    /**
     * Get violations for this student
     */
    public function violations()
    {
        return $this->hasMany(Violation::class);
    }

    /**
     * Get face registrations for this student
     */
    public function faceRegistrations()
    {
        return $this->hasMany(FaceRegistration::class);
    }

    /**
     * Get the active face registration for this student
     */
    public function activeFaceRegistration()
    {
        return $this->hasOne(FaceRegistration::class)->where('is_active', true)->latest();
    }

    /**
     * Check if student has face registered
     */
    public function hasFaceRegistered()
    {
        return $this->activeFaceRegistration()->exists();
    }

    /**
     * Get face registration status for filtering
     */
    public function getFaceRegistrationStatusAttribute()
    {
        return $this->hasFaceRegistered() ? 'registered' : 'not_registered';
    }

    /**
     * Get ID photo as base64 data URL for display
     */
    public function getIdPhotoDataUrlAttribute()
    {
        if ($this->id_photo && $this->id_photo_mime_type) {
            return "data:{$this->id_photo_mime_type};base64,{$this->id_photo}";
        }
        return null;
    }

    /**
     * Check if student has ID photo
     */
    public function hasIdPhoto()
    {
        return !empty($this->id_photo);
    }

    /**
     * Get grades for this student
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Get class schedules for this student
     */
    public function classSchedules()
    {
        return ClassSchedule::where('grade_level', $this->grade_level)
                           ->where('section', $this->section)
                           ->where('academic_year', $this->academic_year)
                           ->where('is_active', true)
                           ->with(['subject', 'teacher'])
                           ->get();
    }

    /**
     * Get grades for a specific quarter with payment check
     */
    public function getGradesForQuarter($quarter, $academicYear = null)
    {
        $academicYear = $academicYear ?: $this->academic_year;
        
        // Check if student has paid for the quarter
        if (!$this->hasPaidForQuarter($quarter)) {
            return collect(); // Return empty collection if not paid
        }

        // Get approved grades from the grades table (final grades)
        $finalGrades = $this->grades()
                          ->where('quarter', $quarter)
                          ->where('academic_year', $academicYear)
                          ->where('is_final', true)
                          ->with(['subject', 'teacher.user'])
                          ->get();

        // If no final grades, check for approved grade submissions
        if ($finalGrades->isEmpty()) {
            $approvedSubmissions = \App\Models\GradeSubmission::where('status', 'approved')
                ->where('academic_year', $academicYear)
                ->where('quarter', $quarter)
                ->whereHas('teacher', function($query) {
                    $query->whereHas('facultyAssignments', function($subQuery) {
                        $subQuery->where('grade_level', $this->grade_level)
                                ->where('section', $this->section)
                                ->where('academic_year', $this->academic_year);
                        
                        // Match strand and track for SHS students
                        if (in_array($this->grade_level, ['Grade 11', 'Grade 12'])) {
                            if ($this->strand) {
                                $subQuery->where('strand', $this->strand);
                            }
                            if ($this->track) {
                                $subQuery->where('track', $this->track);
                            }
                        }
                    });
                })
                ->with(['subject', 'teacher.user'])
                ->get();

            // Convert approved submissions to grade format
            $gradesFromSubmissions = collect();
            foreach ($approvedSubmissions as $submission) {
                $gradesData = $submission->grades_data;
                foreach ($gradesData as $gradeData) {
                    if ($gradeData['student_id'] == $this->id) {
                        $gradesFromSubmissions->push((object)[
                            'id' => null,
                            'student_id' => $this->id,
                            'subject_id' => $submission->subject_id,
                            'teacher_id' => $submission->teacher_id,
                            'grade' => $gradeData['grade'],
                            'remarks' => $gradeData['remarks'] ?? null,
                            'quarter' => $quarter,
                            'academic_year' => $academicYear,
                            'is_final' => true,
                            'subject' => $submission->subject,
                            'teacher' => $submission->teacher,
                            'submitted_at' => $submission->approved_at
                        ]);
                    }
                }
            }
            
            return $gradesFromSubmissions;
        }

        return $finalGrades;
    }

    /**
     * Check if student has paid for a specific quarter
     */
    public function hasPaidForQuarter($quarter)
    {
        // Integration with existing payment system
        // This checks if the student has made payments for the required fees
        return $this->is_paid || $this->total_paid > 0;
    }

    /**
     * Get weekly class schedule
     */
    public function getWeeklySchedule()
    {
        $schedules = $this->classSchedules();
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
     * Get current academic performance
     */
    public function getAcademicPerformance($academicYear = null)
    {
        $academicYear = $academicYear ?: $this->academic_year;
        
        $performance = [
            'total_subjects' => 0,
            'quarters' => [],
            'general_average' => null
        ];
        
        foreach (['1st', '2nd', '3rd', '4th'] as $quarter) {
            // Use the updated getGradesForQuarter method that checks both final grades and approved submissions
            $quarterGrades = $this->getGradesForQuarter($quarter, $academicYear);
            
            if ($quarterGrades->isNotEmpty()) {
                $grades = $quarterGrades->pluck('grade')->filter();
                $performance['quarters'][$quarter] = [
                    'average' => $grades->avg(),
                    'subjects_count' => $quarterGrades->count(),
                    'passing_count' => $grades->filter(function($grade) { return $grade >= 75; })->count()
                ];
                
                // Update total subjects count
                $performance['total_subjects'] = max($performance['total_subjects'], $quarterGrades->count());
            }
        }
        
        // Calculate general average from all quarters
        if (!empty($performance['quarters'])) {
            $allAverages = collect($performance['quarters'])->pluck('average')->filter();
            $performance['general_average'] = $allAverages->avg();
        }
        
        return $performance;
    }

    /**
     * Automatically assign section based on capacity and strand/track
     */
    public static function autoAssignSection($gradeLevel, $academicYear, $strand = null, $track = null)
    {
        // For Senior High School, consider strand/track in section assignment
        $isSeniorHigh = in_array($gradeLevel, ['Grade 11', 'Grade 12']);
        
        // Create section description with strand/track info
        $sectionDescription = $gradeLevel;
        if ($isSeniorHigh && $strand) {
            $sectionDescription .= " - {$strand}";
            if ($track) {
                $sectionDescription .= " - {$track}";
            }
        }

        // Get available sections for this grade level and academic year
        $sectionsQuery = Section::where('grade_level', $gradeLevel)
                               ->where('academic_year', $academicYear)
                               ->where('is_active', true)
                               ->orderBy('section_name'); // A, B, C order

        $sections = $sectionsQuery->get();

        // If no sections exist, create default sections
        if ($sections->isEmpty()) {
            $defaultSections = ['A', 'B', 'C', 'D', 'E', 'F'];
            foreach ($defaultSections as $sectionName) {
                Section::create([
                    'section_name' => $sectionName,
                    'grade_level' => $gradeLevel,
                    'academic_year' => $academicYear,
                    'max_students' => 30, // Default capacity
                    'current_students' => 0,
                    'is_active' => true,
                    'description' => "Section {$sectionName} for {$sectionDescription}"
                ]);
            }
            $sections = $sectionsQuery->get();
        }

        // Find first available section
        foreach ($sections as $section) {
            if ($section->hasAvailableSlots()) {
                // For Senior High, check if we should group by strand/track
                if ($isSeniorHigh && $strand) {
                    // Count students with same strand/track in this section
                    $sameStrandTrackCount = self::where('grade_level', $gradeLevel)
                        ->where('section', $section->section_name)
                        ->where('academic_year', $academicYear)
                        ->where('strand', $strand)
                        ->when($track, function($query) use ($track) {
                            return $query->where('track', $track);
                        })
                        ->count();
                    
                    // If section has mixed strands/tracks and capacity allows, prefer grouping
                    $totalInSection = self::where('grade_level', $gradeLevel)
                        ->where('section', $section->section_name)
                        ->where('academic_year', $academicYear)
                        ->count();
                    
                    // If section is empty or has same strand/track students, use it
                    if ($totalInSection === 0 || $sameStrandTrackCount > 0) {
                        $section->addStudent();
                        return [
                            'section' => $section->section_name,
                            'section_id' => $section->id
                        ];
                    }
                } else {
                    // For elementary/JHS, just use first available section
                    $section->addStudent();
                    return [
                        'section' => $section->section_name,
                        'section_id' => $section->id
                    ];
                }
            }
        }

        // If all sections are full or no suitable section found, create a new one
        $nextSectionLetter = chr(ord('A') + $sections->count());
        $newSection = Section::create([
            'section_name' => $nextSectionLetter,
            'grade_level' => $gradeLevel,
            'academic_year' => $academicYear,
            'max_students' => 30,
            'current_students' => 1, // This student will be the first
            'is_active' => true,
            'description' => "Section {$nextSectionLetter} for {$sectionDescription}"
        ]);

        return [
            'section' => $newSection->section_name,
            'section_id' => $newSection->id
        ];
    }
}