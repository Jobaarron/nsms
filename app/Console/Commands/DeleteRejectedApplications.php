<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollee;
use App\Mail\ApplicationRejectionMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeleteRejectedApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-rejected-applications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete rejected applications after 3 days and send notification emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting deletion of rejected applications...');

        try {
            // Find applications rejected more than 3 days ago
            $threeAaysAgo = Carbon::now()->subDays(3);
            
            $rejectedApplications = Enrollee::where('enrollment_status', 'rejected')
                ->where('rejected_at', '<=', $threeAaysAgo)
                ->get();

            $count = $rejectedApplications->count();
            $this->info("Found {$count} rejected applications to delete.");

            foreach ($rejectedApplications as $application) {
                try {
                    // Send rejection email if not already sent
                    if (!$application->rejection_email_sent) {
                        $this->sendRejectionEmail($application);
                        $application->update(['rejection_email_sent' => true]);
                    }

                    // Send notification to enrollee portal
                    $this->sendRejectionNotification($application);

                    // Delete the application
                    $application->delete();
                    
                    $this->info("Deleted application: {$application->application_id}");
                    Log::info('Rejected application deleted', [
                        'application_id' => $application->application_id,
                        'enrollee_id' => $application->id,
                        'rejected_at' => $application->rejected_at
                    ]);

                } catch (\Exception $e) {
                    $this->error("Error processing application {$application->application_id}: {$e->getMessage()}");
                    Log::error('Error deleting rejected application', [
                        'application_id' => $application->application_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->info("Completed deletion of {$count} rejected applications.");

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('Error in DeleteRejectedApplications command', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send rejection email to applicant
     */
    private function sendRejectionEmail(Enrollee $application)
    {
        try {
            Mail::to($application->email)->send(new ApplicationRejectionMail($application));
            Log::info('Rejection email sent', [
                'application_id' => $application->application_id,
                'email' => $application->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send rejection email', [
                'application_id' => $application->application_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send rejection notification to enrollee portal
     */
    private function sendRejectionNotification(Enrollee $application)
    {
        try {
            \App\Models\Notice::create([
                'enrollee_id' => $application->id,
                'title' => 'Application Rejected - Account Deletion Notice',
                'message' => 'Your enrollment application has been rejected. Your account and all associated data will be permanently deleted in accordance with our data retention policy. If you believe this is an error, please contact the admissions office immediately.',
                'is_global' => false,
                'created_by' => null,
                'priority' => 'urgent'
            ]);

            Log::info('Rejection notification sent to enrollee portal', [
                'application_id' => $application->application_id,
                'enrollee_id' => $application->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send rejection notification', [
                'application_id' => $application->application_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
