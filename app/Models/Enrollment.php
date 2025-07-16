<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Enrollment extends Model
{
    use HasFactory;

    // Add this line to specify the table name explicitly
    protected $table = 'enrollments';

    protected $fillable = [
        'student_id',
        'academic_year',
        'grade_level',
        'strand',
        'status',
        'processed_by',
        'processed_at',
        'remarks',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    // RELATIONSHIPS
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // SCOPES
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    // ACCESSORS
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getIsProcessedAttribute()
    {
        return in_array($this->status, ['approved', 'rejected']);
    }

    // MUTATORS - FIXED
    public function approve($userId = null)
    {
        $this->update([
            'status' => 'approved',
            'processed_by' => $userId ?? (Auth::check() ? Auth::id() : null), // FIXED
            'processed_at' => now(),
        ]);
    }

    public function reject($userId = null, $remarks = null)
    {
        $this->update([
            'status' => 'rejected',
            'processed_by' => $userId ?? (Auth::check() ? Auth::id() : null), // FIXED
            'processed_at' => now(),
            'remarks' => $remarks,
        ]);
    }
}
