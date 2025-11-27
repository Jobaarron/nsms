<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Student;
use App\Models\Violation;
use App\Models\ViolationList;
use App\Models\FaceRegistration;
use Illuminate\Support\Facades\Hash;

class DisciplineOfficerController extends Controller
{
    /**
     * Get dashboard statistics for discipline officer
     */
    public function dashboard(Request $request)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $totalStudents = Student::count();
        $totalViolations = Violation::count();
        $pendingViolations = Violation::where('status', 'pending')->count();
        $violationsToday = Violation::whereDate('violation_date', now()->toDateString())->count();
        $majorViolations = Violation::where('severity', 'major')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => $totalStudents,
                'total_violations' => $totalViolations,
                'pending_violations' => $pendingViolations,
                'violations_today' => $violationsToday,
                'major_violations' => $majorViolations,
            ]
        ]);
    }

    /**
     * Get list of students
     */
    public function getStudents(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $query = Student::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('lrn', 'like', "%{$search}%");
            });
        }

        $students = $query->select(
            'id',
            'first_name',
            'last_name',
            'middle_name',
            'suffix',
            'student_id',
            'grade_level',
            'strand',
            'lrn',
            'guardian_name',
            'guardian_contact'
        )->paginate(20);

        return response()->json([
            'success' => true,
            'students' => $students
        ]);
    }

    /**
     * Get student details with violations
     */
    public function getStudent(Request $request, $studentId)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $student = Student::with(['violations' => function($query) {
            $query->orderBy('violation_date', 'desc');
        }])->find($studentId);

        if (!$student) {
            return response()->json([
                'error' => true,
                'message' => 'Student not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'student' => $student
        ]);
    }

    /**
     * Submit a violation
     */
    public function submitViolation(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validatedData = $request->validate([
            'student_id' => 'required|exists:students,id',
            // Accept either violation_type or title for compatibility
            'violation_type' => 'required_without:title|string',
            'title' => 'required_without:violation_type|string',
            'description' => 'nullable|string',
            'severity' => 'required|in:minor,major,severe',
            'major_category' => 'nullable|string|required_if:severity,major',
            'violation_date' => 'required|date',
            'violation_time' => 'nullable',
            'location' => 'nullable|string',
            'evidence' => 'nullable|string',
            'notes' => 'nullable|string',
            'allowDuplicate' => 'nullable|boolean',
            'force_duplicate' => 'nullable|boolean'
        ]);

        // Use violation_type if present, otherwise use title
        $violationType = $request->input('violation_type', $request->input('title'));

        // Prevent duplicate violation for same student, title, and date unless force_duplicate or allowDuplicate is true
        $allowDuplicate = $request->input('allowDuplicate', false) || $request->input('force_duplicate', false);
        
        if (!$allowDuplicate) {
            $existing = Violation::where('student_id', $validatedData['student_id'])
                ->where('title', $violationType)
                ->whereDate('violation_date', $validatedData['violation_date'])
                ->first();
            if ($existing) {
                return response()->json([
                    'error' => true,
                    'message' => 'Duplicate violation for this student and type on this date.',
                    'is_duplicate' => true,
                    'existing_violation' => [
                        'id' => $existing->id,
                        'title' => $existing->title,
                        'violation_date' => $existing->violation_date,
                        'violation_time' => $existing->violation_time
                    ]
                ], 409);
            }
        }

        // Format date and time
        $formattedDate = date('Y-m-d', strtotime($validatedData['violation_date']));
        $violationTime = $validatedData['violation_time'] ?? $validatedData['time'] ?? null;
        $formattedTime = $violationTime ? date('H:i:s', strtotime($violationTime)) : null;


        // Get the discipline record for the current user
        $discipline = Auth::user()->discipline;
        if (!$discipline) {
            return response()->json([
                'error' => true,
                'message' => 'No discipline record found for this user.'
            ], 422);
        }

        $violationData = [
            'student_id' => $validatedData['student_id'],
            'title' => $violationType,
            'severity' => $validatedData['severity'],
            'violation_date' => $formattedDate,
            'violation_time' => $formattedTime,
            'reported_by' => $discipline->id,
        ];

        $violation = Violation::create($violationData);

        return response()->json([
            'success' => true,
            'message' => 'Violation submitted successfully',
            'violation' => $violation->load('student')
        ], 201);
    }

    /**
     * Get violations list
     */
    public function getViolations(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $query = Violation::with(['student', 'reportedBy', 'resolvedBy']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by severity
        if ($request->has('severity') && $request->severity) {
            $query->where('severity', $request->severity);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $violations = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'violations' => $violations
        ]);
    }

    /**
     * Get violation details
     */
    public function getViolation(Request $request, $violationId)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $violation = Violation::with(['student', 'reportedBy', 'resolvedBy'])->find($violationId);

        if (!$violation) {
            return response()->json([
                'error' => true,
                'message' => 'Violation not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'violation' => $violation
        ]);
    }

    /**
     * Update violation
     */
    public function updateViolation(Request $request, $violationId)
    {
        // ...existing code...

        $violation = Violation::find($violationId);

        if (!$violation) {
            return response()->json([
                'error' => true,
                'message' => 'Violation not found'
            ], 404);
        }

        $validatedData = $request->validate([
            'status' => 'nullable|in:pending,investigating,in_progress,resolved,dismissed',
            'resolution' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $violation->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Violation updated successfully',
            'violation' => $violation->load(['student', 'reportedBy', 'resolvedBy'])
        ]);
    }

    /**
     * Get violation types/options - Formatted for JavaScript with caching
     */
    public function getViolationTypes(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            $formattedViolations = Cache::remember('violation_types_formatted', 3600, function () {
                $violations = ViolationList::all();

                $formattedViolations = [
                    'minor' => [],
                    'major' => [
                        '1' => [],
                        '2' => [],
                        '3' => []
                    ]
                ];

                foreach ($violations as $violation) {
                    if ($violation->severity === 'minor') {
                        $formattedViolations['minor'][] = $violation->title;
                    } elseif ($violation->severity === 'major') {
                        $category = $violation->category ?? '1';
                        if (!isset($formattedViolations['major'][$category])) {
                            $formattedViolations['major'][$category] = [];
                        }
                        $formattedViolations['major'][$category][] = $violation->title;
                    }
                }

                return $formattedViolations;
            });

            return response()->json([
                'success' => true,
                'violation_types' => $formattedViolations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for duplicate violation
     */
    public function checkDuplicateViolation(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'violation_title' => 'required|string',
            'date' => 'required|date',
            'time' => 'nullable'
        ]);

        $query = Violation::where('student_id', $request->student_id)
            ->where('title', $request->violation_title)
            ->whereDate('violation_date', $request->date);

        if ($request->time) {
            $formattedTime = date('H:i:s', strtotime($request->time));
            $query->whereTime('violation_time', $formattedTime);
        }

        $isDuplicate = $query->exists();

        return response()->json([
            'success' => true,
            'is_duplicate' => $isDuplicate
        ]);
    }
}
