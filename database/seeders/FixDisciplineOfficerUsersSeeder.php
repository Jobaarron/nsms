<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Discipline;
use Illuminate\Support\Str;

class FixDisciplineOfficerUsersSeeder extends Seeder
{
    /**
     * Ensure all users with the discipline_officer role have an active Discipline record.
     */
    public function run()
    {
        $officers = User::role('discipline_officer')->get();
        foreach ($officers as $user) {
            $nameParts = explode(' ', $user->name ?? 'Unknown', 2);
            Discipline::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => 'DO' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'position' => 'Discipline Officer',
                    'specialization' => 'discipline_officer',
                    'is_active' => true,
                ]
            );
        }
    }
}
