<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\Enrollee;
use App\Models\Notice;
use App\Models\Registrar;
use App\Mail\StudentCredentialsMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegistrarController extends Controller
{
    /**
     * Show the registrar dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_applications' => Enrollee::count(),
            'pending_applications' => Enrollee::where('enrollment_status', 'pending')->count(),
            'approved_applications' => Enrollee::where('enrollment_status', 'approved')->count(),
            'declined_applications' => Enrollee::where('enrollment_status', 'declined')->count(),
        ];

        $recent_applications = Enrollee::latest()
            ->take(5)
            ->get();

        return view('registrar.dashboard', compact('stats', 'recent_applications'));
    }

    /**
     * Show all applications
     */
    public function applications(Request $request)
    {
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
                  ->orWhere('application_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate summary statistics
        $totalApplications = Enrollee::count();
        $pendingApplications = Enrollee::where('enrollment_status', 'pending')->count();
        $approvedApplications = Enrollee::where('enrollment_status', 'approved')->count();
        $scheduledAppointments = Enrollee::whereNotNull('preferred_schedule')->count();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'applications' => $applications->items(),
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'total' => $applications->total(),
                ]
            ]);
        }

        return view('registrar.applications', compact(
            'applications', 
            'totalApplications', 
            'pendingApplications', 
            'approvedApplications', 
            'scheduledAppointments'
        ));
    }

    /**
     * Get specific application details
     */
    public function getApplication($id): JsonResponse
    {
        try {
            // Try database ID first, then application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }

            // Track first view by registrar
            if (!$application->first_viewed_at) {
                $application->update([
                    'first_viewed_at' => now(),
                    'first_viewed_by' => Auth::guard('registrar')->id()
                ]);
            }

            return response()->json([
                'success' => true,
                'application' => [
                    'id' => $application->id,
                    'application_id' => $application->application_id,
                    'full_name' => trim($application->first_name . ' ' . ($application->middle_name ? $application->middle_name . ' ' : '') . $application->last_name . ($application->suffix ? ' ' . $application->suffix : '')),
                    'first_name' => $application->first_name,
                    'middle_name' => $application->middle_name,
                    'last_name' => $application->last_name,
                    'suffix' => $application->suffix,
                    'email' => $application->email,
                    'contact_number' => $application->contact_number,
                    'date_of_birth' => $application->date_of_birth ? \Carbon\Carbon::parse($application->date_of_birth)->format('Y-m-d') : null,
                    'gender' => $application->gender,
                    'nationality' => $application->nationality,
                    'religion' => $application->religion,
                    'address' => $application->address,
                    'city' => $application->city,
                    'province' => $application->province,
                    'zip_code' => $application->zip_code,
                    'grade_level_applied' => $application->grade_level_applied,
                    'strand_applied' => $application->strand_applied,
                    'track_applied' => $application->track_applied,
                    'student_type' => $application->student_type,
                    'academic_year' => $application->academic_year,
                    'lrn' => $application->lrn,
                    'last_school_name' => $application->last_school_name,
                    'last_school_type' => $application->last_school_type,
                    'father_name' => $application->father_name,
                    'father_occupation' => $application->father_occupation,
                    'father_contact' => $application->father_contact,
                    'mother_name' => $application->mother_name,
                    'mother_occupation' => $application->mother_occupation,
                    'mother_contact' => $application->mother_contact,
                    'guardian_name' => $application->guardian_name,
                    'guardian_contact' => $application->guardian_contact,
                    'medical_history' => $application->medical_history,
                    'payment_mode' => $application->payment_mode,
                    'enrollment_status' => $application->enrollment_status,
                    'application_date' => $application->application_date ? $application->application_date->format('F d, Y g:i A') : $application->created_at->format('F d, Y g:i A'),
                    'created_at' => $application->created_at->format('M d, Y'),
                    'id_photo_data_url' => $application->id_photo_data_url,
                    'documents' => $application->documents,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }
    }

    /**
     * Approve an application
     */
    public function approveApplication(Request $request, $id): JsonResponse
    {
        try {
            // Try database ID first, then application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }

            $application->update([
                'enrollment_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::guard('registrar')->id(),
                'evaluation_completed_at' => now(),
                'evaluation_completed_by' => Auth::guard('registrar')->id(),
            ]);

            // Create approval notice
            Notice::create([
                'enrollee_id' => $application->id,
                'title' => 'Application Approved',
                'message' => 'Congratulations! Your enrollment application has been approved. You can now proceed to the student portal for enrollment completion.',
                'type' => 'success',
                'priority' => 'high',
                'is_global' => false,
                'created_by' => Auth::id(),
            ]);

            Log::info('Application approved', [
                'application_id' => $application->application_id,
                'approved_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application approved successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve application'
            ], 500);
        }
    }

    /**
     * Decline an application
     */
    public function declineApplication(Request $request, $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            // Try database ID first, then application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }

            $application->update([
                'enrollment_status' => 'declined',
                'declined_at' => now(),
                'declined_by' => Auth::guard('registrar')->id(),
                'decline_reason' => $request->reason,
                'evaluation_completed_at' => now(),
                'evaluation_completed_by' => Auth::guard('registrar')->id(),
            ]);

            // Create decline notice
            Notice::create([
                'enrollee_id' => $application->id,
                'title' => 'Application Declined',
                'message' => 'We regret to inform you that your enrollment application has been declined. Reason: ' . $request->reason,
                'type' => 'error',
                'priority' => 'high',
                'is_global' => false,
                'created_by' => Auth::id(),
            ]);

            Log::info('Application declined', [
                'application_id' => $application->application_id,
                'declined_by' => Auth::id(),
                'reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application declined successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error declining application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline application'
            ], 500);
        }
    }

    /**
     * Get appointments data
     */
    public function getAppointments(): JsonResponse
    {
        try {
            $appointments = Enrollee::whereNotNull('preferred_schedule')
                ->select([
                    'id', 'application_id', 'first_name', 'last_name', 'email',
                    'preferred_schedule', 'grade_level_applied', 'enrollment_status',
                    'created_at'
                ])
                ->orderBy('preferred_schedule', 'asc')
                ->get()
                ->map(function ($enrollee) {
                    return [
                        'id' => $enrollee->id,
                        'application_id' => $enrollee->application_id,
                        'full_name' => trim($enrollee->first_name . ' ' . $enrollee->last_name),
                        'email' => $enrollee->email,
                        'grade_level' => $enrollee->grade_level_applied,
                        'preferred_schedule' => $enrollee->preferred_schedule,
                        'status' => $enrollee->enrollment_status,
                        'appointment_status' => $this->getAppointmentStatus($enrollee->preferred_schedule, $enrollee->enrollment_status),
                        'created_at' => $enrollee->created_at->format('M d, Y')
                    ];
                });

            return response()->json([
                'success' => true,
                'appointments' => $appointments
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching appointments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appointments'
            ], 500);
        }
    }

    /**
     * Get notices data
     */
    public function getNotices(): JsonResponse
    {
        try {
            $notices = Notice::with('enrollee:id,application_id,first_name,last_name,email')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($notice) {
                    return [
                        'id' => $notice->id,
                        'title' => $notice->title,
                        'message' => $notice->message,
                        'type' => $notice->type,
                        'priority' => $notice->priority,
                        'is_global' => $notice->is_global,
                        'enrollee' => $notice->enrollee ? [
                            'application_id' => $notice->enrollee->application_id,
                            'full_name' => trim($notice->enrollee->first_name . ' ' . $notice->enrollee->last_name),
                            'email' => $notice->enrollee->email
                        ] : null,
                        'created_at' => $notice->created_at->format('M d, Y H:i'),
                        'read_at' => $notice->read_at ? $notice->read_at->format('M d, Y H:i') : null
                    ];
                });

            return response()->json([
                'success' => true,
                'notices' => $notices
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching notices: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notices'
            ], 500);
        }
    }

    /**
     * Get all documents data for review
     */
    public function getAllDocuments(Request $request): JsonResponse
    {
        try {
            $query = Enrollee::whereNotNull('documents')
                ->select(['id', 'application_id', 'first_name', 'last_name', 'documents', 'created_at']);

            // Apply filters
            if ($request->filled('status')) {
                // We'll filter by document status in the collection
            }

            if ($request->filled('type')) {
                // We'll filter by document type in the collection
            }

            $enrollees = $query->orderBy('created_at', 'desc')->get();
            $documents = collect();

            foreach ($enrollees as $enrollee) {
                $enrolleeDocuments = $enrollee->documents;
                
                // Handle both JSON string and array formats
                if (is_string($enrolleeDocuments)) {
                    $enrolleeDocuments = json_decode($enrolleeDocuments, true);
                }
                
                if (is_array($enrolleeDocuments)) {
                    foreach ($enrolleeDocuments as $index => $doc) {
                        $document = [
                            'application_id' => $enrollee->application_id,
                            'applicant_name' => trim($enrollee->first_name . ' ' . $enrollee->last_name),
                            'type' => $doc['type'] ?? 'Unknown',
                            'filename' => $doc['filename'] ?? 'Unknown file',
                            'path' => $doc['path'] ?? '',
                            'status' => $doc['status'] ?? 'pending',
                            'uploaded_at' => $doc['uploaded_at'] ?? $enrollee->created_at->toISOString(),
                            'index' => $index
                        ];

                        // Apply status filter
                        if ($request->filled('status') && $document['status'] !== $request->status) {
                            continue;
                        }

                        // Apply type filter
                        if ($request->filled('type') && $document['type'] !== $request->type) {
                            continue;
                        }

                        $documents->push($document);
                    }
                }
            }

            // Sort by upload date (newest first)
            $documents = $documents->sortByDesc('uploaded_at')->values();

            return response()->json([
                'success' => true,
                'documents' => $documents
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching documents: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch documents'
            ], 500);
        }
    }

    /**
     * Get appointment status based on schedule and enrollment status
     */
    private function getAppointmentStatus($preferredSchedule, $enrollmentStatus)
    {
        if (!$preferredSchedule) return 'No Schedule';
        
        $scheduleDate = \Carbon\Carbon::parse($preferredSchedule);
        $now = \Carbon\Carbon::now();
        
        if ($enrollmentStatus === 'approved') {
            return 'Completed';
        } elseif ($scheduleDate->isPast()) {
            return 'Overdue';
        } elseif ($scheduleDate->isToday()) {
            return 'Today';
        } else {
            return 'Scheduled';
        }
    }

    /**
     * Show approved applications
     */
    public function approved(Request $request)
    {
        $query = Enrollee::where('enrollment_status', 'approved');

        // Apply filters
        if ($request->filled('grade_level')) {
            $query->where('grade_level_applied', $request->grade_level);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('application_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $approved_applications = $query->orderBy('approved_at', 'desc')->paginate(20);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'applications' => $approved_applications->items(),
                'pagination' => [
                    'current_page' => $approved_applications->currentPage(),
                    'last_page' => $approved_applications->lastPage(),
                    'total' => $approved_applications->total(),
                ]
            ]);
        }

        return view('registrar.approved', compact('approved_applications'));
    }

    /**
     * Show reports
     */
    public function reports()
    {
        $stats = [
            'total_applications' => Enrollee::count(),
            'pending_applications' => Enrollee::where('enrollment_status', 'pending')->count(),
            'approved_applications' => Enrollee::where('enrollment_status', 'approved')->count(),
            'declined_applications' => Enrollee::where('enrollment_status', 'declined')->count(),
        ];

        // Applications by grade level
        $by_grade = Enrollee::selectRaw('grade_level_applied, count(*) as count')
            ->groupBy('grade_level_applied')
            ->orderBy('grade_level_applied')
            ->get();

        // Applications by month
        $by_month = Enrollee::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, count(*) as count')
            ->groupBy('month', 'year')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        return view('registrar.reports', compact('stats', 'by_grade', 'by_month'));
    }

    /**
     * Get application documents
     */
    public function getApplicationDocuments($id): JsonResponse
    {
        try {
            // Try database ID first, then application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }

            $documents = [];
            if ($application->documents) {
                // Handle both JSON string and array formats
                if (is_string($application->documents)) {
                    $documentsData = json_decode($application->documents, true);
                } else {
                    $documentsData = $application->documents;
                }
                
                if (is_array($documentsData)) {
                    foreach ($documentsData as $index => $doc) {
                        $documents[] = [
                            'index' => $index,
                            'type' => $doc['type'] ?? 'Document',
                            'filename' => $doc['filename'] ?? 'Unknown file',
                            'path' => $doc['path'] ?? '',
                            'status' => $doc['status'] ?? 'pending',
                            'uploaded_at' => $doc['uploaded_at'] ?? null
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'documents' => $documents,
                'application_id' => $application->application_id,
                'student_name' => trim($application->first_name . ' ' . $application->last_name)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching application documents: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch documents'
            ], 500);
        }
    }

    /**
     * Update document status
     */
    public function updateDocumentStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'document_index' => 'required|integer',
            'status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Try database ID first, then application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }

            $documents = $application->documents;
            if (is_string($documents)) {
                $documents = json_decode($documents, true);
            }

            if (!is_array($documents) || !isset($documents[$request->document_index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Update document status
            $documents[$request->document_index]['status'] = $request->status;
            $documents[$request->document_index]['reviewed_at'] = now()->toISOString();
            $documents[$request->document_index]['reviewed_by'] = Auth::guard('registrar')->id();
            if ($request->notes) {
                $documents[$request->document_index]['notes'] = $request->notes;
            }

            $application->update(['documents' => $documents]);

            // Track evaluation progress
            if (!$application->evaluation_started_at) {
                $application->update([
                    'evaluation_started_at' => now(),
                    'evaluation_started_by' => Auth::guard('registrar')->id()
                ]);
            }

            // Update document review counts
            $application->updateDocumentCounts();

            // Create notice for document status update
            Notice::create([
                'enrollee_id' => $application->id,
                'title' => 'Document ' . ucfirst($request->status),
                'message' => "Your document '{$documents[$request->document_index]['type']}' has been {$request->status}." . 
                           ($request->notes ? " Notes: {$request->notes}" : ''),
                'type' => $request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'error' : 'info'),
                'priority' => 'normal',
                'is_global' => false,
                'created_by' => Auth::guard('registrar')->id(),
            ]);

            // Always return JSON response since method signature requires JsonResponse
            return response()->json([
                'success' => true,
                'message' => "Document {$request->status} successfully"
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating document status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document status'
            ], 500);
        }
    }

    /**
     * Schedule appointment
     */
    public function scheduleAppointment(Request $request, $id): JsonResponse
    {
        $request->validate([
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required|string',
            'purpose' => 'required|string|max:255'
        ]);

        try {
            // Try database ID first, then application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }

            // Combine date and time
            $appointmentDateTime = $request->appointment_date . ' ' . $request->appointment_time;

            $application->update([
                'preferred_schedule' => $appointmentDateTime,
                'appointment_purpose' => $request->purpose,
                'appointment_scheduled_by' => Auth::id(),
                'appointment_scheduled_at' => now()
            ]);

            // Create appointment notice
            Notice::create([
                'enrollee_id' => $application->id,
                'title' => 'Appointment Scheduled',
                'message' => "Your enrollment appointment has been scheduled for {$appointmentDateTime}. Purpose: {$request->purpose}. Please arrive 15 minutes early.",
                'type' => 'info',
                'priority' => 'high',
                'is_global' => false,
                'created_by' => Auth::id(),
            ]);

            Log::info('Appointment scheduled', [
                'application_id' => $application->application_id,
                'appointment_date' => $appointmentDateTime,
                'scheduled_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment scheduled successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error scheduling appointment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule appointment'
            ], 500);
        }
    }

    /**
     * Send notice to applicant
     */
    public function sendNotice(Request $request, $id): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'priority' => 'required|in:normal,high,urgent'
        ]);

        try {
            // Try database ID first, then application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }

            Notice::create([
                'enrollee_id' => $application->id,
                'title' => $request->subject,
                'message' => $request->message,
                'type' => 'info',
                'priority' => $request->priority,
                'is_global' => false,
                'created_by' => Auth::id(),
            ]);

            Log::info('Notice sent to applicant', [
                'application_id' => $application->application_id,
                'subject' => $request->subject,
                'sent_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notice sent successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending notice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notice'
            ], 500);
        }
    }

    /**
     * Bulk approve applications
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $request->validate([
            'application_ids' => 'required|array',
            'application_ids.*' => 'required|integer'
        ]);

        try {
            $applications = Enrollee::whereIn('id', $request->application_ids)
                ->where('enrollment_status', 'pending')
                ->get();

            $approvedCount = 0;
            foreach ($applications as $application) {
                $application->update([
                    'enrollment_status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                ]);

                // Create approval notice
                Notice::create([
                    'enrollee_id' => $application->id,
                    'title' => 'Application Approved',
                    'message' => 'Congratulations! Your enrollment application has been approved. You can now proceed to the student portal for enrollment completion.',
                    'type' => 'success',
                    'priority' => 'high',
                    'is_global' => false,
                    'created_by' => Auth::id(),
                ]);

                $approvedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$approvedCount} application(s)"
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk approve: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve applications'
            ], 500);
        }
    }

    /**
     * Bulk decline applications
     */
    public function bulkDecline(Request $request): JsonResponse
    {
        $request->validate([
            'application_ids' => 'required|array',
            'application_ids.*' => 'required|integer',
            'reason' => 'required|string|max:500'
        ]);

        try {
            $applications = Enrollee::whereIn('id', $request->application_ids)
                ->where('enrollment_status', 'pending')
                ->get();

            $declinedCount = 0;
            foreach ($applications as $application) {
                $application->update([
                    'enrollment_status' => 'declined',
                    'declined_at' => now(),
                    'declined_by' => Auth::id(),
                    'decline_reason' => $request->reason,
                ]);

                // Create decline notice
                Notice::create([
                    'enrollee_id' => $application->id,
                    'title' => 'Application Declined',
                    'message' => 'We regret to inform you that your enrollment application has been declined. Reason: ' . $request->reason,
                    'type' => 'error',
                    'priority' => 'high',
                    'is_global' => false,
                    'created_by' => Auth::id(),
                ]);

                $declinedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully declined {$declinedCount} application(s)"
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk decline: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline applications'
            ], 500);
        }
    }

    /**
     * Serve document files securely
     */
    public function serveDocument($path)
    {
        try {
            $fullPath = storage_path('app/public/' . $path);
            
            if (!file_exists($fullPath)) {
                abort(404, 'Document not found');
            }
            
            $mimeType = mime_content_type($fullPath);
            $fileName = basename($fullPath);
            
            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Exception $e) {
            Log::error('Error serving document: ' . $e->getMessage());
            abort(404, 'Document not found');
        }
    }

    /**
     * Approve appointment
     */
    public function approveAppointment(Request $request, $id): JsonResponse
    {
        try {
            // Try database ID first, then application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }

            $application->update([
                'appointment_status' => 'approved',
                'appointment_notes' => $request->notes ?? 'Appointment approved by registrar'
            ]);

            // Create notice
            Notice::create([
                'enrollee_id' => $application->id,
                'title' => 'Appointment Approved',
                'message' => 'Your appointment request has been approved. Please arrive on time for your scheduled appointment.',
                'type' => 'success',
                'priority' => 'normal',
                'is_global' => false,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment approved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving appointment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve appointment'
            ], 500);
        }
    }

    /**
     * Reject appointment
     */
    public function rejectAppointment(Request $request, $id): JsonResponse
    {
        $request->validate([
            'notes' => 'required|string|max:500'
        ]);

        try {
            // Try database ID first, then application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }

            $application->update([
                'appointment_status' => 'rejected',
                'appointment_notes' => $request->notes
            ]);

            // Create notice
            Notice::create([
                'enrollee_id' => $application->id,
                'title' => 'Appointment Rejected',
                'message' => 'Your appointment request has been rejected. Reason: ' . $request->notes,
                'type' => 'error',
                'priority' => 'normal',
                'is_global' => false,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment rejected successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting appointment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject appointment'
            ], 500);
        }
    }

    /**
     * Update appointment schedule
     */
    public function updateAppointmentSchedule(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed',
            'new_date' => 'nullable|date',
            'new_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Try database ID first, then application_id
            $application = Enrollee::where('id', $id)->first();
            if (!$application) {
                $application = Enrollee::where('application_id', $id)->firstOrFail();
            }

            $updateData = [
                'appointment_status' => $request->status,
                'appointment_notes' => $request->notes ?? ''
            ];

            // Update schedule if provided
            if ($request->new_date && $request->new_time) {
                $newSchedule = $request->new_date . ' ' . $request->new_time;
                $updateData['preferred_schedule'] = $newSchedule;
            }

            $application->update($updateData);

            // Create notice
            $noticeMessage = 'Your appointment has been updated. Status: ' . ucfirst($request->status);
            if ($request->new_date && $request->new_time) {
                $noticeMessage .= ' New schedule: ' . \Carbon\Carbon::parse($newSchedule)->format('M d, Y g:i A');
            }
            if ($request->notes) {
                $noticeMessage .= ' Notes: ' . $request->notes;
            }

            Notice::create([
                'enrollee_id' => $application->id,
                'title' => 'Appointment Updated',
                'message' => $noticeMessage,
                'type' => $request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'error' : 'info'),
                'priority' => 'normal',
                'is_global' => false,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating appointment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update appointment'
            ], 500);
        }
    }

    /**
     * Create notice
     */
    public function createNotice(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'priority' => 'required|in:normal,high,urgent',
            'recipients' => 'required|in:all,pending,approved,specific',
            'specific_applicant' => 'required_if:recipients,specific|exists:enrollees,id'
        ]);

        try {
            if ($request->recipients === 'specific') {
                // Send to specific applicant
                Notice::create([
                    'enrollee_id' => $request->specific_applicant,
                    'title' => $request->title,
                    'message' => $request->message,
                    'type' => $request->type,
                    'priority' => $request->priority,
                    'is_global' => false,
                    'created_by' => Auth::id(),
                ]);
            } else {
                // Send to filtered group
                $query = Enrollee::query();
                
                if ($request->recipients === 'pending') {
                    $query->where('enrollment_status', 'pending');
                } elseif ($request->recipients === 'approved') {
                    $query->where('enrollment_status', 'approved');
                }
                
                $enrollees = $query->get();
                
                foreach ($enrollees as $enrollee) {
                    Notice::create([
                        'enrollee_id' => $enrollee->id,
                        'title' => $request->title,
                        'message' => $request->message,
                        'type' => $request->type,
                        'priority' => $request->priority,
                        'is_global' => $request->recipients === 'all',
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Notice sent successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating notice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notice'
            ], 500);
        }
    }

    /**
     * Update notice
     */
    public function updateNotice(Request $request, $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'priority' => 'required|in:normal,high,urgent',
            'recipients' => 'required|in:all,pending,approved,specific',
            'specific_applicant' => 'required_if:recipients,specific|exists:enrollees,id'
        ]);

        try {
            $notice = Notice::findOrFail($id);
            
            // Update notice fields
            $notice->update([
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'priority' => $request->priority,
            ]);

            // Handle recipient changes if needed
            if ($request->recipients === 'specific' && $request->specific_applicant) {
                $notice->update([
                    'enrollee_id' => $request->specific_applicant,
                    'is_global' => false
                ]);
            } elseif ($request->recipients === 'all') {
                $notice->update([
                    'enrollee_id' => null,
                    'is_global' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notice updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating notice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notice'
            ], 500);
        }
    }

    /**
     * Send bulk notice
     */
    public function sendBulkNotice(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'priority' => 'required|in:normal,high,urgent',
            'status_filter' => 'nullable|in:pending,approved,rejected',
            'grade_filter' => 'nullable|string'
        ]);

        try {
            $query = Enrollee::query();
            
            if ($request->status_filter) {
                $query->where('enrollment_status', $request->status_filter);
            }
            
            if ($request->grade_filter) {
                $query->where('grade_level_applied', $request->grade_filter);
            }
            
            $enrollees = $query->get();
            $count = 0;
            
            foreach ($enrollees as $enrollee) {
                Notice::create([
                    'enrollee_id' => $enrollee->id,
                    'title' => $request->title,
                    'message' => $request->message,
                    'type' => $request->type,
                    'priority' => $request->priority,
                    'is_global' => !$request->status_filter && !$request->grade_filter,
                    'created_by' => Auth::id(),
                ]);
                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "Bulk notice sent to {$count} applicant(s) successfully"
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending bulk notice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk notice'
            ], 500);
        }
    }

    /**
     * Get single notice
     */
    public function getNotice($id): JsonResponse
    {
        try {
            $notice = Notice::with('enrollee:id,application_id,first_name,last_name,email')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'notice' => [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'message' => $notice->message,
                    'type' => $notice->type,
                    'priority' => $notice->priority,
                    'is_global' => $notice->is_global,
                    'enrollee' => $notice->enrollee ? [
                        'application_id' => $notice->enrollee->application_id,
                        'full_name' => trim($notice->enrollee->first_name . ' ' . $notice->enrollee->last_name),
                        'email' => $notice->enrollee->email
                    ] : null,
                    'created_at' => $notice->created_at->format('M d, Y H:i'),
                    'read_at' => $notice->read_at ? $notice->read_at->format('M d, Y H:i') : null
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching notice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Notice not found'
            ], 404);
        }
    }

    /**
     * Preview recipients for bulk notice
     */
    public function previewRecipients(Request $request): JsonResponse
    {
        try {
            $query = Enrollee::select('id', 'application_id', 'first_name', 'last_name', 'enrollment_status', 'grade_level_applied');
            
            if ($request->status_filter) {
                $query->where('enrollment_status', $request->status_filter);
            }
            
            if ($request->grade_filter) {
                $query->where('grade_level_applied', $request->grade_filter);
            }
            
            $enrollees = $query->get();
            
            $recipients = $enrollees->map(function ($enrollee) {
                return [
                    'application_id' => $enrollee->application_id,
                    'name' => trim($enrollee->first_name . ' ' . $enrollee->last_name),
                    'grade' => $enrollee->grade_level_applied,
                    'status' => $enrollee->enrollment_status
                ];
            });

            return response()->json([
                'success' => true,
                'recipients' => $recipients,
                'count' => $recipients->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error previewing recipients: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to preview recipients'
            ], 500);
        }
    }
}