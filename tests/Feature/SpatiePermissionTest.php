<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpatiePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_assigned_role()
    {
        // Create a role
        $role = Role::create(['name' => 'test-role']);
        
        // Create a user
        $user = User::factory()->create();
        
        // Assign role to user
        $user->assignRole('test-role');
        
        // Assert user has role
        $this->assertTrue($user->hasRole('test-role'));
    }

    public function test_role_can_be_assigned_permissions()
    {
        // Create a permission
        $permission = Permission::create(['name' => 'test-permission']);
        
        // Create a role
        $role = Role::create(['name' => 'test-role']);
        
        // Assign permission to role
        $role->givePermissionTo('test-permission');
        
        // Assert role has permission
        $this->assertTrue($role->hasPermissionTo('test-permission'));
    }
}
