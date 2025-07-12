<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Payment extends Model
{
    use HasFactory;

    // Add this line to specify the table name explicitly
    protected $table = 'payments';

    protected $fillable = [
        'student_id',
        'payment_type',
        'amount',
        'status',
        'payment_method',
        'reference_number',
        'due_date',
        'paid_date',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'float', // FIXED: Changed from 'decimal:2' to 'float'
        'due_date' => 'date',
        'paid_date' => 'date',
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

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                    ->orWhere(function($q) {
                        $q->where('status', 'pending')
                          ->where('due_date', '<', now());
                    });
    }

    public function scopeByPaymentType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    // ACCESSORS
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'paid' => 'success',
            'overdue' => 'danger',
            default => 'secondary'
        };
    }

    public function getIsOverdueAttribute()
    {
        return $this->status === 'pending' && $this->due_date < now();
    }

    public function getFormattedAmountAttribute()
    {
        // FIXED: Convert to float first, then format
        $amount = is_numeric($this->amount) ? (float) $this->amount : 0;
        return '₱' . number_format($amount, 2);
    }

    // MUTATORS - FIXED
    public function markAsPaid($paymentMethod = null, $referenceNumber = null, $userId = null)
    {
        $this->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod,
            'reference_number' => $referenceNumber,
            'paid_date' => now(),
            'processed_by' => $userId ?? (Auth::check() ? Auth::id() : null), // FIXED
        ]);
    }

    public function markAsOverdue()
    {
        if ($this->status === 'pending' && $this->due_date < now()) {
            $this->update(['status' => 'overdue']);
        }
    }
}
