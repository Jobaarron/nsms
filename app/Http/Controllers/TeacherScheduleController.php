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
     * Display all students across teacher's classes
     */
    public function allStudents()
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        // Check if teacher profile exists
        if (!$teacher->teacher) {
            return redirect()->route('teacher.schedule')->with('error', 'Teacher profile not found. Please contact administration.');
        }

        // Get all teacher's assignments
        $assignments = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
                                       ->where('academic_year', $currentAcademicYear)
                                       ->where('status', 'active')
                                       ->with(['subject'])
                                       ->get();

        // Group students by class
        $studentsByClass = collect();
        foreach ($assignments as $assignment) {
            $students = Student::where('grade_level', $assignment->grade_level)
                              ->where('section', $assignment->section)
                              ->where('academic_year', $currentAcademicYear)
                              ->where('is_active', true)
                              ->orderBy('last_name')
                              ->orderBy('first_name')
                              ->get();

            $studentsByClass->push([
                'assignment' => $assignment,
                'students' => $students,
                'class_name' => $assignment->grade_level . ' - ' . $assignment->section,
                'subject' => $assignment->subject ? $assignment->subject->subject_name : 'No Subject Assigned'
            ]);
        }

        return view('teacher.students', compact('teacher', 'studentsByClass', 'currentAcademicYear'));
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

        // Get students in this class
        $students = Student::where('grade_level', $gradeLevel)
                          ->where('section', $section)
                          ->where('academic_year', $currentAcademicYear)
                          ->where('is_active', true)
                          ->orderBy('last_name')
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
