<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Guidance;
use App\Models\User;
use App\Models\Student;
use App\Models\CaseMeeting;
use App\Models\CounselingSession;

class GuidanceController extends Controller
{
    public function __construct()
    {
        // Role and permission management is handled by RolePermissionSeeder
        // No need to create roles/permissions here
    }

    // Show login form
    public function showLogin()
    {
        return view('guidance.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        // Check if user exists and has guidance role
        $user = User::where('email', $credentials['email'])
                   ->first();
        
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Check if user has appropriate role
            if ($user->isGuidanceStaff()) {
                Auth::login($user);
                $user->updateLastLogin(); // Update last login timestamp
                session(['guidance_user' => true]); // Mark as guidance user
                return redirect()->route('guidance.dashboard');
            } else {
                return back()->withErrors(['email' => 'You do not have permission to access this system.']);
            }
        }

        return back()->withErrors(['email' => 'Invalid credentials or account is inactive.']);
    }

    // Show dashboard
    public function dashboard()
    {
        // Check if user is authenticated and is guidance staff
        if (!Auth::check() || !session('guidance_user') || !Auth::user()->isGuidanceStaff()) {
            return redirect()->route('guidance.login')->withErrors(['error' => 'Please login to access the dashboard.']);
        }

        // Get statistics
        $totalStudents = Student::count();
        $activeCaseMeetings = CaseMeeting::where('status', 'scheduled')->count();
        $completedCounselingSessions = CounselingSession::where('status', 'completed')->count();
        $pendingCases = CaseMeeting::where('status', 'pending')->count();
        $scheduledCounseling = CounselingSession::where('status', 'scheduled')->count();
        $houseVisitsScheduled = CaseMeeting::where('meeting_type', 'house_visit')->where('status', 'scheduled')->count();

        $stats = [
            'total_students' => $totalStudents,
            'active_case_meetings' => $activeCaseMeetings,
            'completed_counseling_sessions' => $completedCounselingSessions,
            'pending_cases' => $pendingCases,
            'scheduled_counseling' => $scheduledCounseling,
            'house_visits_scheduled' => $houseVisitsScheduled,
        ];

        return view('guidance.index', compact('stats'));
    }

    // Logout
    public function logout(Request $request)
    {
        session()->forget('guidance_user');
        Auth::logout();
        return redirect()->route('guidance.login');
    }

    // CASE MEETING METHODS

    /**
     * Display case meetings index page
     */
    public function caseMeetingsIndex()
    {
        $caseMeetings = CaseMeeting::with(['student', 'counselor'])
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20);

        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->orderBy('last_name', 'asc')
            ->get();

        return view('guidance.case-meetings', compact('caseMeetings', 'students'));
    }

    /**
     * Schedule a case meeting
     */
    public function scheduleCaseMeeting(Request $request)
    {
        $validatedData = $request->validate([
            'student_id' => 'required|exists:students,id',
            'meeting_type' => 'required|in:case_meeting,house_visit',
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'required',
            'location' => 'nullable|string|max:255',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Get current user
        $user = Auth::user();

        // Log authentication status
        \Log::info('Schedule Case Meeting Attempt', [
            'user_id' => $user ? $user->id : null,
            'user_authenticated' => Auth::check(),
            'session_guidance_user' => session('guidance_user'),
            'is_guidance_staff' => $user ? $user->isGuidanceStaff() : false,
        ]);

        if (!$user) {
            \Log::warning('Schedule Case Meeting: User not authenticated');
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to schedule case meetings.'
                ], 401);
            }
            return redirect()->route('guidance.login')->withErrors(['error' => 'Please login to continue.']);
        }

        if (!session('guidance_user') || !$user->isGuidanceStaff()) {
            \Log::warning('Schedule Case Meeting: User is not guidance staff', [
                'user_id' => $user->id,
                'session_guidance_user' => session('guidance_user'),
                'is_guidance_staff' => $user->isGuidanceStaff(),
            ]);
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to access guidance features.'
                ], 403);
            }
            return redirect()->route('guidance.login')->withErrors(['error' => 'Access denied.']);
        }

        // Get current user's guidance record
        $guidanceRecord = $user->guidance ?? $user->guidanceDiscipline ?? null;

        \Log::info('Schedule Case Meeting: Guidance Record Check', [
            'user_id' => $user->id,
            'has_guidance' => $user->guidance ? true : false,
            'has_guidance_discipline' => $user->guidanceDiscipline ? true : false,
            'guidance_record_id' => $guidanceRecord ? $guidanceRecord->id : null,
            'guidance_is_active' => $guidanceRecord ? $guidanceRecord->is_active : null,
        ]);

        if (!$guidanceRecord) {
            \Log::warning('Schedule Case Meeting: No guidance record found', [
                'user_id' => $user->id,
            ]);
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your guidance profile is not set up. Please contact an administrator.'
                ], 403);
            }
            return back()->withErrors(['error' => 'Your guidance profile is not set up. Please contact an administrator.']);
        }

        if ($user->guidance && !$user->guidance->is_active) {
            \Log::warning('Schedule Case Meeting: Guidance record is inactive', [
                'user_id' => $user->id,
                'guidance_id' => $user->guidance->id,
            ]);
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your guidance account is inactive. Please contact an administrator.'
                ], 403);
            }
            return back()->withErrors(['error' => 'Your guidance account is inactive. Please contact an administrator.']);
        }

        // Check for duplicate meeting
        $existingMeeting = CaseMeeting::where('student_id', $validatedData['student_id'])
            ->where('scheduled_date', $validatedData['scheduled_date'])
            ->where('scheduled_time', $validatedData['scheduled_time'])
            ->first();

        if ($existingMeeting) {
            $message = 'A meeting for this student at the specified date and time already exists.';
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 409);
            }
            return back()->withErrors(['error' => $message]);
        }

        $validatedData['counselor_id'] = $guidanceRecord->id;
        $validatedData['status'] = 'scheduled';

        // Check if there's an existing in_progress case meeting for this student
        $existingMeeting = CaseMeeting::where('student_id', $validatedData['student_id'])
            ->where('status', 'in_progress')
            ->first();

        if ($existingMeeting) {
            // Update the existing in_progress meeting
            $existingMeeting->update($validatedData);
            $caseMeeting = $existingMeeting;
        } else {
            // Create new case meeting
            $caseMeeting = CaseMeeting::create($validatedData);
        }

        \Log::info('Schedule Case Meeting: Success', [
            'user_id' => $user->id,
            'case_meeting_id' => $caseMeeting->id,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Case meeting scheduled successfully.',
                'meeting' => $caseMeeting
            ]);
        }

        return redirect()->route('guidance.case-meetings.index')
            ->with('success', 'Case meeting scheduled successfully.');
    }

    /**
     * Complete a case meeting
     */
    public function completeCaseMeeting(Request $request, CaseMeeting $caseMeeting)
    {
        $user = Auth::user();
        $guidanceRecord = $user->guidance ?? $user->guidanceDiscipline ?? null;
        if (!$guidanceRecord || ($user->guidance && !$user->guidance->is_active)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to complete case meetings.'
                ], 403);
            }
            return back()->withErrors(['error' => 'You do not have permission to complete case meetings.']);
        }

        $caseMeeting->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Case meeting marked as completed.',
                'caseMeeting' => $caseMeeting
            ]);
        }

        return redirect()->route('guidance.case-meetings.index')
            ->with('success', 'Case meeting marked as completed.');
    }

    /**
     * Create case summary
     */
    public function createCaseSummary(Request $request, CaseMeeting $caseMeeting)
    {
        $validatedData = $request->validate([
            'summary' => 'required|string',
            'recommendations' => 'nullable|string',
            'follow_up_required' => 'boolean',
            'follow_up_date' => 'nullable|date|after:today',
        ]);

        $caseMeeting->update([
            'summary' => $validatedData['summary'],
            'recommendations' => $validatedData['recommendations'],
            'follow_up_required' => $validatedData['follow_up_required'] ?? false,
            'follow_up_date' => $validatedData['follow_up_date'],
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Case summary created successfully.',
                'caseMeeting' => $caseMeeting
            ]);
        }

        return redirect()->route('guidance.case-meetings.index')
            ->with('success', 'Case summary created successfully.');
    }

    /**
     * Show case meeting details
     */
    public function showCaseMeeting(CaseMeeting $caseMeeting)
    {
        $caseMeeting->load(['student', 'counselor']);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'meeting' => [
                    'id' => $caseMeeting->id,
                    'student_name' => $caseMeeting->student ? $caseMeeting->student->full_name : 'Unknown',
                    'meeting_type' => $caseMeeting->meeting_type,
                    'meeting_type_display' => ucwords(str_replace('_', ' ', $caseMeeting->meeting_type)),
                    'scheduled_date' => $caseMeeting->scheduled_date,
                    'scheduled_time' => $caseMeeting->scheduled_time,
                    'location' => $caseMeeting->location,
                    'status' => $caseMeeting->status,
                    'status_text' => ucfirst($caseMeeting->status),
                    'status_class' => $this->getStatusClass($caseMeeting->status),
                    'urgency_level' => $caseMeeting->urgency_level,
                    'urgency_color' => $this->getUrgencyColor($caseMeeting->urgency_level),
                    'reason' => $caseMeeting->reason,
                    'notes' => $caseMeeting->notes,
                    'summary' => $caseMeeting->summary,
                ]
            ]);
        }

        return view('guidance.case-meeting-detail', compact('caseMeeting'));
    }

    /**
     * Edit case meeting
     */
    public function editCaseMeeting(CaseMeeting $caseMeeting)
    {
        $caseMeeting->load(['student', 'counselor']);
        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->orderBy('last_name', 'asc')
            ->get();

        return view('guidance.edit-case-meeting', compact('caseMeeting', 'students'));
    }

    /**
     * Export case meetings
     */
    public function exportCaseMeetings(Request $request)
    {
        $query = CaseMeeting::with(['student', 'counselor']);

        // Apply filters if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        if ($request->has('type') && $request->type) {
            $query->where('meeting_type', $request->type);
        }
        if ($request->has('date') && $request->date) {
            $query->whereDate('scheduled_date', $request->date);
        }
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $caseMeetings = $query->orderBy('scheduled_date', 'desc')->get();

        $filename = 'case_meetings_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($caseMeetings) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Student ID',
                'Student Name',
                'Meeting Type',
                'Scheduled Date',
                'Scheduled Time',
                'Location',
                'Status',
                'Reason',
                'Counselor',
                'Created At'
            ]);

            // CSV data
            foreach ($caseMeetings as $meeting) {
                fputcsv($file, [
                    $meeting->student ? $meeting->student->student_id : '',
                    $meeting->student ? $meeting->student->full_name : '',
                    ucwords(str_replace('_', ' ', $meeting->meeting_type)),
                    $meeting->scheduled_date,
                    $meeting->scheduled_time,
                    $meeting->location ?: '',
                    ucfirst($meeting->status),
                    $meeting->reason,
                    $meeting->counselor ? $meeting->counselor->name : '',
                    $meeting->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Forward case to president
     */
    public function forwardToPresident(Request $request, CaseMeeting $caseMeeting)
    {
        $user = Auth::user();
        $guidanceRecord = $user->guidance ?? $user->guidanceDiscipline ?? null;
        if (!$guidanceRecord || ($user->guidance && !$user->guidance->is_active)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to forward cases.'
                ], 403);
            }
            return back()->withErrors(['error' => 'You do not have permission to forward cases.']);
        }

        $validatedData = $request->validate([
            'reason' => 'required|string',
        ]);

        $caseMeeting->update([
            'president_notes' => $validatedData['reason'],
            'forwarded_to_president' => true,
            'forwarded_at' => now(),
            'status' => 'in_progress',
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Case forwarded to president successfully.',
                'caseMeeting' => $caseMeeting
            ]);
        }

        return redirect()->route('guidance.case-meetings.index')
            ->with('success', 'Case forwarded to president successfully.');
    }

    // COUNSELING SESSION METHODS

    /**
     * Display counseling sessions index page
     */
    public function counselingSessionsIndex()
    {
        $counselingSessions = CounselingSession::with(['student', 'counselor'])
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20);

        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->orderBy('last_name', 'asc')
            ->get();

        return view('guidance.counseling-sessions', compact('counselingSessions', 'students'));
    }

    /**
     * Schedule counseling session
     */
    public function scheduleCounselingSession(Request $request)
    {
        $validatedData = $request->validate([
            'student_id' => 'required|exists:students,id',
            'session_type' => 'required|in:individual,group,family,career',
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'required',
            'duration' => 'required|integer|min:30|max:180',
            'location' => 'nullable|string|max:255',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Get current user's guidance record
        $user = Auth::user();
        $guidanceRecord = $user->guidance ?? $user->guidanceDiscipline ?? null;
        if (!$guidanceRecord) {
            return back()->withErrors(['error' => 'You do not have permission to schedule counseling sessions.']);
        }

        $validatedData['counselor_id'] = $guidanceRecord->id;
        $validatedData['status'] = 'scheduled';

        $counselingSession = CounselingSession::create($validatedData);

        return redirect()->route('guidance.counseling-sessions.index')
            ->with('success', 'Counseling session scheduled successfully.');
    }

    /**
     * Create counseling summary
     */
    public function createCounselingSummary(Request $request, CounselingSession $counselingSession)
    {
        $validatedData = $request->validate([
            'session_summary' => 'required|string',
            'student_progress' => 'nullable|string',
            'goals_achieved' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'follow_up_required' => 'boolean',
            'follow_up_date' => 'nullable|date|after:today',
        ]);

        $counselingSession->update([
            'session_summary' => $validatedData['session_summary'],
            'student_progress' => $validatedData['student_progress'],
            'goals_achieved' => $validatedData['goals_achieved'],
            'next_steps' => $validatedData['next_steps'],
            'follow_up_required' => $validatedData['follow_up_required'] ?? false,
            'follow_up_date' => $validatedData['follow_up_date'],
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('guidance.counseling-sessions.index')
            ->with('success', 'Counseling summary created successfully.');
    }

    /**
     * Get status class for badges
     */
    private function getStatusClass($status)
    {
        return match($status) {
            'scheduled' => 'bg-primary',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            'in_progress' => 'bg-warning',
            'pending' => 'bg-secondary',
            default => 'bg-secondary'
        };
    }

    /**
     * Get urgency color for badges
     */
    private function getUrgencyColor($urgency)
    {
        return match($urgency) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'urgent' => 'danger',
            default => 'secondary'
        };
    }
}