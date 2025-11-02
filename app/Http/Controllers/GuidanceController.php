<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
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
                session()->forget('discipline_user'); // Clear discipline user session flag
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
        session()->forget('discipline_user'); // Clear discipline user session flag on logout
        Auth::logout();
        return redirect()->route('guidance.login');
    }

    // CASE MEETING METHODS

    /**
     * Display case meetings index page
     */
    public function caseMeetingsIndex()
    {
        $caseMeetings = CaseMeeting::with(['student', 'counselor', 'sanctions'])
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
        $guidanceRecord = $user->guidance ?? null;

        \Log::info('Schedule Case Meeting: Guidance Record Check', [
            'user_id' => $user->id,
            'has_guidance' => $user->guidance ? true : false,
            'has_guidance_discipline' => false, // Legacy field - now using separate guidance/discipline tables
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

        // Check if there's an existing pending or in_progress case meeting for this student
        $existingMeeting = CaseMeeting::where('student_id', $validatedData['student_id'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->first();

        if ($existingMeeting) {
            // Update the existing in_progress meeting
            $existingMeeting->update($validatedData);
            $caseMeeting = $existingMeeting;
        } else {
            // Create new case meeting
            $caseMeeting = CaseMeeting::create($validatedData);
        }

        // Load related data
        $caseMeeting->load(['student', 'counselor', 'sanctions']);

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

        // Automatically update all related violations' statuses
        foreach ($caseMeeting->violations as $violation) {
            $violation->update(['status' => 'completed']);
        }

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
            // Agreed Actions/Interventions fields
            'written_reflection' => 'nullable|boolean',
            'written_reflection_due' => 'nullable|date',
            'mentorship_counseling' => 'nullable|boolean',
            'mentor_name' => 'nullable|string|max:255',
            'parent_teacher_communication' => 'nullable|boolean',
            'parent_teacher_date' => 'nullable|date',
            'restorative_justice_activity' => 'nullable|boolean',
            'restorative_justice_date' => 'nullable|date',
            'follow_up_meeting' => 'nullable|boolean',
            'follow_up_meeting_date' => 'nullable|date',
            'community_service' => 'nullable|boolean',
            'community_service_date' => 'nullable|date',
            'community_service_area' => 'nullable|string|max:255',
            'suspension' => 'nullable|boolean',
            'suspension_3days' => 'nullable|boolean',
            'suspension_5days' => 'nullable|boolean',
            'suspension_other_days' => 'nullable|integer',
            'suspension_start' => 'nullable|date',
            'suspension_end' => 'nullable|date',
            'suspension_return' => 'nullable|date',
            'expulsion' => 'nullable|boolean',
            'expulsion_date' => 'nullable|date',
        ]);

        // Prepare update data
        $updateData = [
            'summary' => $validatedData['summary'],
            'recommendations' => $validatedData['recommendations'] ?? null,
            'follow_up_required' => $validatedData['follow_up_required'] ?? false,
            'follow_up_date' => $validatedData['follow_up_date'] ?? null,
            'status' => 'pre_completed',
            // Agreed Actions/Interventions
            'written_reflection' => $request->boolean('written_reflection'),
            'written_reflection_due' => $validatedData['written_reflection_due'] ?? null,
            'mentorship_counseling' => $request->boolean('mentorship_counseling'),
            'mentor_name' => $validatedData['mentor_name'] ?? null,
            'parent_teacher_communication' => $request->boolean('parent_teacher_communication'),
            'parent_teacher_date' => $validatedData['parent_teacher_date'] ?? null,
            'restorative_justice_activity' => $request->boolean('restorative_justice_activity'),
            'restorative_justice_date' => $validatedData['restorative_justice_date'] ?? null,
            'follow_up_meeting' => $request->boolean('follow_up_meeting'),
            'follow_up_meeting_date' => $validatedData['follow_up_meeting_date'] ?? null,
            'community_service' => $request->boolean('community_service'),
            'community_service_date' => $validatedData['community_service_date'] ?? null,
            'community_service_area' => $validatedData['community_service_area'] ?? null,
            'suspension' => $request->boolean('suspension'),
            'suspension_3days' => $request->boolean('suspension_3days'),
            'suspension_5days' => $request->boolean('suspension_5days'),
            'suspension_other_days' => $validatedData['suspension_other_days'] ?? null,
            'suspension_start' => $validatedData['suspension_start'] ?? null,
            'suspension_end' => $validatedData['suspension_end'] ?? null,
            'suspension_return' => $validatedData['suspension_return'] ?? null,
            'expulsion' => $request->boolean('expulsion'),
            'expulsion_date' => $validatedData['expulsion_date'] ?? null,
        ];

        $caseMeeting->update($updateData);

        // Automatically update all related violations' statuses
        foreach ($caseMeeting->violations as $violation) {
            $violation->update(['status' => 'pre_completed']);
        }

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
        $caseMeeting->load(['student', 'counselor', 'sanctions', 'violation']);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'meeting' => [
                    'id' => $caseMeeting->id,
                    'student_name' => $caseMeeting->student ? $caseMeeting->student->full_name : 'Unknown',
                    'student_id' => $caseMeeting->student ? $caseMeeting->student->student_id : 'Unknown',
                    'violation_id' => $caseMeeting->violation_id,
                    'status' => $caseMeeting->status,
                    'status_text' => ucfirst($caseMeeting->status),
                    'status_class' => $this->getStatusClass($caseMeeting->status),
                    'notes' => $caseMeeting->notes,
                    'summary' => $caseMeeting->summary,
                    'recommendations' => $caseMeeting->recommendations,
                    'follow_up_required' => $caseMeeting->follow_up_required,
                    'follow_up_date' => $caseMeeting->follow_up_date ? $caseMeeting->follow_up_date->format('M d, Y') : null,
                    'completed_at' => $caseMeeting->completed_at ? $caseMeeting->completed_at->format('M d, Y h:i A') : null,
                    'forwarded_to_president' => $caseMeeting->forwarded_to_president,
                    'scheduled_date' => $caseMeeting->scheduled_date ? $caseMeeting->scheduled_date->format('M d, Y') : null,
                    'scheduled_time' => $caseMeeting->scheduled_time ? $caseMeeting->scheduled_time->format('h:i A') : null,
                    'scheduled_by_name' => $caseMeeting->counselor ? ($caseMeeting->counselor->first_name . ' ' . $caseMeeting->counselor->last_name) : null,
                    'created_at' => $caseMeeting->created_at ? $caseMeeting->created_at->format('Y-m-d H:i:s') : null,
                    // Include violation student reply fields if violation exists
                    'student_statement' => $caseMeeting->violation ? $caseMeeting->violation->student_statement : null,
                    'incident_feelings' => $caseMeeting->violation ? $caseMeeting->violation->incident_feelings : null,
                    'action_plan' => $caseMeeting->violation ? $caseMeeting->violation->action_plan : null,
                    // Add teacher reply fields from the case meeting itself
                    'teacher_statement' => $caseMeeting->teacher_statement,
                    'teacher_action_plan' => $caseMeeting->action_plan,
                    'sanctions' => $caseMeeting->sanctions->map(function ($sanction) {
                        return [
                            'id' => $sanction->id,
                            'type' => $sanction->sanction,
                            'description' => $sanction->notes,
                            'status' => $sanction->status,
                            'created_at' => $sanction->created_at->format('Y-m-d H:i:s'),
                        ];
                    }),
                    // Add the full violation object for modal details
                    'violation' => $caseMeeting->violation ? [
                        'title' => $caseMeeting->violation->title,
                        'description' => $caseMeeting->violation->description,
                        'severity' => $caseMeeting->violation->severity,
                        'major_category' => $caseMeeting->violation->major_category,
                        'status' => $caseMeeting->violation->status,
                        'violation_date' => $caseMeeting->violation->violation_date ? $caseMeeting->violation->violation_date->format('Y-m-d') : null,
                        'violation_time' => $caseMeeting->violation->violation_time,
                    ] : null,
                ]
            ]);
        }

        $caseMeetings = CaseMeeting::with(['student', 'counselor', 'sanctions'])
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20);

        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->orderBy('last_name', 'asc')
            ->get();

        return view('guidance.case-meetings', [
            'caseMeeting' => $caseMeeting,
            'caseMeetings' => $caseMeetings,
            'students' => $students,
            'showDetail' => true
        ]);
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

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'meeting' => [
                    'id' => $caseMeeting->id,
                    'student_id' => $caseMeeting->student_id,
                    'meeting_type' => $caseMeeting->meeting_type,
                    'scheduled_date' => $caseMeeting->scheduled_date ? $caseMeeting->scheduled_date->format('Y-m-d') : null,
                    'scheduled_time' => $caseMeeting->scheduled_time ? $caseMeeting->scheduled_time->format('H:i') : null,
                    'location' => $caseMeeting->location,
                    'urgency_level' => $caseMeeting->urgency_level,
                    'reason' => $caseMeeting->reason,
                    'notes' => $caseMeeting->notes,
                ],
                'students' => $students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->full_name . ' (' . $student->student_id . ')',
                    ];
                })
            ]);
        }

        return view('guidance.edit-case-meeting', compact('caseMeeting', 'students'));
    }

    /**
     * Update case meeting
     */
    public function updateCaseMeeting(Request $request, CaseMeeting $caseMeeting)
    {
        $validatedData = $request->validate([
            // 'student_id' => 'required|exists:students,id',
            // 'meeting_type' => 'required|in:case_meeting,house_visit',
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'required',
            'location' => 'nullable|string|max:255',
            'urgency_level' => 'nullable|in:low,medium,high,urgent',
            // 'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $caseMeeting->update($validatedData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Case meeting updated successfully.',
                'meeting' => $caseMeeting->load(['student', 'counselor'])
            ]);
        }

        return redirect()->route('guidance.case-meetings.index')
            ->with('success', 'Case meeting updated successfully.');
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
 public function forwardToPresident(CaseMeeting $caseMeeting)
{
    try {
        // Debug logs
        Log::info('Forward attempt for CaseMeeting', [
            'id' => $caseMeeting->id,
            'status' => $caseMeeting->status,
            'summary_exists' => !empty($caseMeeting->summary),
            'sanctions_exist' => $caseMeeting->sanctions()->exists(),
            'meeting_type' => $caseMeeting->meeting_type,
        ]);

        // Only require summary and schedule for forwarding
        if (empty($caseMeeting->summary) || $caseMeeting->status !== 'pre_completed') {
            Log::warning('Forward blocked: requirements not met', ['id' => $caseMeeting->id]);
            return response()->json([
                'success' => false,
                'message' => 'Please add both a schedule and a summary report before forwarding.'
            ], 400);
        }


        $caseMeeting->update([
            'status' => 'submitted',
            'forwarded_to_president' => true,
            'forwarded_at' => now(),
        ]);

        // Automatically update all related violations' statuses
        foreach ($caseMeeting->violations as $violation) {
            $violation->update(['status' => 'submitted']);
        }

        Log::info('Forward successful for CaseMeeting', ['id' => $caseMeeting->id]);

        return response()->json([
            'success' => true,
            'message' => 'Case meeting forwarded to president successfully.'
        ]);
    } catch (\Exception $e) {
        Log::error('Error in forwardToPresident', [
            'id' => $caseMeeting->id ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Unexpected error occurred while forwarding. Check logs.'
        ], 500);
    }
}


    // COUNSELING SESSION METHODS

    /**
     * Display counseling sessions index page
     */
    public function counselingSessionsIndex()
    {
        $counselingSessions = CounselingSession::with(['student', 'counselor', 'recommender'])
            ->orderByRaw("CASE WHEN status = 'recommended' THEN 0 ELSE 1 END, start_date DESC")
            ->paginate(20);

        $scheduledSessions = CounselingSession::with('student')
            ->where('status', 'scheduled')
            ->orderBy('start_date', 'desc')
            ->get();

        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->orderBy('last_name', 'asc')
            ->get();

        $counselors = Guidance::where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('guidance.counseling-sessions', compact('counselingSessions', 'scheduledSessions', 'students', 'counselors'));
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
            'referral_academic' => 'nullable|array',
            'referral_academic_other' => 'nullable|string',
            'referral_social' => 'nullable|array',
            'referral_social_other' => 'nullable|string',
            'incident_description' => 'nullable|string',
        ]);

        // Get current user's guidance record
        $user = Auth::user();
        $guidanceRecord = $user->guidance ?? $user->guidanceDiscipline ?? null;
        if (!$guidanceRecord) {
            return back()->withErrors(['error' => 'You do not have permission to schedule counseling sessions.']);
        }

        $validatedData['counselor_id'] = $guidanceRecord->id;
        $validatedData['status'] = 'scheduled';

        // Auto-set session_no based on count of previous sessions for this student
        $studentId = $validatedData['student_id'];
        $sessionCount = CounselingSession::where('student_id', $studentId)->count();
        $validatedData['session_no'] = $sessionCount + 1;

        // Remove 'Others' from the checklist arrays before saving
        $referralAcademic = $validatedData['referral_academic'] ?? [];
        if (($key = array_search('Others', $referralAcademic)) !== false) {
            unset($referralAcademic[$key]);
        }
        $referralSocial = $validatedData['referral_social'] ?? [];
        if (($key = array_search('Others', $referralSocial)) !== false) {
            unset($referralSocial[$key]);
        }
        $counselingSession = CounselingSession::create([
            ...$validatedData,
            'referral_academic' => !empty($referralAcademic) ? json_encode(array_values($referralAcademic)) : null,
            'referral_academic_other' => $validatedData['referral_academic_other'] ?? null,
            'referral_social' => !empty($referralSocial) ? json_encode(array_values($referralSocial)) : null,
            'referral_social_other' => $validatedData['referral_social_other'] ?? null,
            'incident_description' => $validatedData['incident_description'] ?? null,
        ]);

        return redirect()->route('guidance.counseling-sessions.index')
            ->with('success', 'Counseling session scheduled successfully.');
    }

    /**
     * Schedule a recommended counseling session
     */
    public function scheduleRecommendedSession(Request $request, CounselingSession $counselingSession)
    {
        // Ensure the session is recommended
        if ($counselingSession->status !== 'recommended') {
            return response()->json([
                'success' => false,
                'message' => 'This session is not in recommended status.'
            ], 400);
        }

        $validatedData = $request->validate([
            'counselor_id' => 'required|exists:guidances,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:15|max:240',
            'location' => 'nullable|string|max:255',
        ]);

        $counselingSession->update([
            'counselor_id' => $validatedData['counselor_id'],
            'scheduled_date' => $validatedData['scheduled_date'],
            'scheduled_time' => $validatedData['scheduled_time'],
            'duration' => $validatedData['duration'],
            'location' => $validatedData['location'],
            'status' => 'scheduled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recommended counseling session scheduled successfully.',
            'counseling_session' => $counselingSession->load(['student', 'counselor', 'recommender'])
        ]);
    }




    public function scheduleInline(Request $request, $id)
{
    try {
        $session = CounselingSession::findOrFail($id);

        $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $session->update([
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'location' => $request->location,
            'notes' => $request->notes,
            'status' => 'Scheduled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Counseling session scheduled successfully!',
            'data' => $session
        ]);
    } catch (\Exception $e) {
        \Log::error('Error scheduling session inline: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to schedule counseling session.'
        ], 500);
    }
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
     * Show counseling session details
     */
    public function showCounselingSession(CounselingSession $counselingSession)
    {
        $counselingSession->load(['student', 'counselor', 'recommender']);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $counselingSession->id,
                    'student_name' => $counselingSession->student ? $counselingSession->student->full_name : 'Unknown',
                    'student_id' => $counselingSession->student ? $counselingSession->student->student_id : 'Unknown',
                    'counselor_name' => $counselingSession->counselor ? $counselingSession->counselor->name : 'Unknown',
                    'session_type' => $counselingSession->session_type,
                    'session_type_text' => ucwords(str_replace('_', ' ', $counselingSession->session_type)),
                    'session_type_icon' => $this->getSessionTypeIcon($counselingSession->session_type),
                    'session_type_class' => $this->getSessionTypeClass($counselingSession->session_type),
                    'scheduled_date' => $counselingSession->scheduled_date ? $counselingSession->scheduled_date->format('M d, Y') : null,
                    'scheduled_time' => $counselingSession->scheduled_time ? $counselingSession->scheduled_time->format('h:i A') : null,
                    'duration' => $counselingSession->duration,
                    'location' => $counselingSession->location,
                    'status' => $counselingSession->status,
                    'status_text' => ucfirst($counselingSession->status),
                    'status_class' => $this->getStatusClass($counselingSession->status),
                    'reason' => $counselingSession->reason,
                    'notes' => $counselingSession->notes,
                    'summary' => $counselingSession->session_summary,
                    'follow_up_required' => $counselingSession->follow_up_required,
                    'follow_up_date' => $counselingSession->follow_up_date ? $counselingSession->follow_up_date->format('M d, Y') : null,
                    'completed_at' => $counselingSession->completed_at ? $counselingSession->completed_at->format('M d, Y h:i A') : null,
                ]
            ]);
        }

        $counselingSessions = CounselingSession::with(['student', 'counselor', 'recommender'])
            ->orderByRaw("CASE WHEN status = 'recommended' THEN 0 ELSE 1 END, start_date DESC")
            ->paginate(20);

        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->orderBy('last_name', 'asc')
            ->get();

        return view('guidance.counseling-sessions', [
            'counselingSession' => $counselingSession,
            'counselingSessions' => $counselingSessions,
            'students' => $students,
            'showDetail' => true
        ]);
    }

    /**
     * Edit counseling session
     */
    public function editCounselingSession(CounselingSession $counselingSession)
    {
        $counselingSession->load(['student', 'counselor']);
        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->orderBy('last_name', 'asc')
            ->get();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $counselingSession->id,
                    'student_id' => $counselingSession->student_id,
                    'session_type' => $counselingSession->session_type,
                    'scheduled_date' => $counselingSession->scheduled_date ? $counselingSession->scheduled_date->format('Y-m-d') : null,
                    'scheduled_time' => $counselingSession->scheduled_time ? $counselingSession->scheduled_time->format('H:i') : null,
                    'duration' => $counselingSession->duration,
                    'location' => $counselingSession->location,
                    'reason' => $counselingSession->reason,
                    'notes' => $counselingSession->notes,
                ],
                'students' => $students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->full_name . ' (' . $student->student_id . ')',
                    ];
                })
            ]);
        }

        return view('guidance.edit-counseling-session', compact('counselingSession', 'students'));
    }

    /**
     * Update counseling session
     */
    public function updateCounselingSession(Request $request, CounselingSession $counselingSession)
    {
        $validatedData = $request->validate([
            'student_id' => 'required|exists:students,id',
            'session_type' => 'required|in:individual,group,family,career',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required',
            'duration' => 'required|integer|min:15|max:240',
            'location' => 'nullable|string|max:255',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $counselingSession->update($validatedData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Counseling session updated successfully.',
                'session' => $counselingSession->load(['student', 'counselor'])
            ]);
        }

        return redirect()->route('guidance.counseling-sessions.index')
            ->with('success', 'Counseling session updated successfully.');
    }

    /**
     * Complete counseling session
     */
    public function completeCounselingSession(Request $request, CounselingSession $counselingSession)
    {
        $validatedData = $request->validate([
            'summary' => 'required|string',
        ]);

        $counselingSession->update([
            'session_summary' => $validatedData['summary'],
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Counseling session marked as completed.',
                'counselingSession' => $counselingSession
            ]);
        }

        return redirect()->route('guidance.counseling-sessions.index')
            ->with('success', 'Counseling session marked as completed.');
    }

    /**
     * Reschedule counseling session
     */
    public function rescheduleCounselingSession(Request $request, CounselingSession $counselingSession)
    {
        $validatedData = $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|date_format:H:i',
        ]);

        $counselingSession->update([
            'scheduled_date' => $validatedData['scheduled_date'],
            'scheduled_time' => $validatedData['scheduled_time'],
            'status' => 'rescheduled',
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Counseling session rescheduled successfully.',
                'counselingSession' => $counselingSession
            ]);
        }

        return redirect()->route('guidance.counseling-sessions.index')
            ->with('success', 'Counseling session rescheduled successfully.');
    }

    /**
     * Export counseling sessions
     */
    public function exportCounselingSessions(Request $request)
    {
        $query = CounselingSession::with(['student', 'counselor', 'recommender']);

        // Apply filters if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        if ($request->has('type') && $request->type) {
            $query->where('session_type', $request->type);
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

        $counselingSessions = $query->orderBy('scheduled_date', 'desc')->get();

        $filename = 'counseling_sessions_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($counselingSessions) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Student ID',
                'Student Name',
                'Session Type',
                'Scheduled Date',
                'Scheduled Time',
                'Duration',
                'Location',
                'Status',
                'Reason',
                'Counselor',
                'Recommender',
                'Created At'
            ]);

            // CSV data
            foreach ($counselingSessions as $session) {
                fputcsv($file, [
                    $session->student ? $session->student->student_id : '',
                    $session->student ? $session->student->full_name : '',
                    ucwords(str_replace('_', ' ', $session->session_type)),
                    $session->scheduled_date,
                    $session->scheduled_time,
                    $session->duration,
                    $session->location ?: '',
                    ucfirst($session->status),
                    $session->reason,
                    $session->counselor ? $session->counselor->name : '',
                    $session->recommender ? $session->recommender->name : '',
                    $session->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
     * Get counselors for API
     */
    public function getCounselors()
    {
        $counselors = Guidance::where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function ($counselor) {
                return [
                    'id' => $counselor->id,
                    'name' => $counselor->full_name,
                ];
            });

        return response()->json([
            'success' => true,
            'counselors' => $counselors
        ]);
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

    /**
     * Get session type icon
     */
    private function getSessionTypeIcon($type)
    {
        return match($type) {
            'individual' => 'ri-user-heart-line',
            'group' => 'ri-group-line',
            'family' => 'ri-home-heart-line',
            'career' => 'ri-briefcase-line',
            default => 'ri-heart-pulse-line'
        };
    }

    /**
     * Get session type class
     */
    private function getSessionTypeClass($type)
    {
        return match($type) {
            'individual' => 'bg-primary',
            'group' => 'bg-info',
            'family' => 'bg-success',
            'career' => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    /**
     * Approve counseling session via AJAX
     */
    public function approveCounselingSession(Request $request)
    {

        $request->validate([
            'session_id' => 'required|exists:counseling_sessions,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'frequency' => 'required|in:everyday,every_other_day,once_a_week,twice_a_week',
            'time_limit' => 'required|integer|min:1|max:240',
            'time' => 'required|date_format:H:i',
        ]);

        $session = CounselingSession::findOrFail($request->session_id);
        $session->start_date = $request->start_date;
        $session->end_date = $request->end_date;
        $session->frequency = $request->frequency;
        $session->time_limit = $request->time_limit;
        $session->time = $request->time;
        $session->status = 'scheduled';

        // Auto-set session_no: count all scheduled+completed sessions for this student (excluding this one if already scheduled)
        $studentId = $session->student_id;
        $sessionCount = CounselingSession::where('student_id', $studentId)
            ->whereIn('status', ['scheduled', 'completed'])
            ->where('id', '!=', $session->id)
            ->count();
        $session->session_no = $sessionCount + 1;

        $session->save();

        // Archive the approved session in archive_violations
        $user = Auth::user();
        $disciplineId = $user && $user->discipline ? $user->discipline->id : null;
        $guidanceId = $user && $user->guidance ? $user->guidance->id : null;
        $reportedBy = $disciplineId ?? $guidanceId;
        if (!$reportedBy) {
            $reportedBy = $user ? $user->id : 1;
        }
        $archiveData = [
            'counseling_session_id' => $session->id,
            'student_id' => $session->student_id,
            'counselor_id' => $session->counselor_id,
            'title' => 'Counseling Session Approved',
            'description' => $session->reason ?? $session->notes ?? 'Approved counseling session',
            'reason' => $session->reason,
            'notes' => $session->notes,
            'archived_at' => now(),
            'violation_date' => $session->start_date ?? now(),
            'reported_by' => $reportedBy,
            'feedback' => null,
        ];
        \App\Models\ArchiveViolation::create($archiveData);

        return response()->json(['success' => true]);
    }
    /**
     * Reject counseling session with feedback and archive it
     */
    public function rejectCounselingSessionWithFeedback(Request $request, CounselingSession $counselingSession)
    {   
        $request->validate([
            'feedback' => 'required|string',
        ]);

        // Archive the session with feedback
        $user = Auth::user();
        // Try to get discipline or guidance id for reported_by
        $disciplineId = $user && $user->discipline ? $user->discipline->id : null;
        $guidanceId = $user && $user->guidance ? $user->guidance->id : null;
        $reportedBy = $disciplineId ?? $guidanceId;
        if (!$reportedBy) {
            // Fallback: use user id if neither discipline nor guidance exists
            $reportedBy = $user ? $user->id : 1;
        }
        $archiveData = [
            'counseling_session_id' => $counselingSession->id,
            'student_id' => $counselingSession->student_id,
            'counselor_id' => $counselingSession->counselor_id,
            'title' => 'Counseling Session Rejection',
            'description' => $counselingSession->reason ?? $counselingSession->notes ?? 'Rejected counseling session',
            'reason' => $counselingSession->reason,
            'notes' => $counselingSession->notes,
            'feedback' => $request->feedback,
            'archived_at' => now(),
            'violation_date' => $counselingSession->scheduled_date ?? now(),
            'reported_by' => $reportedBy,
        ];

        // Use ArchiveViolation model to store archive
        \App\Models\ArchiveViolation::create($archiveData);

        // Update the session status to rejected
        $counselingSession->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Counseling session rejected and archived with feedback.'
        ]);
    }
        /**
     * API: Get counseling session details (for modal)
     */
    public function apiShowCounselingSession($id)
    {
        $session = \App\Models\CounselingSession::with(['student', 'counselor'])
            ->find($id);
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found.']);
        }
        $student = $session->student;
        $counselor = $session->counselor;
        $documentsHtml = '';
        // If you have documents, render them here. Otherwise, show placeholder.
        // Example: $documentsHtml = '<a href="/path/to/doc.pdf">Document.pdf</a>';
        return response()->json([
            'success' => true,
            'session' => [
                'session_no' => $session->session_no,
                'status_display' => ucfirst($session->status),
                'reason' => $session->reason,
                'notes' => $session->notes,
                'scheduled_date' => $session->start_date ? $session->start_date->format('Y-m-d') : null,
                'scheduled_time' => $session->time ? $session->time->format('H:i') : null,
                'location' => $session->location,
                'counselor_name' => $counselor ? ($counselor->first_name . ' ' . $counselor->last_name) : null,
                'student_full_name' => $student ? ($student->full_name ?? ($student->first_name . ' ' . $student->last_name)) : null,
                'student_lrn' => $student ? $student->lrn : null,
                'student_birthdate' => ($student && $student->date_of_birth) ? (method_exists($student->date_of_birth, 'format') ? $student->date_of_birth->format('F j, Y') : (string)$student->date_of_birth) : null,
                'student_gender' => $student ? $student->gender : null,
                'student_nationality' => $student ? $student->nationality : null,
                'student_religion' => $student ? $student->religion : null,
                'student_photo_url' => $student && $student->photo_url ? $student->photo_url : null,
                'documents_html' => $documentsHtml,
            ]
        ]);
    }
      /**
     * Store counseling summary report for a session
     */
    public function createCounselingSummaryReport(Request $request, CounselingSession $counselingSession)
    {
        try {
            if (!$counselingSession) {
                \Log::error('CounselingSession not found for summary report', ['id' => $request->route('counselingSession')]);
                return response()->json([
                    'success' => false,
                    'message' => 'Counseling session not found.'
                ], 404);
            }
            $validatedData = $request->validate([
                'counseling_summary_report' => 'required|string',
            ]);
            $counselingSession->counseling_summary_report = $validatedData['counseling_summary_report'];
            $counselingSession->save();
            // Always return JSON for AJAX or fetch requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Counseling summary report saved successfully.',
                    'counselingSession' => $counselingSession
                ]);
            }
            // Fallback for non-AJAX requests
            return redirect()->route('guidance.counseling-sessions.index')
                ->with('success', 'Counseling summary report saved successfully.');
        } catch (\Exception $e) {
            \Log::error('Error saving counseling summary report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            // Always return JSON for AJAX or fetch requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error saving summary report.'
                ], 500);
            }
            // Fallback for non-AJAX requests
            return redirect()->back()->with('error', 'Error saving summary report.');
        }
    }
        /**
     * API: Get all unique sanctions for dropdowns (AJAX)
     */
    public function sanctionList()
    {
        $sanctions = \App\Models\Sanction::query()
            ->select('sanction')
            ->distinct()
            ->orderBy('sanction')
            ->pluck('sanction');
        return response()->json(['success' => true, 'sanctions' => $sanctions]);
    }
}
