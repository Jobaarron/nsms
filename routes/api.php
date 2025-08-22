<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\FaceRegistration;
use App\Http\Controllers\ViolationController;
use App\Http\Controllers\AuthController;

// -------------------------
// Login (Mobile)
// -------------------------
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/logout', [AuthController::class, 'apiLogout'])->middleware('auth:sanctum');

// -------------------------
// Students (Protected)
// -------------------------
Route::middleware('auth:sanctum')->get('/students', function () {
    $students = Student::select(
        'id',
        'first_name',
        'last_name',
        'grade_level',
        'lrn' // include LRN
    )->get();

    return response()->json([
        'success' => true,
        'students' => $students
    ]);
});


// -------------------------
// Register Face (Protected)
// -------------------------
Route::middleware('auth:sanctum')->post('/register-face', function (Request $request) {
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'face_encoding' => 'required|array',
        'source' => 'required|in:id_photo,manual_upload,camera_capture',
        'face_image_data' => 'nullable',
        'face_image_mime_type' => 'nullable|string',
        'device_id' => 'nullable|string',
        'confidence_score' => 'nullable|numeric',
        'face_landmarks' => 'nullable|array',
    ]);

    // Check if student already has a registered face
    $existingFace = FaceRegistration::where('student_id', $request->student_id)->first();
    if ($existingFace) {
        return response()->json([
            'success' => false,
            'message' => 'Face already registered for this student',
            'error_code' => 'FACE_ALREADY_REGISTERED'
        ], 409); // 409 Conflict status code
    }

    $encoding = $request->face_encoding;

    // Normalize embedding
    $norm = sqrt(array_sum(array_map(fn($v) => $v*$v, $encoding)));
    $encoding = array_map(fn($v) => $v/$norm, $encoding);

    $face = FaceRegistration::create([
        'student_id' => $request->student_id,
        'face_encoding' => json_encode($encoding),
        'face_image_data' => $request->face_image_data ?? null,
        'face_image_mime_type' => $request->face_image_mime_type ?? null,
        'confidence_score' => $request->input('confidence_score', 0.0),
        'face_landmarks' => $request->input('face_landmarks') ? json_encode($request->input('face_landmarks')) : null,
        'source' => $request->source,
        'device_id' => $request->device_id,
        'registered_by' => $request->user()->id,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Face registered successfully',
        'face' => $face
    ], 201);
});
// -------------------------
// Recognize Face (Public)
// -------------------------
Route::post('/recognize-face', function (Request $request) {
    $request->validate([
        'face_encoding' => 'required|array',
        'threshold' => 'nullable|numeric|min:0.1|max:1.0',
    ]);

    $threshold = $request->input('threshold', 0.35); // cosine similarity threshold
    $inputEncoding = $request->face_encoding;

    // Normalize input embedding
    $norm = sqrt(array_sum(array_map(fn($v) => $v*$v, $inputEncoding)));
    $inputEncoding = array_map(fn($v) => $v/$norm, $inputEncoding);

    $registeredFaces = FaceRegistration::with('student')->whereNotNull('face_encoding')->get();

    $bestMatch = null;
    $bestScore = -1;
    $debug = [];

    foreach ($registeredFaces as $face) {
        $storedEncoding = json_decode($face->face_encoding, true);
        if (!is_array($storedEncoding) || empty($storedEncoding)) continue;

        // Normalize stored embedding
        $normStored = sqrt(array_sum(array_map(fn($v)=>$v*$v, $storedEncoding)));
        $storedEncoding = array_map(fn($v)=>$v/$normStored, $storedEncoding);

        // Cosine similarity
        $dot = array_sum(array_map(fn($a,$b)=>$a*$b, $inputEncoding, $storedEncoding));

        $debug[] = [
            'student_id' => $face->student_id,
            'student_name' => $face->student->first_name . ' ' . $face->student->last_name,
            'similarity' => $dot
        ];

        if ($dot > $bestScore && $dot >= $threshold) {
            $bestScore = $dot;
            $bestMatch = $face;
        }
    }

    if ($bestMatch) {
        return response()->json([
            'success' => true,
            'recognized' => true,
            'confidence' => $bestScore,
            'student' => $bestMatch->student,
            'face' => $bestMatch,
            'debug' => $debug
        ]);
    }

    return response()->json([
        'success' => true,
        'recognized' => false,
        'message' => 'No matching face found',
        'debug' => $debug
    ]);
});

// -------------------------
// Violation Routes (Protected)
// -------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/violations', [ViolationController::class,'store']); // Submit violation
    Route::get('/violations', [ViolationController::class,'index']); // List all
    Route::get('/violations/statistics', [ViolationController::class,'statistics']); // Stats
    Route::get('/violations/{id}', [ViolationController::class,'show']); // Show one
    Route::get('/students/{studentId}/violations', [ViolationController::class,'studentViolations']); // Student violations
});