<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollee;

class DebugEnrolleeDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:enrollee-documents {application_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug enrollee documents storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $applicationId = $this->argument('application_id');
        
        if ($applicationId) {
            $enrollee = Enrollee::where('application_id', $applicationId)->first();
            if (!$enrollee) {
                $this->error("Enrollee with Application ID {$applicationId} not found.");
                return;
            }
            $enrollees = collect([$enrollee]);
        } else {
            $enrollees = Enrollee::all();
        }
        
        foreach ($enrollees as $enrollee) {
            $this->info("=== Enrollee: {$enrollee->application_id} ({$enrollee->full_name}) ===");
            
            // Check ID Photo
            $this->line("ID Photo: " . ($enrollee->id_photo ? 'Present (' . strlen($enrollee->id_photo) . ' chars base64)' : 'Missing'));
            $this->line("ID Photo MIME: " . ($enrollee->id_photo_mime_type ?? 'Not set'));
            
            // Check Documents
            $this->line("Documents field type: " . gettype($enrollee->documents));
            
            if (is_array($enrollee->documents)) {
                $this->line("Documents count: " . count($enrollee->documents));
                foreach ($enrollee->documents as $index => $doc) {
                    if (is_string($doc)) {
                        $this->line("  Document {$index}: {$doc} (string path)");
                        // Check if file exists
                        $fullPath = storage_path('app/public/' . $doc);
                        $exists = file_exists($fullPath) ? 'EXISTS' : 'MISSING';
                        $this->line("    File status: {$exists}");
                    } else {
                        $this->line("  Document {$index}: " . json_encode($doc) . " (array)");
                    }
                }
            } else {
                $this->line("Documents: " . ($enrollee->documents ?? 'NULL'));
            }
            
            $this->line("");
        }
    }
}
