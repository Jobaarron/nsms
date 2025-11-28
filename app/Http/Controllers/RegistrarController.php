<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\Enrollee;
use App\Models\Notice;
use App\Models\Registrar;
use App\Models\Student;
use App\Models\Section;
use App\Models\FacultyAssignment;
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
            'declined_applications' => Enrollee::where('enrollment_status', 'rejected')->count(),
        ];

        $recent_applications = Enrollee::latest()
            ->take(5)
            ->get();

        // Applications by grade level (from reports)
        $by_grade = Enrollee::selectRaw('grade_level_applied, count(*) as count')
            ->groupBy('grade_level_applied')
            ->orderBy('grade_level_applied')
            ->get();

        // Applications by month (from reports)
        $by_month = Enrollee::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, count(*) as count')
            ->groupBy('month', 'year')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        return view('registrar.dashboard', compact('stats', 'recent_applications', 'by_grade', 'by_month'));
    }

    /**
     * Get dashboard statistics for AJAX requests
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            $stats = [
                'total_applications' => Enrollee::count(),
                'pending_applications' => Enrollee::where('enrollment_status', 'pending')->count(),
                'approved_applications' => Enrollee::where('enrollment_status', 'approved')->count(),
                'declined_applications' => Enrollee::where('enrollment_status', 'rejected')->count(),
            ];

            $recent_applications = Enrollee::latest()
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'recent_applications' => $recent_applications,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching dashboard stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics'
            ], 500);
        }
    }

    /**
     * Show all applications
     */
    public function applications(Request $request)
    {
        // Only show pending applications (approved/declined ones go to archives)
        $query = Enrollee::where('enrollment_status', 'pending');

        // Apply filters - only pending status filter is relevant now
        if ($request->filled('status') && $request->status === 'pending') {
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

        // Calculate summary statistics (only for pending applications - declined/approved go to archives)
        $totalApplications = Enrollee::count(); // All applications ever submitted
        $pendingApplications = Enrollee::where('enrollment_status', 'pending')->count();
        $declinedApplications = Enrollee::where('enrollment_status', 'rejected')->count(); // For display only, these are in archives

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'applications' => $applications->items(), // Only pending applications
                'totalApplications' => $totalApplications,
                'pendingApplications' => $pendingApplications,
                'declinedApplications' => $declinedApplications, // For stats display only
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'total' => $applications->total(),
                ],
                'timestamp' => now()->toISOString()
            ]);
        }

        return view('registrar.applications', compact(
            'applications', 
            'totalApplications', 
            'pendingApplications', 
            'declinedApplications', 
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
                'processed_by' => Auth::guard('registrar')->id(),
                'evaluation_completed_at' => now(),
                'evaluation_completed_by' => Auth::guard('registrar')->id(),
            ]);

            // Create approval notice
            Notice::create([
                'enrollee_id' => $application->id,
                'title' => 'Application Approved',
                'message' => 'Congratulations! Your enrollment application has been approved. You can now proceed to the student portal for enrollment completion.',
                'is_global' => false,
                'created_by' => Auth::guard('registrar')->id(),
            ]);

            Log::info('Application approved', [
                'application_id' => $application->application_id,
                'approved_by' => Auth::guard('registrar')->id()
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
                'enrollment_status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => Auth::guard('registrar')->id(),
                'processed_by' => Auth::guard('registrar')->id(),
                'status_reason' => $request->reason,
                'evaluation_completed_at' => now(),
                'evaluation_completed_by' => Auth::guard('registrar')->id(),
            ]);

            // Create decline notice
            Notice::create([
                'enrollee_id' => $application->id,
                'title' => 'Application Declined',
                'message' => 'We regret to inform you that your enrollment application has been declined. Reason: ' . $request->reason,
                'is_global' => false,
                'created_by' => Auth::guard('registrar')->id(),
            ]);

            // Send rejection email immediately
            try {
                Mail::to($application->email)->send(new \App\Mail\ApplicationRejectionMail($application));
                Log::info('Rejection email sent', [
                    'application_id' => $application->application_id,
                    'email' => $application->email
                ]);
            } catch (\Exception $emailError) {
                Log::error('Failed to send rejection email', [
                    'application_id' => $application->application_id,
                    'error' => $emailError->getMessage()
                ]);
            }

            Log::info('Application declined', [
                'application_id' => $application->application_id,
                'declined_by' => Auth::guard('registrar')->id(),
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
     * Show applicant archives (approved and declined applications)
     */
    public function applicantArchives(Request $request)
    {
        // Get approved applications with student relationship
        $approvedQuery = Enrollee::with('student')
            ->where('enrollment_status', 'approved');
        
        // Get declined applications
        $declinedQuery = Enrollee::where('enrollment_status', 'rejected');
        
        // Get all archived applications (approved + rejected)
        $allArchivesQuery = Enrollee::with('student')
            ->whereIn('enrollment_status', ['approved', 'rejected']);

        // Apply filters to all queries
        $queries = [$approvedQuery, $declinedQuery, $allArchivesQuery];
        
        foreach ($queries as $query) {
            if ($request->filled('status')) {
                if ($request->status === 'approved') {
                    $query->where('enrollment_status', 'approved');
                } elseif ($request->status === 'declined') {
                    $query->where('enrollment_status', 'rejected');
                }
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
        }

        // Get paginated results
        $approvedApplications = $approvedQuery->orderBy('approved_at', 'desc')->paginate(20, ['*'], 'approved_page');
        $declinedApplications = $declinedQuery->orderBy('rejected_at', 'desc')->paginate(20, ['*'], 'declined_page');
        $allArchives = $allArchivesQuery->orderBy('updated_at', 'desc')->paginate(20, ['*'], 'all_page');

        // Calculate counts
        $approvedCount = Enrollee::where('enrollment_status', 'approved')->count();
        $declinedCount = Enrollee::where('enrollment_status', 'rejected')->count();
        $enrolledCount = \App\Models\Student::where('enrollment_status', 'enrolled')->count();

        if ($request->expectsJson()) {
            $tab = $request->get('tab', 'approved');
            
            switch ($tab) {
                case 'declined':
                    $applications = $declinedApplications;
                    break;
                case 'all-archives':
                    $applications = $allArchives;
                    break;
                default:
                    $applications = $approvedApplications;
                    break;
            }
            
            return response()->json([
                'success' => true,
                'applications' => $applications->items(),
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'total' => $applications->total(),
                ],
                'counts' => [
                    'approved' => $approvedCount,
                    'declined' => $declinedCount,
                    'enrolled' => $enrolledCount,
                    'total_archived' => $approvedCount + $declinedCount
                ]
            ]);
        }

        return view('registrar.applicant-archives', compact(
            'approvedApplications',
            'declinedApplications', 
            'allArchives',
            'approvedCount',
            'declinedCount',
            'enrolledCount'
        ));
    }

    /**
     * Reconsider a declined application
     */
    public function reconsiderApplication(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $application = Enrollee::findOrFail($id);
            
            if ($application->enrollment_status !== 'declined') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only declined applications can be reconsidered'
                ], 400);
            }

            $application->update([
                'enrollment_status' => 'pending',
                'declined_at' => null,
                'decline_reason' => null,
                'reconsider_reason' => $request->reason,
                'reconsidered_at' => now(),
                'reconsidered_by' => auth()->id()
            ]);

            // Create a notice for the applicant
            Notice::create([
                'enrollee_id' => $application->id,
                'title' => 'Application Reconsidered',
                'message' => "Your application has been reconsidered and is now under review again. Reason: {$request->reason}",
                'is_global' => false,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application has been reconsidered and moved back to pending status'
            ]);

        } catch (\Exception $e) {
            Log::error('Error reconsidering application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reconsider application'
            ], 500);
        }
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
                'is_global' => false,
                'created_by' => Auth::guard('registrar')->id(),
            ]);

            // Check if all documents are approved and auto-approve application
            if ($request->status === 'approved') {
                $allDocumentsApproved = true;
                foreach ($documents as $doc) {
                    if (($doc['status'] ?? 'pending') !== 'approved') {
                        $allDocumentsApproved = false;
                        break;
                    }
                }

                // Auto-approve application if all documents are approved
                if ($allDocumentsApproved && $application->enrollment_status === 'pending') {
                    $application->update([
                        'enrollment_status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => Auth::guard('registrar')->id()
                    ]);

                    // Create approval notice
                    Notice::create([
                        'enrollee_id' => $application->id,
                        'title' => 'Application Approved',
                        'message' => 'Congratulations! Your enrollment application has been approved. All your documents have been verified and accepted. You may now proceed with the next steps.',
                        'type' => 'success',
                        'priority' => 'high',
                        'is_global' => false,
                        'created_by' => Auth::guard('registrar')->id(),
                    ]);

                    $responseMessage = "Document approved successfully. Application automatically approved since all documents are now approved.";
                } else {
                    $responseMessage = "Document {$request->status} successfully";
                }
            } else {
                $responseMessage = "Document {$request->status} successfully";
            }

            // Always return JSON response since method signature requires JsonResponse
            return response()->json([
                'success' => true,
                'message' => $responseMessage
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
     * Send notice to applicant
     */
    public function sendNotice(Request $request, $id): JsonResponse
    {
        Log::info('sendNotice called', [
            'id' => $id,
            'request_data' => $request->all(),
            'subject' => $request->get('subject'),
            'message' => $request->get('message')
        ]);

        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000'
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
            'recipients' => 'required|in:all,pending,approved,specific',
            'specific_applicant' => 'required_if:recipients,specific|exists:enrollees,id'
        ]);

        try {
            $notice = Notice::findOrFail($id);
            
            // Update notice fields
            $notice->update([
                'title' => $request->title,
                'message' => $request->message,
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
        // Support both application_ids (simple) and filters (complex) approach
        if ($request->has('application_ids')) {
            // Simple bulk notice to specific applications
            $request->validate([
                'application_ids' => 'required|array',
                'application_ids.*' => 'required|string',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'nullable|in:info,success,warning,error'
            ]);

            try {
                // Find enrollees by application_id (not database id)
                $enrollees = Enrollee::whereIn('application_id', $request->application_ids)->get();
                $count = 0;
                
                foreach ($enrollees as $enrollee) {
                    Notice::create([
                        'enrollee_id' => $enrollee->id,
                        'title' => $request->title,
                        'message' => $request->message,
                        'is_global' => false,
                        'created_by' => Auth::id(),
                    ]);
                    $count++;
                }

                return response()->json([
                    'success' => true,
                    'message' => "Notice sent to {$count} student(s) successfully"
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending bulk notice: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send notices'
                ], 500);
            }
        } else {
            // Complex bulk notice with filters (original logic)
            $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
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
    }

    /**
     * Get applications data for AJAX requests
     */
    public function getApplicationsData(Request $request): JsonResponse
    {
        try {
            $tab = $request->get('tab', 'pending');
            
            // Base query
            $query = Enrollee::query();
            
            // Filter by tab
            switch ($tab) {
                case 'pending':
                    $query->where('enrollment_status', 'pending');
                    break;
                case 'approved':
                    $query->where('enrollment_status', 'approved');
                    break;
                case 'declined':
                    $query->where('enrollment_status', 'declined');
                    break;
                case 'applications':
                    // Show all applications (same as 'all')
                    break;
                case 'notices':
                    // For notices tab, show all applications
                    break;
                case 'all':
                    // No additional filter
                    break;
                default:
                    // Default to pending if unknown tab
                    $query->where('enrollment_status', 'pending');
                    break;
            }
            
            // Apply additional filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('application_id', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('status')) {
                $query->where('enrollment_status', $request->status);
            }
            
            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }
            
            $applications = $query->select([
                'id',
                'application_id', 
                'first_name', 
                'last_name', 
                'email',
                'enrollment_status',
                'grade_level_applied',
                'created_at'
            ])
            ->orderBy('created_at', 'desc')
            ->get();
            
            // Calculate counts for all tabs
            $counts = [
                'pending' => Enrollee::where('enrollment_status', 'pending')->count(),
                'approved' => Enrollee::where('enrollment_status', 'approved')->count(),
                'declined' => Enrollee::where('enrollment_status', 'declined')->count(),
                'applications' => Enrollee::count(),
                'notices' => Enrollee::count(),
                'all' => Enrollee::count(),
            ];

            return response()->json([
                'success' => true,
                'applications' => $applications,
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getApplicationsData: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load applications data: ' . $e->getMessage()
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

    /**
     * Display class lists with filtering options
     */
    public function classLists(Request $request)
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get filter parameters
        $selectedGrade = $request->get('grade_level');
        $selectedStrand = $request->get('strand');
        $selectedTrack = $request->get('track');
        $selectedSection = $request->get('section');
        
        // Get all available grade levels from enrolled students with settled payments
        // Only need to be enrolled (1st quarter paid), not fully paid
        $availableGrades = Student::where('academic_year', $currentAcademicYear)
                                 ->where('is_active', true)
                                 ->where('enrollment_status', 'enrolled')
                                 ->distinct()
                                 ->pluck('grade_level')
                                 ->sort()
                                 ->values();
        
        // Grade level order for proper sorting
        $gradeOrder = [
            'Nursery', 'Junior Casa', 'Senior Casa', 'Kinder',
            'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
            'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
        ];
        
        // Sort grades according to proper order, fallback to all grades if no students found
        if ($availableGrades->count() > 0) {
            $orderedGrades = collect($gradeOrder)->filter(function($grade) use ($availableGrades) {
                return $availableGrades->contains($grade);
            });
        } else {
            $orderedGrades = collect($gradeOrder);
        }
        
        // Get available strands for selected grade (if Grade 11 or 12)
        $availableStrands = collect();
        if ($selectedGrade && in_array($selectedGrade, ['Grade 11', 'Grade 12'])) {
            $availableStrands = Student::where('grade_level', $selectedGrade)
                                     ->where('academic_year', $currentAcademicYear)
                                     ->where('is_active', true)
                                     ->where('enrollment_status', 'enrolled')
                                     ->whereNotNull('strand')
                                     ->distinct()
                                     ->pluck('strand')
                                     ->sort()
                                     ->values();
        }
        
        // Get available tracks for selected strand (if TVL)
        $availableTracks = collect();
        if ($selectedStrand === 'TVL') {
            $availableTracks = Student::where('grade_level', $selectedGrade)
                                    ->where('strand', 'TVL')
                                    ->where('academic_year', $currentAcademicYear)
                                    ->where('is_active', true)
                                    ->where('enrollment_status', 'enrolled')
                                    ->whereNotNull('track')
                                    ->distinct()
                                    ->pluck('track')
                                    ->sort()
                                    ->values();
        }
        
        // Get available sections for selected grade/strand/track
        $availableSections = collect();
        if ($selectedGrade) {
            $sectionsQuery = Student::where('grade_level', $selectedGrade)
                                   ->where('academic_year', $currentAcademicYear)
                                   ->where('is_active', true)
                                   ->where('enrollment_status', 'enrolled');
            
            if ($selectedStrand) {
                $sectionsQuery->where('strand', $selectedStrand);
            }
            
            if ($selectedTrack) {
                $sectionsQuery->where('track', $selectedTrack);
            }
            
            $availableSections = $sectionsQuery->distinct()
                                             ->pluck('section')
                                             ->sort()
                                             ->values();
        }
        
        // Get students for selected filters
        $students = collect();
        $classInfo = null;
        $classAdviser = null;
        $subjectTeachers = collect();
        
        if ($selectedGrade && $selectedSection) {
            // Build the query - only enrolled students (1st quarter paid)
            $studentsQuery = Student::where('grade_level', $selectedGrade)
                                   ->where('section', $selectedSection)
                                   ->where('academic_year', $currentAcademicYear)
                                   ->where('is_active', true)
                                   ->where('enrollment_status', 'enrolled');
            
            // Add strand/track filters if applicable
            if ($selectedStrand) {
                $studentsQuery->where('strand', $selectedStrand);
            }
            
            if ($selectedTrack) {
                $studentsQuery->where('track', $selectedTrack);
            }
            
            // Get students ordered by name
            $students = $studentsQuery->orderBy('last_name')
                                    ->orderBy('first_name')
                                    ->get();
            
            // Build class info string using consistent format with strand/track first
            if ($selectedStrand) {
                if ($selectedTrack) {
                    // For TVL with track: "Grade 11 TVL-ICT Section A"
                    $classInfo = $selectedGrade . ' ' . $selectedStrand . '-' . $selectedTrack . ' Section ' . $selectedSection;
                } else {
                    // For non-TVL strands: "Grade 11 STEM Section A"
                    $classInfo = $selectedGrade . ' ' . $selectedStrand . ' Section ' . $selectedSection;
                }
            } else {
                // For Elementary/JHS: "Grade 7 Section A"
                $classInfo = $selectedGrade . ' Section ' . $selectedSection;
            }
            
            // Get class adviser
            $adviserQuery = FacultyAssignment::where('grade_level', $selectedGrade)
                                           ->where('section', $selectedSection)
                                           ->where('assignment_type', 'class_adviser')
                                           ->where('academic_year', $currentAcademicYear)
                                           ->where('status', 'active')
                                           ->with(['teacher.user']);
            
            if ($selectedStrand) {
                $adviserQuery->where('strand', $selectedStrand);
            }
            
            if ($selectedTrack) {
                $adviserQuery->where('track', $selectedTrack);
            }
            
            $classAdviser = $adviserQuery->first();
            
            // Get subject teachers
            $teachersQuery = FacultyAssignment::where('grade_level', $selectedGrade)
                                            ->where('section', $selectedSection)
                                            ->where('assignment_type', 'subject_teacher')
                                            ->where('academic_year', $currentAcademicYear)
                                            ->where('status', 'active')
                                            ->with(['teacher.user', 'subject']);
            
            if ($selectedStrand) {
                $teachersQuery->where('strand', $selectedStrand);
            }
            
            if ($selectedTrack) {
                $teachersQuery->where('track', $selectedTrack);
            }
            
            $subjectTeachers = $teachersQuery->get();
        }
        
        return view('registrar.class-lists', compact(
            'orderedGrades',
            'availableStrands',
            'availableTracks',
            'availableSections',
            'students',
            'classInfo',
            'classAdviser',
            'subjectTeachers',
            'selectedGrade',
            'selectedStrand',
            'selectedTrack',
            'selectedSection',
            'currentAcademicYear'
        ));
    }
    
    /**
     * Get sections via AJAX for dynamic filtering
     */
    public function getClassListSections(Request $request)
    {
        $gradeLevel = $request->get('grade_level');
        $strand = $request->get('strand');
        $track = $request->get('track');
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        if (!$gradeLevel) {
            return response()->json(['success' => false, 'message' => 'Grade level is required']);
        }
        
        $sectionsQuery = Student::where('grade_level', $gradeLevel)
                               ->where('academic_year', $currentAcademicYear)
                               ->where('is_active', true)
                               ->where('enrollment_status', 'enrolled');
        
        if ($strand) {
            $sectionsQuery->where('strand', $strand);
        }
        
        if ($track) {
            $sectionsQuery->where('track', $track);
        }
        
        $sections = $sectionsQuery->select('section')
                                 ->selectRaw('COUNT(*) as student_count')
                                 ->groupBy('section')
                                 ->orderBy('section')
                                 ->get();
        
        // If no sections found in database, provide default sections with 0 count
        if ($sections->isEmpty()) {
            $sections = collect([
                ['section' => 'A', 'student_count' => 0],
                ['section' => 'B', 'student_count' => 0],
                ['section' => 'C', 'student_count' => 0]
            ]);
        }
        
        return response()->json([
            'success' => true,
            'sections' => $sections
        ]);
    }
    
    /**
     * Get strands via AJAX for dynamic filtering
     */
    public function getClassListStrands(Request $request)
    {
        $gradeLevel = $request->get('grade_level');
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        if (!$gradeLevel || !in_array($gradeLevel, ['Grade 11', 'Grade 12'])) {
            return response()->json(['success' => true, 'strands' => []]);
        }
        
        $strands = Student::where('grade_level', $gradeLevel)
                         ->where('academic_year', $currentAcademicYear)
                         ->where('is_active', true)
                         ->where('enrollment_status', 'enrolled')
                         ->whereNotNull('strand')
                         ->distinct()
                         ->pluck('strand')
                         ->sort()
                         ->values();
        
        // If no strands found in database, provide default options
        if ($strands->isEmpty()) {
            $strands = collect(['STEM', 'ABM', 'HUMSS', 'TVL']);
        }
        
        return response()->json([
            'success' => true,
            'strands' => $strands
        ]);
    }
    
    /**
     * Get tracks via AJAX for dynamic filtering
     */
    public function getClassListTracks(Request $request)
    {
        $gradeLevel = $request->get('grade_level');
        $strand = $request->get('strand');
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        if (!$gradeLevel || $strand !== 'TVL') {
            return response()->json(['success' => true, 'tracks' => []]);
        }
        
        $tracks = Student::where('grade_level', $gradeLevel)
                        ->where('strand', $strand)
                        ->where('academic_year', $currentAcademicYear)
                        ->where('is_active', true)
                        ->where('enrollment_status', 'enrolled')
                        ->whereNotNull('track')
                        ->distinct()
                        ->pluck('track')
                        ->sort()
                        ->values();
        
        // If no tracks found in database, provide default options
        if ($tracks->isEmpty()) {
            $tracks = collect(['ICT', 'H.E']);
        }
        
        return response()->json([
            'success' => true,
            'tracks' => $tracks
        ]);
    }
    
    /**
     * Get students via AJAX for dynamic filtering
     */
    public function getClassListStudents(Request $request)
    {
        $gradeLevel = $request->get('grade_level');
        $section = $request->get('section');
        $strand = $request->get('strand');
        $track = $request->get('track');
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        if (!$gradeLevel || !$section) {
            return response()->json([
                'success' => false,
                'message' => 'Grade level and section are required'
            ]);
        }
        
        // Build the query - only enrolled students (1st quarter paid)
        $studentsQuery = Student::where('grade_level', $gradeLevel)
                               ->where('section', $section)
                               ->where('academic_year', $currentAcademicYear)
                               ->where('is_active', true)
                               ->where('enrollment_status', 'enrolled');
        
        // Add strand filter for Grade 11 & 12
        if (in_array($gradeLevel, ['Grade 11', 'Grade 12']) && $strand) {
            $studentsQuery->where('strand', $strand);
        }
        
        // Add track filter for TVL
        if ($strand === 'TVL' && $track) {
            $studentsQuery->where('track', $track);
        }
        
        // Add search functionality
        $search = $request->get('search');
        if ($search) {
            $studentsQuery->where(function($query) use ($search) {
                $query->where('first_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('student_id', 'LIKE', '%' . $search . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $search . '%']);
            });
        }
        
        $students = $studentsQuery->orderBy('last_name')
                                 ->orderBy('first_name')
                                 ->get();
        
        // Build class info
        $classInfo = $gradeLevel . ' - ' . $section;
        if ($strand) {
            $classInfo .= ' - ' . $strand;
            if ($track) {
                $classInfo .= ' - ' . $track;
            }
        }
        
        return response()->json([
            'success' => true,
            'students' => $students,
            'class_info' => $classInfo,
            'count' => $students->count(),
            'search' => $search
        ]);
    }
    
    /**
     * Get total student count for a grade level (for badge display)
     */
    public function getClassListStudentCount(Request $request)
    {
        $gradeLevel = $request->get('grade_level');
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        if (!$gradeLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Grade level is required'
            ]);
        }
        
        try {
            // Count all enrolled students (1st quarter paid) for the grade level
            $totalStudents = Student::where('grade_level', $gradeLevel)
                                   ->where('academic_year', $currentAcademicYear)
                                   ->where('is_active', true)
                                   ->where('enrollment_status', 'enrolled')
                                   ->count();
            
            return response()->json([
                'success' => true,
                'total_students' => $totalStudents,
                'grade_level' => $gradeLevel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error counting students: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get student details for modal display
     */
    public function getStudentDetails($studentId)
    {
        try {
            $student = Student::where('id', $studentId)
                             ->where('is_active', true)
                             ->where('enrollment_status', 'enrolled')
                             ->first();
            
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found or not enrolled'
                ]);
            }
            
            // Build complete class information
            $classInfo = $student->grade_level . ' - ' . $student->section;
            if ($student->strand) {
                $classInfo .= ' - ' . $student->strand;
                if ($student->track) {
                    $classInfo .= ' - ' . $student->track;
                }
            }
            
            return response()->json([
                'success' => true,
                'student' => array_merge($student->toArray(), [
                    'class_info' => $classInfo,
                    'strand_full_name' => $this->getStrandFullName($student->strand),
                    'track_full_name' => $this->getTrackFullName($student->track)
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading student details'
            ]);
        }
    }
    
    /**
     * Get full name for strand
     */
    private function getStrandFullName($strand)
    {
        $strandNames = [
            'STEM' => 'Science, Technology, Engineering, and Mathematics',
            'ABM' => 'Accountancy, Business, and Management',
            'HUMSS' => 'Humanities and Social Sciences',
            'TVL' => 'Technical-Vocational-Livelihood'
        ];
        return $strandNames[$strand] ?? null;
    }
    
    /**
     * Get full name for track
     */
    private function getTrackFullName($track)
    {
        $trackNames = [
            'ICT' => 'Information and Communications Technology',
            'H.E' => 'Home Economics'
        ];
        return $trackNames[$track] ?? null;
    }

    public static function getNewApplicationsCount()
    {
        $oneDayAgo = now()->subDay();
        
        return Enrollee::where('enrollment_status', 'pending')
            ->where('first_viewed_at', null)
            ->where('created_at', '>=', $oneDayAgo)
            ->count();
    }

    public function markAlertViewed(Request $request)
    {
        $alertType = $request->input('alert_type');
        
        if ($alertType === 'applications') {
            session(['applications_alert_viewed' => true]);
        }
        
        return response()->json(['success' => true]);
    }
}