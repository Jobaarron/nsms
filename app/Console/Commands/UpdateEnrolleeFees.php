<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollee;
use App\Models\Fee;

class UpdateEnrolleeFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollees:update-fees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing enrollees with calculated fees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating enrollee fees...');
        
        $enrollees = Enrollee::whereNull('total_fees_due')
            ->orWhere('total_fees_due', 0)
            ->get();
            
        if ($enrollees->isEmpty()) {
            $this->info('No enrollees need fee updates.');
            return;
        }
        
        $updated = 0;
        
        foreach ($enrollees as $enrollee) {
            try {
                $feeCalculation = Fee::calculateTotalFeesForGrade(
                    $enrollee->grade_level_applied, 
                    $enrollee->academic_year
                );
                
                $enrollee->update([
                    'total_fees_due' => $feeCalculation['total_amount'] ?? 0,
                    'total_paid' => $enrollee->total_paid ?? 0
                ]);
                
                $this->line("Updated {$enrollee->application_id}: {$enrollee->grade_level_applied} - ₱" . number_format($feeCalculation['total_amount'] ?? 0, 2));
                $updated++;
                
            } catch (\Exception $e) {
                $this->error("Failed to update {$enrollee->application_id}: " . $e->getMessage());
            }
        }
        
        $this->info("Successfully updated {$updated} enrollees.");
    }
}
