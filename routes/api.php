<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Student;
use App\Models\FaceRegistration;

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    $user = User::where('email', $request->email)->first();
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }
    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]
    ]);
});

Route::get('/students', function () {
    $students = Student::select('id', 'first_name', 'last_name')->get();
    return response()->json(['success' => true, 'students' => $students]);
});

Route::post('/register-face', function (Request $request) {
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'source' => 'required|in:id_photo,manual_upload,camera_capture',
        'face_encoding' => 'nullable',
        'face_image_data' => 'nullable',
        'face_image_mime_type' => 'nullable|string',
        'device_id' => 'nullable|string',
        'confidence_score' => 'nullable|numeric',
        'face_landmarks' => 'nullable|array',
    ]);

    $encoding = $request->face_encoding;
    if (is_string($encoding)) {
        $decoded = json_decode($encoding, true);
        if (json_last_error() === JSON_ERROR_NONE) $encoding = $decoded;
    }
    if (!is_array($encoding) || empty($encoding)) {
        return response()->json(['error' => 'face_encoding must be a non-empty array'], 422);
    }

    $face = FaceRegistration::create([
        'student_id' => $request->student_id,
        'face_encoding' => json_encode($encoding),
        'face_image_data' => $request->face_image_data ?? null,
        'face_image_mime_type' => $request->face_image_mime_type ?? null,
        'confidence_score' => $request->input('confidence_score', 0.0),
        'face_landmarks' => $request->input('face_landmarks') ? json_encode($request->input('face_landmarks')) : null,
        'source' => $request->source,
        'device_id' => $request->device_id,
        'registered_by' => optional($request->user())->id,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Face registered successfully',
        'face' => $face
    ], 201);
});
