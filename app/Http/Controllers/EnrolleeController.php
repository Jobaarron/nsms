<?php

namespace App\Http\Controllers;

use App\Models\Enrollee;
use App\Models\Notice;
use App\Models\DataChangeRequest;
use App\Models\Appeal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\EnrolleeCredentialsMail;
use App\Mail\StudentCredentialsMail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EnrolleeController extends Controller
{
    /**
     * Show the enrollee login form
     */
    public function showLoginForm()
    {
        return view('enrollee.login');
    }

    /**
     * Handle enrollee login
     */
    public function login(Request $request)
    {
        $request->validate([
            'application_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('application_id', 'password');

        if (Auth::guard('enrollee')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('enrollee.dashboard'));
        }

        return back()->withErrors([
            'application_id' => 'The provided credentials do not match our records.',
        ])->onlyInput('application_id');
    }

    /**
     * Show the enrollee dashboard
     */
    public function index()
    {
        $enrollee = Auth::guard('enrollee')->user();
        return view('enrollee.index', compact('enrollee'));
    }

    /**
     * Show the enrollee application details
     */
    public function application()
    {
        $enrollee = Auth::guard('enrollee')->user();
        return view('enrollee.application', compact('enrollee'));
    }

    /**
     * Show the enrollee documents
     */
    public function documents()
    {
        $enrollee = Auth::guard('enrollee')->user();
        return view('enrollee.documents', compact('enrollee'));
    }

    /**
     * Upload a document
     */
    public function uploadDocument(Request $request)
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        // Only allow uploads for pending applications
        if ($enrollee->enrollment_status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documents can only be uploaded while your application is pending.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Documents can only be uploaded while your application is pending.');
        }

        try {
            $request->validate([
                'document_files' => 'required|array',
                'document_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max per file
                'document_notes' => 'nullable|string|max:500'
            ]);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            $files = $request->file('document_files');
            
            // Get existing documents or initialize empty array
            $documents = $enrollee->documents;
            if (is_string($documents)) {
                $documents = json_decode($documents, true) ?? [];
            }
            if (!is_array($documents)) {
                $documents = [];
            }
            
            $uploadedCount = 0;
            
            // Process each uploaded file
            foreach ($files as $file) {
                // Store file in public storage for easy access
                $path = $file->store('documents', 'public');
                
                // Add new document
                $documents[] = [
                    'type' => 'Document',
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_at' => now()->toISOString(),
                    'status' => 'pending',
                    'notes' => $request->document_notes
                ];
                
                $uploadedCount++;
            }
            
            // Update enrollee documents
            $enrollee->update(['documents' => $documents]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $uploadedCount === 1 ? 'Document uploaded successfully!' : "{$uploadedCount} documents uploaded successfully!",
                    'uploadedCount' => $uploadedCount
                ]);
            }
            
            return redirect()->back()->with('success', $uploadedCount === 1 ? 'Document uploaded successfully!' : "{$uploadedCount} documents uploaded successfully!");
            
        } catch (\Exception $e) {
            \Log::error('Document upload error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload document. Please try again.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to upload document. Please try again.');
        }
    }




    /**
     * Show the enrollee profile
     */
    // public function profile()
    // {
    //     $enrollee = Auth::guard('enrollee')->user();
    //     return view('enrollee.profile', compact('enrollee'));
    // }

    /**
     * Update enrollee profile
     */
    public function updateProfile(Request $request)
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        // Only allow profile updates for pending applications
        if ($enrollee->enrollment_status !== 'pending') {
            return redirect()->back()->with('error', 'Profile can only be updated while your application is pending.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'religion' => 'nullable|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'guardian_name' => 'required|string|max:255',
            'guardian_contact' => 'required|string|max:20',
            'medical_history' => 'nullable|string'
        ]);

        try {
            // Get the data to update
            $updateData = $request->only([
                'first_name', 'middle_name', 'last_name', 'contact_number', 'religion',
                'address', 'city', 'province', 'zip_code', 'guardian_name', 
                'guardian_contact', 'medical_history'
            ]);
            
            // Set default "N/A" for medical_history if empty
            if (empty($updateData['medical_history']) || trim($updateData['medical_history']) === '') {
                $updateData['medical_history'] = 'N/A';
            }
            
            $enrollee->update($updateData);
            
            return redirect()->back()->with('success', 'Profile updated successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update profile. Please try again.');
        }
    }

    /**
     * Logout enrollee
     */
    public function logout(Request $request)
    {
        Auth::guard('enrollee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('enrollee.login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Replace a document
     */
    public function replaceDocument(Request $request)
    {
        try {
            $enrollee = Auth::guard('enrollee')->user();
            
            // Only allow replacement for pending applications
            if ($enrollee->enrollment_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Documents can only be replaced for pending applications.'
                ], 403);
            }

            $request->validate([
                'document_index' => 'required|integer|min:0',
                'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
                'document_notes' => 'nullable|string|max:500'
            ]);

            $documentIndex = $request->input('document_index');
            
            // Handle both array and JSON string formats
            $documents = $enrollee->documents;
            if (is_string($documents)) {
                $documents = json_decode($documents, true) ?? [];
            }
            if (!is_array($documents)) {
                $documents = [];
            }
            
            if (!isset($documents[$documentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.'
                ], 404);
            }

            // Get the old document to delete from storage
            $oldDocument = $documents[$documentIndex];
            
            // Handle both old format (string paths) and new format (arrays with metadata)
            if (is_string($oldDocument)) {
                // Old format: just the file path
                $oldFullPath = storage_path('app/public/' . $oldDocument);
            } else {
                // New format: array with metadata
                $oldFullPath = storage_path('app/public/' . $oldDocument['path']);
            }
            
            // Delete old file if it exists
            if (file_exists($oldFullPath)) {
                unlink($oldFullPath);
            }

            // Upload new file
            $file = $request->file('document_file');
            $path = $file->store('documents', 'public');
            
            // Update document with new file information
            $documents[$documentIndex] = [
                'type' => is_array($oldDocument) ? $oldDocument['type'] : 'Document',
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_at' => now()->toISOString(),
                'status' => 'pending', // Reset status to pending for review
                'notes' => $request->document_notes,
                'replaced_at' => now()->toISOString()
            ];
            
            // Update enrollee record
            $enrollee->documents = $documents;
            $enrollee->save();

            return response()->json([
                'success' => true, 
                'message' => 'Document replaced successfully! It will be reviewed again.',
                'document' => $documents[$documentIndex]
            ]);

        } catch (\Exception $e) {
            \Log::error('Document replacement error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while replacing the document.'
            ], 500);
        }
    }

    /**
     * Delete a document
     */
    public function deleteDocument(Request $request)
    {
        try {
            $enrollee = Auth::guard('enrollee')->user();
            
            // Only allow deletion for pending applications
            if ($enrollee->enrollment_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Documents can only be deleted for pending applications.'
                ], 403);
            }

            $documentIndex = $request->input('document_index');
            
            if (!is_numeric($documentIndex)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document index.'
                ], 400);
            }

            // Handle both array and JSON string formats
            $documents = $enrollee->documents;
            if (is_string($documents)) {
                $documents = json_decode($documents, true) ?? [];
            }
            if (!is_array($documents)) {
                $documents = [];
            }
            
            if (!isset($documents[$documentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.'
                ], 404);
            }

            // Get the document to delete from storage
            $document = $documents[$documentIndex];
            
            // Handle both old format (string paths) and new format (arrays with metadata)
            if (is_string($document)) {
                // Old format: just the file path
                $fullPath = storage_path('app/public/' . $document);
            } else {
                // New format: array with metadata
                $fullPath = storage_path('app/public/' . $document['path']);
            }
            
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            // Remove document from array
            unset($documents[$documentIndex]);
            
            // Re-index the array to maintain proper indexing
            $documents = array_values($documents);
            
            // Update enrollee record
            $enrollee->documents = $documents;
            $enrollee->save();

            return response()->json(['success' => true, 'message' => 'Document deleted successfully']);

        } catch (\Exception $e) {
            \Log::error('Document deletion error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the document.'
            ], 500);
        }
    }

    public function viewDocument($index)
    {
        $enrollee = auth('enrollee')->user();
        
        // Handle both array and JSON string formats
        $documents = $enrollee->documents;
        if (is_string($documents)) {
            $documents = json_decode($documents, true) ?? [];
        }
        if (!is_array($documents)) {
            $documents = [];
        }
        
        if (!$enrollee || !isset($documents[$index])) {
            abort(404, 'Document not found');
        }
        
        $document = $documents[$index];
        
        // Handle both old format (string paths) and new format (arrays with metadata)
        if (is_string($document)) {
            // Old format: just the file path
            $filePath = storage_path('app/public/' . $document);
        } else {
            // New format: array with metadata
            $filePath = storage_path('app/public/' . $document['path']);
        }
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }
        
        return response()->file($filePath);
    }

    public function downloadDocument($index)
    {
        $enrollee = auth('enrollee')->user();
        
        // Handle both array and JSON string formats
        $documents = $enrollee->documents;
        if (is_string($documents)) {
            $documents = json_decode($documents, true) ?? [];
        }
        if (!is_array($documents)) {
            $documents = [];
        }
        
        if (!$enrollee || !isset($documents[$index])) {
            abort(404, 'Document not found');
        }
        
        $document = $documents[$index];
        
        // Handle both old format (string paths) and new format (arrays with metadata)
        if (is_string($document)) {
            // Old format: just the file path
            $filePath = storage_path('app/public/' . $document);
            $filename = basename($document); // Extract filename from path
        } else {
            // New format: array with metadata
            $filePath = storage_path('app/public/' . $document['path']);
            $filename = $document['filename'];
        }
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }
        
        return response()->download($filePath, $filename);
    }

    /**
     * Update enrollee password
     */
    public function updatePassword(Request $request)
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Verify current password
            if (!Hash::check($request->current_password, $enrollee->password)) {
                return redirect()->back()->withErrors([
                    'current_password' => 'The current password is incorrect.'
                ])->withInput();
            }

            // Update password
            $enrollee->update([
                'password' => Hash::make($request->new_password)
            ]);

            return redirect()->back()->with('success', 'Password updated successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Password update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update password. Please try again.');
        }
    }

    /**
     * Show enrollee notices
     */
    public function notices()
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        // For now, we'll use static data. In the future, this would come from a notices database table
        $unreadCount = 3; // This would be calculated from database
        
        return view('enrollee.notices', compact('enrollee', 'unreadCount'));
    }

    /**
     * Get unread notices count for AJAX
     */
    public function getUnreadNoticesCount(): JsonResponse
    {
        try {
            $enrollee = Auth::guard('enrollee')->user();
            
            if (!$enrollee) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                    'count' => 0
                ], 401);
            }
            
            // Get unread notices count (excluding guidance-specific notices)
            $unreadCount = Notice::where(function($query) use ($enrollee) {
                $query->where('enrollee_id', $enrollee->id)
                      ->orWhere('is_global', true);
            })
            ->excludeGuidanceSpecific()
            ->where('is_read', false)
            ->count();
            
            return response()->json([
                'success' => true,
                'count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting unread notices count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving unread count',
                'count' => 0
            ], 500);
        }
    }


    /**
     * Get single notice details
     */
    public function getNotice($id): JsonResponse
    {
        try {
            $enrollee = Auth::guard('enrollee')->user();
            $notice = Notice::where('id', $id)
                ->where(function($query) use ($enrollee) {
                    $query->where('enrollee_id', $enrollee->id)
                          ->orWhere('is_global', true);
                })
                ->excludeGuidanceSpecific()
                ->with('createdBy')
                ->first();

            if (!$notice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notice not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'notice' => [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'message' => $notice->message,
                    'is_read' => $notice->is_read,
                    'formatted_date' => $notice->formatted_date,
                    'creator_name' => $notice->creator_name ?? 'Registrar'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching notice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading notice'
            ], 500);
        }
    }

    /**
     * Mark notice as read
     */
    public function markNoticeAsRead($id): JsonResponse
    {
        try {
            $enrollee = Auth::guard('enrollee')->user();
            $notice = Notice::where('id', $id)
                ->where(function($query) use ($enrollee) {
                    $query->where('enrollee_id', $enrollee->id)
                          ->orWhere('is_global', true);
                })
                ->excludeGuidanceSpecific()
                ->first();

            if (!$notice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notice not found'
                ], 404);
            }

            if (!$notice->is_read) {
                $notice->markAsRead();
            }

            return response()->json([
                'success' => true,
                'message' => 'Notice marked as read'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking notice as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating notice status'
            ], 500);
        }
    }

    /**
     * Mark all notices as read for current enrollee
     */
    public function markAllNoticesAsRead(): JsonResponse
    {
        try {
            $enrollee = Auth::guard('enrollee')->user();
            
            if (!$enrollee) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // Get all unread notices for this enrollee (excluding guidance-specific notices)
            $notices = Notice::where(function($query) use ($enrollee) {
                $query->where('enrollee_id', $enrollee->id)
                      ->orWhere('is_global', true);
            })
            ->excludeGuidanceSpecific()
            ->where('is_read', false)
            ->get();

            // Mark each notice as read using the model method
            $updatedCount = 0;
            foreach ($notices as $notice) {
                $notice->markAsRead();
                $updatedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "All notices marked as read ({$updatedCount} notices updated)"
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking all notices as read: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error updating notices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pre-register enrollee as student
     */
    public function preRegister(Request $request): JsonResponse
    {
        try {
            $enrollee = Auth::guard('enrollee')->user();
            
            \Log::info('Pre-registration attempt', [
                'enrollee_id' => $enrollee->id,
                'application_id' => $enrollee->application_id,
                'enrollment_status' => $enrollee->enrollment_status,
                'student_id' => $enrollee->student_id
            ]);
            
            // Check if already pre-registered
            if ($enrollee->student_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already completed pre-registration.'
                ], 400);
            }
            
            // Also check if a student record already exists for this enrollee
            $existingStudentByEnrollee = \App\Models\Student::where('enrollee_id', $enrollee->id)->first();
            if ($existingStudentByEnrollee) {
                return response()->json([
                    'success' => false,
                    'message' => 'A student record already exists for this enrollee.'
                ], 400);
            }
            
            // Check if application is approved - updated logic for new workflow
            if ($enrollee->enrollment_status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your application must be approved before you can pre-register.'
                ], 400);
            }
            
            // Generate student ID (format: NS-25001)
            $year = date('Y');
            $shortYear = substr($year, -2); // Get last 2 digits of year (25 for 2025)
            
            // Find the last student for this year
            $lastStudent = \App\Models\Student::where('student_id', 'like', "NS-{$shortYear}%")
                ->orderBy('student_id', 'desc')
                ->first();
            
            if ($lastStudent) {
                // Extract number after NS-25 (e.g., "NS-25001" -> "001")
                $lastNumber = (int) substr($lastStudent->student_id, 5);
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }
            
            // Generate student ID in format: NS-25001 (NS = Nicolites School, 25 = year, 001 = sequence)
            $studentId = 'NS-' . $shortYear . $newNumber;
            
            // Double-check that the generated student_id is unique
            $existingStudent = \App\Models\Student::where('student_id', $studentId)->first();
            if ($existingStudent) {
                \Log::error('Generated student ID already exists', [
                    'generated_id' => $studentId,
                    'enrollee_id' => $enrollee->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating unique student ID. Please try again.'
                ], 500);
            }
            
            // Generate password (format: 25-001 based on application_id)
            // Use the same format as enrollment password (application_id is already "25-001")
            $password = $enrollee->application_id;
            
            // Validate required fields before creating student record
            $requiredFields = [
                'first_name' => $enrollee->first_name,
                'last_name' => $enrollee->last_name,
                'date_of_birth' => $enrollee->date_of_birth,
                'gender' => $enrollee->gender,
                'address' => $enrollee->address,
                'grade_level_applied' => $enrollee->grade_level_applied,
                'guardian_name' => $enrollee->guardian_name,
                'guardian_contact' => $enrollee->guardian_contact
            ];
            
            $missingFields = [];
            foreach ($requiredFields as $field => $value) {
                if (empty($value)) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                \Log::error('Missing required fields for student creation', [
                    'enrollee_id' => $enrollee->id,
                    'missing_fields' => $missingFields
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required information: ' . implode(', ', $missingFields)
                ], 400);
            }
            
            // Create student record with all available fields
            try {
                $fullName = trim($enrollee->first_name . ' ' . ($enrollee->middle_name ? $enrollee->middle_name . ' ' : '') . $enrollee->last_name . ($enrollee->suffix ? ' ' . $enrollee->suffix : ''));
                
                $student = \App\Models\Student::create([
                    'enrollee_id' => $enrollee->id,
                    'student_id' => $studentId,
                    'password' => Hash::make($password),
                    'lrn' => $enrollee->lrn,
                    'first_name' => $enrollee->first_name,
                    'middle_name' => $enrollee->middle_name,
                    'last_name' => $enrollee->last_name,
                    'suffix' => $enrollee->suffix,
                    'full_name' => $fullName,
                    'date_of_birth' => $enrollee->date_of_birth,
                    'place_of_birth' => $enrollee->place_of_birth,
                    'gender' => $enrollee->gender,
                    'nationality' => $enrollee->nationality ?? 'Filipino',
                    'religion' => $enrollee->religion,
                    'contact_number' => $enrollee->contact_number,
                    'email' => $enrollee->email,
                    'address' => $enrollee->address,
                    'city' => $enrollee->city,
                    'province' => $enrollee->province,
                    'zip_code' => $enrollee->zip_code,
                    'grade_level' => $enrollee->grade_level_applied,
                    'strand' => $enrollee->strand_applied,
                    'track' => $enrollee->track_applied,
                    'student_type' => $enrollee->student_type ?? 'new',
                    'enrollment_status' => 'pre_registered',
                    'academic_year' => $enrollee->academic_year ?? (date('Y') . '-' . (date('Y') + 1)),
                    'documents' => $enrollee->documents,
                    'id_photo' => $enrollee->id_photo,
                    'id_photo_mime_type' => $enrollee->id_photo_mime_type,
                    'father_name' => $enrollee->father_name,
                    'father_occupation' => $enrollee->father_occupation,
                    'father_contact' => $enrollee->father_contact,
                    'mother_name' => $enrollee->mother_name,
                    'mother_occupation' => $enrollee->mother_occupation,
                    'mother_contact' => $enrollee->mother_contact,
                    'guardian_name' => $enrollee->guardian_name,
                    'guardian_contact' => $enrollee->guardian_contact,
                    'last_school_type' => $enrollee->last_school_type,
                    'last_school_name' => $enrollee->last_school_name,
                    'medical_history' => $enrollee->medical_history,
                    'pre_registered_at' => now(),
                    'is_active' => true
                ]);
            
            } catch (\Exception $studentError) {
                \Log::error('Error creating student record', [
                    'error' => $studentError->getMessage(),
                    'trace' => $studentError->getTraceAsString(),
                    'enrollee_id' => $enrollee->id,
                    'student_id' => $studentId ?? 'not_generated',
                    'enrollee_data' => [
                        'first_name' => $enrollee->first_name,
                        'last_name' => $enrollee->last_name,
                        'email' => $enrollee->email,
                        'guardian_name' => $enrollee->guardian_name,
                        'guardian_contact' => $enrollee->guardian_contact,
                        'address' => $enrollee->address,
                        'grade_level_applied' => $enrollee->grade_level_applied,
                        'date_of_birth' => $enrollee->date_of_birth,
                        'gender' => $enrollee->gender
                    ]
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating student record: ' . $studentError->getMessage()
                ], 500);
            }
            
            // Update enrollee with student_id
            $enrollee->update([
                'student_id' => $studentId,
                'pre_registered_at' => now()
            ]);
            
            // Send student credentials email
            try {
                Mail::to($enrollee->email)->send(new StudentCredentialsMail($student, $studentId, $password));
                \Log::info('Student credentials email sent', [
                    'student_id' => $studentId,
                    'email' => $enrollee->email
                ]);
            } catch (\Exception $emailError) {
                \Log::error('Failed to send student credentials email: ' . $emailError->getMessage());
                // Don't fail the pre-registration if email fails
            }
            
            \Log::info('Enrollee pre-registered as student', [
                'enrollee_id' => $enrollee->id,
                'student_id' => $studentId,
                'application_id' => $enrollee->application_id
            ]);
            
            // Store credentials in session for display after page refresh
            session()->flash('new_student_credentials', [
                'student_id' => $studentId,
                'password' => $password,
                'show_modal' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pre-registration completed successfully!',
                'student_id' => $studentId,
                'password' => $password // Send password for user reference
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error during pre-registration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error during pre-registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new data change request
     */
    public function storeDataChangeRequest(Request $request): JsonResponse
    {
        $request->validate([
            'field_name' => 'required|string|max:255',
            'new_value' => 'required|string|max:1000',
            'old_value' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|max:1000',
        ]);

        $enrollee = Auth::guard('enrollee')->user();

        // Check if enrollee can submit requests
        if ($enrollee->enrollment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'You can only submit change requests while your application is pending.'
            ], 403);
        }

        // Check if there's already a pending request for this field
        $existingRequest = $enrollee->dataChangeRequests()
            ->where('field_name', $request->field_name)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending change request for this field.'
            ], 422);
        }

        try {
            $changeRequest = $enrollee->dataChangeRequests()->create([
                'field_name' => $request->field_name,
                'old_value' => $request->old_value,
                'new_value' => $request->new_value,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data change request submitted successfully!',
                'request' => $changeRequest
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating data change request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error submitting request. Please try again.'
            ], 500);
        }
    }

    /**
     * Show a specific data change request
     */
    public function showDataChangeRequest($id): JsonResponse
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        $request = $enrollee->dataChangeRequests()->findOrFail($id);

        return response()->json([
            'success' => true,
            'request' => [
                'id' => $request->id,
                'field_name' => $request->field_name,
                'human_field_name' => $request->human_field_name,
                'old_value' => $request->old_value,
                'new_value' => $request->new_value,
                'reason' => $request->reason,
                'status' => $request->status,
                'status_badge_class' => $request->status_badge_class,
                'admin_notes' => $request->admin_notes,
                'created_at' => $request->created_at,
                'processed_at' => $request->processed_at,
            ]
        ]);
    }

    /**
     * Update a data change request
     */
    public function updateDataChangeRequest(Request $request, $id): JsonResponse
    {
        $request->validate([
            'new_value' => 'required|string|max:1000',
            'reason' => 'nullable|string|max:1000',
        ]);

        $enrollee = Auth::guard('enrollee')->user();
        
        $changeRequest = $enrollee->dataChangeRequests()->findOrFail($id);

        // Check if request can be updated
        if ($changeRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be updated.'
            ], 403);
        }

        if ($enrollee->enrollment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'You can only update requests while your application is pending.'
            ], 403);
        }

        try {
            $changeRequest->update([
                'new_value' => $request->new_value,
                'reason' => $request->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data change request updated successfully!',
                'request' => $changeRequest
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating data change request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating request. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a data change request
     */
    public function destroyDataChangeRequest($id): JsonResponse
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        $changeRequest = $enrollee->dataChangeRequests()->findOrFail($id);

        // Check if request can be deleted
        if ($changeRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be cancelled.'
            ], 403);
        }

        if ($enrollee->enrollment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'You can only cancel requests while your application is pending.'
            ], 403);
        }

        try {
            $changeRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data change request cancelled successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting data change request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling request. Please try again.'
            ], 500);
        }
    }

    /**
     * Get real-time alert counts for enrollee
     */
    public function getAlertCounts()
    {
        try {
            $enrollee = Auth::guard('enrollee')->user();
            
            // Check if user is authenticated
            if (!$enrollee) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                    'counts' => ['unread_notices' => 0]
                ], 401);
            }
            
            $counts = [
                'unread_notices' => Notice::forEnrollee($enrollee->id)
                    ->excludeGuidanceSpecific()
                    ->unread()
                    ->count(),
            ];
            
            return response()->json([
                'success' => true,
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching alert counts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching alert counts',
                'error' => $e->getMessage(),
                'counts' => ['unread_notices' => 0]
            ], 500);
        }
    }

    /**
     * Store a new appeal
     */
    public function storeAppeal(Request $request)
    {
        try {
            $enrollee = Auth::guard('enrollee')->user();
            
            // Validate the enrollee can submit an appeal
            if (!$enrollee->canSubmitAppeal()) {
                return back()->withErrors([
                    'error' => 'You cannot submit an appeal at this time. Either your application is not rejected or you already have a pending appeal.'
                ]);
            }

            $request->validate([
                'reason' => 'required|string|min:50|max:2000',
                'appeal_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max
                'declaration' => 'required|accepted',
            ]);

            // Handle file uploads
            $documents = [];
            if ($request->hasFile('appeal_documents')) {
                foreach ($request->file('appeal_documents') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('appeal_documents/' . $enrollee->id, $filename, 'public');
                    
                    $documents[] = [
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_at' => now(),
                    ];
                }
            }

            // Create the appeal
            Appeal::create([
                'enrollee_id' => $enrollee->id,
                'reason' => $request->reason,
                'documents' => $documents,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            // Update enrollee status to indicate active appeal
            $enrollee->update([
                'enrollment_status' => 'rejected_appeal',
                'status_reason' => 'Appeal submitted on ' . now()->format('M d, Y g:i A') . '. Application under appeal review.',
            ]);

            return redirect()->route('enrollee.dashboard')->with('success', 'Your appeal has been submitted successfully and is now under review.');

        } catch (\Exception $e) {
            \Log::error('Appeal submission error: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'There was an error submitting your appeal. Please try again.'
            ]);
        }
    }

    /**
     * Show a specific appeal
     */
    public function showAppeal($id)
    {
        try {
            $enrollee = Auth::guard('enrollee')->user();
            $appeal = Appeal::where('id', $id)
                ->where('enrollee_id', $enrollee->id)
                ->with(['reviewer'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'appeal' => [
                    'id' => $appeal->id,
                    'reason' => $appeal->reason,
                    'status' => $appeal->status,
                    'status_badge_class' => $appeal->status_badge_class,
                    'admin_notes' => $appeal->admin_notes,
                    'submitted_at' => $appeal->formatted_submitted_date,
                    'reviewed_at' => $appeal->reviewed_at ? $appeal->reviewed_at->format('M d, Y g:i A') : null,
                    'reviewer_name' => $appeal->reviewer ? $appeal->reviewer->name : null,
                    'documents_count' => is_array($appeal->documents) ? count($appeal->documents) : 0,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Appeal not found or access denied.'
            ], 404);
        }
    }
}
