<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\teacherController;
use App\Http\Controllers\adminController;
use App\Http\Controllers\studentController;
use App\Http\Controllers\guidancedisciplineController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/enroll', function () {
    return view('enroll');
});

Route::get('/teacher', [teacherController::class, 'index']);
Route::get('/admin', [adminController::class, 'index']);
Route::get('/student', [studentController::class, 'index']);
Route::get('/guidance', [guidancedisciplineController::class, 'index']);
