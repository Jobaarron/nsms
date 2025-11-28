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
        'receipt_token',
        'receipt_token_expires_at',
        'receipt_access_count',
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
        'receipt_token_expires_at' => 'datetime',
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
        // Get all existing transaction IDs and find the highest sequential number
        $existingTransactions = self::where('transaction_id', 'LIKE', 'TXN-%')
            ->pluck('transaction_id')
            ->toArray();
        
        $maxSequential = -1;
        
        foreach ($existingTransactions as $transactionId) {
            // Extract the last part of the transaction ID (after the last dash)
            $parts = explode('-', $transactionId);
            if (count($parts) >= 3) {
                $sequential = (int) end($parts);
                $maxSequential = max($maxSequential, $sequential);
            }
        }
        
        // Next sequential number
        $sequentialNumber = $maxSequential + 1;
        
        // Format: TXN-{student_id}-{sequential_number_padded}
        // Pad with zeros to make it 4 digits (0000, 0001, 0002, etc.)
        return 'TXN-' . $studentId . '-' . str_pad($sequentialNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get count of pending payments due within 7 days for a student
     * Alert shows only when payment date is near (within 7 days)
     */
    public static function getDuePaymentsCountForStudent($studentId)
    {
        $today = now()->startOfDay();
        $sevenDaysFromNow = now()->addDays(7)->endOfDay();
        
        return self::where('payable_type', Student::class)
            ->where('payable_id', $studentId)
            ->where('status', 'pending')
            ->where('confirmation_status', 'pending')
            ->whereBetween('scheduled_date', [$today, $sevenDaysFromNow])
            ->count();
    }

    public static function getPendingPaymentConfirmationsCount()
    {
        $today = now()->startOfDay();
        $sevenDaysFromNow = now()->addDays(7)->endOfDay();
        
        return self::where('status', 'pending')
            ->where('confirmation_status', 'pending')
            ->whereBetween('scheduled_date', [$today, $sevenDaysFromNow])
            ->count();
    }
}
