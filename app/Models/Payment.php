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
        'status',
        'payment_method',
        'reference_number',
        'notes',
        'paid_at',
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
}
