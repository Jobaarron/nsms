<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id',
        'fee_id',
        'payable_id',
        'payable_type',
        'amount',
        'entrance_fee',
        'miscellaneous_fee',
        'tuition_fee',
        'others_fee',
        'total_fee',
        'status',
        'payment_method', // Now stores payment schedule: full, quarterly, monthly
        'reference_number',
        'notes',
        'paid_at',
        'processed_by',
        'confirmed_at',
        'cashier_notes',
        'confirmation_status',
        'scheduled_date',
        'period_name',
        // 'payment_mode', // Removed - redundant with payment_method
        'amount_received',
    ];

    /**
     * Get the parent payable model (enrollee or student).
     */
    public function payable()
    {
        return $this->morphTo();
    }

    /**
     * Get the fee that this payment is for.
     */
    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    /**
     * Get the cashier who processed this payment.
     */
    public function cashier()
    {
        return $this->belongsTo(Cashier::class, 'processed_by');
    }

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'scheduled_date' => 'date',
        'amount' => 'decimal:2',
        'entrance_fee' => 'decimal:2',
        'miscellaneous_fee' => 'decimal:2',
        'tuition_fee' => 'decimal:2',
        'others_fee' => 'decimal:2',
        'total_fee' => 'decimal:2',
        'amount_received' => 'decimal:2',
    ];

    /**
     * Scope for pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('confirmation_status', 'pending');
    }

    /**
     * Scope for confirmed payments.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('confirmation_status', 'confirmed');
    }

    /**
     * Scope for due payments.
     */
    public function scopeDue($query)
    {
        return $query->where('status', 'pending')
                    ->where('confirmation_status', 'pending');
    }

    /**
     * Scope for completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'paid')
                    ->where('confirmation_status', 'confirmed');
    }

    /**
     * Generate a sequential transaction ID
     */
    public static function generateTransactionId($studentId)
    {
        // Get the highest sequential number used so far for this student
        $lastPayment = self::where('transaction_id', 'LIKE', 'TXN-' . $studentId . '-%')
            ->orderBy('id', 'desc')
            ->first();
        
        $sequentialNumber = 0;
        
        if ($lastPayment) {
            // Extract the last part of the transaction ID (after the last dash)
            $parts = explode('-', $lastPayment->transaction_id);
            $lastSequential = (int) end($parts);
            $sequentialNumber = $lastSequential + 1;
        }
        
        // Format: TXN-{student_id}-{sequential_number_padded}
        // Pad with zeros to make it 4 digits (0000, 0001, 0002, etc.)
        return 'TXN-' . $studentId . '-' . str_pad($sequentialNumber, 4, '0', STR_PAD_LEFT);
    }
}
