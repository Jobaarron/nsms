<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]
    ]);
});

Route::get('/students', function () {
    $students = Student::select('id', 'first_name', 'last_name')->get();

    return response()->json([
        'success' => true,
        'students' => $students
    ]);
});

// Register face
Route::post('/register-face', function (Request $request) {
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'face_encoding' => 'required',
        'face_image_data' => 'nullable',
        'face_image_mime_type' => 'nullable',
        'confidence_score' => 'nullable|numeric',
        'face_landmarks' => 'nullable|json',
        'source' => 'required|in:id_photo,manual_upload,camera_capture',
        'device_id' => 'nullable',
    ]);

    $face = FaceRegistration::create([
        'student_id' => $request->student_id,
        'face_encoding' => $request->face_encoding,
        'face_image_data' => $request->face_image_data,
        'face_image_mime_type' => $request->face_image_mime_type,
        'confidence_score' => $request->confidence_score ?? 0.0,
        'face_landmarks' => $request->face_landmarks,
        'source' => $request->source,
        'device_id' => $request->device_id,
        'registered_by' => auth()->id(), // or null if not using auth
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Face registered successfully',
        'face' => $face,
    ]);
});
