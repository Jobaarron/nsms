<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'user_id',
        'employee_id',
        'department',
        'position',
        'admin_level',
        'permissions',
        'last_login_at',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Delegate authentication to the user
    public function getAuthIdentifierName()
    {
        return $this->user->getAuthIdentifierName();
    }

    // Check if admin is super admin
    public function isSuperAdmin()
    {
        return $this->admin_level === 'super_admin';
    }

    // Scope for active admins
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
