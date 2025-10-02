<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Registrar extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The guard name for roles and permissions
     */
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'password',
        'contact_number',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'province',
        'zip_code',
        'position',
        'department',
        'hire_date',
        'employment_status',
        'qualifications',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'hire_date' => 'date',
        ];
    }

    /**
     * Boot method to generate employee ID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registrar) {
            if (empty($registrar->employee_id)) {
                $registrar->employee_id = self::generateEmployeeId();
            }
        });
    }

    /**
     * Generate unique employee ID for registrar
     */
    public static function generateEmployeeId()
    {
        $year = date('y'); // Last 2 digits of current year
        $latestRegistrar = self::where('employee_id', 'like', "REG-{$year}%")
                              ->orderBy('employee_id', 'desc')
                              ->first();

        if ($latestRegistrar) {
            $lastNumber = intval(substr($latestRegistrar->employee_id, -3));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'REG-' . $year . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get the full name attribute
     */
    public function getFullNameAttribute()
    {
        $name = trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
        return $this->suffix ? $name . ' ' . $this->suffix : $name;
    }

    /**
     * Get the name attribute (for compatibility)
     */
    public function getNameAttribute()
    {
        return $this->getFullNameAttribute();
    }

    /**
     * Scope for active registrars
     */
    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    /**
     * Check if registrar is active
     */
    public function isActive()
    {
        return $this->employment_status === 'active';
    }

    /**
     * Get years of service
     */
    public function getYearsOfServiceAttribute()
    {
        if (!$this->hire_date) {
            return 0;
        }

        return \Carbon\Carbon::parse($this->hire_date)->diffInYears(now());
    }
}
