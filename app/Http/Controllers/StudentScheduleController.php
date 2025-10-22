<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassSchedule;
use App\Models\Student;

class StudentScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    /**
     * Display student's class schedule
     */
    public function index()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }

        // Get the student's weekly schedule
        $weeklySchedule = $student->getWeeklySchedule();
        
        // Get current academic year
        $currentAcademicYear = $student->academic_year;
        
        // Get schedule statistics
        $stats = [
            'total_subjects' => $student->classSchedules()->count(),
            'total_hours' => $this->calculateTotalHours($student->classSchedules()),
            'grade_level' => $student->grade_level,
            'section' => $student->section
        ];

        return view('student.schedule', compact('student', 'weeklySchedule', 'stats', 'currentAcademicYear'));
    }

    /**
     * Display weekly calendar view
     */
    public function weeklyCalendar()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }

        $weeklySchedule = $student->getWeeklySchedule();
        
        // Time slots for calendar display (7:00 AM to 6:00 PM)
        $timeSlots = [];
        for ($hour = 7; $hour <= 18; $hour++) {
            $timeSlots[] = sprintf('%02d:00', $hour);
        }

        return view('student.schedule.calendar', compact('student', 'weeklySchedule', 'timeSlots'));
    }

    /**
     * Get schedule data for AJAX requests
     */
    public function getScheduleData(Request $request)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $weeklySchedule = $student->getWeeklySchedule();
        
        // Format for calendar display
        $events = [];
        foreach ($weeklySchedule as $day => $schedules) {
            foreach ($schedules as $schedule) {
                $events[] = [
                    'id' => $schedule->id,
                    'title' => $schedule->subject->subject_name,
                    'teacher' => $schedule->teacher->name,
                    'room' => $schedule->room,
                    'day' => $day,
                    'start_time' => $schedule->start_time->format('H:i'),
                    'end_time' => $schedule->end_time->format('H:i'),
                    'time_range' => $schedule->time_range,
                    'color' => $this->getSubjectColor($schedule->subject->category)
                ];
            }
        }

        return response()->json([
            'events' => $events,
            'student_info' => [
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'academic_year' => $student->academic_year
            ]
        ]);
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
