<?php

namespace App\Http\Controllers;

use App\Models\Enrollee;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminEnrollmentController extends Controller
{
    /**
     * Display the enrollment management dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $status = $request->get('status');
        $gradeLevel = $request->get('grade_level');
        $search = $request->get('search');
        
        // Build query for applications
        $applicationsQuery = Enrollee::query();
        
        // Apply filters
        if ($status) {
            $applicationsQuery->where('enrollment_status', $status);
        }
        
        if ($gradeLevel) {
            $applicationsQuery->where('grade_level_applied', $gradeLevel);
        }
        
        if ($search) {
            $applicationsQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('application_id', 'like', "%{$search}%");
            });
        }
        
        // Get applications with pagination
        $applications = $applicationsQuery->orderBy('created_at', 'desc')->paginate(50);
        
        // Get summary statistics
        $totalApplications = Enrollee::count();
        $pendingApplications = Enrollee::where('enrollment_status', 'pending')->count();
        $approvedApplications = Enrollee::where('enrollment_status', 'approved')->count();
        $scheduledAppointments = Enrollee::whereNotNull('preferred_schedule')->count();
        
        // Get documents data
        $documentsData = Enrollee::whereNotNull('documents')
            ->where('documents', '!=', '[]')
            ->get()
            ->map(function ($enrollee) {
                $documents = is_array($enrollee->documents) ? $enrollee->documents : json_decode($enrollee->documents, true) ?? [];
                return [
                    'enrollee' => $enrollee,
                    'documents' => $documents,
                    'document_count' => count($documents)
                ];
            });
        
        // Get appointments data
        $appointments = Enrollee::whereNotNull('preferred_schedule')
            ->orWhereNotNull('enrollment_date')
            ->get()
            ->map(function ($enrollee) {
                $status = 'pending';
                if ($enrollee->enrollment_status === 'approved') {
                    $status = 'completed';
                } elseif ($enrollee->preferred_schedule) {
                    $status = 'scheduled';
                }
                
                return [
                    'enrollee' => $enrollee,
                    'status' => $status,
                    'scheduled_date' => $enrollee->preferred_schedule ?? $enrollee->enrollment_date
                ];
            });
        
        // Get notices data
        $notices = Notice::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.enrollments', compact(
            'applications',
            'totalApplications',
            'pendingApplications', 
            'approvedApplications',
            'scheduledAppointments',
            'documentsData',
            'appointments',
            'notices',
            'status',
            'gradeLevel',
            'search'
        ));
    }

    /**
     * Get applications data with filtering
     */
    public function getApplications(Request $request): JsonResponse
    {
        try {
            $query = Enrollee::query();

            // Apply filters
            if ($request->filled('status')) {
                $query->where('enrollment_status', $request->status);
            }

            if ($request->filled('grade_level')) {
                $query->where('grade_level_applied', $request->grade_level);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('application_id', 'like', "%{$search}%");
                });
            }

            $applications = $query->orderBy('application_date', 'desc')->get();

            // Calculate summary statistics
            $summary = [
                'total' => Enrollee::count(),
                'pending' => Enrollee::where('enrollment_status', 'pending')->count(),
                'approved' => Enrollee::where('enrollment_status', 'approved')->count(),
                'enrolled' => Enrollee::where('enrollment_status', 'enrolled')->count(),
                'appointments' => Enrollee::whereNotNull('preferred_schedule')
                                        ->where('enrollment_status', 'pending')
                                        ->count()
            ];

            return response()->json([
                'success' => true,
                'applications' => $applications->map(function($app) {
                    return [
                        'id' => $app->id,
                        'application_id' => $app->application_id,
                        'full_name' => $app->full_name,
                        'grade_level_applied' => $app->grade_level_applied,
                        'email' => $app->email,
                        'enrollment_status' => $app->enrollment_status,
                        'application_date' => $app->application_date,
                        'contact_number' => $app->contact_number,
                        'student_type' => $app->student_type
                    ];
                }),
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching applications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading applications data'
            ], 500);
        }
    }

    /**
     * Get single application details
     */
    public function getApplication($id): JsonResponse
    {
        try {
            // Try to find by database ID first, then by application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }
            
            return response()->json([
                'success' => true,
                'application' => [
                    'id' => $application->id,
                    'application_id' => $application->application_id,
                    'full_name' => $application->full_name,
                    'first_name' => $application->first_name,
                    'middle_name' => $application->middle_name,
                    'last_name' => $application->last_name,
                    'suffix' => $application->suffix,
                    'date_of_birth' => $application->date_of_birth,
                    'gender' => $application->gender,
                    'email' => $application->email,
                    'contact_number' => $application->contact_number,
                    'address' => $application->address,
                    'city' => $application->city,
                    'province' => $application->province,
                    'zip_code' => $application->zip_code,
                    'grade_level_applied' => $application->grade_level_applied,
                    'strand_applied' => $application->strand_applied,
                    'student_type' => $application->student_type,
                    'father_name' => $application->father_name,
                    'father_occupation' => $application->father_occupation,
                    'father_contact' => $application->father_contact,
                    'mother_name' => $application->mother_name,
                    'mother_occupation' => $application->mother_occupation,
                    'mother_contact' => $application->mother_contact,
                    'guardian_name' => $application->guardian_name,
                    'guardian_contact' => $application->guardian_contact,
                    'last_school_name' => $application->last_school_name,
                    'last_school_type' => $application->last_school_type,
                    'medical_history' => $application->medical_history,
                    'enrollment_status' => $application->enrollment_status,
                    'status_reason' => $application->status_reason,
                    'application_date' => $application->application_date,
                    'id_photo_data_url' => $application->id_photo_data_url,
                    'documents' => $application->documents,
                    'nationality' => $application->nationality,
                    'religion' => $application->religion,
                    'lrn' => $application->lrn,
                    'track_applied' => $application->track_applied,
                    'created_at' => $application->created_at,
                    'updated_at' => $application->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching application details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }
    }

    /**
     * Update application status
     */
    public function updateApplicationStatus(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,approved,rejected,enrolled',
                'reason' => 'nullable|string|max:500'
            ]);

            $application = Enrollee::findOrFail($id);
            $oldStatus = $application->enrollment_status;
            
            $application->update([
                'enrollment_status' => $request->status,
                'status_reason' => $request->reason,
                'processed_by' => Auth::id(),
                'approved_by' => $request->status === 'approved' ? Auth::id() : $application->approved_by,
                'approved_at' => $request->status === 'approved' ? now() : $application->approved_at,
                'rejected_by' => $request->status === 'rejected' ? Auth::id() : $application->rejected_by,
                'rejected_at' => $request->status === 'rejected' ? now() : $application->rejected_at,
                'enrolled_at' => $request->status === 'enrolled' ? now() : $application->enrolled_at,
            ]);

            // Create notice for status change
            $this->createStatusChangeNotice($application, $oldStatus, $request->status, $request->reason);

            Log::info('Application status updated', [
                'application_id' => $application->application_id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application status updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating application status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating application status'
            ], 500);
        }
    }

    /**
     * Get documents data
     */
    public function getDocuments(Request $request): JsonResponse
    {
        try {
            $query = Enrollee::whereNotNull('documents');

            // Apply filters
            if ($request->filled('status')) {
                // Filter by document status within JSON
                $query->whereJsonContains('documents', ['status' => $request->status]);
            }

            $enrollees = $query->get();
            $documents = [];

            foreach ($enrollees as $enrollee) {
                if ($enrollee->documents && is_array($enrollee->documents)) {
                    foreach ($enrollee->documents as $index => $doc) {
                        if (is_array($doc)) {
                            $documents[] = [
                                'enrollee_id' => $enrollee->id,
                                'application_id' => $enrollee->application_id,
                                'student_name' => $enrollee->full_name,
                                'document_index' => $index,
                                'document_type' => $doc['type'] ?? 'Unknown',
                                'filename' => $doc['filename'] ?? 'Unknown',
                                'upload_date' => $doc['uploaded_at'] ?? $enrollee->created_at,
                                'status' => $doc['status'] ?? 'pending',
                                'path' => $doc['path'] ?? null
                            ];
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'documents' => $documents
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching documents: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading documents data'
            ], 500);
        }
    }

    /**
     * Get appointments data
     */
    public function getAppointments(Request $request): JsonResponse
    {
        try {
            $query = Enrollee::whereNotNull('preferred_schedule')
                            ->orWhereNotNull('enrollment_date');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('enrollment_status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->where('preferred_schedule', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('preferred_schedule', '<=', $request->date_to);
            }

            $enrollees = $query->orderBy('preferred_schedule', 'asc')->get();
            $appointments = [];

            foreach ($enrollees as $enrollee) {
                $appointments[] = [
                    'enrollee_id' => $enrollee->id,
                    'application_id' => $enrollee->application_id,
                    'student_name' => $enrollee->full_name,
                    'email' => $enrollee->email,
                    'contact_number' => $enrollee->contact_number,
                    'grade_level' => $enrollee->grade_level_applied,
                    'strand' => $enrollee->strand_applied,
                    'preferred_schedule' => $enrollee->preferred_schedule,
                    'enrollment_date' => $enrollee->enrollment_date,
                    'enrollment_status' => $enrollee->enrollment_status,
                    'application_date' => $enrollee->application_date,
                    'student_type' => $enrollee->student_type,
                    'appointment_status' => $this->getAppointmentStatus($enrollee)
                ];
            }

            return response()->json([
                'success' => true,
                'appointments' => $appointments
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching appointments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading appointments data'
            ], 500);
        }
    }

    /**
     * Determine appointment status based on enrollee data
     */
    private function getAppointmentStatus($enrollee)
    {
        if ($enrollee->enrollment_date) {
            return 'completed';
        } elseif ($enrollee->preferred_schedule && $enrollee->preferred_schedule < now()) {
            return 'overdue';
        } elseif ($enrollee->preferred_schedule) {
            return 'scheduled';
        } else {
            return 'pending';
        }
    }

    /**
     * Get documents for a specific application
     */
    public function getApplicationDocuments($applicationId): JsonResponse
    {
        try {
            $enrollee = Enrollee::where('application_id', $applicationId)->firstOrFail();
            
            $documents = [];
            $enrolleeDocuments = $enrollee->documents;
            
            // Handle both JSON string and array formats
            if (is_string($enrolleeDocuments)) {
                $enrolleeDocuments = json_decode($enrolleeDocuments, true) ?? [];
            }
            
            if ($enrolleeDocuments && is_array($enrolleeDocuments)) {
                foreach ($enrolleeDocuments as $index => $doc) {
                    if (is_array($doc)) {
                        $documents[] = [
                            'index' => $index,
                            'enrollee_id' => $enrollee->id,
                            'type' => $doc['type'] ?? 'Unknown',
                            'filename' => $doc['filename'] ?? 'Unknown',
                            'upload_date' => $doc['uploaded_at'] ?? $enrollee->created_at,
                            'status' => $doc['status'] ?? 'pending',
                            'path' => $doc['path'] ?? null,
                            'size' => $doc['size'] ?? null,
                            'mime_type' => $doc['mime_type'] ?? null
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'documents' => $documents,
                'application_id' => $applicationId,
                'student_name' => $enrollee->full_name
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching application documents: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading application documents'
            ], 500);
        }
    }

    /**
     * Get single document details or serve document file
     */
    public function getDocument($enrolleeId, $documentIndex)
    {
        try {
            $enrollee = Enrollee::findOrFail($enrolleeId);
            $documents = $enrollee->documents;
            
            // Handle both JSON string and array formats
            if (is_string($documents)) {
                $documents = json_decode($documents, true) ?? [];
            }
            
            if (!$documents || !is_array($documents) || !isset($documents[$documentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            $document = $documents[$documentIndex];
            
            // If requesting file download/view
            if (request()->has('view') || request()->has('download')) {
                if (!isset($document['path']) || !Storage::disk('public')->exists($document['path'])) {
                    abort(404, 'Document file not found');
                }
                
                $filePath = storage_path('app/public/' . $document['path']);
                $mimeType = $document['mime_type'] ?? 'application/octet-stream';
                
                return response()->file($filePath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => request()->has('download') ? 'attachment' : 'inline'
                ]);
            }
            
            // Return document details as JSON
            return response()->json([
                'success' => true,
                'enrollee_id' => $enrollee->id,
                'application_id' => $enrollee->application_id,
                'student_name' => $enrollee->full_name,
                'document_index' => $documentIndex,
                'document' => $document,
                'document_url' => isset($document['path']) ? Storage::url($document['path']) : null
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }
    }

    /**
     * Update document status
     */
    public function updateDocumentStatus(Request $request, $enrolleeId, $documentIndex): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,approved,verified,rejected',
                'notes' => 'nullable|string|max:500'
            ]);

            $enrollee = Enrollee::findOrFail($enrolleeId);
            $documents = $enrollee->documents;
            
            // Handle both JSON string and array formats
            if (is_string($documents)) {
                $documents = json_decode($documents, true) ?? [];
            }
            
            if (!$documents || !is_array($documents) || !isset($documents[$documentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Update document status
            $documents[$documentIndex]['status'] = $request->status;
            $documents[$documentIndex]['reviewed_by'] = Auth::id();
            $documents[$documentIndex]['reviewed_at'] = now()->toISOString();
            $documents[$documentIndex]['review_notes'] = $request->notes;

            $enrollee->update(['documents' => $documents]);

            // Create notice for document status change
            $this->createDocumentStatusNotice($enrollee, $documents[$documentIndex], $request->status, $request->notes);

            return response()->json([
                'success' => true,
                'message' => 'Document status updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating document status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating document status'
            ], 500);
        }
    }

    /**
     * Update appointment details for an application
     */
    public function updateAppointment(Request $request, $applicationId): JsonResponse
    {
        try {
            $enrollee = Enrollee::where('application_id', $applicationId)->firstOrFail();
            
            $validatedData = $request->validate([
                'preferred_schedule' => 'nullable|date',
                'enrollment_date' => 'nullable|date',
                'admin_notes' => 'nullable|string|max:1000'
            ]);
            
            // Update appointment fields
            if (isset($validatedData['preferred_schedule'])) {
                $enrollee->preferred_schedule = $validatedData['preferred_schedule'];
            }
            
            if (isset($validatedData['enrollment_date'])) {
                $enrollee->enrollment_date = $validatedData['enrollment_date'];
            }
            
            if (isset($validatedData['admin_notes'])) {
                $enrollee->admin_notes = $validatedData['admin_notes'];
            }
            
            $enrollee->save();
            
            // Create a notice for the enrollee about the appointment change
            if (isset($validatedData['preferred_schedule']) || isset($validatedData['enrollment_date'])) {
                $message = 'Your appointment details have been updated by the admin.';
                if (isset($validatedData['preferred_schedule'])) {
                    $message .= ' New preferred schedule: ' . date('M d, Y g:i A', strtotime($validatedData['preferred_schedule']));
                }
                if (isset($validatedData['enrollment_date'])) {
                    $message .= ' New enrollment date: ' . date('M d, Y', strtotime($validatedData['enrollment_date']));
                }
                
                Notice::create([
                    'enrollee_id' => $enrollee->id,
                    'title' => 'Appointment Updated',
                    'message' => $message,
                    'priority' => 'normal',
                    'type' => 'appointment'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Appointment updated successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating appointment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating appointment'
            ], 500);
        }
    }

    /**
     * Create notice for status change
     */
    private function createStatusChangeNotice($application, $oldStatus, $newStatus, $reason = null)
    {
        $title = "Application Status Update";
        $message = "Your application status has been changed from {$oldStatus} to {$newStatus}.";
        
        if ($reason) {
            $message .= "\n\nReason: {$reason}";
        }

        $priority = $newStatus === 'approved' ? 'high' : ($newStatus === 'rejected' ? 'urgent' : 'normal');

        Notice::create([
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'enrollee_id' => $application->id,
            'created_by' => Auth::id(),
            'is_read' => false
        ]);
    }

    /**
     * Create notice for document status change
     */
    private function createDocumentStatusNotice($enrollee, $document, $status, $notes = null)
    {
        $title = "Document Review Update";
        $documentType = $document['filename'] ?? 'Document';
        $message = "Your document '{$documentType}' has been {$status}.";
        
        if ($notes) {
            $message .= "\n\nNotes: {$notes}";
        }

        $priority = $status === 'verified' ? 'normal' : ($status === 'rejected' ? 'high' : 'normal');

        Notice::create([
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'enrollee_id' => $enrollee->id,
            'created_by' => Auth::id(),
            'is_read' => false
        ]);
    }

    /**
     * Approve application
     */
    public function approveApplication(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:500'
            ]);

            // Try to find by database ID first, then by application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }
            
            $application->update([
                'enrollment_status' => 'approved',
                'status_reason' => $request->reason,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'processed_by' => Auth::id()
            ]);

            // Create approval notice
            $this->createStatusChangeNotice($application, $application->enrollment_status, 'approved', $request->reason);

            Log::info('Application approved', [
                'application_id' => $application->application_id,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application approved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving application'
            ], 500);
        }
    }

    /**
     * Decline application
     */
    public function declineApplication(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:500'
            ]);

            // Try to find by database ID first, then by application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }
            
            $application->update([
                'enrollment_status' => 'rejected',
                'status_reason' => $request->reason,
                'rejected_by' => Auth::id(),
                'rejected_at' => now(),
                'processed_by' => Auth::id()
            ]);

            // Create rejection notice
            $this->createStatusChangeNotice($application, $application->enrollment_status, 'rejected', $request->reason);

            Log::info('Application declined', [
                'application_id' => $application->application_id,
                'admin_id' => Auth::id(),
                'reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application declined successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error declining application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error declining application'
            ], 500);
        }
    }

    /**
     * Delete application
     */
    public function deleteApplication($id): JsonResponse
    {
        try {
            // Try to find by database ID first, then by application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }
            $applicationId = $application->application_id;
            
            // Delete associated notices
            Notice::where('enrollee_id', $application->id)->delete();
            
            // Delete the application
            $application->delete();

            Log::info('Application deleted', [
                'application_id' => $applicationId,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting application'
            ], 500);
        }
    }

    /**
     * Bulk approve applications
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'application_ids' => 'required|array',
                'application_ids.*' => 'integer|exists:enrollees,id',
                'reason' => 'nullable|string|max:500'
            ]);

            $applications = Enrollee::whereIn('id', $request->application_ids)->get();
            $approvedCount = 0;

            foreach ($applications as $application) {
                if ($application->enrollment_status === 'pending') {
                    $application->update([
                        'enrollment_status' => 'approved',
                        'status_reason' => $request->reason,
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                        'processed_by' => Auth::id()
                    ]);

                    // Create approval notice
                    $this->createStatusChangeNotice($application, 'pending', 'approved', $request->reason);
                    $approvedCount++;
                }
            }

            Log::info('Bulk applications approved', [
                'count' => $approvedCount,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$approvedCount} applications approved successfully"
            ]);

        } catch (\Exception $e) {
            Log::error('Error bulk approving applications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving applications'
            ], 500);
        }
    }

    /**
     * Bulk decline applications
     */
    public function bulkDecline(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'application_ids' => 'required|array',
                'application_ids.*' => 'integer|exists:enrollees,id',
                'reason' => 'required|string|max:500'
            ]);

            $applications = Enrollee::whereIn('id', $request->application_ids)->get();
            $declinedCount = 0;

            foreach ($applications as $application) {
                if ($application->enrollment_status === 'pending') {
                    $application->update([
                        'enrollment_status' => 'rejected',
                        'status_reason' => $request->reason,
                        'rejected_by' => Auth::id(),
                        'rejected_at' => now(),
                        'processed_by' => Auth::id()
                    ]);

                    // Create rejection notice
                    $this->createStatusChangeNotice($application, 'pending', 'rejected', $request->reason);
                    $declinedCount++;
                }
            }

            Log::info('Bulk applications declined', [
                'count' => $declinedCount,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$declinedCount} applications declined successfully"
            ]);

        } catch (\Exception $e) {
            Log::error('Error bulk declining applications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error declining applications'
            ], 500);
        }
    }

    /**
     * Bulk delete applications
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'application_ids' => 'required|array',
                'application_ids.*' => 'integer|exists:enrollees,id'
            ]);

            // Delete associated notices first
            Notice::whereIn('enrollee_id', $request->application_ids)->delete();
            
            // Delete applications
            $deletedCount = Enrollee::whereIn('id', $request->application_ids)->delete();

            Log::info('Bulk applications deleted', [
                'count' => $deletedCount,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} applications deleted successfully"
            ]);

        } catch (\Exception $e) {
            Log::error('Error bulk deleting applications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting applications'
            ], 500);
        }
    }

    /**
     * Export enrollments data
     */
    public function export(Request $request)
    {
        try {
            $query = Enrollee::query();

            // Apply same filters as the main view
            if ($request->filled('status')) {
                $query->where('enrollment_status', $request->status);
            }

            if ($request->filled('grade_level')) {
                $query->where('grade_level_applied', $request->grade_level);
            }

            $enrollments = $query->orderBy('application_date', 'desc')->get();

            $filename = 'enrollments_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($enrollments) {
                $file = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($file, [
                    'Application ID', 'Full Name', 'Email', 'Grade Level', 'Status', 
                    'Student Type', 'Contact Number', 'Application Date'
                ]);

                foreach ($enrollments as $enrollment) {
                    fputcsv($file, [
                        $enrollment->application_id,
                        $enrollment->full_name,
                        $enrollment->email,
                        $enrollment->grade_level_applied,
                        $enrollment->enrollment_status,
                        $enrollment->student_type,
                        $enrollment->contact_number,
                        $enrollment->application_date ? $enrollment->application_date->format('Y-m-d') : 'N/A'
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting enrollments: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error exporting enrollments data');
        }
    }

    /**
     * Get notices data
     */
    public function getNotices(Request $request): JsonResponse
    {
        try {
            $query = Notice::with(['enrollee', 'createdBy']);

            // Apply filters
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('is_global')) {
                $query->where('is_global', $request->boolean('is_global'));
            }

            if ($request->filled('target_status')) {
                $query->where('target_status', $request->target_status);
            }

            $notices = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'notices' => $notices
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching notices: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading notices data'
            ], 500);
        }
    }

    /**
     * Create a new notice
     */
    public function createNotice(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'priority' => 'required|in:low,medium,high',
                'type' => 'required|in:individual,global',
                'target_status' => 'nullable|string',
                'target_grade_level' => 'nullable|string'
            ]);

            $noticeData = [
                'title' => $request->title,
                'message' => $request->message,
                'priority' => $request->priority,
                'created_by' => Auth::id(),
                'is_global' => $request->type === 'global',
                'target_status' => $request->target_status,
                'target_grade_level' => $request->target_grade_level
            ];

            if ($request->type === 'global') {
                // Create global notice
                Notice::create($noticeData);
            } else {
                // For individual notices, we would need to specify enrollee_id
                // This would typically be handled differently in the UI
                return response()->json([
                    'success' => false,
                    'message' => 'Individual notices require specific enrollee selection'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notice created successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating notice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating notice'
            ], 500);
        }
    }

    /**
     * Send bulk notices
     */
    public function sendBulkNotices(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'application_ids' => 'required|array',
                'application_ids.*' => 'exists:enrollees,application_id',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'priority' => 'required|in:low,medium,high'
            ]);

            $enrollees = Enrollee::whereIn('application_id', $request->application_ids)->get();
            $noticesCreated = 0;

            foreach ($enrollees as $enrollee) {
                Notice::create([
                    'title' => $request->title,
                    'message' => $request->message,
                    'priority' => $request->priority,
                    'enrollee_id' => $enrollee->id,
                    'created_by' => Auth::id(),
                    'is_global' => false
                ]);
                $noticesCreated++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully sent notices to {$noticesCreated} applicants"
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending bulk notices: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending notices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View document file (same as enrollee functionality)
     */
    public function viewDocument($enrolleeId, $index)
    {
        try {
            $enrollee = Enrollee::findOrFail($enrolleeId);
            
            // Handle both array and JSON string formats
            $documents = $enrollee->documents;
            if (is_string($documents)) {
                $documents = json_decode($documents, true) ?? [];
            }
            if (!is_array($documents)) {
                $documents = [];
            }
            
            if (!isset($documents[$index])) {
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
            
        } catch (\Exception $e) {
            Log::error('Error viewing document: ' . $e->getMessage());
            abort(404, 'Document not found');
        }
    }

    /**
     * Download document file
     */
    public function downloadDocument($enrolleeId, $index)
    {
        try {
            $enrollee = Enrollee::findOrFail($enrolleeId);
            
            // Handle both array and JSON string formats
            $documents = $enrollee->documents;
            if (is_string($documents)) {
                $documents = json_decode($documents, true) ?? [];
            }
            if (!is_array($documents)) {
                $documents = [];
            }
            
            if (!isset($documents[$index])) {
                abort(404, 'Document not found');
            }
            
            $document = $documents[$index];
            
            // Handle both old format (string paths) and new format (arrays with metadata)
            if (is_string($document)) {
                // Old format: just the file path
                $filePath = storage_path('app/public/' . $document);
                $filename = basename($document);
            } else {
                // New format: array with metadata
                $filePath = storage_path('app/public/' . $document['path']);
                $filename = $document['filename'] ?? basename($document['path']);
            }
            
            if (!file_exists($filePath)) {
                abort(404, 'File not found');
            }
            
            return response()->download($filePath, $filename);
            
        } catch (\Exception $e) {
            Log::error('Error downloading document: ' . $e->getMessage());
            abort(404, 'Document not found');
        }
    }

    /**
     * Delete notice
     */
    public function deleteNotice($noticeId)
    {
        try {
            $notice = Notice::findOrFail($noticeId);
            $notice->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Notice deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting notice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting notice: ' . $e->getMessage()
            ], 500);
        }
    }
}
