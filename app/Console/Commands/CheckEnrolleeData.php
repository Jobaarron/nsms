<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollee;

class CheckEnrolleeData extends Command
{
    protected $signature = 'check:enrollee-data {application_id}';
    protected $description = 'Check detailed enrollee data';

    public function handle()
    {
        $applicationId = $this->argument('application_id');
        $enrollee = Enrollee::where('application_id', $applicationId)->first();
        
        if (!$enrollee) {
            $this->error("Enrollee not found!");
            return 1;
        }

        $this->info("Detailed enrollee data:");
        
        $fields = [
            'id', 'application_id', 'lrn', 'first_name', 'middle_name', 'last_name', 'suffix',
            'date_of_birth', 'place_of_birth', 'gender', 'nationality', 'religion',
            'contact_number', 'email', 'address', 'city', 'province', 'zip_code',
            'grade_level_applied', 'strand_applied', 'track_applied', 'student_type',
            'enrollment_status', 'academic_year', 'documents', 'id_photo_data_url',
            'father_name', 'father_occupation', 'father_contact',
            'mother_name', 'mother_occupation', 'mother_contact',
            'guardian_name', 'guardian_contact', 'last_school_type', 'last_school_name',
            'medical_history', 'is_paid', 'payment_date', 'payment_completed_at'
        ];
        
        foreach ($fields as $field) {
            $value = $enrollee->$field;
            if (is_null($value)) {
                $this->line("❌ {$field}: NULL");
            } elseif (is_array($value)) {
                $this->line("✅ {$field}: [array with " . count($value) . " items]");
            } elseif (is_string($value) && strlen($value) > 50) {
                $this->line("✅ {$field}: " . substr($value, 0, 50) . "...");
            } else {
                $this->line("✅ {$field}: {$value}");
            }
        }
        
        return 0;
    }
}
