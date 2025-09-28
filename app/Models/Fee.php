<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'amount',
        'academic_year',
        'applicable_grades',
        'educational_level',
        'fee_category',
        'payment_schedule',
        'is_required',
        'payment_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'applicable_grades' => 'array',
        'amount' => 'decimal:2',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get fees applicable for a specific grade level
     *
     * @param string $gradeLevel
     * @param string|null $academicYear
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getFeesForGrade($gradeLevel, $academicYear = null)
    {
        $academicYear = $academicYear ?: (date('Y') . '-' . (date('Y') + 1));
        
        return self::where('is_active', true)
            ->where('academic_year', $academicYear)
            ->where(function ($query) use ($gradeLevel) {
                $query->whereJsonContains('applicable_grades', $gradeLevel)
                      ->orWhereNull('applicable_grades');
            })
            ->orderBy('payment_order')
            ->get();
    }

    /**
     * Calculate total fees for a grade level
     *
     * @param string $gradeLevel
     * @param string|null $academicYear
     * @return array
     */
    public static function calculateTotalFeesForGrade($gradeLevel, $academicYear = null)
    {
        $fees = self::getFeesForGrade($gradeLevel, $academicYear);
        
        $feeBreakdown = [];
        $totalAmount = 0;
        
        foreach ($fees as $fee) {
            $feeBreakdown[] = [
                'id' => $fee->id,
                'name' => $fee->name,
                'description' => $fee->description,
                'amount' => $fee->amount,
                'fee_category' => $fee->fee_category,
                'payment_schedule' => $fee->payment_schedule,
                'is_required' => $fee->is_required,
                'payment_order' => $fee->payment_order,
            ];
            
            if ($fee->is_required) {
                $totalAmount += $fee->amount;
            }
        }
        
        return [
            'fees' => $feeBreakdown,
            'total_amount' => $totalAmount,
            'breakdown' => [
                'entrance' => $fees->where('fee_category', 'entrance')->sum('amount'),
                'tuition' => $fees->where('fee_category', 'tuition')->sum('amount'),
                'miscellaneous' => $fees->where('fee_category', 'miscellaneous')->sum('amount'),
                'other' => $fees->whereNotIn('fee_category', ['entrance', 'tuition', 'miscellaneous'])->sum('amount'),
            ]
        ];
    }

    /**
     * Get educational level for a grade
     *
     * @param string $gradeLevel
     * @return string
     */
    public static function getEducationalLevel($gradeLevel)
    {
        $preschoolGrades = ['Nursery', 'Junior Casa', 'Senior Casa'];
        $elementaryGrades = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];
        $juniorHighGrades = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
        $seniorHighGrades = ['Grade 11', 'Grade 12'];

        if (in_array($gradeLevel, $preschoolGrades)) {
            return 'preschool';
        } elseif (in_array($gradeLevel, $elementaryGrades)) {
            return 'elementary';
        } elseif (in_array($gradeLevel, $juniorHighGrades)) {
            return 'junior_high';
        } elseif (in_array($gradeLevel, $seniorHighGrades)) {
            return 'senior_high';
        }

        return 'other';
    }

    /**
     * Relationship with payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
