<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/enroll', function () {
    return view('enroll');
});

Route::get('/teacher', [teacherController::class, 'index']);
Route::get('/admin', [adminController::class, 'index']);
Route::get('/student', [studentController::class, 'index']);
Route::get('/guidance', [guidancedisciplineController::class, 'index']);


// Enrollment Create & Store
Route::get('/enroll', [EnrollmentController::class, 'create'])
     ->name('enroll.create');
Route::post('/enroll', [EnrollmentController::class, 'store'])
     ->name('enroll.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
