<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'subject',
        'quarter',
        'grade',
        'remarks',
        'academic_year',
    ];

    protected $casts = [
        'grade' => 'decimal:2',
    ];

    // RELATIONSHIPS
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // SCOPES
    public function scopeByAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    public function scopeByQuarter($query, $quarter)
    {
        return $query->where('quarter', $quarter);
    }

    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    // ACCESSORS
    public function getGradeStatusAttribute()
    {
        if ($this->grade >= 90) return 'Excellent';
        if ($this->grade >= 85) return 'Very Good';
        if ($this->grade >= 80) return 'Good';
        if ($this->grade >= 75) return 'Satisfactory';
        return 'Needs Improvement';
    }

    public function getIsPassingAttribute()
    {
        return $this->grade >= 75;
    }
}
