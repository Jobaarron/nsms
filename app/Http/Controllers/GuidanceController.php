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

        // Get current user's guidance record
        $user = Auth::user();
        $guidanceRecord = $user->guidance ?? $user->guidanceDiscipline ?? null;
        if (!$guidanceRecord) {
            return back()->withErrors(['error' => 'You do not have permission to schedule case meetings.']);
        }

        $validatedData['counselor_id'] = $guidanceRecord->id;
        $validatedData['status'] = 'scheduled';

        $caseMeeting = CaseMeeting::create($validatedData);

        return redirect()->route('guidance.case-meetings.index')
            ->with('success', 'Case meeting scheduled successfully.');
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

        return redirect()->route('guidance.case-meetings.index')
            ->with('success', 'Case summary created successfully.');
    }

    /**
     * Forward case to president
     */
    public function forwardToPresident(Request $request, CaseMeeting $caseMeeting)
    {
        $validatedData = $request->validate([
            'sanction_recommendation' => 'required|string',
            'urgency_level' => 'required|in:low,medium,high,urgent',
            'president_notes' => 'nullable|string',
        ]);

        $caseMeeting->update([
            'sanction_recommendation' => $validatedData['sanction_recommendation'],
            'urgency_level' => $validatedData['urgency_level'],
            'president_notes' => $validatedData['president_notes'],
            'forwarded_to_president' => true,
            'forwarded_at' => now(),
            'status' => 'forwarded',
        ]);

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
}
