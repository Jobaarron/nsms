<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnrollStudentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Enrollee;
use App\Models\Fee;
use App\Mail\EnrolleeCredentialsMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Show the enrollment form.
     */
    public function create()
    {
        return view('enroll');
    }

    /**
     * Handle the enrollment submission.
     */
    public function store(EnrollStudentRequest $request): RedirectResponse
    {
        // 1) Validation is now handled by EnrollStudentRequest.
        $data = $request->validated();

        // The old inline validation is removed. The validated data is accessed via $request->validated().
        /*
        $data = $request->validate([
            'id_photo'           => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'documents'          => 'required|array|min:1',
            'documents.*'        => 'file|mimes:pdf,docx,jpeg,png,jpg|max:4096',
            
            // Enrollee Info
            'lrn'                => 'nullable|string|max:12',
            'student_type'       => 'required|in:new,transferee,returnee,continuing',
            
            // Name fields
            'first_name'         => 'required|string|max:50',
            'middle_name'        => 'nullable|string|max:50',
            'last_name'          => 'required|string|max:50',
            'suffix'             => 'nullable|string|max:10',
            
            // Personal Info
            'date_of_birth'      => 'required|date|before:today',
            'gender'             => 'required|in:male,female',
            'religion'           => 'nullable|string|max:100',
            
            // Contact Info
            'email'              => 'required|email|unique:enrollees,email',
            'contact_number'     => 'nullable|regex:/^09[0-9]{9}$/',
            
            // Address
            'address'            => 'required|string|max:255',
            'city'               => 'nullable|string|max:100',
            'province'           => 'nullable|string|max:100',
            'zip_code'           => 'nullable|string|max:10',
            
            // Academic Info Applied For
            'grade_level'        => 'required|in:Nursery,Junior Casa,Senior Casa,Grade 1,Grade 2,Grade 3,Grade 4,Grade 5,Grade 6,Grade 7,Grade 8,Grade 9,Grade 10,Grade 11,Grade 12',
            'strand'             => [
                'nullable',
                'required_if:grade_level,Grade 11,Grade 12',
                'string',
                'max:50',
            ],
            
            // Parent/Guardian Info
            'father_name'        => 'nullable|string|max:100',
            'father_occupation'  => 'nullable|string|max:100',
            'father_contact'     => 'nullable|regex:/^09[0-9]{9}$/',
            'mother_name'        => 'nullable|string|max:100',
            'mother_occupation'  => 'nullable|string|max:100',
            'mother_contact'     => 'nullable|regex:/^09[0-9]{9}$/',
            'guardian_name'      => 'required|string|max:100',
            'guardian_contact'   => [
                'required',
                'regex:/^09[0-9]{9}$/',
            ],
            
            // School Info
            'last_school_type'   => 'nullable|in:public,private',
            'last_school_name'   => 'nullable|string|max:100',
            
            // Medical & Payment
            'medical_history'    => 'nullable|string|max:1000',
            'payment_mode'       => 'required|in:cash,online payment,scholarship,voucher',
        ]);
        */

        // 2) Handle ID photo from temporary files or direct upload
        $tempFiles = session('temp_enrollment_files', []);
        
        // Find ID photo in temporary files
        $idPhotoFile = null;
        foreach ($tempFiles as $fileData) {
            if ($fileData['type'] === 'id_photo') {
                $idPhotoFile = $fileData;
                break;
            }
        }
        
        if ($idPhotoFile && Storage::disk('public')->exists($idPhotoFile['path'])) {
            // Use temporary file
            $photoContent = Storage::disk('public')->get($idPhotoFile['path']);
            $data['id_photo'] = base64_encode($photoContent);
            $data['id_photo_mime_type'] = $idPhotoFile['mime_type'];
        } elseif ($request->hasFile('id_photo')) {
            // Fallback to direct upload
            $photo = $request->file('id_photo');
            $data['id_photo'] = base64_encode(file_get_contents($photo->getRealPath()));
            $data['id_photo_mime_type'] = $photo->getMimeType();
        }

        // 3) Handle documents from temporary files or direct upload
        $documents = [];
        
        // Get documents from temporary files
        foreach ($tempFiles as $fileData) {
            if ($fileData['type'] === 'documents' && Storage::disk('public')->exists($fileData['path'])) {
                // Move temporary file to permanent location
                $permanentPath = 'documents/' . $fileData['id'] . '.' . $fileData['extension'];
                Storage::disk('public')->move($fileData['path'], $permanentPath);
                
                $documents[] = [
                    'type' => strtoupper($fileData['extension']) ?: 'Unknown',
                    'filename' => $fileData['original_name'],
                    'path' => $permanentPath,
                    'mime_type' => $fileData['mime_type'],
                    'size' => $fileData['size'],
                    'uploaded_at' => $fileData['uploaded_at'],
                    'status' => 'pending'
                ];
            }
        }
        
        // Fallback to direct upload if no temporary files
        if (empty($documents) && $request->hasFile('documents')) {
            foreach ($request->file('documents') as $doc) {
                $path = $doc->store('documents', 'public');
                $documents[] = [
                    'type' => strtoupper($doc->getClientOriginalExtension()) ?: 'Unknown',
                    'filename' => $doc->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $doc->getMimeType(),
                    'size' => $doc->getSize(),
                    'uploaded_at' => now()->toISOString(),
                    'status' => 'pending'
                ];
            }
        }
        
        $data['documents'] = $documents; // Store as array of objects with metadata

        // 4) Map grade_level to grade_level_applied for enrollee
        $data['grade_level_applied'] = $data['grade_level'];
        unset($data['grade_level']);
        
        // 5) Map strand to strand_applied for enrollee
        if (isset($data['strand'])) {
            $data['strand_applied'] = $data['strand'];
            unset($data['strand']);
        }

        // 5.5) Handle track_applied for TVL students
        // track_applied field is already properly named and will be included in the data

        // 6) Set enrollment defaults
        $data['enrollment_status'] = 'pending';
        $data['academic_year'] = date('Y') . '-' . (date('Y') + 1);
        $data['application_date'] = now();

        // 6.5) Calculate fees for the selected grade level
        try {
            $feeCalculation = Fee::calculateTotalFeesForGrade($data['grade_level_applied'], $data['academic_year']);
            $data['total_fees_due'] = $feeCalculation['total_amount'] ?? 0;
            $data['total_paid'] = 0;
            
            Log::info('Fee calculation successful for enrollment', [
                'grade_level' => $data['grade_level_applied'],
                'academic_year' => $data['academic_year'],
                'total_fees_due' => $data['total_fees_due'],
                'fee_count' => count($feeCalculation['fees'] ?? [])
            ]);
        } catch (\Exception $e) {
            Log::error('Fee calculation failed during enrollment', [
                'grade_level' => $data['grade_level_applied'],
                'academic_year' => $data['academic_year'],
                'error' => $e->getMessage()
            ]);
            
            // Set default values if fee calculation fails
            $data['total_fees_due'] = 0;
            $data['total_paid'] = 0;
        }

        // 6.9) Set default "N/A" for medical_history if empty
        if (empty($data['medical_history']) || trim($data['medical_history']) === '') {
            $data['medical_history'] = 'N/A';
        }

        // 7) Create enrollment application (Enrollee record)
        $enrollee = Enrollee::create($data);

        // 7.5) Clean up temporary files after successful enrollment
        $this->cleanupTempFiles($tempFiles);

        // 8) Send enrollment credentials email
        try {
            Mail::to($enrollee->email)->send(new EnrolleeCredentialsMail($enrollee));
            Log::info('Enrollment credentials email sent to: ' . $enrollee->email . ' with Application ID: ' . $enrollee->application_id);
        } catch (\Exception $e) {
            Log::error('Failed to send enrollment credentials email: ' . $e->getMessage());
        }

        // 9) Redirect back with success message
        return redirect()
            ->route('enroll.create')
            ->with('success', 'Enrollment application submitted successfully! Please check your email for your login credentials.');
    }

    /**
     * Calculate fees for a specific grade level (API endpoint)
     *
     * @param string $gradeLevel
     * @return JsonResponse
     */
    public function calculateFees($gradeLevel): JsonResponse
    {
        try {
            // Decode URL-encoded grade level (e.g., "Grade%201" becomes "Grade 1")
            $gradeLevel = urldecode($gradeLevel);
            
            // Get current academic year
            $academicYear = date('Y') . '-' . (date('Y') + 1);
            
            // Calculate fees for the grade level
            $feeCalculation = Fee::calculateTotalFeesForGrade($gradeLevel, $academicYear);
            
            // Add educational level information
            $feeCalculation['educational_level'] = Fee::getEducationalLevel($gradeLevel);
            $feeCalculation['grade_level'] = $gradeLevel;
            $feeCalculation['academic_year'] = $academicYear;
            
            return response()->json([
                'success' => true,
                'data' => $feeCalculation
            ]);
            
        } catch (\Exception $e) {
            Log::error('Fee calculation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate fees for the selected grade level.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload temporary file for enrollment form
     */
    public function uploadTempFile(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|max:8192', // 8MB limit
                'type' => 'required|in:id_photo,documents'
            ]);

            $file = $request->file('file');
            $type = $request->input('type');

            // Validate file type based on upload type
            if ($type === 'id_photo') {
                $request->validate([
                    'file' => 'image|mimes:jpeg,png,jpg|max:5120' // 5MB for ID photo
                ]);
            } else {
                $request->validate([
                    'file' => 'mimes:pdf,jpeg,png,jpg|max:8192' // 8MB for documents
                ]);
            }

            // Generate unique file ID
            $fileId = (string) Str::uuid();
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            // Store file temporarily
            $tempPath = "temp/enrollment/{$fileId}.{$extension}";
            $file->storeAs('temp/enrollment', "{$fileId}.{$extension}", 'public');

            // Store file metadata in session
            $tempFiles = session('temp_enrollment_files', []);
            $tempFiles[$fileId] = [
                'id' => $fileId,
                'type' => $type,
                'original_name' => $originalName,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $size,
                'path' => $tempPath,
                'uploaded_at' => now()->toISOString()
            ];
            session(['temp_enrollment_files' => $tempFiles]);

            return response()->json([
                'success' => true,
                'file' => [
                    'id' => $fileId,
                    'name' => $originalName,
                    'size' => $size,
                    'type' => $type
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Temporary file upload failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'File upload failed: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete temporary file
     */
    public function deleteTempFile(string $fileId): JsonResponse
    {
        try {
            $tempFiles = session('temp_enrollment_files', []);
            
            if (isset($tempFiles[$fileId])) {
                $fileData = $tempFiles[$fileId];
                
                // Delete physical file
                if (Storage::disk('public')->exists($fileData['path'])) {
                    Storage::disk('public')->delete($fileData['path']);
                }
                
                // Remove from session
                unset($tempFiles[$fileId]);
                session(['temp_enrollment_files' => $tempFiles]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Temporary file deletion failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'File deletion failed'
            ], 500);
        }
    }

    /**
     * Get temporary files for current session
     */
    public function getTempFiles(): JsonResponse
    {
        try {
            $tempFiles = session('temp_enrollment_files', []);
            
            $files = [
                'id_photo' => null,
                'documents' => []
            ];
            
            foreach ($tempFiles as $fileData) {
                if ($fileData['type'] === 'id_photo') {
                    $files['id_photo'] = [
                        'id' => $fileData['id'],
                        'name' => $fileData['original_name'],
                        'size' => $fileData['size']
                    ];
                } else {
                    $files['documents'][] = [
                        'id' => $fileData['id'],
                        'name' => $fileData['original_name'],
                        'size' => $fileData['size']
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'files' => $files
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get temporary files failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve files'
            ], 500);
        }
    }

    /**
     * Clean up temporary files and session data
     */
    private function cleanupTempFiles(array $tempFiles): void
    {
        try {
            // Delete physical temporary files
            foreach ($tempFiles as $fileData) {
                if (Storage::disk('public')->exists($fileData['path'])) {
                    Storage::disk('public')->delete($fileData['path']);
                }
            }
            
            // Clear session data
            session()->forget('temp_enrollment_files');
            
        } catch (\Exception $e) {
            Log::error('Temporary file cleanup failed: ' . $e->getMessage());
        }
    }
}
