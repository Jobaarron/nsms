<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\DataChangeRequest;
use App\Models\Enrollee;
use App\Models\Notice;
use App\Models\User;

class DataChangeRequestController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware is already applied in routes, but we can add additional checks here if needed
    }

    /**
     * Display the data change requests page
     */
    public function index()
    {
        // Get statistics for the page
        $stats = [
            'total_requests' => DataChangeRequest::count(),
            'pending_requests' => DataChangeRequest::where('status', 'pending')->count(),
            'approved_requests' => DataChangeRequest::where('status', 'approved')->count(),
            'rejected_requests' => DataChangeRequest::where('status', 'rejected')->count(),
        ];

        return view('registrar.data-change-requests', compact('stats'));
    }

    /**
     * Get all data change requests for registrar
     */
    public function getDataChangeRequests(Request $request): JsonResponse
    {
        try {
            Log::info('DataChangeRequestController: getDataChangeRequests called');
            Log::info('Request headers: ' . json_encode($request->headers->all()));
            Log::info('Auth guard: ' . (Auth::guard('registrar')->check() ? 'authenticated' : 'not authenticated'));
            Log::info('User: ' . (Auth::guard('registrar')->user() ? Auth::guard('registrar')->user()->email : 'none'));
            
            // Check if DataChangeRequest model exists and has data
            $totalRequests = DataChangeRequest::count();
            Log::info('Total data change requests in database: ' . $totalRequests);
            
            // If no requests exist, return empty array
            if ($totalRequests === 0) {
                return response()->json([
                    'success' => true,
                    'requests' => [],
                    'total' => 0,
                    'message' => 'No data change requests found'
                ]);
            }
            
            $query = DataChangeRequest::with(['enrollee', 'processedBy'])
                ->orderBy('created_at', 'desc');

            // Apply status filter if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Apply search filter if provided
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('enrollee', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('application_id', 'like', "%{$search}%");
                })->orWhere('field_name', 'like', "%{$search}%")
                  ->orWhere('new_value', 'like', "%{$search}%");
            }

            $requests = $query->get();
            
            Log::info('DataChangeRequestController: Found ' . $requests->count() . ' requests');

            // Transform data for frontend
            $transformedRequests = $requests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'field_name' => $request->field_name,
                    'human_field_name' => $request->human_field_name,
                    'old_value' => $request->old_value,
                    'new_value' => $request->new_value,
                    'reason' => $request->reason,
                    'status' => $request->status,
                    'admin_notes' => $request->admin_notes,
                    'created_at' => $request->created_at,
                    'processed_at' => $request->processed_at,
                    'enrollee' => [
                        'id' => $request->enrollee->id,
                        'application_id' => $request->enrollee->application_id,
                        'full_name' => trim($request->enrollee->first_name . ' ' . 
                                          ($request->enrollee->middle_name ? $request->enrollee->middle_name . ' ' : '') . 
                                          $request->enrollee->last_name . 
                                          ($request->enrollee->suffix ? ' ' . $request->enrollee->suffix : '')),
                        'email' => $request->enrollee->email,
                    ],
                    'processed_by' => $request->processedBy ? [
                        'id' => $request->processedBy->id,
                        'name' => $request->processedBy->name,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'requests' => $transformedRequests,
                'total' => $requests->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching data change requests: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data change requests: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a data change request (approve/reject)
     */
    public function processDataChangeRequest(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'action' => 'required|in:approved,rejected',
                'admin_notes' => 'nullable|string|max:1000',
            ]);

            $changeRequest = DataChangeRequest::with('enrollee')->findOrFail($id);

            if ($changeRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This request has already been processed',
                ], 400);
            }

            $action = $request->action;
            $adminNotes = $request->admin_notes;

            // Update the change request
            $changeRequest->update([
                'status' => $action,
                'admin_notes' => $adminNotes,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // If approved, apply the change to the enrollee record
            if ($action === 'approved') {
                $this->applyChangeToEnrollee($changeRequest);
            }

            // Create notice for enrollee
            $this->createChangeRequestNotice($changeRequest, $action);

            return response()->json([
                'success' => true,
                'message' => "Change request {$action} successfully",
                'request' => [
                    'id' => $changeRequest->id,
                    'status' => $changeRequest->status,
                    'processed_at' => $changeRequest->processed_at,
                    'admin_notes' => $changeRequest->admin_notes,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing data change request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process change request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply approved change to enrollee record
     */
    private function applyChangeToEnrollee(DataChangeRequest $changeRequest): void
    {
        try {
            $enrollee = $changeRequest->enrollee;
            $fieldName = $changeRequest->field_name;
            $newValue = $changeRequest->new_value;

            // Validate that the field exists in the enrollee model
            if (!in_array($fieldName, $enrollee->getFillable())) {
                Log::warning("Attempted to update non-fillable field: {$fieldName}");
                return;
            }

            // Update the enrollee record
            $enrollee->update([
                $fieldName => $newValue
            ]);

            Log::info("Applied data change for enrollee {$enrollee->id}: {$fieldName} = {$newValue}");

        } catch (\Exception $e) {
            Log::error('Error applying change to enrollee: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create notice for enrollee about change request status
     */
    private function createChangeRequestNotice(DataChangeRequest $changeRequest, string $action): void
    {
        $enrollee = $changeRequest->enrollee;
        $fieldName = $changeRequest->human_field_name;

        $titles = [
            'approved' => 'Data Change Request Approved',
            'rejected' => 'Data Change Request Rejected'
        ];

        $messages = [
            'approved' => "Your request to change '{$fieldName}' has been approved and applied to your application.",
            'rejected' => "Your request to change '{$fieldName}' has been rejected."
        ];

        $priorities = [
            'approved' => 'normal',
            'rejected' => 'high'
        ];

        $message = $messages[$action];
        if ($changeRequest->admin_notes) {
            $message .= "\n\nRegistrar Notes: " . $changeRequest->admin_notes;
        }

        Notice::create([
            'enrollee_id' => $enrollee->id,
            'title' => $titles[$action],
            'message' => $message,
            'priority' => $priorities[$action],
            'type' => 'data_change',
            'is_read' => false,
        ]);
    }
}
