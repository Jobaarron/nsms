<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FullClassListTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:full-class-test {--setup : Setup test data first} {--cleanup : Cleanup after testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run complete class list logic testing with setup and cleanup options';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Full Class List Logic Testing Suite');
        $this->info('====================================');

        // Step 1: Setup test data if requested
        if ($this->option('setup')) {
            $this->info("\n📋 Step 1: Setting up test data...");
            Artisan::call('test:setup-class-data');
            $this->info(Artisan::output());
        }

        // Step 2: Run the main logic tests
        $this->info("\n🧪 Step 2: Running logic tests...");
        Artisan::call('test:class-logic');
        $this->info(Artisan::output());

        // Step 3: Provide usage instructions
        $this->info("\n📖 Step 3: Manual Testing Instructions");
        $this->info("=====================================");
        $this->info("Now you can manually test the following:");
        $this->info("");
        $this->info("🎯 Faculty Head Module:");
        $this->info("  1. Go to Faculty Head Dashboard");
        $this->info("  2. Click on section badges in assignment tables");
        $this->info("  3. Verify modal titles show: 'Class List - Grade X - Section - Strand - Track'");
        $this->info("  4. Verify student lists show only students from that specific class");
        $this->info("");
        $this->info("🎯 Registrar Module:");
        $this->info("  1. Go to Registrar Class Lists");
        $this->info("  2. Filter by Grade Level, Strand, Track, Section");
        $this->info("  3. Verify page header shows: 'Student List - Grade X - Section - Strand - Track'");
        $this->info("  4. Verify only students from selected filters appear");
        $this->info("");
        $this->info("🎯 Teacher Module:");
        $this->info("  1. Go to Teacher Advisory page");
        $this->info("  2. Verify class names show full academic structure");
        $this->info("  3. Go to Grade Entry");
        $this->info("  4. Verify class information displays properly");
        $this->info("");
        $this->info("🎯 Student Module:");
        $this->info("  1. Login as any test student");
        $this->info("  2. Check Dashboard, Subjects, Schedule, Grades pages");
        $this->info("  3. Verify class information shows full academic structure");

        // Step 4: Cleanup if requested
        if ($this->option('cleanup')) {
            $this->info("\n🧹 Step 4: Cleaning up test data...");
            if ($this->confirm('Remove all test data now?')) {
                Artisan::call('test:cleanup', ['--force' => true]);
                $this->info(Artisan::output());
            }
        } else {
            $this->info("\n💡 Tip: Run 'php artisan test:cleanup' when you're done testing to remove test data.");
        }

        $this->info("\n✅ Full testing suite completed!");
        
        return 0;
    }
}
