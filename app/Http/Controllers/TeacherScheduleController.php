<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassSchedule;
use App\Models\FacultyAssignment;
use App\Models\Student;

class TeacherScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:teacher|faculty_head']);
    }

    /**
     * Display teacher's class schedule
     */
    public function index()
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        try {
            // Get teacher's assignments
            $assignments = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
                ->where('academic_year', $currentAcademicYear)
                ->where('status', 'active')
                ->with(['subject', 'teacher'])
                ->get();
            
            // Get teacher's weekly schedule
            $weeklySchedule = ClassSchedule::where('teacher_id', $teacher->teacher->id)
                ->where('academic_year', $currentAcademicYear)
                ->with(['subject'])
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();
            
            // Calculate schedule statistics
            $stats = [
                'total_classes' => $assignments->count(),
                'total_hours' => $weeklySchedule->sum('duration_hours') ?: 0,
                'subjects_taught' => $assignments->pluck('subject.subject_name')->unique()->count(),
                'sections_handled' => $assignments->map(function($a) { return $a->grade_level . ' - ' . $a->section; })->unique()->count()
            ];
        } catch (\Exception $e) {
            // Handle case where tables don't exist yet
            $assignments = collect();
            $weeklySchedule = collect();
            $stats = [
                'total_classes' => 0,
                'total_hours' => 0,
                'subjects_taught' => 0,
                'sections_handled' => 0
            ];
        }

        return view('teacher.schedule', compact('teacher', 'weeklySchedule', 'assignments', 'stats', 'currentAcademicYear'));
    }

    /**
     * Display weekly calendar view
     */
    public function calendar()
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        $weeklySchedule = $teacher->getWeeklySchedule($currentAcademicYear);
        
        // Time slots for calendar display (7:00 AM to 6:00 PM)
        $timeSlots = [];
        for ($hour = 7; $hour <= 18; $hour++) {
            $timeSlots[] = sprintf('%02d:00', $hour);
        }

        return view('teacher.schedule', compact('teacher', 'weeklySchedule', 'timeSlots'));
    }

    /**
     * Display students in teacher's advisory class only
     */
    public function advisory()
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        // Check if teacher profile exists
        if (!$teacher->teacher) {
            return redirect()->route('teacher.schedule')->with('error', 'Teacher profile not found. Please contact administration.');
        }

        // Get only the class adviser assignment (not subject teacher assignments)
        $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
                                              ->where('academic_year', $currentAcademicYear)
                                              ->where('assignment_type', 'class_adviser')
                                              ->where('status', 'active')
                                              ->first();

        // If no advisory assignment found, show message
        if (!$advisoryAssignment) {
            return view('teacher.advisory', [
                'teacher' => $teacher,
                'advisoryAssignment' => null,
                'students' => collect(),
                'className' => null,
                'currentAcademicYear' => $currentAcademicYear
            ]);
        }

        // Get students in the advisory class only
        $studentsQuery = Student::where('grade_level', $advisoryAssignment->grade_level)
                               ->where('section', $advisoryAssignment->section)
                               ->where('academic_year', $currentAcademicYear)
                               ->where('is_active', true)
                               ->where('enrollment_status', 'enrolled')
                               ->where('is_paid', true);
        
        // Add strand filter if assignment has strand
        if ($advisoryAssignment->strand) {
            $studentsQuery->where('strand', $advisoryAssignment->strand);
        }
        
        // Add track filter if assignment has track
        if ($advisoryAssignment->track) {
            $studentsQuery->where('track', $advisoryAssignment->track);
        }
        
        $students = $studentsQuery->orderBy('last_name')
                                ->orderBy('first_name')
                                ->get();

        // Build proper class name with strand and track
        $className = $advisoryAssignment->grade_level . ' - ' . $advisoryAssignment->section;
        if ($advisoryAssignment->strand) {
            $className = $advisoryAssignment->grade_level . ' - ' . $advisoryAssignment->section . ' - ' . $advisoryAssignment->strand;
            if ($advisoryAssignment->track) {
                $className = $advisoryAssignment->grade_level . ' - ' . $advisoryAssignment->section . ' - ' . $advisoryAssignment->strand . ' - ' . $advisoryAssignment->track;
            }
        }

        return view('teacher.advisory', compact('teacher', 'advisoryAssignment', 'students', 'className', 'currentAcademicYear'));
    }

    /**
     * Display students in a specific class
     */
    public function students(Request $request)
    {
        $teacher = Auth::user();
        $subjectId = $request->get('subject_id');
        $gradeLevel = $request->get('grade_level');
        $section = $request->get('section');
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        // Verify teacher is assigned to this class
        $assignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
                                     ->where('subject_id', $subjectId)
                                     ->where('grade_level', $gradeLevel)
                                     ->where('section', $section)
                                     ->where('academic_year', $currentAcademicYear)
                                     ->where('status', 'active')
                                     ->with('subject')
                                     ->first();

        if (!$assignment) {
            return redirect()->route('teacher.schedule')->with('error', 'You are not assigned to this class.');
        }

        // Get students in this class - only enrolled and paid students
        $studentsQuery = Student::where('grade_level', $gradeLevel)
                               ->where('section', $section)
                               ->where('academic_year', $currentAcademicYear)
                               ->where('is_active', true)
                               ->where('enrollment_status', 'enrolled')
                               ->where('is_paid', true);
        
        // Add strand filter if assignment has strand
        if ($assignment->strand) {
            $studentsQuery->where('strand', $assignment->strand);
        }
        
        // Add track filter if assignment has track
        if ($assignment->track) {
            $studentsQuery->where('track', $assignment->track);
        }
        
        $students = $studentsQuery->orderBy('last_name')
                                ->orderBy('first_name')
                                ->get();

        // Get class schedule
        $schedule = ClassSchedule::where('teacher_id', $teacher->id)
                                ->where('subject_id', $subjectId)
                                ->where('grade_level', $gradeLevel)
                                ->where('section', $section)
                                ->where('academic_year', $currentAcademicYear)
                                ->where('is_active', true)
                                ->get();

        return view('teacher.schedule', compact('teacher', 'assignment', 'students', 'schedule'));
    }

    /**
     * Get schedule data for AJAX requests
     */
    public function getScheduleData(Request $request)
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        $weeklySchedule = $teacher->getWeeklySchedule($currentAcademicYear);
        
        // Format for calendar display
        $events = [];
        foreach ($weeklySchedule as $day => $schedules) {
            foreach ($schedules as $schedule) {
                $events[] = [
                    'id' => $schedule->id,
                    'title' => $schedule->subject->subject_name,
                    'class' => $schedule->grade_level . ' - ' . $schedule->section,
                    'room' => $schedule->room,
                    'day' => $day,
                    'start_time' => $schedule->start_time->format('H:i'),
                    'end_time' => $schedule->end_time->format('H:i'),
                    'time_range' => $schedule->time_range,
                    'student_count' => $schedule->student_count,
                    'color' => $this->getSubjectColor($schedule->subject->category)
                ];
            }
        }

        return response()->json([
            'events' => $events,
            'teacher_info' => [
                'name' => $teacher->name,
                'total_classes' => count($events),
                'academic_year' => $currentAcademicYear
            ]
        ]);
    }

    /**
     * Get class list for AJAX requests
     */
    public function getClassList(Request $request)
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        $assignments = $teacher->getCurrentTeachingLoad($currentAcademicYear);
        
        $classes = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'subject' => $assignment->subject->subject_name,
                'grade_level' => $assignment->grade_level,
                'section' => $assignment->section,
                'class_name' => $assignment->grade_level . ' - ' . $assignment->section,
                'assignment_type' => $assignment->assignment_type,
                'is_adviser' => $assignment->isClassAdviser(),
                'student_count' => $assignment->student_count,
                'schedule' => $assignment->classSchedule() ? [
                    'day' => $assignment->classSchedule()->day_of_week,
                    'time' => $assignment->classSchedule()->time_range,
                    'room' => $assignment->classSchedule()->room
                ] : null
            ];
        });

        return response()->json(['classes' => $classes]);
    }

    /**
     * Calculate total hours per week
     */
    private function calculateTotalHours($schedules)
    {
        $totalMinutes = 0;
        
        foreach ($schedules as $schedule) {
            $start = \Carbon\Carbon::parse($schedule->start_time);
            $end = \Carbon\Carbon::parse($schedule->end_time);
            $totalMinutes += $start->diffInMinutes($end);
        }
        
        return round($totalMinutes / 60, 1);
    }

    /**
     * Get color for subject category
     */
    private function getSubjectColor($category)
    {
        $colors = [
            'core' => '#2B7A3B',
            'specialized' => '#4CAF50',
            'default' => '#6c757d'
        ];
        
        return $colors[$category] ?? $colors['default'];
    }
}
