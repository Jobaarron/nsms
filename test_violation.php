<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $violation = App\Models\Violation::create([
        'student_id' => 2,
        'title' => 'Test violation',
        'severity' => 'minor',
        'violation_date' => '2025-10-03',
        'reported_by' => 1,
        'status' => 'pending'
    ]);

    echo "Success: Violation created with ID " . $violation->id . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
