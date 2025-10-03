<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckStudentData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:student-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check student data for debugging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $student = \App\Models\Student::first();
        
        if ($student) {
            $this->info('Student found:');
            $this->info('ID: ' . $student->id);
            $this->info('Student ID: ' . $student->student_id);
            $this->info('First Name: ' . $student->first_name);
            $this->info('Last Name: ' . $student->last_name);
            $this->info('Full Name: ' . ($student->full_name ?? 'Not set'));
            $student->refresh(); // Refresh from database
            $this->info('Has Photo: ' . ($student->id_photo_data_url ? 'Yes (' . strlen($student->id_photo_data_url) . ' chars)' : 'No'));
            $this->info('Email: ' . ($student->email ?? 'Not set'));
        } else {
            $this->error('No student found');
        }
    }
}
