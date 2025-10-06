<?php

namespace App\Http\Controllers;

use App\Models\Enrollee;
use App\Models\Notice;
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
                'document_type' => 'required|string',
                'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:5120', // 5MB max
                'other_document_type' => 'nullable|string|max:255|required_if:document_type,other',
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
            $file = $request->file('document_file');
            $documentType = $request->document_type === 'other' ? $request->other_document_type : $request->document_type;
            
            // Store file in public storage for easy access
            $path = $file->store('documents', 'public');
            
            // Get existing documents or initialize empty array
            $documents = $enrollee->documents;
            if (is_string($documents)) {
                $documents = json_decode($documents, true) ?? [];
            }
            if (!is_array($documents)) {
                $documents = [];
            }
            
            // Add new document
            $documents[] = [
                'type' => $documentType,
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_at' => now()->toISOString(),
                'status' => 'pending',
                'notes' => $request->document_notes
            ];
            
            // Update enrollee documents
            $enrollee->update(['documents' => $documents]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document uploaded successfully!',
                    'document' => end($documents)
                ]);
            }
            
            return redirect()->back()->with('success', 'Document uploaded successfully!');
            
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
     * Show the enrollee schedule
     */
    public function schedule()
    {
        $enrollee = Auth::guard('enrollee')->user();
        return view('enrollee.schedule', compact('enrollee'));
    }

    /**
     * Update preferred schedule
     */
    public function updateSchedule(Request $request)
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        // Only allow schedule changes for pending applications
        if ($enrollee->enrollment_status !== 'pending') {
            return redirect()->back()->with('error', 'Schedule can only be changed while your application is pending.');
        }

        $request->validate([
            'preferred_schedule' => 'required|date|after:today',
            'reason' => 'required|string|max:500'
        ]);

        try {
            $enrollee->update([
                'preferred_schedule' => $request->preferred_schedule,
                'admin_notes' => ($enrollee->admin_notes ?? '') . "\n\nSchedule change requested on " . now()->format('Y-m-d H:i:s') . ": " . $request->reason
            ]);
            
            return redirect()->back()->with('success', 'Schedule change request submitted successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update schedule. Please try again.');
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
            $enrollee->update($request->only([
                'first_name', 'middle_name', 'last_name', 'contact_number', 'religion',
                'address', 'city', 'province', 'zip_code', 'guardian_name', 
                'guardian_contact', 'medical_history'
            ]));
            
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
     * Request an appointment
     */
    public function requestAppointment(Request $request)
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        $request->validate([
            'appointment_type' => 'required|string',
            'preferred_date' => 'required|date|after:today|before:' . date('Y-m-d', strtotime('+31 days')),
            'preferred_time' => 'required|string',
            'contact_method' => 'required|string|in:phone,email,in_person',
            'appointment_notes' => 'nullable|string|max:500',
        ]);

        try {
            // For now, we'll just log the appointment request
            // In the future, this would save to an appointments database table
            \Log::info('Appointment request from enrollee: ' . $enrollee->application_id, [
                'enrollee_id' => $enrollee->id,
                'appointment_type' => $request->appointment_type,
                'preferred_date' => $request->preferred_date,
                'preferred_time' => $request->preferred_time,
                'contact_method' => $request->contact_method,
                'notes' => $request->appointment_notes,
            ]);

            // Here you would typically:
            // 1. Save to appointments table
            // 2. Send email notification to admin
            // 3. Send confirmation email to enrollee
            
            return redirect()->back()->with('success', 'Your appointment request has been submitted successfully! We will contact you within 24-48 hours to confirm your appointment.');
            
        } catch (\Exception $e) {
            \Log::error('Appointment request error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to submit appointment request. Please try again.');
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
                    'priority' => $notice->priority,
                    'priority_badge' => $notice->priority_badge,
                    'is_read' => $notice->is_read,
                    'formatted_date' => $notice->formatted_date,
                    'created_by_name' => $notice->createdBy->name ?? 'School Administration'
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
            
            // Mark all unread notices for this enrollee
            Notice::where(function($query) use ($enrollee) {
                $query->where('enrollee_id', $enrollee->id)
                      ->orWhere('is_global', true);
            })
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All notices marked as read'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking all notices as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating notices'
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
            
            // Check if application is approved - updated logic for new workflow
            if ($enrollee->enrollment_status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your application must be approved before you can pre-register.'
                ], 400);
            }
            
            // Generate student ID (format: NS-25001)
            $latestStudent = \App\Models\Student::orderBy('id', 'desc')->first();
            $nextNumber = $latestStudent ? (intval(substr($latestStudent->student_id, 3)) + 1) : 25001;
            $studentId = 'NS-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            
            // Generate password (format: 25-001 based on application_id)
            // Use the same format as enrollment password (application_id is already "25-001")
            $password = $enrollee->application_id;
            
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
                    'place_of_birth' => $enrollee->place_of_birth ?? 'Not specified',
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
                    'track' => $enrollee->track_applied ?? 'Academic',
                    'student_type' => $enrollee->student_type ?? 'new',
                    'enrollment_status' => 'pre_registered',
                    'academic_year' => $enrollee->academic_year ?? '2024-2025',
                    'documents' => $enrollee->documents,
                    'id_photo_data_url' => $enrollee->id_photo_data_url,
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
                    'medical_history' => $enrollee->medical_history ?? 'None specified',
                    'pre_registered_at' => now(),
                    'is_active' => true
                ]);
            
            } catch (\Exception $studentError) {
                \Log::error('Error creating student record: ' . $studentError->getMessage());
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
}
