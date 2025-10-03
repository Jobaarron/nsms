<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Discipline;
use App\Models\User;
use App\Models\Student;
use App\Models\Violation;
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
        
        $stats = [
            'total_students' => $totalStudents,
            'violations_this_month' => $violationsThisMonth,
            'total_violations' => $totalViolations,
            'pending_violations' => $pendingViolations,
            'violations_today' => $violationsToday,
            'major_violations' => $majorViolations,
            'severe_violations' => $severeViolations,
        ];

        return view('discipline.index', compact('stats'));
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
        $validatedData = $request->validate([
            'student_id' => 'required|exists:students,id',
            'violation_type' => 'required|string|in:late,uniform,misconduct,academic,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:minor,major,severe',
            'violation_date' => 'required|date',
            'violation_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'witnesses' => 'nullable|string',
            'evidence' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        // Get current user's discipline record
        $user = Auth::user();
        $disciplineRecord = $user->discipline ?? $user->guidanceDiscipline ?? null;
        if (!$disciplineRecord) {
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

        // Process witnesses
        if ($request->witnesses) {
            $witnesses = array_filter(explode("\n", $request->witnesses));
            $validatedData['witnesses'] = $witnesses;
        }

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('violations', 'public');
                $attachments[] = $path;
            }
            $validatedData['attachments'] = $attachments;
        }

        $validatedData['reported_by'] = $disciplineRecord->id;

        $violation = Violation::create($validatedData);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Violation reported successfully.',
                'violation' => $violation->load(['student', 'reportedBy'])
            ]);
        }

        return redirect()->route('discipline.violations.index')
            ->with('success', 'Violation reported successfully.');
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
                'violation_type' => 'required|string|in:late,uniform,misconduct,academic,other',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'severity' => 'required|in:minor,major,severe',
                'violation_date' => 'required|date',
                'violation_time' => 'nullable',
                'location' => 'nullable|string|max:255',
                'witnesses' => 'nullable',
                'evidence' => 'nullable|string',
                'status' => 'required|in:pending,investigating,resolved,dismissed',
                'resolution' => 'nullable|string',
                'student_statement' => 'nullable|string',
                'disciplinary_action' => 'nullable|string',
                'parent_notified' => 'nullable|boolean',
                'notes' => 'nullable|string',
                'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
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

        // Process witnesses
        if ($request->witnesses) {
            $witnesses = array_filter(explode("\n", $request->witnesses));
            $validatedData['witnesses'] = $witnesses;
        }

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $attachments = $violation->attachments ?: [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('violations', 'public');
                $attachments[] = $path;
            }
            $validatedData['attachments'] = $attachments;
        }

        // If status is being changed to resolved, set resolved_by and resolved_at
        if ($validatedData['status'] === 'resolved' && $violation->status !== 'resolved') {
            $user = Auth::user();
            if ($user) {
                // Try to get discipline record or fallback to guidance discipline record
                $disciplineRecord = $user->discipline ?? $user->guidanceDiscipline ?? null;
                if ($disciplineRecord) {
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
            // Delete associated files
            if ($violation->attachments) {
                foreach ($violation->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment);
                }
            }

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
}
