<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckStudentsTable extends Command
{
    protected $signature = 'check:students-table';
    protected $description = 'Check students table structure';

    public function handle()
    {
        $this->info("Students table columns:");
        
        $columns = Schema::getColumnListing('students');
        
        foreach ($columns as $column) {
            $this->line("- {$column}");
        }
        
        $this->info("\nTotal columns: " . count($columns));
        
        // Check if specific fields exist
        $requiredFields = [
            'full_name', 'track', 'student_type', 'enrollment_status', 
            'academic_year', 'documents', 'id_photo_data_url', 
            'enrollee_id', 'pre_registered_at'
        ];
        
        $this->info("\nChecking required fields:");
        foreach ($requiredFields as $field) {
            $exists = Schema::hasColumn('students', $field);
            $status = $exists ? '✅' : '❌';
            $this->line("{$status} {$field}");
        }
        
        return 0;
    }
}
