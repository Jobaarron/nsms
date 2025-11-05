<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\User;

class CleanupTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up test data created for class list logic testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete all test students and users with student IDs starting with "TEST-". Continue?')) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }

        $this->info('Cleaning up test data...');

        // Find all test students
        $testStudents = Student::where('student_id', 'LIKE', 'TEST-%')->get();
        $testUserIds = $testStudents->pluck('user_id')->filter();

        $studentCount = $testStudents->count();
        $userCount = $testUserIds->count();

        // Delete test students
        Student::where('student_id', 'LIKE', 'TEST-%')->delete();

        // Delete associated user accounts
        if ($testUserIds->isNotEmpty()) {
            User::whereIn('id', $testUserIds)->delete();
        }

        $this->info("Deleted {$studentCount} test students and {$userCount} associated user accounts.");
        $this->info('Test data cleanup complete!');

        return 0;
    }
}
