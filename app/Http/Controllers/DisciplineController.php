<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Discipline;
use App\Models\User;
use App\Models\Student;
use App\Models\Violation;
use App\Models\ViolationList;
use App\Models\CaseMeeting;
use App\Models\Guidance;
use App\Models\ArchiveViolation;
use App\Models\Sanction;
use Illuminate\Support\Facades\Storage;

class DisciplineController extends Controller
{
    public function __construct()
    {
        // Role and permission management is handled by RolePermissionSeeder
        // No need to create roles/permissions here
    }

    // Show login form
    public function showLogin()
    {
        return view('discipline.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        // Check if user exists and has discipline role
        $user = User::where('email', $credentials['email'])
                   ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Check if user has appropriate role
            if ($user->isDisciplineStaff()) {
                Auth::login($user);
                $user->updateLastLogin(); // Update last login timestamp
                session()->forget('guidance_user'); // Clear guidance user session flag
                session(['discipline_user' => true]); // Mark as discipline user

                // Create discipline record if not exists
                if (!$user->discipline) {
                    Discipline::create([
                        'user_id' => $user->id,
                        'first_name' => $user->first_name ?? $user->name ?? 'Unknown',
                        'last_name' => $user->last_name ?? '',
                        'is_active' => true,
                        'specialization' => 'discipline_officer',
                    ]);
                }

                return redirect()->route('discipline.dashboard');
            } else {
                return back()->withErrors(['email' => 'You do not have permission to access this system.']);
            }
        }

        return back()->withErrors(['email' => 'Invalid credentials or account is inactive.']);
    }

    // Show dashboard
    public function dashboard()
    {
        // Check if user is authenticated and is discipline staff
        if (!Auth::check() || !session('discipline_user') || !Auth::user()->isDisciplineStaff()) {
            return redirect()->route('discipline.login')->withErrors(['error' => 'Please login to access the dashboard.']);
        }

        // Get statistics
        $totalStudents = Student::count();

        // Get violations statistics
        $violationsThisMonth = Violation::whereMonth('violation_date', now()->month)
            ->whereYear('violation_date', now()->year)
            ->count();

        $totalViolations = Violation::count();
        $pendingViolations = Violation::where('status', 'pending')->count();
        $violationsToday = Violation::whereDate('violation_date', now()->toDateString())->count();
        $majorViolations = Violation::where('severity', 'major')->count();
        $severeViolations = Violation::where('severity', 'severe')->count();

        // Get weekly violations (last 7 days)
        $weeklyViolations = Violation::with(['student', 'reportedBy'])
            ->where('violation_date', '>=', now()->subDays(7))
            ->orderBy('violation_date', 'desc')
            ->orderBy('violation_time', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'total_students' => $totalStudents,
            'violations_this_month' => $violationsThisMonth,
            'total_violations' => $totalViolations,
            'pending_violations' => $pendingViolations,
            'violations_today' => $violationsToday,
            'major_violations' => $majorViolations,
            'severe_violations' => $severeViolations,
            'weekly_violations' => $weeklyViolations->count(),
        ];

        return view('discipline.index', compact('stats', 'weeklyViolations'));
    }

    // Logout
    public function logout(Request $request)
    {
        session()->forget('discipline_user');
        session()->forget('guidance_user'); // Clear guidance user session flag on logout
        Auth::logout();
        return redirect()->route('discipline.login');
    }

    // STUDENT MANAGEMENT METHODS

    /**
     * Display students index page
     */
    public function studentsIndex()
    {
        // Check permission
        // if (!auth()->user()->can('view_students')) {
        //     abort(403, 'Unauthorized access');
        // }

        $students = Student::with('activeFaceRegistration')
            ->orderBy('last_name', 'asc')
            ->paginate(20);

        return view('discipline.student-profile', compact('students'));
    }

    /**
     * Show student profile
     */
    public function showStudent(Student $student)
    {
        // Check permission
        // if (!auth()->user()->can('view_students')) {
        //     abort(403, 'Unauthorized access');
        // }

        $student->load(['violations']);
        return response()->json($student);
    }

    /**
     * Get student info for AJAX requests
     */
    public function getStudentInfo(Student $student)
    {
        return response()->json($student);
    }

    // VIOLATIONS MANAGEMENT METHODS

    /**
     * Display violations index page
     */
    public function violationsIndex()
    {
        // Fetch all violations with related models
        $allViolations = Violation::with(['student', 'reportedBy', 'resolvedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group violations by student and title to count occurrences
        $counts = [];
        foreach ($allViolations as $violation) {
            $key = $violation->student_id . '|' . $violation->title;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        // Add effective_severity property based on count
        foreach ($allViolations as $violation) {
            $key = $violation->student_id . '|' . $violation->title;
            if (($counts[$key] ?? 0) >= 3) {
                $violation->effective_severity = 'major';
            } else {
                $violation->effective_severity = $violation->severity;
            }
        }

        // Filter to only include major effective_severity violations
        $filtered = $allViolations->filter(function ($violation) {
            return $violation->effective_severity === 'major';
        });

        // Paginate filtered results manually
        $perPage = 20;
        $page = request()->get('page', 1);
        $items = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $filtered->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->orderBy('last_name', 'asc')
            ->get();


        $stats = [
            'pending' => $filtered->where('status', 'pending')->count(),
            'investigating' => $filtered->whereIn('status', ['investigating', 'in_progress'])->count(),
            'submitted' => $filtered->where('status', 'submitted')->count(),
            'completed' => $filtered->where('status', 'completed')->count(),
            'resolved' => $filtered->where('status', 'resolved')->count(),
            'severe' => $filtered->where('effective_severity', 'severe')->count(),
        ];

        return view('discipline.violations', ['violations' => $paginated, 'students' => $students, 'stats' => $stats]);
    }

    /**
     * Store a new violation
     */
    public function storeViolation(Request $request)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to report violations.'
                ], 401);
            }
            return redirect()->route('discipline.login');
        }

        $validatedData = $request->validate([
            'student_id' => 'required|exists:students,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'severity' => 'required|in:minor,major,severe',
            'major_category' => 'nullable|string|required_if:severity,major',
            'violation_date' => 'required|date',
            'violation_time' => 'nullable',
            'status' => 'nullable|in:pending,investigating,in_progress,resolved,dismissed',
            'urgency_level' => 'nullable|in:low,medium,high,urgent',
        ]);

        // Set default urgency_level if not provided
        if (!isset($validatedData['urgency_level']) || $validatedData['urgency_level'] === null) {
            $validatedData['urgency_level'] = 'medium';
        }

        // Automatically calculate sanction based on severity and major_category
        $sanctionDetails = $this->calculateSanction($validatedData['student_id'], $validatedData['severity'], $validatedData['major_category'] ?? null);
        if ($sanctionDetails) {
            $validatedData['sanction'] = $sanctionDetails['sanction'];
        }

        // Get current user's discipline record
        $user = Auth::user();
        $disciplineRecord = $user->discipline;
        if (!$disciplineRecord || !$disciplineRecord->is_active) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to report violations.'
                ], 403);
            }
            return back()->withErrors(['error' => 'You do not have permission to report violations.']);
        }

        // Process violation time
        if (isset($validatedData['violation_time']) && $validatedData['violation_time']) {
            $time = $validatedData['violation_time'];
            if (preg_match('/^(\d{1,2}):(\d{2})$/', $time)) {
                $validatedData['violation_time'] = $time . ':00';
            } elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $time)) {
                $validatedData['violation_time'] = $time;
            }
        }

        $validatedData['reported_by'] = $disciplineRecord->id;

        try {
            \Log::info('Starting violation creation', [
                'validatedData' => $validatedData,
                'user_id' => Auth::id(),
                'discipline_record_id' => $disciplineRecord->id ?? null
            ]);

            $violation = Violation::create($validatedData);

            \Log::info('Violation created successfully', [
                'violation_id' => $violation->id,
                'student_id' => $violation->student_id
            ]);

            // Check if this makes it a major offense (3 or more same violations)
            $sameViolationCount = Violation::where('student_id', $validatedData['student_id'])
                ->where('title', $validatedData['title'])
                ->count();

            if ($sameViolationCount >= 3) {
                try {
                    // Get all same violations (including the newly created one)
                    $violationsToEscalate = Violation::where('student_id', $validatedData['student_id'])
                        ->where('title', $validatedData['title'])
                        ->get();

                    // Archive all violations except one, which will become major
                    $keepOne = true;
                    foreach ($violationsToEscalate as $violationToEscalate) {
                        if ($keepOne) {
                            // Keep this one and make it major
                            $updated = $violationToEscalate->update([
                                'severity' => 'major',
                                'major_category' => $validatedData['major_category'] ?? 1,
                                'title' => 'Escalated: ' . $violationToEscalate->title,
                                'description' => 'This violation has been escalated to major due to multiple occurrences (' . $sameViolationCount . ' total). Original description: ' . $violationToEscalate->description
                            ]);
                            if ($updated) {
                                \Log::info("Kept violation ID {$violationToEscalate->id} updated to major.");
                            } else {
                                \Log::error("Failed to update kept violation ID {$violationToEscalate->id} to major.");
                            }
                            $keepOne = false;
                        } else {
                            // Archive the others
                            ArchiveViolation::create([
                                'student_id' => $violationToEscalate->student_id,
                                'reported_by' => $violationToEscalate->reported_by,
                                'violation_type' => $violationToEscalate->violation_type,
                                'title' => $violationToEscalate->title,
                                'description' => $violationToEscalate->description,
                                'severity' => $violationToEscalate->severity,
                                'major_category' => $violationToEscalate->major_category,
                                'sanction' => $violationToEscalate->sanction,
                                'violation_date' => $violationToEscalate->violation_date,
                                'violation_time' => $violationToEscalate->violation_time,
                                'location' => $violationToEscalate->location,
                                'witnesses' => $violationToEscalate->witnesses,
                                'evidence' => $violationToEscalate->evidence,
                                'attachments' => $violationToEscalate->attachments,
                                'status' => $violationToEscalate->status,
                                'resolution' => $violationToEscalate->resolution,
                                'resolved_by' => $violationToEscalate->resolved_by,
                                'resolved_at' => $violationToEscalate->resolved_at,
                                'student_statement' => $violationToEscalate->student_statement,
                                'disciplinary_action' => $violationToEscalate->disciplinary_action,
                                'parent_notified' => $violationToEscalate->parent_notified,
                                'parent_notification_date' => $violationToEscalate->parent_notification_date,
                                'notes' => $violationToEscalate->notes,
                                'archived_at' => now(),
                                'archive_reason' => 'escalation_to_major',
                            ]);
                            // Delete the archived violation from student_violations table
                            $violationToEscalate->delete();
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to escalate and archive violations: ' . $e->getMessage(), [
                        'student_id' => $validatedData['student_id'],
                        'title' => $validatedData['title'],
                        'error' => $e->getTraceAsString()
                    ]);
                    // Continue without escalation - violation is still created
                }
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Violation reported successfully.',
                    'violation' => $violation->load(['student', 'reportedBy', 'sanctions'])
                ]);
            }

            return redirect()->route('discipline.violations.index')
                ->with('success', 'Violation reported successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error during violation creation: ' . $e->getMessage(), [
                'validatedData' => $validatedData,
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'error_code' => $e->getCode()
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Database error: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            \Log::error('Violation creation error: ' . $e->getMessage(), [
                'validatedData' => $validatedData,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create violation: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to create violation: ' . $e->getMessage()]);
        }
    }

    /**
     * Show violation details
     */
    public function showViolation(Violation $violation)
    {
        $violation->load(['student', 'reportedBy', 'resolvedBy']);
        return response()->json($violation);
    }

    /**
     * Show edit violation form
     */
    public function editViolation(Violation $violation)
    {
        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->orderBy('last_name', 'asc')
            ->get();

        return response()->json([
            'violation' => $violation->load(['student', 'reportedBy', 'resolvedBy']),
            'students' => $students
        ]);
    }

    /**
     * Update violation
     */
    public function updateViolation(Request $request, Violation $violation)
    {
        try {
            $validatedData = $request->validate([
                'student_id' => 'required|exists:students,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'severity' => 'required|in:minor,major,severe',
                'violation_date' => 'required|date',
                'violation_time' => 'nullable',
                'status' => 'required|in:pending,investigating,in_progress,resolved,dismissed',
                'resolution' => 'nullable|string',
                'student_statement' => 'nullable|string',
                'disciplinary_action' => 'nullable|string',
                'parent_notified' => 'nullable|boolean',
                'notes' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        // Process violation time
        if (isset($validatedData['violation_time']) && $validatedData['violation_time']) {
            $time = $validatedData['violation_time'];
            if (preg_match('/^(\d{1,2}):(\d{2})$/', $time)) {
                $validatedData['violation_time'] = $time . ':00';
            } elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $time)) {
                $validatedData['violation_time'] = $time;
            }
        }

        // If status is being changed to resolved, set resolved_by and resolved_at
        if ($validatedData['status'] === 'resolved' && $violation->status !== 'resolved') {
            $user = Auth::user();
            if ($user) {
                // Get discipline record
                $disciplineRecord = $user->discipline;
                if ($disciplineRecord && $disciplineRecord->is_active) {
                    $validatedData['resolved_by'] = $disciplineRecord->id;
                } else {
                    // Fallback: use user ID if no discipline record
                    $validatedData['resolved_by'] = $user->id;
                }
                $validatedData['resolved_at'] = now();
            }
        }

        $violation->update($validatedData);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Violation updated successfully.',
                'violation' => $violation->load(['student', 'reportedBy', 'resolvedBy'])
            ]);
        }

        return redirect()->route('discipline.violations.index')
            ->with('success', 'Violation updated successfully.');
    }

    /**
     * Archive violation (instead of delete)
     */
    public function destroyViolation(Request $request, Violation $violation)
    {
        try {
            $violationId = $violation->id;

            // Archive the violation before deleting
            ArchiveViolation::create([
                'student_id' => $violation->student_id,
                'reported_by' => $violation->reported_by,
                'violation_type' => $violation->violation_type,
                'title' => $violation->title,
                'description' => $violation->description,
                'severity' => $violation->severity,
                'major_category' => $violation->major_category,
                'sanction' => $violation->sanction,
                'violation_date' => $violation->violation_date,
                'violation_time' => $violation->violation_time,
                'location' => $violation->location,
                'witnesses' => $violation->witnesses,
                'evidence' => $violation->evidence,
                'attachments' => $violation->attachments,
                'status' => $violation->status,
                'resolution' => $violation->resolution,
                'resolved_by' => $violation->resolved_by,
                'resolved_at' => $violation->resolved_at,
                'student_statement' => $violation->student_statement,
                'disciplinary_action' => $violation->disciplinary_action,
                'parent_notified' => $violation->parent_notified,
                'parent_notification_date' => $violation->parent_notification_date,
                'notes' => $violation->notes,
                'archived_at' => now(),
                'archive_reason' => 'manual_deletion',
            ]);

            // Delete the original violation
            $violation->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Violation archived successfully.',
                    'violation_id' => $violationId
                ]);
            }

            return redirect()->route('discipline.violations.index')
                ->with('success', 'Violation archived successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to archive violation.'
                ], 500);
            }

            return redirect()->route('discipline.violations.index')
                ->with('error', 'Failed to archive violation.');
        }
    }

    /**
     * Forward violation to case meeting
     */
    public function forwardViolation(Request $request, Violation $violation)
    {
        // Ensure the discipline officer has a valid discipline record
        $user = Auth::user();
        $discipline = $user ? $user->discipline : null;
        if (!$discipline) {
            return response()->json([
                'success' => false,
                'message' => 'No discipline record found for this user.'
            ], 422);
        }

        // Get an active guidance counselor to assign the case meeting
        $guidanceCounselor = Guidance::active()->counselors()->first();

        // Create case meeting with violation data
        $caseMeeting = CaseMeeting::create([
            'student_id' => $violation->student_id,
            'counselor_id' => $guidanceCounselor ? $guidanceCounselor->id : null,
            'meeting_type' => 'case_meeting',
            'location' => 'Guidance Office',
            'reason' => 'Violation: ' . $violation->title . ' - ' . $violation->description,
            'notes' => 'Submitted from Discipline Office. ' .
                      'Student Involved: ' . $violation->student->first_name . ' ' . $violation->student->last_name . ' (' . $violation->student->student_id . '). ' .
                      'Date and Time: ' . $violation->violation_date . ' ' . ($violation->violation_time ?: 'N/A') . '. ' .
                      'Incident Details: ' . $violation->description . '. ' .
                      'Violation Information: ' . $violation->title . ' (Severity: ' . $violation->severity . '). ' .
                      'Status: ' . $violation->status . '. ' .
                      'Urgency Level: ' . ($violation->urgency_level ?: 'Not specified') . '. ' .
                      'Violation ID: ' . $violation->id,
            'status' => 'in_progress', // Set to in_progress when forwarded
            'sanction_recommendation' => $violation->sanction,
            'urgency_level' => $violation->urgency_level,
        ]);

        // Update violation status and reported_by to ensure valid discipline id
        $violation->update([
            'status' => 'in_progress',
            'reported_by' => $discipline->id,
        ]);

        // Create a sanction for the case meeting based on the violation
        Sanction::create([
            'case_meeting_id' => $caseMeeting->id,
            'violation_id' => $violation->id,
            'severity' => $violation->severity,
            'category' => null,
            'major_category' => $violation->major_category,
            'sanction' => $violation->sanction ?: 'Pending sanction determination',
            'deportment_grade_action' => 'No change',
            'suspension' => 'None',
            'notes' => 'Created from violation forwarding',
            'is_automatic' => true,
            'is_approved' => false, // Requires approval from guidance
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Violation submitted to case meeting successfully. Guidance will schedule the meeting.',
            'case_meeting_id' => $caseMeeting->id,
        ]);
    }

    /**
     * Get violations summary for sanction system
     */
    public function violationsSummary()
    {
        try {
            $violations = Violation::select('student_id', 'severity', 'major_category')->get();

            $violationList = Violation::with(['student', 'reportedBy', 'resolvedBy'])->get();

            $violationOptions = [
                'minor' => ViolationList::where('severity', 'minor')->pluck('title')->toArray(),
                'major' => [
                    '1' => ViolationList::where('severity', 'major')->where('category', '1')->pluck('title')->toArray(),
                    '2' => ViolationList::where('severity', 'major')->where('category', '2')->pluck('title')->toArray(),
                    '3' => ViolationList::where('severity', 'major')->where('category', '3')->pluck('title')->toArray(),
                ]
            ];

            return response()->json([
                'violations' => $violations,
                'list' => $violationList,
                'options' => $violationOptions,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching violations summary: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to fetch violations summary',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search students for AJAX requests
     */
    public function searchStudents(Request $request)
    {
        $query = $request->input('q', '');

        $students = Student::query()
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('student_id', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'student_id', 'grade_level', 'section']);

        return response()->json($students);
    }

    /**
     * Get student info for sanction overview
     */
    public function getStudent($id)
    {
        $student = Student::findOrFail($id);
        return response()->json($student);
    }

    /**
     * Calculate sanction based on student's violation history, severity, and major category
     */
    private function calculateSanction($studentId, $severity, $majorCategory = null)
    {
        // Fetch student's previous violations
        $violations = \App\Models\Violation::where('student_id', $studentId)->get();

        $minorCount = 0;
        $majorCount = 0;
        $majorByCategory = [1 => 0, 2 => 0, 3 => 0];

        foreach ($violations as $violation) {
            if ($violation->severity === 'minor') {
                $minorCount++;
            } elseif ($violation->severity === 'major') {
                $majorCount++;
                if ($violation->major_category && isset($majorByCategory[$violation->major_category])) {
                    $majorByCategory[$violation->major_category]++;
                }
            }
        }

        // Add current violation counts
        if ($severity === 'minor') {
            $minorCount++;
        } elseif ($severity === 'major') {
            $majorCount++;
            if ($majorCategory && isset($majorByCategory[$majorCategory])) {
                $majorByCategory[$majorCategory]++;
            }
        }

        // Determine sanction based on counts and policy
        if ($severity === 'minor') {
            switch ($minorCount) {
                case 1:
                    return ['sanction' => 'Verbal reprimand / warning'];
                case 2:
                    return ['sanction' => 'Written warning'];
                case 3:
                    return ['sanction' => 'One step lower in the Deportment Grade'];
                default:
                    return ['sanction' => 'One step lower in the Deportment Grade'];
            }
        } elseif ($severity === 'major') {
            switch ($majorCount) {
                case 1:
                    return ['sanction' => 'One step lower in the Deportment Grade, Community Service'];
                case 2:
                    return ['sanction' => 'Needs Improvement in Deportment, 3-5 days suspension, Community Service'];
                case 3:
                    return ['sanction' => 'Needs Improvement in Deportment, Dismissal or Expulsion'];
                default:
                    return ['sanction' => 'Needs Improvement in Deportment, Dismissal or Expulsion'];
            }
        }

        return null;
    }
}
