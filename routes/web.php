<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnrollmentController;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentWelcomeMail;
use App\Models\Student;
use App\Http\Controllers\teacherController;
use App\Http\Controllers\adminController;
use App\Http\Controllers\studentController;
use App\Http\Controllers\guidancedisciplineController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/login', function () {
//     return view('login');
// }); Excluded in guest side

// Enrollment side
Route::get('/enroll', function () {
    return view('enroll');
});


// Enrollment Create & Store
Route::get('/enroll', [EnrollmentController::class, 'create'])
     ->name('enroll.create');
Route::post('/enroll', [EnrollmentController::class, 'store'])
     ->name('enroll.store');

    //  Route::get('/mailtrap-test', function () {
    //     // Create or grab a Student instance — here we fake one
    //     $student = new Student([
    //         'first_name' => 'Job Aarron',
    //         'email'      => 'jobaarronmisenas26@gmail.com',
    //     ]);
    
    //     Mail::to(env('MAIL_TEST_RECIPIENT'))
    //     ->send(new StudentWelcomeMail($student, 'TestPwd123'));
    //     if (count(Mail::failures())) {
    //         dd('Failures: ', Mail::failures());
    //     } Testing Purposes
    
    //     return 'Check your Mailtrap inbox!';
    // });

Route::get('/teacher', [teacherController::class, 'index']);
Route::get('/admin', [adminController::class, 'index']);
Route::get('/student', [studentController::class, 'index']);
Route::get('/guidance', [guidancedisciplineController::class, 'index']);
