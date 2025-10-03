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
        $violations = Violation::with(['student', 'reportedBy', 'resolvedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->orderBy('last_name', 'asc')
            ->get();

        $stats = [
            'pending' => Violation::where('status', 'pending')->count(),
            'investigating' => Violation::where('status', 'investigating')->count(),
            'resolved' => Violation::where('status', 'resolved')->count(),
            'severe' => Violation::where('severity', 'severe')->count(),
        ];

        return view('discipline.violations', compact('violations', 'students', 'stats'));
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
            'major_category' => 'nullable|string',
            'violation_date' => 'required|date',
            'violation_time' => 'nullable',
            'status' => 'nullable|in:pending,investigating,resolved,dismissed',
        ]);

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
                'status' => 'required|in:pending,investigating,resolved,dismissed',
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
     * Delete violation
     */
    public function destroyViolation(Request $request, Violation $violation)
    {
        try {
            $violationId = $violation->id;
            $violation->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Violation deleted successfully.',
                    'violation_id' => $violationId
                ]);
            }

            return redirect()->route('discipline.violations.index')
                ->with('success', 'Violation deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete violation.'
                ], 500);
            }

            return redirect()->route('discipline.violations.index')
                ->with('error', 'Failed to delete violation.');
        }
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
                    'Category 1' => ViolationList::where('severity', 'major')->where('category', '1')->pluck('title')->toArray(),
                    'Category 2' => ViolationList::where('severity', 'major')->where('category', '2')->pluck('title')->toArray(),
                    'Category 3' => ViolationList::where('severity', 'major')->where('category', '3')->pluck('title')->toArray(),
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
}