<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Create a test discipline user and record
    $user = App\Models\User::where('email', 'test@example.com')->first();
    if (!$user) {
        $user = App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    $discipline = App\Models\Discipline::where('user_id', $user->id)->first();
    if (!$discipline) {
        $discipline = App\Models\Discipline::create([
            'user_id' => $user->id,
            'employee_id' => 'EMP' . $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'is_active' => true,
            'specialization' => 'discipline_officer',
        ]);
    }

    // Simulate creating violations through the DisciplineController storeViolation method
    $controller = new App\Http\Controllers\DisciplineController();

    // Create 3 violations with same title for student_id 2 using the controller method
    for ($i = 1; $i <= 3; $i++) {
        $request = new Illuminate\Http\Request([
            'student_id' => 2,
            'title' => 'Test violation',
            'description' => 'Test description ' . $i,
            'severity' => 'minor',
            'violation_date' => '2025-10-03',
            'violation_time' => '10:00:00',
            'status' => 'pending'
        ]);

        // Mock authentication
        Illuminate\Support\Facades\Auth::login($user);

        $response = $controller->storeViolation($request);

        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 302) {
            echo "Success: Violation $i created\n";
        } else {
            echo "Failed: Violation $i not created, status: " . $response->getStatusCode() . "\n";
            $content = $response->getContent();
            if ($content) {
                echo "Response: " . $content . "\n";
            }
        }
    }

    // Check remaining violations
    $violations = App\Models\Violation::where('student_id', 2)
        ->where('title', 'Test violation')
        ->get();

    echo "Total active violations: " . $violations->count() . "\n";
    foreach ($violations as $v) {
        echo "Violation ID {$v->id}: severity = {$v->severity}\n";
    }

    // Check archive
    $archives = App\Models\ArchiveViolation::where('student_id', 2)
        ->where('title', 'Test violation')
        ->get();

    echo "Total archived: " . $archives->count() . "\n";
    foreach ($archives as $a) {
        echo "Archive ID {$a->id}: severity = {$a->severity}, reason = {$a->archive_reason}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
