<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        
        // Only create test user if it doesn't exist
        // if (!User::where('email', 'test@example.com')->exists()) {
        //     User::factory()->create([
        //         'name' => 'Test User',
        //         'email' => 'test@example.com',
        //     ]);
        // }

        $this->call([
            RolePermissionSeeder::class,
            FeesTableSeeder::class,
            SubjectSeeder::class,
            // CashierSeeder::class, // Moved to RolePermissionSeeder
            SanctionsSeeder::class,
            ViolationListSeeder::class,
            // TestStudentSeeder::class, // Added test student data for login testing - AlvinTheKings
            // EnrolleeSeeder::class, // Added enrollee test data / Do no touch, it's still error. - Job
        ]);
    }
}
