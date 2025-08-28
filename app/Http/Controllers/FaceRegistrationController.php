<?php

namespace App\Http\Controllers;

use App\Models\FaceRegistration;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FaceRegistrationController extends Controller
{
public function register(Request $request)
{
    // Validate input
    $validated = $request->validate([
        'student_id' => 'required|integer|exists:students,id',
        'face_image_data' => 'required|string',
        'face_image_mime_type' => 'required|string|in:image/jpeg,image/png',
        'source' => 'nullable|string|in:registration_form,admin_portal,mobile_app'
    ]);

    DB::beginTransaction();
    try {
        // Deactivate any existing registration
        FaceRegistration::where('student_id', $validated['student_id'])
            ->where('is_active', true)
            ->update(['is_active' => false, 'deactivated_at' => now()]);

        // Create new registration with all required fields
        $registration = FaceRegistration::create([
            'student_id' => $validated['student_id'],
            'face_image_data' => $this->compressImageData($validated['face_image_data']),
            'face_image_mime_type' => $validated['face_image_mime_type'],
            'source' => $validated['source'] ?? 'registration_form',
            'registered_at' => now(),
            'is_active' => true,
            'face_encoding' => [],
            'confidence_score' => 0.0,
            'face_landmarks' => [],
            'registered_by' => auth()->id(),
            'device_id' => $request->header('Device-ID') ?? 'web',
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_time' => now()->toDateTimeString()
            ]
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'data' => $registration
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ], 500);
    }
}
}