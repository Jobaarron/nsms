<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckTableStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:table-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check students table structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $columns = \DB::select('SHOW COLUMNS FROM students');
        
        $this->info('Students table columns:');
        foreach ($columns as $column) {
            $this->info("- {$column->Field} ({$column->Type})");
        }
        
        // Check if id_photo_data_url exists
        $hasPhotoField = collect($columns)->contains('Field', 'id_photo_data_url');
        $this->info('Has id_photo_data_url field: ' . ($hasPhotoField ? 'Yes' : 'No'));
        
        // Check student record
        $student = \App\Models\Student::first();
        if ($student) {
            $this->info('Student raw attributes:');
            $attributes = $student->getAttributes();
            $this->info('id_photo_data_url in attributes: ' . (array_key_exists('id_photo_data_url', $attributes) ? 'Yes' : 'No'));
            if (array_key_exists('id_photo_data_url', $attributes)) {
                $this->info('Value: ' . ($attributes['id_photo_data_url'] ? 'Has value (' . strlen($attributes['id_photo_data_url']) . ' chars)' : 'NULL'));
            }
        }
    }
}
