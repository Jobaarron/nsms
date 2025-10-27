<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DisciplineOfficerController;
use App\Http\Controllers\Api\FaceRecognitionController;


// -------------------------
// Login (Mobile) - TODO: Create AuthController
// -------------------------
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/logout', [AuthController::class, 'apiLogout'])->middleware('auth:sanctum');

// -------------------------
// Recognize Face (Public)
// -------------------------

Route::post('/recognize-face', [FaceRecognitionController::class, 'recognizeFace'])->name('api.recognize-face');

Route::middleware('auth:sanctum')->get('/students/face-registration-status', function () {
    $students = Student::with(['faceRegistrations' => function($query) {
        $query->orderBy('created_at', 'desc');
    }])->get();

    $students = $students->map(function($student) {
        $latestFace = $student->faceRegistrations->first();
        return [
            'id' => $student->id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'middle_name' => $student->middle_name,
            'suffix' => $student->suffix,
            'grade_level' => $student->grade_level,
            'strand' => $student->strand,
            'lrn' => $student->lrn,
            'guardian_name' => $student->guardian_name,
            'guardian_contact' => $student->guardian_contact,
            'has_face_registered' => $student->faceRegistrations->count() > 0,
            'latest_face' => $latestFace ? [
                'face_image_data' => $latestFace->face_image_data,
                'face_image_mime_type' => $latestFace->face_image_mime_type,
                'created_at' => $latestFace->created_at,
            ] : null,
        ];
    });

    return response()->json([
        'success' => true,
        'students' => $students
    ]);
});


// -------------------------
// Discipline Officer API Routes (Protected)
// -------------------------
Route::middleware('auth:sanctum')->prefix('discipline-officer')->group(function () {
    Route::get('/dashboard', [DisciplineOfficerController::class, 'dashboard']);
    Route::get('/students', [DisciplineOfficerController::class, 'getStudents']);
    Route::get('/students/{studentId}', [DisciplineOfficerController::class, 'getStudent']);
    Route::post('/violations', [DisciplineOfficerController::class, 'submitViolation']);
    Route::get('/violations', [DisciplineOfficerController::class, 'getViolations']);
    Route::get('/violations/{violationId}', [DisciplineOfficerController::class, 'getViolation']);
    Route::put('/violations/{violationId}', [DisciplineOfficerController::class, 'updateViolation']);
    Route::get('/violation-types', [DisciplineOfficerController::class, 'getViolationTypes']);
    Route::post('/check-duplicate-violation', [DisciplineOfficerController::class, 'checkDuplicateViolation']);
});
