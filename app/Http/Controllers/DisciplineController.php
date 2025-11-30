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
use App\Models\Notice;
use App\Models\Guidance;
use App\Models\ArchiveViolation;
use App\Models\Sanction;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DisciplineController extends Controller
{
    /**
     * Get school time (Philippine Time)
     */
    private function schoolNow()
    {
        return Carbon::now('Asia/Manila');
    }

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
        $now = $this->schoolNow();
        $violationsThisMonth = Violation::whereMonth('violation_date', $now->month)
            ->whereYear('violation_date', $now->year)
            ->count();

        $totalViolations = Violation::count();
        $pendingViolations = Violation::where('status', 'pending')->count();
        $violationsToday = Violation::whereDate('violation_date', $now->toDateString())->count();

    $majorViolations = Violation::where('severity', 'major')->count();
    $minorViolations = Violation::where('severity', 'minor')->count();
    $severeViolations = Violation::where('severity', 'severe')->count();

        // Get weekly violations (last 7 days)
        $weeklyViolations = Violation::with(['student', 'reportedBy'])
            ->where('violation_date', '>=', $now->copy()->subDays(7))
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
            'minor_violations' => $minorViolations,
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
            ->where('enrollment_status', 'enrolled') // Only show enrolled students
            ->orderBy('last_name', 'asc')
            ->paginate(20);

        // Define all possible grade levels (matching the enrollment system)
        $gradeLevels = [
            'Nursery',
            'Junior Casa',
            'Senior Casa',
            'Grade 1',
            'Grade 2',
            'Grade 3',
            'Grade 4',
            'Grade 5',
            'Grade 6',
            'Grade 7',
            'Grade 8',
            'Grade 9',
            'Grade 10',
            'Grade 11',
            'Grade 12'
        ];

        return view('discipline.student-profile', compact('students', 'gradeLevels'));
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
     * Get notification count (case_closed violations)
     */
    public function getNotificationCount()
    {
        try {
            $userId = auth()->id();
            
            // Check if the new columns exist, if not fall back to session-based approach
            if (!$this->disciplineNotificationColumnsExist()) {
                return $this->getFallbackNotificationCount();
            }
            
            // Create notifications for any case_closed violations that don't have notifications yet
            $this->createMissingDisciplineNotifications($userId);
            
            // Get count of unread discipline notifications for this user
            $unreadCount = Notice::getUnreadDisciplineNotificationCount($userId);
            
            return response()->json([
                'count' => $unreadCount,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            // Log error and return fallback
            \Log::error('Error in getNotificationCount: ' . $e->getMessage());
            return $this->getFallbackNotificationCount();
        }
    }
    
    /**
     * Mark all notifications as read for current user
     */
    public function markNotificationsAsRead()
    {
        try {
            $userId = auth()->id();
            
            // Check if the new columns exist, if not fall back to session-based approach
            if (!$this->disciplineNotificationColumnsExist()) {
                return $this->markNotificationsAsReadFallback();
            }
            
            // Mark all discipline notifications as read for this user
            Notice::markAllDisciplineNotificationsAsRead($userId);
            
            return response()->json([
                'status' => 'success',
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in markNotificationsAsRead: ' . $e->getMessage());
            return $this->markNotificationsAsReadFallback();
        }
    }

    /**
     * Fallback method to mark notifications as read using session
     */
    private function markNotificationsAsReadFallback()
    {
        // Get all case_closed violation IDs
        $violationIds = Violation::where('status', 'case_closed')
            ->pluck('id')
            ->toArray();
        
        // Store in session as viewed
        session(['viewed_case_closed_notifications' => $violationIds]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Check if the discipline notification columns exist in notices table
     */
    private function disciplineNotificationColumnsExist()
    {
        try {
            return \Schema::hasColumns('notices', ['notification_type', 'violation_id', 'user_id']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Fallback notification count using session-based approach
     */
    private function getFallbackNotificationCount()
    {
        // Get case_closed violations
        $violations = Violation::where('status', 'case_closed')->get();
        
        // Get viewed notification IDs from session
        $viewedIds = session('viewed_case_closed_notifications', []);
        
        // Count unviewed violations
        $unviewedCount = $violations->filter(function($violation) use ($viewedIds) {
            return !in_array($violation->id, $viewedIds);
        })->count();
        
        return response()->json([
            'count' => $unviewedCount,
            'status' => 'success'
        ]);
    }

    /**
     * Create discipline notifications for case_closed violations that don't have notifications yet
     */
    private function createMissingDisciplineNotifications($userId)
    {
        // Get case_closed violations that don't have discipline notifications for this user
        $violationsWithoutNotifications = Violation::where('status', 'case_closed')
            ->whereNotExists(function ($query) use ($userId) {
                $query->select('id')
                    ->from('notices')
                    ->where('notification_type', 'discipline')
                    ->where('user_id', $userId)
                    ->whereColumn('violation_id', 'student_violations.id');
            })
            ->get();

        // Create notifications for violations that don't have them
        foreach ($violationsWithoutNotifications as $violation) {
            Notice::createDisciplineNotification(
                $violation->id,
                $userId,
                'Case Closed - ' . $violation->title,
                "Violation case for {$violation->student->full_name} has been closed and requires your attention."
            );
        }
    }

    /**
     * Get violations data for notifications
     */
    public function getViolationsData(Request $request)
    {
        $status = $request->query('status', 'case_closed');
        
        $violations = Violation::with(['student', 'caseMeeting', 'sanctions'])
            ->where('status', $status)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($violation) {
                // Get disciplinary action - check multiple sources in order of priority
                $disciplinaryAction = 'N/A';
                
                // 1. First check the violation's disciplinary_action field directly (used in view modal)
                if (!empty($violation->disciplinary_action)) {
                    $disciplinaryAction = $violation->disciplinary_action;
                }
                // 2. Try to get it from the direct sanctions relationship (hasMany)
                elseif ($violation->sanctions && $violation->sanctions->isNotEmpty()) {
                    // Get the most recent sanction
                    $latestSanction = $violation->sanctions->sortByDesc('created_at')->first();
                    $disciplinaryAction = $latestSanction->sanction ?? 'N/A';
                }
                // 3. If not found, try to get it from case meeting sanctions
                elseif ($violation->caseMeeting) {
                    $caseMeeting = $violation->caseMeeting->load('sanctions');
                    if ($caseMeeting->sanctions && $caseMeeting->sanctions->isNotEmpty()) {
                        // Get the sanction for this specific violation
                        $violationSanction = $caseMeeting->sanctions
                            ->where('violation_id', $violation->id)
                            ->first();
                        
                        if ($violationSanction) {
                            $disciplinaryAction = $violationSanction->sanction ?? 'N/A';
                        }
                    }
                }
                
                return [
                    'id' => $violation->id,
                    'title' => $violation->title,
                    'description' => $violation->description,
                    'student' => $violation->student ? [
                        'full_name' => $violation->student->first_name . ' ' . $violation->student->last_name,
                        'first_name' => $violation->student->first_name,
                        'last_name' => $violation->student->last_name,
                        'student_id' => $violation->student->student_id
                    ] : null,
                    'student_name' => $violation->student 
                        ? $violation->student->first_name . ' ' . $violation->student->last_name 
                        : 'N/A',
                    'student_id' => $violation->student ? $violation->student->student_id : 'N/A',
                    'violation_date' => $violation->violation_date 
                        ? $violation->violation_date->format('Y-m-d') 
                        : 'N/A',
                    'violation_time' => $violation->violation_time 
                        ? date('H:i:s', strtotime($violation->violation_time)) 
                        : null,
                    'status' => $violation->status,
                    'severity' => $violation->severity,
                    'disciplinary_action' => $disciplinaryAction,
                    'case_meeting' => $violation->caseMeeting ? [
                        'id' => $violation->caseMeeting->id,
                        'status' => $violation->caseMeeting->status,
                        'summary' => $violation->caseMeeting->summary,
                        'recommendations' => $violation->caseMeeting->recommendations,
                        'president_notes' => $violation->caseMeeting->president_notes,
                        'written_reflection' => $violation->caseMeeting->written_reflection,
                        'written_reflection_due' => $violation->caseMeeting->written_reflection_due,
                        'follow_up_meeting' => $violation->caseMeeting->follow_up_meeting,
                        'follow_up_meeting_date' => $violation->caseMeeting->follow_up_meeting_date,
                        'mentorship_counseling' => $violation->caseMeeting->mentorship_counseling,
                        'mentor_name' => $violation->caseMeeting->mentor_name,
                        'parent_teacher_communication' => $violation->caseMeeting->parent_teacher_communication,
                        'parent_teacher_date' => $violation->caseMeeting->parent_teacher_date,
                        'restorative_justice_activity' => $violation->caseMeeting->restorative_justice_activity,
                        'restorative_justice_date' => $violation->caseMeeting->restorative_justice_date,
                        'community_service' => $violation->caseMeeting->community_service,
                        'community_service_date' => $violation->caseMeeting->community_service_date,
                        'community_service_area' => $violation->caseMeeting->community_service_area,
                        'suspension' => $violation->caseMeeting->suspension,
                        'suspension_3days' => $violation->caseMeeting->suspension_3days,
                        'suspension_5days' => $violation->caseMeeting->suspension_5days,
                        'suspension_other_days' => $violation->caseMeeting->suspension_other_days,
                        'suspension_start' => $violation->caseMeeting->suspension_start,
                        'suspension_end' => $violation->caseMeeting->suspension_end,
                        'suspension_return' => $violation->caseMeeting->suspension_return,
                        'expulsion' => $violation->caseMeeting->expulsion,
                        'expulsion_date' => $violation->caseMeeting->expulsion_date,
                    ] : null,
                ];
            });
        
        return response()->json([
            'violations' => $violations,
            'status' => 'success'
        ]);
    }

    /**
     * Display violations index page
     */
    public function violationsIndex()
    {
        // Fetch all violations with related models including case meetings for detailed interventions
        $allViolations = Violation::with(['student', 'reportedBy', 'resolvedBy', 'caseMeeting'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group violations by student and title to count occurrences
        $countsByTitle = [];
        $countsByStudent = [];
        foreach ($allViolations as $violation) {
            $key = $violation->student_id . '|' . $violation->title;
            $countsByTitle[$key] = ($countsByTitle[$key] ?? 0) + 1;
            
            // Count minor violations per student
            if ($violation->severity === 'minor') {
                $countsByStudent[$violation->student_id] = ($countsByStudent[$violation->student_id] ?? 0) + 1;
            }
        }

        // Process violations and handle escalated cases
        $processed = collect();
        $escalatedStudents = []; // Track which students have escalated violations
        
        // First pass: identify students with 3+ minor violations
        foreach ($allViolations as $violation) {
            if ($violation->severity === 'minor') {
                $totalMinorForStudent = $countsByStudent[$violation->student_id] ?? 0;
                if ($totalMinorForStudent >= 3) {
                    $escalatedStudents[$violation->student_id] = true;
                }
            }
        }
        
        // Get students who have grouped violations to avoid showing individual violations
        $studentsWithGroupedViolations = $allViolations->where('title', 'Multiple Minor Violations - Escalated to Major')
                                                       ->pluck('student_id')
                                                       ->unique()
                                                       ->toArray();

        // Process all violations
        foreach ($allViolations as $violation) {
            // Skip individual minor violations completely
            if ($violation->severity === 'minor') {
                continue;
            }
            
            // For major violations, skip individual escalated ones if there's a grouped violation for this student
            if ($violation->severity === 'major' && $violation->is_escalated && 
                $violation->title !== 'Multiple Minor Violations - Escalated to Major' &&
                in_array($violation->student_id, $studentsWithGroupedViolations)) {
                continue;
            }
                
            // Show this violation
            $key = $violation->student_id . '|' . $violation->title;
            $countForTitle = $countsByTitle[$key] ?? 0;
            $violation->effective_severity = $violation->severity;
            
            // Check if this is an escalated group violation
            if ($violation->title === 'Multiple Minor Violations - Escalated to Major') {
                $violation->is_escalated = true;
                $violation->escalation_reason = $violation->escalation_reason ?? '3+ minor violations - escalated to major';
            }
            
            $violation->occurrence_count = $countForTitle;
            $processed->push($violation);
        }


        // Filter to only include major effective_severity violations
        $filtered = $processed;

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
            'case_closed' => $filtered->where('status', 'case_closed')->count(),
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

        try {
            $validatedData = $request->validate([
                'student_id' => 'required|exists:students,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'severity' => 'required|in:minor,major,severe',
                'major_category' => 'nullable|string',
                'violation_date' => 'required|date',
                'violation_time' => 'nullable|string',
                'status' => 'nullable|in:pending,investigating,in_progress,resolved,dismissed',
                'urgency_level' => 'nullable|in:low,medium,high,urgent',
                'force_duplicate' => 'nullable',
                'location' => 'nullable|string',
                'witnesses' => 'nullable|string',
                'evidence' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', array_flatten($e->errors())),
                    'errors' => $e->errors()
                ], 422); // Use 422 for validation errors, not 409
            }
            throw $e;
        }

        // Log request data for debugging
        \Log::info('Violation submission request data:', [
            'force_duplicate' => $request->input('force_duplicate'),
            'force_duplicate_type' => gettype($request->input('force_duplicate')),
            'all_request_data' => $request->all()
        ]);

        // Check if student is fully enrolled
        $student = Student::find($validatedData['student_id']);
        if (!$student || $student->enrollment_status !== 'enrolled') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Violations can only be recorded for fully enrolled students.'
                ], 422);
            }
            return back()->withErrors(['student_id' => 'Violations can only be recorded for fully enrolled students.']);
        }

        // Check for duplicate violation unless force_duplicate is true
        $forceDuplicate = $request->input('force_duplicate', false);
        
        // Handle string 'true' as boolean
        if ($forceDuplicate === 'true') {
            $forceDuplicate = true;
        }
        
        \Log::info('Force duplicate check:', [
            'force_duplicate_raw' => $request->input('force_duplicate'),
            'force_duplicate_processed' => $forceDuplicate,
            'will_check_duplicates' => !$forceDuplicate
        ]);
        
        if (!$forceDuplicate) {
            $exists = Violation::where('student_id', $validatedData['student_id'])
                ->where('title', $validatedData['title'])
                ->whereDate('violation_date', $validatedData['violation_date'])
                ->exists();
                
            \Log::info('Duplicate check result:', [
                'exists' => $exists,
                'student_id' => $validatedData['student_id'],
                'title' => $validatedData['title'],
                'violation_date' => $validatedData['violation_date']
            ]);
                
            if ($exists) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A violation with the same title already exists for this student on this date.',
                        'is_duplicate' => true
                    ], 409);
                }
                return back()->withErrors(['error' => 'A violation with the same title already exists for this student on this date.']);
            }
        }

        // Map major_category numeric values to ENUM strings and handle 'null' string
        if (isset($validatedData['major_category'])) {
            if ($validatedData['major_category'] == '3' || $validatedData['major_category'] === 3) {
                $validatedData['major_category'] = 'major';
            } elseif ($validatedData['major_category'] == '2' || $validatedData['major_category'] === 2) {
                $validatedData['major_category'] = 'minor';
            } elseif ($validatedData['major_category'] === 'null' || $validatedData['major_category'] === null) {
                $validatedData['major_category'] = null;
            }
        }

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
            
            // Try to convert from 12-hour format (h:i A) to 24-hour format (H:i:s)
            if (preg_match('/(AM|PM|am|pm)/', $time)) {
                try {
                    $validatedData['violation_time'] = \Carbon\Carbon::createFromFormat('h:i A', $time)->format('H:i:s');
                } catch (\Exception $e) {
                    // Fallback for different 12-hour format variations
                    $validatedData['violation_time'] = date('H:i:s', strtotime($time));
                }
            } elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $time)) {
                $validatedData['violation_time'] = $time . ':00';
            } elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $time)) {
                $validatedData['violation_time'] = $time;
            }
            
            // Validate school hours (7:00 AM to 4:00 PM)
            $timeForValidation = preg_replace('/:\d{2}$/', '', $validatedData['violation_time']); // Remove seconds for validation
            if ($timeForValidation) {
                list($hour, $minute) = explode(':', $timeForValidation);
                $timeInMinutes = ($hour * 60) + $minute;
                $schoolStart = 7 * 60; // 7:00 AM
                $schoolEnd = 16 * 60;  // 4:00 PM
                
                if ($timeInMinutes < $schoolStart || $timeInMinutes > $schoolEnd) {
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Violation time must be within school hours (7:00 AM - 4:00 PM).'
                        ], 422);
                    }
                    return back()->withErrors(['violation_time' => 'Violation time must be within school hours (7:00 AM - 4:00 PM).']);
                }
            }
        }

        $validatedData['reported_by'] = $disciplineRecord->id;

        // Remove force_duplicate from data before creating violation
        unset($validatedData['force_duplicate']);

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

        // Check for escalation: 3+ minor violations within the same week
        $violationDate = \Carbon\Carbon::parse($validatedData['violation_date']);
        $weekStart = $violationDate->copy()->startOfWeek();
        $weekEnd = $violationDate->copy()->endOfWeek();
        
        $weeklyMinorViolations = Violation::where('student_id', $validatedData['student_id'])
            ->where('severity', 'minor')
            ->whereBetween('violation_date', [$weekStart, $weekEnd])
            ->count();

        if ($validatedData['severity'] === 'minor' && $weeklyMinorViolations >= 3) {
            try {
                // Create/update a grouped escalated violation ONLY for this week
                $weekLabel = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y');
                $escalationReason = "3+ minor violations in week of {$weekLabel} - escalated to major";
                
                // Check if there's already an escalated group violation for this student for this week
                $existingEscalated = Violation::where('student_id', $validatedData['student_id'])
                    ->where('title', 'Multiple Minor Violations - Escalated to Major')
                    ->whereBetween('violation_date', [$weekStart, $weekEnd])
                    ->first();
                
                if ($existingEscalated) {
                    // Update the existing escalated violation count
                    $existingEscalated->update([
                        'occurrence_count' => $weeklyMinorViolations,
                        'description' => "Escalated violation group containing {$weeklyMinorViolations} minor violations for week of {$weekLabel}"
                    ]);
                } else {
                    // Create a new grouped escalated violation for this week
                    Violation::create([
                        'student_id' => $validatedData['student_id'],
                        'title' => 'Multiple Minor Violations - Escalated to Major',
                        'description' => "Escalated violation group containing {$weeklyMinorViolations} minor violations for week of {$weekLabel}",
                        'severity' => 'major',
                        'violation_date' => $validatedData['violation_date'],
                        'violation_time' => $validatedData['violation_time'],
                        'reported_by' => $validatedData['reported_by'] ?? auth()->user()->discipline->id,
                        'status' => 'pending',
                        'sanction' => 'One step lower in the Deportment Grade, Community Service',
                        'escalation_reason' => $escalationReason,
                        'is_escalated' => true,
                        'occurrence_count' => $weeklyMinorViolations
                    ]);
                }
                
                // DO NOT MODIFY the individual minor violation - keep it as minor
                
                \Log::info("Grouped escalated violation created/updated for student {$validatedData['student_id']} with {$weeklyMinorViolations} minor violations for week {$weekLabel}");
            } catch (\Exception $e) {
                \Log::error('Failed to create grouped escalated violation: ' . $e->getMessage(), [
                    'student_id' => $validatedData['student_id'],
                    'weekly_minor_violations' => $weeklyMinorViolations,
                    'week_period' => $weekLabel ?? 'unknown',
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
     * Show violation details for AJAX
     */
    public function showViolation(Violation $violation)
    {
        $violation->load(['student', 'reportedBy', 'resolvedBy', 'caseMeeting', 'sanctions']);
        
        // Convert to array and add case meeting details if exists
        $violationData = $violation->toArray();
        
        // If this is a grouped escalated violation, get related minor violations from the same week
        if ($violation->title === 'Multiple Minor Violations - Escalated to Major') {
            $violationDate = \Carbon\Carbon::parse($violation->violation_date);
            $weekStart = $violationDate->copy()->startOfWeek();
            $weekEnd = $violationDate->copy()->endOfWeek();
            
            $relatedViolations = Violation::where('student_id', $violation->student_id)
                ->where('severity', 'minor')
                ->whereBetween('violation_date', [$weekStart, $weekEnd])
                ->with(['reportedBy'])
                ->orderBy('violation_date', 'desc')
                ->get();
            

            
            $violationData['related_violations'] = $relatedViolations->toArray();
        }
        
        if ($violation->caseMeeting) {
            $violationData['case_meeting'] = array_merge($violation->caseMeeting->toArray(), [
                'teacher_statement' => $violation->caseMeeting->teacher_statement,
                'action_plan' => $violation->caseMeeting->action_plan,
                'summary' => $violation->caseMeeting->summary,
            ]);
        }
        
        return response()->json($violationData);
    }

    /**
     * Show edit violation form
     */
    public function editViolation(Violation $violation)
    {
        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->where('enrollment_status', 'enrolled')
            ->orderBy('last_name', 'asc')
            ->orderBy('first_name', 'asc')
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
        // Check if student has replied
        $hasStudentReply = !empty($violation->student_statement) || 
                          !empty($violation->incident_feelings) || 
                          !empty($violation->action_plan);
        
        if ($hasStudentReply && $violation->status === 'pending') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot edit violation: Student has already replied.'
                ], 403);
            }
            return back()->withErrors(['error' => 'Cannot edit violation: Student has already replied.']);
        }
        
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
            
            // Try to convert from 12-hour format (h:i A) to 24-hour format (H:i:s)
            if (preg_match('/(AM|PM|am|pm)/', $time)) {
                try {
                    $validatedData['violation_time'] = \Carbon\Carbon::createFromFormat('h:i A', $time)->format('H:i:s');
                } catch (\Exception $e) {
                    // Fallback for different 12-hour format variations
                    $validatedData['violation_time'] = date('H:i:s', strtotime($time));
                }
            } elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $time)) {
                $validatedData['violation_time'] = $time . ':00';
            } elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $time)) {
                $validatedData['violation_time'] = $time;
            }
            
            // Validate school hours (7:00 AM to 4:00 PM)
            $timeForValidation = preg_replace('/:\d{2}$/', '', $validatedData['violation_time']); // Remove seconds for validation
            if ($timeForValidation) {
                list($hour, $minute) = explode(':', $timeForValidation);
                $timeInMinutes = ($hour * 60) + $minute;
                $schoolStart = 7 * 60; // 7:00 AM
                $schoolEnd = 16 * 60;  // 4:00 PM
                
                if ($timeInMinutes < $schoolStart || $timeInMinutes > $schoolEnd) {
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Violation time must be within school hours (7:00 AM - 4:00 PM).'
                        ], 422);
                    }
                    return back()->withErrors(['violation_time' => 'Violation time must be within school hours (7:00 AM - 4:00 PM).']);
                }
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
                $validatedData['resolved_at'] = $this->schoolNow();
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
        // Check if student has replied
        $hasStudentReply = !empty($violation->student_statement) || 
                          !empty($violation->incident_feelings) || 
                          !empty($violation->action_plan);
        
        if ($hasStudentReply) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete violation: Student has already replied.'
                ], 403);
            }
            return back()->withErrors(['error' => 'Cannot delete violation: Student has already replied.']);
        }
        
        // Check if violation status allows deletion
        if ($violation->status !== 'pending') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending violations can be deleted. Current status: ' . $violation->status
                ], 400);
            }
            
            return redirect()->route('discipline.violations.index')
                ->with('error', 'Only pending violations can be deleted.');
        }
        
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

        // Check if student has replied for major violations
        if ($violation->severity === 'major') {
            $hasStudentReply = !empty($violation->student_statement) || 
                              !empty($violation->incident_feelings) || 
                              !empty($violation->action_plan);
            
            if (!$hasStudentReply) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot forward major violation to case meeting. Student must reply to the narrative report first.'
                ], 400);
            }
        }

        // Get an active guidance counselor to assign the case meeting
        $guidanceCounselor = Guidance::active()->counselors()->first();

        // Create case meeting with violation data
        $caseMeeting = CaseMeeting::create([
            'student_id' => $violation->student_id,
            'violation_id' => $violation->id, // <-- link the violation
            'counselor_id' => $guidanceCounselor ? $guidanceCounselor->id : null,
            'meeting_type' => 'case_meeting',
            'location' => $violation->location ?: 'Guidance Office', // Use violation location or default to Guidance Office
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

        // Update violation status, reported_by, and link to case meeting
        $violation->update([
            'status' => 'in_progress',
            'reported_by' => $discipline->id,
            'case_meeting_id' => $caseMeeting->id, // Link violation to case meeting
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

        // Create notification for guidance about forwarded case
        try {
            $disciplineOfficerName = Auth::user()->name ?? 'Discipline Officer';
            $studentName = $violation->student ? $violation->student->full_name : 'Student';
            $violationTitle = $violation->title ?? 'Violation';
            $severityBadge = ucfirst($violation->severity ?? 'Unknown');
            
            \App\Models\Notice::createGlobal(
                "Case Forwarded from Discipline Office",
                "Discipline Officer {$disciplineOfficerName} has forwarded a {$severityBadge} violation case for {$studentName} to the Guidance Office. Violation: {$violationTitle}. A case meeting has been automatically created and requires your attention.",
                null, // created_by will be null for system-generated notices
                null, // target_status
                null  // target_grade_level
            );
            
            \Log::info('Notification created for forwarded discipline case', [
                'case_meeting_id' => $caseMeeting->id,
                'violation_id' => $violation->id,
                'discipline_officer' => $disciplineOfficerName,
                'student_name' => $studentName
            ]);
        } catch (\Exception $notificationError) {
            // Log notification error but don't fail the main operation
            \Log::error('Failed to create notification for forwarded discipline case', [
                'case_meeting_id' => $caseMeeting->id,
                'violation_id' => $violation->id,
                'error' => $notificationError->getMessage()
            ]);
        }

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
            ->where('enrollment_status', 'enrolled') // Only show enrolled students
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('student_id', 'like', "%{$query}%");
            })
            ->orderBy('last_name', 'asc')
            ->orderBy('first_name', 'asc')
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'student_id', 'grade_level', 'section', 'enrollment_status']);

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

        /**
     * Return minor and major violation counts as JSON for dashboard pie chart
     */
    public function getMinorMajorViolationStats(Request $request)
    {
        $period = $request->get('period', 'month');
        
        $query = \App\Models\Violation::query();
        $now = $this->schoolNow();
        
        switch ($period) {
            case 'quarter':
                $query->where('violation_date', '>=', $now->copy()->subMonths(3));
                break;
            case 'year':
                $query->where('violation_date', '>=', $now->copy()->subYear());
                break;
            case 'month':
                $query->whereMonth('violation_date', $now->month)
                      ->whereYear('violation_date', $now->year);
                break;
            case 'all':
            default:
                // No date filter for 'all'
                break;
        }
        
        $minor = $query->clone()->where('severity', 'minor')->count();
        $major = $query->clone()->where('severity', 'major')->count();
        
        return response()->json([
            'minor' => $minor,
            'major' => $major,
        ]);
    }



        /**
     * Return monthly minor and major violation counts for bar chart
     */
    public function getViolationBarStats(Request $request)
    {
        $period = $request->get('period', '12months');
        
        $months = [];
        $minorCounts = [];
        $majorCounts = [];
        
        switch ($period) {
            case '6months':
                $monthsCount = 6;
                break;
            case '24months':
                $monthsCount = 24;
                break;
            case '12months':
            default:
                $monthsCount = 12;
                break;
        }
        
        // Get the specified number of months
        for ($i = $monthsCount - 1; $i >= 0; $i--) {
            $now = $this->schoolNow();
            $date = $now->copy()->subMonths($i);
            $date = $now->copy()->subMonths($i);
            $label = $date->format('M Y');
            $months[] = $label;
            $minorCounts[] = \App\Models\Violation::where('severity', 'minor')
                ->whereYear('violation_date', $date->year)
                ->whereMonth('violation_date', $date->month)
                ->count();
            $majorCounts[] = \App\Models\Violation::where('severity', 'major')
                ->whereYear('violation_date', $date->year)
                ->whereMonth('violation_date', $date->month)
                ->count();
        }
        return response()->json([
            'labels' => $months,
            'minor' => $minorCounts,
            'major' => $majorCounts,
        ]);
    }
            /**
         * Return pending, ongoing, and completed case counts for dashboard pie chart
         */
        public function getCaseStatusStats(Request $request)
        {
            $period = $request->get('period', 'month');
            
            // Get violation-based case status with period filtering
            $violationQuery = Violation::query();
            $caseMeetingQuery = CaseMeeting::query();
            
            switch ($period) {
                case 'quarter':
                    $now = $this->schoolNow();
                    $violationQuery->where('violation_date', '>=', $now->copy()->subMonths(3));
                    $caseMeetingQuery->where('created_at', '>=', $now->copy()->subMonths(3));
                    break;
                case 'year':
                    $now = $this->schoolNow();
                    $violationQuery->where('violation_date', '>=', $now->copy()->subYear());
                    $caseMeetingQuery->where('created_at', '>=', $now->copy()->subYear());
                    break;
                case 'month':
                    $now = $this->schoolNow();
                    $violationQuery->whereMonth('violation_date', $now->month)
                                  ->whereYear('violation_date', $now->year);
                    $caseMeetingQuery->whereMonth('created_at', $now->month)
                                    ->whereYear('created_at', $now->year);
                    break;
                case 'all':
                default:
                    // No date filter for 'all'
                    break;
            }
            
            $pending = $violationQuery->clone()->where('status', 'pending')->count();
            $ongoing = $violationQuery->clone()->whereIn('status', ['investigating', 'in_progress'])->count();
            $completed = $violationQuery->clone()->where('status', 'resolved')->count();
            
            // Also include case meeting data
            $caseMeetingPending = $caseMeetingQuery->clone()->where('status', 'scheduled')->count();
            $caseMeetingOngoing = $caseMeetingQuery->clone()->where('status', 'in_progress')->count();
            $caseMeetingCompleted = $caseMeetingQuery->clone()->whereIn('status', ['case_closed', 'pre_completed'])->count();
            
            return response()->json([
                'pending' => $pending + $caseMeetingPending,
                'ongoing' => $ongoing + $caseMeetingOngoing,
                'completed' => $completed + $caseMeetingCompleted,
            ]);
        }
        
        /**
         * Download student attachment for violations (discipline access)
         */
        public function downloadStudentAttachment(Violation $violation)
        {
            // Check if violation has student attachment
            if (!$violation->student_attachment_path || !Storage::disk('public')->exists($violation->student_attachment_path)) {
                abort(404, 'Student attachment not found.');
            }

            return Storage::disk('public')->download($violation->student_attachment_path);
        }

        /**
         * Get recent violations for dashboard
         */
        public function getRecentViolations(Request $request)
        {
            $filter = $request->get('filter', 'week');
            
            $query = Violation::with(['student', 'reportedBy']);
            
            switch ($filter) {
                case 'today':
                    $query->whereDate('violation_date', now()->toDateString());
                    break;
                case 'month':
                    $query->whereMonth('violation_date', now()->month)
                          ->whereYear('violation_date', now()->year);
                    break;
                case 'week':
                default:
                    $query->where('violation_date', '>=', now()->subDays(7));
                    break;
            }
            
            $violations = $query->orderBy('violation_date', 'desc')
                               ->orderBy('violation_time', 'desc')
                               ->limit(10)
                               ->get()
                               ->map(function ($violation) {
                                   return [
                                       'id' => $violation->id,
                                       'student_name' => $violation->student->first_name . ' ' . $violation->student->last_name,
                                       'title' => $violation->title,
                                       'severity' => $violation->severity,
                                       'status' => $violation->status,
                                       'date' => $violation->violation_date->format('M d'),
                                       'time' => $violation->violation_time ? date('h:i A', strtotime($violation->violation_time)) : null,
                                   ];
                               });
            
            return response()->json(['violations' => $violations]);
        }

        /**
         * Get pending actions for dashboard
         */
        public function getPendingActions(Request $request)
        {
            $filter = $request->get('filter', 'all');
            
            $pendingViolations = Violation::with(['student'])
                ->where('status', 'pending')
                ->get();
            
            $actions = [];
            
            foreach ($pendingViolations as $violation) {
                $priority = 'medium';
                
                // Determine priority based on severity and age
                $daysSinceViolation = $this->schoolNow()->diffInDays($violation->violation_date);
                
                if ($violation->severity === 'major' || $daysSinceViolation > 3) {
                    $priority = 'high';
                } elseif ($violation->severity === 'severe' || $daysSinceViolation > 7) {
                    $priority = 'high';
                } elseif ($daysSinceViolation <= 1) {
                    $priority = 'low';
                }
                
                if ($filter === 'all' || $filter === $priority) {
                    $actions[] = [
                        'id' => $violation->id,
                        'title' => 'Review Violation: ' . $violation->title,
                        'description' => $violation->student->first_name . ' ' . $violation->student->last_name . ' - ' . $violation->severity . ' violation',
                        'priority' => $priority,
                        'date' => $violation->violation_date->format('M d, Y'),
                        'student_id' => $violation->student_id,
                    ];
                }
            }
            
            // Add case meetings that need attention
            $pendingCaseMeetings = CaseMeeting::with(['student'])
                ->where('status', 'in_progress')
                ->get();
            
            foreach ($pendingCaseMeetings as $meeting) {
                $priority = 'medium';
                
                if ($meeting->urgency_level === 'high' || $meeting->urgency_level === 'urgent') {
                    $priority = 'high';
                } elseif ($meeting->urgency_level === 'low') {
                    $priority = 'low';
                }
                
                if ($filter === 'all' || $filter === $priority) {
                    $actions[] = [
                        'id' => $meeting->id,
                        'title' => 'Case Meeting: ' . $meeting->reason,
                        'description' => $meeting->student->first_name . ' ' . $meeting->student->last_name . ' - Case meeting in progress',
                        'priority' => $priority,
                        'date' => $meeting->created_at->format('M d, Y'),
                        'student_id' => $meeting->student_id,
                    ];
                }
            }
            
            // Sort by priority (high first)
            usort($actions, function($a, $b) {
                $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
                return $priorityOrder[$b['priority']] - $priorityOrder[$a['priority']];
            });
            
            return response()->json(['actions' => array_slice($actions, 0, 10)]);
        }

        /**
         * Get critical cases for dashboard
         */
        public function getCriticalCases(Request $request)
        {
            $limit = (int) $request->get('limit', 5);
            
            // Get students with multiple major violations or severe violations
            $criticalCases = Violation::with(['student'])
                ->where(function($query) {
                    $query->where('severity', 'severe')
                          ->orWhere('severity', 'major');
                })
                ->where('status', '!=', 'resolved')
                ->get()
                ->groupBy('student_id')
                ->map(function ($violations, $studentId) {
                    $student = $violations->first()->student;
                    $majorCount = $violations->where('severity', 'major')->count();
                    $severeCount = $violations->where('severity', 'severe')->count();
                    
                    // Determine status based on violation severity and count
                    $status = 'active'; // Default status
                    if ($severeCount > 2) {
                        $status = 'critical';
                    } elseif ($majorCount > 4) {
                        $status = 'serious';
                    }
                    
                    return [
                        'student_id' => $studentId,
                        'student_name' => $student->first_name . ' ' . $student->last_name,
                        'violation_type' => $severeCount > 0 ? 'Multiple Severe Violations' : 'Multiple Major Violations',
                        'severity' => $severeCount > 0 ? 'severe' : 'major',
                        'violation_count' => $violations->count(),
                        'major_count' => $majorCount,
                        'severe_count' => $severeCount,
                        'status' => $status,
                        'priority_score' => ($severeCount * 3) + ($majorCount * 2), // For sorting
                    ];
                })
                ->sortByDesc('priority_score')
                ->take($limit)
                ->values();
            
            return response()->json(['cases' => $criticalCases]);
        }

        /**
         * Get violation trends data for dashboard chart
         */
        public function getViolationTrends(Request $request)
        {
            $period = $request->get('period', '12months');
            
            $months = [];
            $minorData = [];
            $majorData = [];
            
            switch ($period) {
                case '3months':
                    $monthsCount = 3;
                    break;
                case '6months':
                    $monthsCount = 6;
                    break;
                case '12months':
                default:
                    $monthsCount = 12;
                    break;
            }
            
            for ($i = $monthsCount - 1; $i >= 0; $i--) {
                $now = $this->schoolNow();
                $date = $now->copy()->subMonths($i);
                $months[] = $date->format('M Y');
                
                $minorCount = Violation::where('severity', 'minor')
                    ->whereYear('violation_date', $date->year)
                    ->whereMonth('violation_date', $date->month)
                    ->count();
                
                $majorCount = Violation::where('severity', 'major')
                    ->whereYear('violation_date', $date->year)
                    ->whereMonth('violation_date', $date->month)
                    ->count();
                
                $minorData[] = $minorCount;
                $majorData[] = $majorCount;
            }
            
            return response()->json([
                'labels' => $months,
                'minor' => $minorData,
                'major' => $majorData,
            ]);
        }

    /**
     * Mark alert as viewed (for dismissing notification badges)
     */
    public function markAlertViewed(Request $request)
    {
        $alertType = $request->input('alert_type');
        
        if ($alertType === 'violations') {
            session(['violations_alert_viewed_at' => now()]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Get real-time alert counts for discipline
     */
    public function getAlertCounts()
    {
        try {
            // Get timestamp of when alerts were last viewed
            $lastViewedAt = session('violations_alert_viewed_at');
            
            // Count violations with student replies that were added after last view
            $query = Violation::whereNotNull('student_statement')
                ->where('student_statement', '!=', '');
            
            // If there's a last viewed timestamp, only count newer replies
            if ($lastViewedAt) {
                $query->where('updated_at', '>', $lastViewedAt);
            }
            
            $counts = [
                'student_replies' => $query->count(),
            ];
            
            return response()->json([
                'success' => true,
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching alert counts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    }
