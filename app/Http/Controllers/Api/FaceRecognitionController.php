<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\FaceRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FaceRecognitionController extends Controller
{
    /**
     * Register face from student ID photo
     */
    public function registerFace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'face_encoding' => 'required|array',
            'confidence_score' => 'nullable|numeric|between:0,1',
            'face_landmarks' => 'nullable|array',
            'device_id' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $student = Student::findOrFail($request->student_id);

            // Check if student has ID photo
            if (!$student->hasIdPhoto()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student does not have an ID photo for face registration'
                ], 400);
            }

            // Deactivate existing face registrations for this student
            FaceRegistration::where('student_id', $student->id)
                           ->where('is_active', true)
                           ->update(['is_active' => false]);

            // Create new face registration from ID photo
            // Ensure face_encoding is always stored as an array
            $faceEncoding = $request->face_encoding;
            if (is_string($faceEncoding)) {
                $decoded = json_decode($faceEncoding, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $faceEncoding = $decoded;
                }
            }
            $faceRegistration = FaceRegistration::create([
                'student_id' => $student->id,
                'face_encoding' => $faceEncoding,
                'face_image_data' => $student->id_photo,
                'face_image_mime_type' => $student->id_photo_mime_type,
                'confidence_score' => $request->confidence_score ?? 0.0,
                'face_landmarks' => $request->face_landmarks,
                'source' => 'id_photo',
                'registered_at' => now(),
                'registered_by' => auth()->id(),
                'is_active' => true,
                'device_id' => $request->device_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Face registered successfully',
                'data' => [
                    'registration_id' => $faceRegistration->id,
                    'student_id' => $student->id,
                    'student_name' => $student->full_name,
                    'confidence_score' => $faceRegistration->confidence_score,
                    'registered_at' => $faceRegistration->registered_at
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Face registration failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Face registration failed',
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Recognize face from camera/uploaded image
     */
    public function recognizeFace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'face_encoding' => 'required|array',
            'confidence_threshold' => 'nullable|numeric|between:0,1',
            'device_id' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $confidenceThreshold = $request->confidence_threshold ?? 0.6;
            $inputEncoding = $request->face_encoding;
            if (is_string($inputEncoding)) {
                $decoded = json_decode($inputEncoding, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $inputEncoding = $decoded;
                }
            }

            // Get all active face registrations
            $activeRegistrations = FaceRegistration::active()
                                                 ->with('student')
                                                 ->get();

            $matches = [];

            foreach ($activeRegistrations as $registration) {
                // Calculate similarity between encodings
                // This is a simplified comparison - in production, use proper face recognition library
                $similarity = $this->calculateSimilarity($inputEncoding, $registration->face_encoding);

                if ($similarity >= $confidenceThreshold) {
                    $matches[] = [
                        'student_id' => $registration->student_id,
                        'student_name' => $registration->student->full_name,
                        'student_number' => $registration->student->student_id,
                        'confidence_score' => $similarity,
                        'registration_id' => $registration->id
                    ];
                }
            }

            // Sort matches by confidence score (highest first)
            usort($matches, function ($a, $b) {
                return $b['confidence_score'] <=> $a['confidence_score'];
            });

            if (empty($matches)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No matching face found',
                    'data' => []
                ], 404);
            }

            // Log recognition attempt
            Log::info('Face recognition attempt', [
                'matches_found' => count($matches),
                'best_match' => $matches[0] ?? null,
                'device_id' => $request->device_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Face recognized successfully',
                'data' => [
                    'matches' => $matches,
                    'best_match' => $matches[0],
                    'total_matches' => count($matches)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Face recognition failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Face recognition failed',
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get face registration status for a student
     */
    public function getFaceStatus(Student $student)
    {
        try {
            $faceRegistration = $student->activeFaceRegistration;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'student_id' => $student->id,
                    'student_name' => $student->full_name,
                    'has_face_registered' => $student->hasFaceRegistered(),
                    'has_id_photo' => $student->hasIdPhoto(),
                    'can_register_face' => $student->hasIdPhoto() && !$student->hasFaceRegistered(),
                    'registration' => $faceRegistration ? [
                        'id' => $faceRegistration->id,
                        'source' => $faceRegistration->source,
                        'confidence_score' => $faceRegistration->confidence_score,
                        'registered_at' => $faceRegistration->registered_at,
                        'registered_by' => $faceRegistration->registeredBy?->name
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get face status failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get face status',
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Calculate similarity between two face encodings
     * This is a simplified implementation - use proper face recognition library in production
     */
    private function calculateSimilarity(array $encoding1, array $encoding2)
    {
        if (count($encoding1) !== count($encoding2)) {
            return 0.0;
        }

        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        for ($i = 0; $i < count($encoding1); $i++) {
            $dotProduct += $encoding1[$i] * $encoding2[$i];
            $norm1 += $encoding1[$i] * $encoding1[$i];
            $norm2 += $encoding2[$i] * $encoding2[$i];
        }

        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);

        if ($norm1 == 0 || $norm2 == 0) {
            return 0.0;
        }

        // Cosine similarity
        return $dotProduct / ($norm1 * $norm2);
    }

    /**
     * Get student ID photo for face processing
     */
    public function getStudentPhoto(Student $student)
    {
        try {
            if (!$student->hasIdPhoto()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student does not have an ID photo'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'student_id' => $student->id,
                    'student_name' => $student->full_name,
                    'image_data_url' => $student->id_photo_data_url,
                    'mime_type' => $student->id_photo_mime_type
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get student photo failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get student photo',
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
