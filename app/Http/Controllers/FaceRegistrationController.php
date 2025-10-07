<?php

namespace App\Http\Controllers;

use App\Models\FaceRegistration;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FaceRegistrationController extends Controller
{
    /**
     * Display the student face registration page.
     */
    public function index()
    {
        $student = auth()->user()->load(['faceRegistrations' => function ($query) {
            $query->orderBy('registered_at', 'desc');
        }]);

        return view('student.face-registration', compact('student'));
    }

    /**
     * Register or update face data for a student.
     */
    public function register(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'face_image_data' => 'required|string',
            'face_image_mime_type' => 'required|string|in:image/jpeg,image/png',
            'source' => 'nullable|string|in:registration_form,admin_portal,mobile_app'
        ]);

        try {
            // Check if the student already has an active registration
            $existing = FaceRegistration::where('student_id', $validated['student_id'])
                ->where('is_active', true)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'This student’s face is already registered.'
                ], 400);
            }

            DB::beginTransaction();

            // Create new registration
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
                'message' => 'Face registered successfully.',
                'data' => $registration
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Face registration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to compress base64 image data
     */
    private function compressImageData($base64Data)
    {
        if (preg_match('/^data:(.*?);base64,(.*)$/', $base64Data, $matches)) {
            return $matches[2]; // Return only the raw base64 portion
        }
        return $base64Data;
    }
}
