<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollee;
use App\Http\Controllers\EnrolleeController;
use Illuminate\Http\Request;

class TestControllerPreRegistration extends Command
{
    protected $signature = 'test:controller-pre-registration {application_id}';
    protected $description = 'Test pre-registration via controller';

    public function handle()
    {
        $applicationId = $this->argument('application_id');
        $enrollee = Enrollee::where('application_id', $applicationId)->first();
        
        if (!$enrollee) {
            $this->error("Enrollee not found!");
            return 1;
        }

        $this->info("Testing controller pre-registration for: {$enrollee->full_name}");
        
        // Authenticate the enrollee first
        \Illuminate\Support\Facades\Auth::guard('enrollee')->login($enrollee);
        
        // Create a mock request
        $request = new Request();
        
        // Create controller instance
        $controller = new EnrolleeController();
        
        try {
            // Call the preRegister method
            $response = $controller->preRegister($request);
            
            // Get response data
            $responseData = $response->getData(true);
            
            if ($responseData['success']) {
                $this->info("✅ Pre-registration successful!");
                $this->line("Student ID: " . $responseData['student_id']);
                $this->line("Password: " . $responseData['password']);
                $this->line("Message: " . $responseData['message']);
            } else {
                $this->error("❌ Pre-registration failed:");
                $this->error($responseData['message']);
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Exception during pre-registration:");
            $this->error($e->getMessage());
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
