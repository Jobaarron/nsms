<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckFieldType extends Command
{
    protected $signature = 'check:field-type {table} {field}';
    protected $description = 'Check field type in database';

    public function handle()
    {
        $table = $this->argument('table');
        $field = $this->argument('field');
        
        $result = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'");
        
        if (empty($result)) {
            $this->error("Field '{$field}' not found in table '{$table}'");
            return 1;
        }
        
        $column = $result[0];
        $this->info("Field: {$column->Field}");
        $this->info("Type: {$column->Type}");
        $this->info("Null: {$column->Null}");
        $this->info("Key: {$column->Key}");
        $this->info("Default: " . ($column->Default ?? 'NULL'));
        $this->info("Extra: {$column->Extra}");
        
        return 0;
    }
}
