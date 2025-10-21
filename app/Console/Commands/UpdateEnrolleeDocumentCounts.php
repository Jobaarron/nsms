<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollee;

class UpdateEnrolleeDocumentCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollees:update-document-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update document counts for all existing enrollees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating document counts for all enrollees...');
        
        $enrollees = Enrollee::all();
        $updated = 0;
        
        foreach ($enrollees as $enrollee) {
            $result = $enrollee->updateDocumentCounts();
            $this->line("Updated {$enrollee->application_id}: {$result['reviewed']}/{$result['total']} documents reviewed");
            $updated++;
        }
        
        $this->info("Successfully updated document counts for {$updated} enrollees.");
        
        return 0;
    }
}
