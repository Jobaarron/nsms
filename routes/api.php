<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;
use App\Models\FaceRegistration;
use App\Http\Controllers\ViolationController;
use App\Http\Controllers\AuthController;

// -------------------------
// Login (Mobile) - TODO: Create AuthController
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
        'middle_name',
        'suffix',
        'grade_level',
        'strand',
        'lrn',
        'guardian_name',
        'guardian_contact'
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
// Violation Routes (Protected)
// -------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/violations', [ViolationController::class,'store']); // Submit violation
    Route::get('/violations', [ViolationController::class,'index']); // List all
    Route::get('/violations/statistics', [ViolationController::class,'statistics']); // Stats
    Route::get('/violations/{id}', [ViolationController::class,'show']); // Show one
    Route::put('/violations/{id}', [ViolationController::class,'update']); // ✅ Update violation
    Route::post('/violations/check-duplicate', [ViolationController::class, 'checkDuplicate']);
    Route::get('/students/{studentId}/violations', [ViolationController::class,'studentViolations']); // Student violations
});

Route::middleware('auth:sanctum')->get('/students/{id}', function ($id) {
    $student = Student::with(['faceRegistrations' => function($query) {
        $query->orderBy('created_at', 'desc');
    }])->find($id);

    if (!$student) {
        return response()->json([
            'success' => false,
            'message' => 'Student not found'
        ], 404);
    }

    $latestFace = $student->faceRegistrations->first();

    return response()->json([
        'success' => true,
        'student' => [
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
            // Add face registrations array for the details tab
            'face_registrations' => $student->faceRegistrations->map(function($registration) {
                return [
                    'face_image_data' => $registration->face_image_data,
                    'face_image_mime_type' => $registration->face_image_mime_type,
                    'created_at' => $registration->created_at,
                ];
            })->toArray()
        ]
    ]);
});

Route::middleware('auth:sanctum')->get('/students/{studentId}/face-registrations', function ($studentId) {
    $registrations = FaceRegistration::where('student_id', $studentId)
        ->orderBy('created_at', 'desc')
        ->get(['face_image_data', 'face_image_mime_type', 'created_at']);
    
    return response()->json([
        'success' => true,
        'faces' => $registrations
    ]);
});