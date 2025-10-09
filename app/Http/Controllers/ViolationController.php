<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use App\Models\Student;
use App\Models\GuidanceDiscipline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ViolationController extends Controller
{
    /**
     * Display a listing of violations. | React Native Controller
     */
    public function index()
    {
        $violations = Violation::with(['student', 'reportedBy', 'resolvedBy'])
            ->orderBy('violation_date', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'violations' => $violations
        ]);
    }

    /**
     * Check for duplicate violation on the same day.
     */
    public function checkDuplicate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'violation_type' => 'required|string|max:50',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $date = Carbon::parse($request->date)->format('Y-m-d');
            
            $duplicate = Violation::where('student_id', $request->student_id)
                ->where('violation_type', $request->violation_type)
                ->whereDate('violation_date', $date)
                ->exists();

            return response()->json([
                'success' => true,
                'is_duplicate' => $duplicate
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check for duplicate violation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created violation.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'violation_type' => 'required|string|max:50',
            // description is now optional
            'description' => 'nullable|string',
            // Accept both 'date' and 'violation_date'
            'date' => 'nullable|date',
            'violation_date' => 'nullable|date',
            'title' => 'nullable|string|max:100',
            'time' => 'nullable|string',
            'severity' => 'nullable|in:minor,major,severe',
            'location' => 'nullable|string|max:100',
            'evidence' => 'nullable|string',
            'notes' => 'nullable|string',
            'allowDuplicate' => 'nullable|boolean',
            'image_base64' => 'nullable|string',
            'image_mime_type' => 'nullable|string',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Accept both 'date' and 'violation_date' from request
            $date = $request->date ?? $request->violation_date;
            if ($date) {
                $date = Carbon::parse($date)->format('Y-m-d');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Violation date is required.'
                ], 422);
            }

            // Duplicate check unless allowDuplicate is true
            $allowDuplicate = $request->input('allowDuplicate', false);
            if (!$allowDuplicate) {
                $duplicate = Violation::where('student_id', $request->student_id)
                    ->where('violation_type', $request->violation_type)
                    ->whereDate('violation_date', $date)
                    ->exists();
                if ($duplicate) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Duplicate violation found for this student on the same day',
                        'is_duplicate' => true
                    ], 409);
                }
            }

            // Auto-generate description if not provided
            $description = $request->description;
            if (!$description) {
                $description = 'Violation: ' . $request->violation_type . ' recorded.';
            }

            // Use title if provided, else default
            $title = $request->title ?? (ucfirst($request->violation_type) . ' Violation');

            // Find the discipline record for the current user (from disciplines table)
            $discipline = \App\Models\Discipline::where('user_id', Auth::id())->first();
            if (!$discipline) {
                return response()->json([
                    'success' => false,
                    'message' => 'Discipline record not found for this user.'
                ], 422);
            }

            $violation = Violation::create([
                'student_id' => $request->student_id,
                'reported_by' => $discipline->id,
                'violation_type' => $request->violation_type,
                'title' => $title,
                'description' => $description,
                'severity' => $request->severity ?? 'minor',
                'violation_date' => $date,
                'location' => $request->location,
                'evidence' => $request->evidence,
                'notes' => $request->notes,
                'status' => 'pending',
                // Optionally store time if you have a column for it
                // 'time' => $request->time,
            ]);

            // Optionally handle image_base64 saving here if needed

            return response()->json([
                'success' => true,
                'message' => 'Violation submitted successfully',
                'violation_id' => $violation->id,
                'violation' => $violation->load('student')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit violation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified violation.
     */
    public function show($id)
    {
        $violation = Violation::with(['student', 'reportedBy', 'resolvedBy'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'violation' => $violation
        ]);
    }

    /**
     * Update the specified violation.
     */
    public function update(Request $request, $id)
    {
        $violation = Violation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,investigating,resolved,dismissed',
            'resolution' => 'required_if:status,resolved,dismissed|string',
            'disciplinary_action' => 'nullable|string',
            'parent_notified' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updates = [
                'status' => $request->status,
                'resolution' => $request->resolution,
                'disciplinary_action' => $request->disciplinary_action,
                'notes' => $request->notes,
            ];

            if ($request->status === 'resolved' || $request->status === 'dismissed') {
                $updates['resolved_by'] = Auth::id();
                $updates['resolved_at'] = now();
            }

            if ($request->parent_notified) {
                $updates['parent_notified'] = true;
                $updates['parent_notification_date'] = now();
            }

            $violation->update($updates);

            return response()->json([
                'success' => true,
                'message' => 'Violation updated successfully',
                'violation' => $violation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update violation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get violation statistics.
     */
    public function statistics()
    {
        $stats = [
            'total' => Violation::count(),
            'pending' => Violation::where('status', 'pending')->count(),
            'resolved' => Violation::where('status', 'resolved')->count(),
            'by_type' => Violation::selectRaw('violation_type, count(*) as count')
                ->groupBy('violation_type')
                ->get(),
            'by_severity' => Violation::selectRaw('severity, count(*) as count')
                ->groupBy('severity')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }

    /**
     * Get violations for a specific student.
     */
    public function studentViolations($studentId)
    {
        $violations = Violation::where('student_id', $studentId)
            ->with(['reportedBy', 'resolvedBy'])
            ->orderBy('violation_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'violations' => $violations
        ]);
    }
}