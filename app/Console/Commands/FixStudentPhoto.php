<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixStudentPhoto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:student-photo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix student photo by transferring from enrollee';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $student = \App\Models\Student::first();
        
        if (!$student) {
            $this->error('No student found');
            return;
        }
        
        $this->info('Student found: ' . $student->student_id);
        
        if ($student->enrollee_id) {
            $enrollee = \App\Models\Enrollee::find($student->enrollee_id);
            
            if ($enrollee && $enrollee->id_photo_data_url) {
                $student->update(['id_photo_data_url' => $enrollee->id_photo_data_url]);
                $this->info('✅ Photo transferred from enrollee to student');
                $this->info('Photo size: ' . strlen($enrollee->id_photo_data_url) . ' characters');
            } else {
                $this->warn('Enrollee found but no photo available');
            }
        } else {
            $this->warn('Student has no linked enrollee_id');
        }
    }
}
