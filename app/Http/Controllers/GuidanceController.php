<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Guidance;
use App\Models\User;
use App\Models\Student;
use App\Models\CaseMeeting;
use App\Models\CounselingSession;
use App\Models\ArchivedCounselingSession;
use App\Models\ArchivedMeeting;
use App\Models\Violation;
use App\Models\FacultyAssignment;
use App\Models\Sanction;
use Carbon\Carbon;

class GuidanceController extends Controller
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
        // Count unique students with at least one violation
        $studentsWithDisciplinaryRecord = Violation::distinct('student_id')->count('student_id');

        $stats = [
            'total_students' => $totalStudents,
            'active_case_meetings' => $activeCaseMeetings,
            'completed_counseling_sessions' => $completedCounselingSessions,
            'pending_cases' => $pendingCases,
            'scheduled_counseling' => $scheduledCounseling,
            'students_with_disciplinary_record' => $studentsWithDisciplinaryRecord,
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
        $caseMeetings = CaseMeeting::with(['student', 'counselor', 'sanctions', 'violation'])
            ->where('case_meetings.status', '!=', 'case_closed')
            ->leftJoin('student_violations', 'case_meetings.violation_id', '=', 'student_violations.id')
            ->orderByRaw('CASE WHEN student_violations.reported_by IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('case_meetings.created_at', 'desc')
            ->select('case_meetings.*')
            ->paginate(10);

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

        // Convert time from 12-hour format (h:i A) to 24-hour format (H:i:s) for MySQL
        if (isset($validatedData['scheduled_time'])) {
            try {
                $validatedData['scheduled_time'] = \Carbon\Carbon::createFromFormat('h:i A', $validatedData['scheduled_time'])->format('H:i:s');
            } catch (\Exception $e) {
                // Fallback if already in 24-hour format or different format
                $validatedData['scheduled_time'] = date('H:i:s', strtotime($validatedData['scheduled_time']));
            }
        }

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

        // Get the student to find their class adviser
        $student = Student::find($validatedData['student_id']);
        if ($student) {
            \Log::info('Found student for adviser lookup', [
                'student_id' => $student->id,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'academic_year' => $student->academic_year
            ]);
            
            // Find the class adviser for this student's grade level and section
            $advisoryAssignment = FacultyAssignment::where('grade_level', $student->grade_level)
                ->where('section', $student->section)
                ->where('academic_year', $student->academic_year)
                ->where('assignment_type', 'class_adviser')
                ->where('status', 'active')
                ->with(['teacher.user'])
                ->first();
            
            \Log::info('Advisory assignment search result', [
                'found' => $advisoryAssignment ? 'yes' : 'no',
                'assignment_id' => $advisoryAssignment->id ?? null,
                'teacher_id' => $advisoryAssignment->teacher_id ?? null
            ]);
            
            if ($advisoryAssignment && $advisoryAssignment->teacher && $advisoryAssignment->teacher->user) {
                $validatedData['adviser_id'] = $advisoryAssignment->teacher->user->id;
                \Log::info('Set adviser_id', ['adviser_id' => $validatedData['adviser_id']]);
            } else {
                \Log::warning('Could not find adviser for student', [
                    'student_grade_level' => $student->grade_level,
                    'student_section' => $student->section,
                    'academic_year' => $student->academic_year
                ]);
            }
        }

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
                'meeting' => $caseMeeting,
                'meeting_id' => $caseMeeting->id
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
            'completed_at' => $this->schoolNow(),
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

        // Automatically create sanctions based on selected interventions
        $this->createAutomaticSanctions($caseMeeting, $updateData);

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
     * View case summary for AJAX requests
     */
    public function viewCaseSummary(CaseMeeting $caseMeeting)
    {
        $caseMeeting->load(['student', 'counselor', 'sanctions', 'violation']);

        return response()->json([
            'success' => true,
            'meeting' => [
                'id' => $caseMeeting->id,
                'student' => $caseMeeting->student ? [
                    'id' => $caseMeeting->student->id,
                    'full_name' => $caseMeeting->student->full_name,
                    'student_id' => $caseMeeting->student->student_id,
                    'grade_level' => $caseMeeting->student->grade_level,
                ] : null,
                'meeting_type' => $caseMeeting->meeting_type ?? 'case_meeting',
                'scheduled_date' => $caseMeeting->scheduled_date,
                'scheduled_time' => $caseMeeting->scheduled_time,
                'violation_id' => $caseMeeting->violation_id,
                'summary' => $caseMeeting->summary,
                'recommendations' => $caseMeeting->recommendations,
                'notes' => $caseMeeting->notes,
                'president_notes' => $caseMeeting->president_notes,
                'student_statement' => $caseMeeting->violation ? $caseMeeting->violation->student_statement : null,
                'incident_feelings' => $caseMeeting->violation ? $caseMeeting->violation->incident_feelings : null,
                'action_plan' => $caseMeeting->violation ? $caseMeeting->violation->action_plan : null,
                'teacher_statement' => $caseMeeting->teacher_statement,
                'violation' => $caseMeeting->violation ? [
                    'student_attachment_path' => $caseMeeting->violation->student_attachment_path,
                ] : null,
                'sanctions' => $caseMeeting->sanctions->map(function ($sanction) {
                    return [
                        'id' => $sanction->id,
                        'sanction' => $sanction->sanction,
                        'deportment_grade_action' => $sanction->deportment_grade_action,
                        'suspension' => $sanction->suspension,
                        'notes' => $sanction->notes,
                        'is_approved' => $sanction->is_approved,
                        'approved_at' => $sanction->approved_at,
                    ];
                }),
            ]
        ]);
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
                    'action_plan' => $caseMeeting->action_plan,
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
            ->paginate(10);

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

        // Convert time from 12-hour format (h:i A) to 24-hour format (H:i:s) for MySQL
        if (isset($validatedData['scheduled_time'])) {
            try {
                $validatedData['scheduled_time'] = \Carbon\Carbon::createFromFormat('h:i A', $validatedData['scheduled_time'])->format('H:i:s');
            } catch (\Exception $e) {
                // Fallback if already in 24-hour format or different format
                $validatedData['scheduled_time'] = date('H:i:s', strtotime($validatedData['scheduled_time']));
            }
        }

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

        $filename = 'case_meetings_' . $this->schoolNow()->format('Y-m-d_H-i-s') . '.csv';

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

        // Basic requirements: summary and schedule
        if (empty($caseMeeting->summary) || $caseMeeting->status !== 'pre_completed') {
            Log::warning('Forward blocked: basic requirements not met', ['id' => $caseMeeting->id]);
            return response()->json([
                'success' => false,
                'message' => 'Please add both a schedule and a summary report before forwarding.'
            ], 400);
        }

        // Check for required attachments and replies
        $missingItems = [];

        // Check if student narrative report exists and has student reply
        $violation = $caseMeeting->violation;
        if ($violation && $violation->severity === 'major') {
            // For major violations, student narrative report is required
            if (empty($violation->student_statement) && empty($violation->incident_feelings) && empty($violation->action_plan)) {
                $missingItems[] = 'Student Narrative Report (student reply required)';
            }
        }

        // Check if teacher observation report exists and has teacher reply
        if (empty($caseMeeting->teacher_statement) && empty($caseMeeting->action_plan)) {
            $missingItems[] = 'Teacher Observation Report (teacher reply required)';
        }

        // Check if disciplinary conference report can be generated (requires summary)
        if (empty($caseMeeting->summary)) {
            $missingItems[] = 'Disciplinary Conference Report (summary required)';
        }

        // If there are missing attachments/replies, prevent forwarding
        if (!empty($missingItems)) {
            Log::warning('Forward blocked: missing attachments/replies', [
                'id' => $caseMeeting->id,
                'missing_items' => $missingItems
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Cannot forward to president. Missing: ' . implode(', ', $missingItems) . '. Please ensure all required reports are completed with proper replies.'
            ], 400);
        }


        $caseMeeting->update([
            'status' => 'submitted',
            'forwarded_to_president' => true,
            'forwarded_at' => $this->schoolNow(),
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
            ->where('status', '!=', 'completed')
            ->orderByRaw("CASE WHEN status = 'recommended' THEN 0 ELSE 1 END, start_date DESC")
            ->paginate(10);

        $scheduledSessions = CounselingSession::with('student')
            ->where('status', 'scheduled')
            ->orderBy('start_date', 'desc')
            ->paginate(10, ['*'], 'scheduled_page');

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
     * Display archived counseling sessions and case meetings
     */
    public function archivedCounselingSessions()
    {
        // Get completed counseling sessions from main table
        $completedSessions = CounselingSession::with(['student', 'counselor', 'recommender'])
            ->where('status', 'completed')
            ->get()
            ->map(function ($session) {
                return (object) [
                    'id' => $session->id,
                    'type' => 'counseling_session',
                    'student_name' => $session->student ? ($session->student->first_name . ' ' . $session->student->last_name) : null,
                    'student_id_number' => $session->student ? $session->student->student_id : null,
                    'counselor_name' => $session->counselor ? ($session->counselor->first_name . ' ' . $session->counselor->last_name) : null,
                    'recommended_by_name' => $session->recommender ? $session->recommender->name : null,
                    'session_no' => $session->session_no,
                    'start_date' => $session->start_date ? \Carbon\Carbon::parse($session->start_date) : null,
                    'time' => $session->time,
                    'status' => $session->status,
                    'archived_at' => $session->completed_at ? \Carbon\Carbon::parse($session->completed_at) : $session->updated_at,
                    'archive_reason' => 'completed',
                    'archived_by' => 'System',
                    'counseling_summary_report' => $session->session_summary,
                    'original_session_id' => $session->id,
                    'is_from_main_table' => true,
                    'meeting_type' => null,
                    'violation_description' => null
                ];
            });

        // Get completed case meetings from main table
        $completedMeetings = CaseMeeting::with(['student', 'counselor', 'violation'])
            ->where('status', 'completed')
            ->get()
            ->map(function ($meeting) {
                return (object) [
                    'id' => $meeting->id,
                    'type' => 'case_meeting',
                    'student_name' => $meeting->student ? ($meeting->student->first_name . ' ' . $meeting->student->last_name) : null,
                    'student_id_number' => $meeting->student ? $meeting->student->student_id : null,
                    'counselor_name' => $meeting->counselor ? ($meeting->counselor->first_name . ' ' . $meeting->counselor->last_name) : null,
                    'recommended_by_name' => null,
                    'session_no' => null,
                    'start_date' => $meeting->scheduled_date ? \Carbon\Carbon::parse($meeting->scheduled_date) : null,
                    'time' => $meeting->scheduled_time,
                    'status' => $meeting->status,
                    'archived_at' => $meeting->completed_at ? \Carbon\Carbon::parse($meeting->completed_at) : $meeting->updated_at,
                    'archive_reason' => 'completed',
                    'archived_by' => 'System',
                    'counseling_summary_report' => $meeting->summary,
                    'original_session_id' => $meeting->id,
                    'is_from_main_table' => true,
                    'meeting_type' => $meeting->meeting_type ?? 'Case Meeting',
                    'violation_description' => $meeting->violation ? $meeting->violation->description : null
                ];
            });

        // Get archived sessions from archived table
        $archivedSessions = ArchivedCounselingSession::orderBy('archived_at', 'desc')
            ->get()
            ->map(function ($session) {
                $session->type = 'archived_counseling_session';
                $session->meeting_type = null;
                $session->violation_description = null;
                return $session;
            });

        // Get archived meetings from archived table
        $archivedMeetings = ArchivedMeeting::orderBy('archived_at', 'desc')
            ->get()
            ->map(function ($meeting) {
                // Fetch related data using stored IDs
                $student = $meeting->student_id ? Student::find($meeting->student_id) : null;
                $counselor = $meeting->counselor_id ? Guidance::find($meeting->counselor_id) : null;
                $violation = $meeting->violation_id ? Violation::find($meeting->violation_id) : null;
                
                return (object) [
                    'id' => $meeting->id,
                    'type' => 'archived_case_meeting',
                    'student_name' => $student ? ($student->first_name . ' ' . $student->last_name) : null,
                    'student_id_number' => $student ? $student->student_id : null,
                    'counselor_name' => $counselor ? ($counselor->first_name . ' ' . $counselor->last_name) : null,
                    'recommended_by_name' => null,
                    'session_no' => null,
                    'start_date' => $meeting->scheduled_date ? \Carbon\Carbon::parse($meeting->scheduled_date) : null,
                    'time' => $meeting->scheduled_time,
                    'status' => $meeting->status,
                    'archived_at' => $meeting->archived_at,
                    'archive_reason' => $meeting->archive_reason,
                    'archived_by' => $meeting->archived_by,
                    'counseling_summary_report' => $meeting->summary,
                    'original_session_id' => $meeting->original_case_meeting_id,
                    'is_from_main_table' => false,
                    'meeting_type' => $meeting->meeting_type ?? 'Case Meeting',
                    'violation_description' => $violation ? $violation->description : null
                ];
            });

        // Combine all collections
        $allRecords = $completedSessions->concat($completedMeetings)
            ->concat($archivedSessions)
            ->concat($archivedMeetings)
            ->sortByDesc('archived_at')
            ->values();

        // Manual pagination
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedRecords = $allRecords->slice($offset, $perPage);
        
        $archivedSessions = new LengthAwarePaginator(
            $paginatedRecords,
            $allRecords->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        // Archive password
        $archivePassword = 'nsmsguidance';

        return view('guidance.archived-counseling-sessions', compact('archivedSessions', 'archivePassword'));
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

        // Convert time from 12-hour format (h:i A) to 24-hour format (H:i:s) for MySQL
        if (isset($validatedData['scheduled_time'])) {
            try {
                $validatedData['scheduled_time'] = \Carbon\Carbon::createFromFormat('h:i A', $validatedData['scheduled_time'])->format('H:i:s');
            } catch (\Exception $e) {
                // Fallback if already in 24-hour format or different format
                $validatedData['scheduled_time'] = date('H:i:s', strtotime($validatedData['scheduled_time']));
            }
        }

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
            'scheduled_time' => 'required',
            'duration' => 'required|integer|min:15|max:240',
            'location' => 'nullable|string|max:255',
        ]);

        // Convert time from 12-hour format (h:i A) to 24-hour format (H:i:s) for MySQL
        if (isset($validatedData['scheduled_time'])) {
            try {
                $validatedData['scheduled_time'] = \Carbon\Carbon::createFromFormat('h:i A', $validatedData['scheduled_time'])->format('H:i:s');
            } catch (\Exception $e) {
                // Fallback if already in 24-hour format or different format
                $validatedData['scheduled_time'] = date('H:i:s', strtotime($validatedData['scheduled_time']));
            }
        }

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

        // Convert time from 12-hour format (h:i A) to 24-hour format (H:i:s) for MySQL
        $scheduledTime = $request->scheduled_time;
        try {
            $scheduledTime = \Carbon\Carbon::createFromFormat('h:i A', $scheduledTime)->format('H:i:s');
        } catch (\Exception $e) {
            // Fallback if already in 24-hour format or different format
            $scheduledTime = date('H:i:s', strtotime($scheduledTime));
        }

        $session->update([
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $scheduledTime,
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

        // Convert time from 12-hour format (h:i A) to 24-hour format (H:i:s) for MySQL
        if (isset($validatedData['scheduled_time'])) {
            try {
                $validatedData['scheduled_time'] = \Carbon\Carbon::createFromFormat('h:i A', $validatedData['scheduled_time'])->format('H:i:s');
            } catch (\Exception $e) {
                // Fallback if already in 24-hour format or different format
                $validatedData['scheduled_time'] = date('H:i:s', strtotime($validatedData['scheduled_time']));
            }
        }

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

        // Optionally archive the session immediately (uncomment if you want immediate archiving)
        // $this->archiveCompletedSession($counselingSession);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Counseling session marked as completed and will appear in archived sessions.',
                'counselingSession' => $counselingSession
            ]);
        }

        return redirect()->route('guidance.counseling-sessions.index')
            ->with('success', 'Counseling session marked as completed and will appear in archived sessions.');
    }

    /**
     * Archive a completed counseling session
     */
    private function archiveCompletedSession(CounselingSession $session)
    {
        $archivedData = [
            'original_session_id' => $session->id,
            'counseling_summary_report' => $session->session_summary,
            'student_id' => $session->student_id,
            'counselor_id' => $session->counselor_id,
            'recommended_by' => $session->recommended_by,
            'start_date' => $session->start_date,
            'end_date' => $session->end_date,
            'frequency' => $session->frequency,
            'time_limit' => $session->time_limit,
            'time' => $session->time,
            'session_no' => $session->session_no,
            'status' => $session->status,
            'referral_academic' => $session->referral_academic,
            'referral_academic_other' => $session->referral_academic_other,
            'referral_social' => $session->referral_social,
            'referral_social_other' => $session->referral_social_other,
            'incident_description' => $session->incident_description,
            'archived_at' => now(),
            'archive_reason' => 'completed',
            'archive_notes' => 'Automatically archived upon completion',
            'archived_by' => 'System',
            'student_name' => $session->student ? ($session->student->first_name . ' ' . $session->student->last_name) : null,
            'student_id_number' => $session->student ? $session->student->student_id : null,
            'counselor_name' => $session->counselor ? ($session->counselor->first_name . ' ' . $session->counselor->last_name) : null,
            'recommended_by_name' => $session->recommender ? $session->recommender->name : null,
            'original_created_at' => $session->created_at,
            'original_updated_at' => $session->updated_at,
        ];

        ArchivedCounselingSession::create($archivedData);

        // Optionally delete the original session after archiving
        // $session->delete();
    }

    /**
     * Reschedule counseling session
     */
    public function rescheduleCounselingSession(Request $request, CounselingSession $counselingSession)
    {
        $validatedData = $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required',
        ]);

        // Convert time from 12-hour format (h:i A) to 24-hour format (H:i:s) for MySQL
        if (isset($validatedData['scheduled_time'])) {
            try {
                $validatedData['scheduled_time'] = \Carbon\Carbon::createFromFormat('h:i A', $validatedData['scheduled_time'])->format('H:i:s');
            } catch (\Exception $e) {
                // Fallback if already in 24-hour format or different format
                $validatedData['scheduled_time'] = date('H:i:s', strtotime($validatedData['scheduled_time']));
            }
        }

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

        $filename = 'counseling_sessions_' . $this->schoolNow()->format('Y-m-d_H-i-s') . '.csv';

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
            'archived_at' => $this->schoolNow(),
            'violation_date' => $session->start_date ?? $this->schoolNow(),
            'reported_by' => $reportedBy,
            'feedback' => null,
        ];
        \App\Models\ArchiveViolation::create($archiveData);

        return response()->json(['success' => true]);
    }

        /**
     * API: Get counseling session details (for modal)
     */
    public function apiShowCounselingSession($id)
    {
        try {
            $session = \App\Models\CounselingSession::with(['student', 'counselor', 'recommender'])
                ->find($id);
            if (!$session) {
                return response()->json(['success' => false, 'message' => 'Session not found.']);
            }
            $student = $session->student;
            $counselor = $session->counselor;
            $documentsHtml = '';
            
            // Build documents HTML with additional reports (main PDF is handled by frontend)
            $documents = [];
            
            // Counseling Summary (if session has summary/notes)
            if (!empty($session->summary) || !empty($session->notes)) {
                $documents[] = '<a href="#" onclick="alert(\'Counseling Summary: ' . addslashes($session->summary ?? $session->notes ?? '') . '\')" class="btn btn-outline-info btn-sm"><i class="ri-file-text-line me-2"></i>Counseling Summary</a>';
            }
            
            // Join documents with line breaks
            if (!empty($documents)) {
                $documentsHtml = implode('<br>', $documents);
            }
            
            // Calculate all scheduled dates based on frequency
            $scheduledDates = [];
            if ($session->start_date && $session->end_date && $session->frequency) {
                $currentDate = $session->start_date->copy();
                $endDate = $session->end_date;
                
                // Convert frequency to number of days
                $dayInterval = 1; // default daily
                if (is_numeric($session->frequency)) {
                    $dayInterval = (int)$session->frequency;
                } else {
                    // Handle string frequency values
                    $frequencyMap = [
                        'daily' => 1,
                        'every_other_day' => 2,
                        'once_a_week' => 7,
                        'twice_a_week' => 3, // approximate
                        'monthly' => 30,
                    ];
                    $dayInterval = $frequencyMap[$session->frequency] ?? 1;
                }
                
                while ($currentDate <= $endDate) {
                    $scheduledDates[] = $currentDate->format('Y-m-d');
                    $currentDate->addDays($dayInterval);
                }
            } elseif ($session->start_date) {
                $scheduledDates[] = $session->start_date->format('Y-m-d');
            }
            
            // Build student name safely
            $studentFullName = null;
            if ($student) {
                $studentFullName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
                if (empty($studentFullName)) {
                    $studentFullName = $student->lrn ?? 'Unknown';
                }
            }
            
            return response()->json([
                'success' => true,
                'session' => [
                    'session_no' => $session->session_no,
                    'status_display' => ucfirst($session->status ?? 'scheduled'),
                    'reason' => $session->reason,
                    'notes' => $session->notes,
                    'scheduled_date' => $session->start_date ? $session->start_date->format('Y-m-d') : null,
                    'scheduled_dates' => $scheduledDates,
                    'scheduled_time' => $session->time ? $session->time->format('H:i') : null,
                    'location' => $session->location ?? null,
                    'recommended_by_name' => $session->recommender ? $session->recommender->name : null,
                    'student_full_name' => $studentFullName,
                    'student_lrn' => $student ? $student->lrn : null,
                    'student_birthdate' => ($student && $student->date_of_birth) ? (method_exists($student->date_of_birth, 'format') ? $student->date_of_birth->format('F j, Y') : (string)$student->date_of_birth) : null,
                    'student_gender' => $student ? $student->gender : null,
                    'student_nationality' => $student ? $student->nationality : null,
                    'student_religion' => $student ? $student->religion : null,
                    'student_photo_url' => $student && $student->photo_url ? $student->photo_url : null,
                    'student_age' => $student && $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->age : null,
                    'student' => $student ? [
                        'student_id' => $student->student_id,
                        'grade_level' => $student->grade_level,
                        'id' => $student->id,
                        'lrn' => $student->lrn,
                        'full_name' => $studentFullName
                    ] : null,
                    'student_type_badge' => 'New',
                    'student_type_desc' => '',
                    'documents_html' => $documentsHtml,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in apiShowCounselingSession: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error loading session details: ' . $e->getMessage()
            ], 500);
        }
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
            $counselingSession->status = 'completed';
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
       /**
     * API: Get case status counts for dashboard pie chart (dynamic filtering)
     */
    public function getCaseStatusStats(Request $request)
    {
        $period = $request->get('period', 'month');
        
        // Calculate date filter
        $startDate = $this->getDateRangeStart($period);
        
        // Build query with date filter
        $query = \App\Models\CaseMeeting::query();
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        $onGoing = (clone $query)->where('status', 'in_progress')->count();
        $scheduled = (clone $query)->where('status', 'scheduled')->count();
        $preCompleted = (clone $query)->where('status', 'pre_completed')->count();
        $closed = (clone $query)->where('status', 'case_closed')->count();
        
        return response()->json([
            'success' => true,
            'on_going_cases' => $onGoing,
            'scheduled_meeting' => $scheduled,
            'pre_completed' => $preCompleted,
            'closed_cases' => $closed,
            'period' => $period
        ]);
    }
        /**
     * API: Get closed cases per month for bar chart (dynamic filtering)
     */
    public function getClosedCasesStats(Request $request)
    {
        $period = $request->get('period', '6months');
        $view = $request->get('view', 'monthly');
        
        $monthsBack = match($period) {
            '3months' => 3,
            '6months' => 6,
            '12months' => 12,
            '24months' => 24,
            default => 6
        };
        
        $labels = [];
        $data = [];
        
        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $now = $this->schoolNow();
            $month = $now->copy()->subMonths($i);
            
            if ($view === 'quarterly' && $monthsBack >= 12) {
                // Group by quarters for longer periods
                if ($i % 3 === 2) {
                    $labels[] = 'Q' . ceil((13 - $i) / 3) . ' ' . $month->format('Y');
                    $quarterStart = $month->copy()->startOfQuarter();
                    $quarterEnd = $month->copy()->endOfQuarter();
                    
                    $count = \App\Models\CaseMeeting::where('status', 'case_closed')
                        ->whereBetween('completed_at', [$quarterStart, $quarterEnd])
                        ->count();
                    $data[] = $count;
                }
            } else {
                // Monthly view (default)
                $labels[] = $month->format('M Y');
                $count = \App\Models\CaseMeeting::where('status', 'case_closed')
                    ->whereYear('completed_at', $month->year)
                    ->whereMonth('completed_at', $month->month)
                    ->count();
                $data[] = $count;
            }
        }
        
        return response()->json([
            'success' => true,
            'labels' => $labels,
            'data' => $data,
            'period' => $period,
            'view' => $view
        ]);
    }
        /**
     * API: Get counseling sessions per month for bar chart (dynamic filtering)
     */
    public function getCounselingSessionsStats(Request $request)
    {
        $period = $request->get('period', '6months');
        $status = $request->get('status', 'all');
        
        $monthsBack = match($period) {
            '3months' => 3,
            '6months' => 6,
            '12months' => 12,
            '24months' => 24,
            default => 6
        };
        
        $labels = [];
        $data = [];
        
        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $now = $this->schoolNow();
            $month = $now->copy()->subMonths($i);
            $labels[] = $month->format('M Y');
            
            $query = \App\Models\CounselingSession::whereYear('start_date', $month->year)
                ->whereMonth('start_date', $month->month);
            
            if ($status !== 'all') {
                $query->where('status', $status);
            }
            
            $count = $query->count();
            $data[] = $count;
        }
        
        return response()->json([
            'success' => true,
            'labels' => $labels,
            'data' => $data,
            'period' => $period,
            'status' => $status
        ]);
    }
    
public function getDisciplineVsTotalStats(Request $request)
{
    $period = $request->get('period', '5years');
    $view = $request->get('view', 'comparison');
    
    $yearsBack = match($period) {
        '3years' => 3,
        '5years' => 5,
        '10years' => 10,
        default => 5
    };
    
    $now = $this->schoolNow();
    $currentYear = $now->year;
    $years = [];
    $withDiscipline = [];
    $totalStudents = [];
    $percentages = [];
    
    for ($i = $currentYear - $yearsBack; $i <= $currentYear; $i++) {
        $years[] = (string)$i;
        
        // Only count violations with a valid violation_date in this year
        $disciplineCount = \App\Models\Violation::whereNotNull('violation_date')
            ->whereYear('violation_date', $i)
            ->distinct('student_id')
            ->count('student_id');
            
        // Count all students created up to and including this year
        $studentCount = \App\Models\Student::whereYear('created_at', '<=', $i)
            ->count();
        
        $withDiscipline[] = $disciplineCount;
        $totalStudents[] = $studentCount;
        
        // Calculate percentage for trend analysis
        $percentage = $studentCount > 0 ? round(($disciplineCount / $studentCount) * 100, 1) : 0;
        $percentages[] = $percentage;
    }
    
    $data = match($view) {
        'percentage' => [
            'percentages' => $percentages,
        ],
        'discipline_only' => [
            'with_discipline' => $withDiscipline,
        ],
        'comparison' => [
            'with_discipline' => $withDiscipline,
            'total_students' => $totalStudents,
        ],
        default => [
            'with_discipline' => $withDiscipline,
            'total_students' => $totalStudents,
        ]
    };
    
    return response()->json([
        'success' => true,
        'labels' => $years,
        'data' => $data,
        'period' => $period,
        'view' => $view
    ]);
}    // Weekly violation list for dashboard
        // Weekly violation list for dashboard
    // API: Get Top Cases for dashboard (dynamic filtering)
    public function getTopCases(Request $request)
    {
        $dateRange = $request->get('date_range', 'month');
        $limit = $request->get('limit', 5);
        
        // Calculate date filter
        $startDate = $this->getDateRangeStart($dateRange);
        
        // Build query with date filter
        $query = \App\Models\Violation::with('student')
            ->selectRaw('student_id, title as case_title, COUNT(*) as count');
            
        if ($startDate) {
            $query->where('violation_date', '>=', $startDate);
        }
        
        $topCases = $query->groupBy('student_id', 'case_title')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        $formatted = $topCases->map(function($c) {
            $student = $c->student;
            return [
                'student_name' => $student ? ($student->first_name . ' ' . $student->last_name) : 'Unknown Student',
                'case_title' => $c->case_title,
                'count' => $c->count,
            ];
        });

        return response()->json([
            'success' => true,
            'cases' => $formatted,
            'date_range' => $dateRange,
            'limit' => $limit
        ]);
    }

    /**
     * API: Get violation trends by category (dynamic filtering)
     */
    public function getViolationTrends(Request $request)
    {
        $period = $request->get('period', '12months');
        $chartType = $request->get('chart_type', 'line');
        $severity = $request->get('severity', 'all');
        
        // Validate chart type - only allow line and bar
        if (!in_array($chartType, ['line', 'bar'])) {
            $chartType = 'line';
        }
        
        // Validate severity - exclude 'severe' option
        if (!in_array($severity, ['all', 'minor', 'major'])) {
            $severity = 'all';
        }
        
        // Calculate period range
        $monthsBack = match($period) {
            '6months' => 6,
            '12months' => 12,
            '24months' => 24,
            '36months' => 36,
            default => 12
        };
        
        $labels = [];
        $violations = [];
        
        // Build query with filters
        $query = \App\Models\Violation::query();
        
        if ($severity !== 'all' && in_array($severity, ['minor', 'major'])) {
            $query->where('severity', $severity);
        }
        
        // Get data by months
        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $now = $this->schoolNow();
            $date = $now->copy()->subMonths($i);
            
            $labels[] = $date->format('M Y');
            
            $count = (clone $query)
                ->whereYear('violation_date', $date->year)
                ->whereMonth('violation_date', $date->month)
                ->count();
            $violations[] = $count;
        }

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'data' => $violations,
            'chart_type' => $chartType,
            'severity' => $severity,
            'period' => $period
        ]);
    }

    /**
     * API: Get violation severity distribution
     */
    public function getViolationSeverity()
    {
        $severities = \App\Models\Violation::select('severity', \DB::raw('count(*) as count'))
            ->whereNotNull('severity')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $severities
        ]);
    }

    /**
     * API: Get counseling session effectiveness (dynamic filtering)
     */
    public function getCounselingEffectiveness(Request $request)
    {
        $period = $request->get('period', 'month');
        
        // Calculate date filter
        $startDate = $this->getDateRangeStart($period);
        
        // Build query with filters
        $query = \App\Models\CounselingSession::query();
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        $total = $query->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $scheduled = (clone $query)->where('status', 'scheduled')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $in_progress = (clone $query)->where('status', 'in_progress')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'completed' => $completed,
                'scheduled' => $scheduled,
                'cancelled' => $cancelled,
                'in_progress' => $in_progress,
                'total' => $total,
                'effectiveness_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                'period' => $period
            ]
        ]);
    }

    /**
     * API: Get recent activities for timeline
     */
    public function getRecentActivities(Request $request)
    {
        $dateRange = $request->get('date_range', 'week');
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');
        $contentTypes = explode(',', $request->get('content_types', 'caseMeetings,counseling,violations,activities'));
        
        // Calculate date filter
        $startDate = $this->getDateRangeStart($dateRange);
        
        $activities = collect();

        // Recent case meetings (with filters)
        $caseMeetingsQuery = \App\Models\CaseMeeting::with(['student', 'counselor'])
            ->orderBy('created_at', 'desc');
            
        if ($startDate) {
            $caseMeetingsQuery->where('created_at', '>=', $startDate);
        }
        
        if ($status !== 'all') {
            $caseMeetingsQuery->where('status', $status);
        }
        
        if ($search) {
            $caseMeetingsQuery->whereHas('student', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }
        
        $caseMeetings = $caseMeetingsQuery->limit(5)->get()
            ->map(function($meeting) {
                return [
                    'type' => 'case_meeting',
                    'icon' => 'ri-calendar-event-line',
                    'color' => 'success',
                    'title' => 'Case Meeting Scheduled',
                    'description' => 'Meeting with ' . ($meeting->student->first_name ?? 'Unknown') . ' ' . ($meeting->student->last_name ?? ''),
                    'timestamp' => $meeting->created_at,
                    'human_time' => $meeting->created_at->diffForHumans()
                ];
            });

        // Recent counseling sessions
        $counselingSessions = \App\Models\CounselingSession::with(['student', 'counselor'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($session) {
                return [
                    'type' => 'counseling',
                    'icon' => 'ri-heart-pulse-line',
                    'color' => 'success',
                    'title' => 'Counseling Session',
                    'description' => 'Session ' . $session->session_no . ' with ' . ($session->student->first_name ?? 'Unknown'),
                    'timestamp' => $session->created_at,
                    'human_time' => $session->created_at->diffForHumans()
                ];
            });

        // Recent violations
        $violations = \App\Models\Violation::with('student')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($violation) {
                return [
                    'type' => 'violation',
                    'icon' => 'ri-error-warning-line',
                    'color' => 'danger',
                    'title' => 'New Violation Reported',
                    'description' => $violation->case_title . ' - ' . ($violation->student->first_name ?? 'Unknown'),
                    'timestamp' => $violation->created_at,
                    'human_time' => $violation->created_at->diffForHumans()
                ];
            });

        // Merge and sort by timestamp
        $activities = $activities->concat($caseMeetings)
            ->concat($counselingSessions)
            ->concat($violations)
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();

        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }

    /**
     * API: Get upcoming tasks and reminders
     */
    public function getUpcomingTasks(Request $request)
    {
        $dateRange = $request->get('date_range', 'week');
        $status = $request->get('status', 'all');
        $priority = $request->get('priority', 'all');
        $search = $request->get('search', '');
        
        // Calculate date filter
        $startDate = $this->getDateRangeStart($dateRange);
        $now = $this->schoolNow();
        $endDate = $dateRange === 'today' ? $now->copy()->endOfDay() : 
                  ($dateRange === 'week' ? $now->copy()->endOfWeek() : 
                   ($dateRange === 'month' ? $now->copy()->endOfMonth() : $now->copy()->addWeek()));
        
        $tasks = collect();

        // Upcoming case meetings
        $upcomingMeetings = \App\Models\CaseMeeting::with(['student'])
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', $this->schoolNow())
            ->where('scheduled_date', '<=', $this->schoolNow()->addWeek())
            ->orderBy('scheduled_date', 'asc')
            ->get()
            ->map(function($meeting) {
                return [
                    'type' => 'meeting',
                    'title' => 'Case Meeting',
                    'student' => ($meeting->student->first_name ?? 'Unknown') . ' ' . ($meeting->student->last_name ?? ''),
                    'date' => $meeting->scheduled_date,
                    'time' => $meeting->scheduled_time,
                    'priority' => 'medium',
                    'status' => $meeting->status
                ];
            });

        // Upcoming counseling sessions
        $upcomingSessions = \App\Models\CounselingSession::with(['student'])
            ->where('status', 'scheduled')
            ->where('start_date', '>=', $this->schoolNow())
            ->where('start_date', '<=', $this->schoolNow()->addWeek())
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(function($session) {
                return [
                    'type' => 'counseling',
                    'title' => 'Counseling Session',
                    'student' => ($session->student->first_name ?? 'Unknown') . ' ' . ($session->student->last_name ?? ''),
                    'date' => $session->start_date,
                    'time' => $session->time,
                    'priority' => 'medium',
                    'status' => $session->status
                ];
            });

        $tasks = $tasks->concat($upcomingMeetings)->concat($upcomingSessions)->sortBy('date');

        return response()->json([
            'success' => true,
            'tasks' => $tasks->values()
        ]);
    }

    /**
     * API: Get performance metrics for counselors
     */
    public function getCounselorPerformance()
    {
        $counselors = \App\Models\Guidance::where('is_active', true)
            ->with(['caseMeetings', 'counselingSessions'])
            ->get()
            ->map(function($counselor) {
                $totalMeetings = $counselor->caseMeetings->count();
                $completedMeetings = $counselor->caseMeetings->where('status', 'completed')->count();
                $totalSessions = $counselor->counselingSessions->count();
                $completedSessions = $counselor->counselingSessions->where('status', 'completed')->count();
                
                return [
                    'name' => $counselor->user->name ?? 'Unknown',
                    'total_meetings' => $totalMeetings,
                    'completed_meetings' => $completedMeetings,
                    'completion_rate' => $totalMeetings > 0 ? round(($completedMeetings / $totalMeetings) * 100, 1) : 0,
                    'total_sessions' => $totalSessions,
                    'session_completion_rate' => $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0
                ];
            });

        return response()->json([
            'success' => true,
            'counselors' => $counselors
        ]);
    }

    /**
     * Helper method to calculate date range start based on filter
     */
    private function getDateRangeStart($dateRange)
    {
        switch($dateRange) {
            case 'today':
                return $this->schoolNow()->startOfDay();
            case 'week':
                return $this->schoolNow()->startOfWeek();
            case 'month':
                return $this->schoolNow()->startOfMonth();
            case 'quarter':
                return $this->schoolNow()->startOfQuarter();
            case 'year':
                return $this->schoolNow()->startOfYear();
            default:
                return null; // 'all' or any other value
        }
    }

    /**
     * Forward Teacher Observation Report to adviser after scheduling a case meeting
     */
    public function forwardObservationReportToAdviser(Request $request, $caseMeetingId)
    {
        try {
            $caseMeeting = CaseMeeting::with(['adviser', 'student'])->findOrFail($caseMeetingId);
            
            // Check if case meeting has an adviser assigned
            if (!$caseMeeting->adviser_id || !$caseMeeting->adviser) {
                return response()->json([
                    'success' => false,
                    'message' => 'No adviser assigned to this case meeting.'
                ]);
            }

            // Here you would implement the actual forwarding logic
            // For example: sending an email notification to the adviser
            // Mail::to($caseMeeting->adviser->email)->send(new TeacherObservationReportMail($caseMeeting));
            
            // For now, we'll just return success
            return response()->json([
                'success' => true,
                'message' => 'Teacher Observation Report forwarded to ' . ($caseMeeting->adviser->full_name ?? $caseMeeting->adviser->name) . ' successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error forwarding observation report to adviser', [
                'case_meeting_id' => $caseMeetingId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error forwarding report to adviser.'
            ], 500);
        }
    }

    /**
     * Create automatic sanctions based on selected interventions
     */
    private function createAutomaticSanctions(CaseMeeting $caseMeeting, array $interventions)
    {
        // Clear existing automatic sanctions to avoid duplicates
        $caseMeeting->sanctions()->where('is_automatic', true)->delete();

        $sanctionsToCreate = [];

        // Written Reflection
        if ($interventions['written_reflection']) {
            $sanctionsToCreate[] = [
                'case_meeting_id' => $caseMeeting->id,
                'violation_id' => $caseMeeting->violation_id,
                'severity' => 'Low',
                'category' => 'Intervention',
                'major_category' => 'Written Reflection',
                'sanction' => 'Student must write a one-page reflection on the importance of respect, responsibility, and self-control.',
                'deportment_grade_action' => 'Intervention noted in record',
                'suspension' => 0,
                'notes' => 'Due date: ' . ($interventions['written_reflection_due'] ? date('M d, Y', strtotime($interventions['written_reflection_due'])) : date('M d, Y', strtotime('+7 days'))),
                'is_automatic' => true,
                'is_approved' => false,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Mentorship/Counseling
        if ($interventions['mentorship_counseling']) {
            $sanctionsToCreate[] = [
                'case_meeting_id' => $caseMeeting->id,
                'violation_id' => $caseMeeting->violation_id,
                'severity' => 'Medium',
                'category' => 'Intervention',
                'major_category' => 'Mentorship/Counseling',
                'sanction' => 'Weekly meetings with school counselor or mentor for behavior management and coping strategies.',
                'deportment_grade_action' => 'Counseling intervention recorded',
                'suspension' => 0,
                'notes' => 'Mentor: ' . ($interventions['mentor_name'] ?? 'TBD') . ' | Duration: 4 weeks',
                'is_automatic' => true,
                'is_approved' => false,
                'created_at' => $this->schoolNow(),
                'updated_at' => $this->schoolNow()
            ];
        }

        // Suspension
        if ($interventions['suspension']) {
            $suspensionDays = 3; // default
            if ($interventions['suspension_3days']) {
                $suspensionDays = 3;
            } elseif ($interventions['suspension_5days']) {
                $suspensionDays = 5;
            } elseif ($interventions['suspension_other_days']) {
                $suspensionDays = $interventions['suspension_other_days'];
            }

            $suspensionNotes = "Duration: {$suspensionDays} days";
            if ($interventions['suspension_start']) {
                $suspensionNotes .= " | Start: " . date('M d, Y', strtotime($interventions['suspension_start']));
            }
            if ($interventions['suspension_end']) {
                $suspensionNotes .= " | End: " . date('M d, Y', strtotime($interventions['suspension_end']));
            }
            if ($interventions['suspension_return']) {
                $suspensionNotes .= " | Return: " . date('M d, Y', strtotime($interventions['suspension_return']));
            }

            $sanctionsToCreate[] = [
                'case_meeting_id' => $caseMeeting->id,
                'violation_id' => $caseMeeting->violation_id,
                'severity' => 'High',
                'category' => 'Disciplinary Action',
                'major_category' => 'Suspension',
                'sanction' => "Student suspended from school for {$suspensionDays} days as consequence for actions. Must complete activity sheets for missed classes.",
                'suspension' => $suspensionDays,
                'deportment_grade_action' => 'Suspension recorded in deportment grade',
                'notes' => $suspensionNotes,
                'is_automatic' => true,
                'is_approved' => false,
                'created_at' => $this->schoolNow(),
                'updated_at' => $this->schoolNow()
            ];
        }

        // Expulsion
        if ($interventions['expulsion']) {
            $expulsionNotes = 'Expulsion Date: ' . ($interventions['expulsion_date'] ? date('M d, Y', strtotime($interventions['expulsion_date'])) : date('M d, Y'));
            
            $sanctionsToCreate[] = [
                'case_meeting_id' => $caseMeeting->id,
                'violation_id' => $caseMeeting->violation_id,
                'severity' => 'Critical',
                'category' => 'Disciplinary Action',
                'major_category' => 'Expulsion',
                'sanction' => 'Student expelled from school. Certificate of eligibility may not be issued at the end of school year.',
                'deportment_grade_action' => 'Expulsion recorded in permanent record',
                'suspension' => 0,
                'notes' => $expulsionNotes,
                'is_automatic' => true,
                'is_approved' => false,
                'created_at' => $this->schoolNow(),
                'updated_at' => $this->schoolNow()
            ];
        }

        // Insert all sanctions at once
        if (!empty($sanctionsToCreate)) {
            Sanction::insert($sanctionsToCreate);
            
            // Log the creation of automatic sanctions
            \Log::info('Automatic sanctions created for case meeting', [
                'case_meeting_id' => $caseMeeting->id,
                'sanctions_created' => count($sanctionsToCreate)
            ]);
        }
    }

    /**
     * Check if the case meeting has any interventions selected.
     *
     * @param CaseMeeting $caseMeeting
     * @return bool
     */
    private function hasAnyInterventions(CaseMeeting $caseMeeting): bool
    {
        // Check if any intervention/sanction fields are set to true
        $interventionFields = [
            'written_reflection',
            'mentorship_counseling', 
            'parent_teacher_communication',
            'restorative_justice_activity',
            'follow_up_meeting',
            'community_service',
            'suspension',
            'suspension_3days',
            'suspension_5days',
            'expulsion'
        ];

        foreach ($interventionFields as $field) {
            if ($caseMeeting->{$field}) {
                return true;
            }
        }

        // Also check if suspension_other_days has a value greater than 0
        if ($caseMeeting->suspension_other_days && $caseMeeting->suspension_other_days > 0) {
            return true;
        }

        // Check if there are any sanctions in the sanctions table as well (for backward compatibility)
        return $caseMeeting->sanctions()->exists();
    }
}