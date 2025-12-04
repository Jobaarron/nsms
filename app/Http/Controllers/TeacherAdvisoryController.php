<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\FacultyAssignment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\Teacher;
use App\Models\CounselingSession;

class TeacherAdvisoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:teacher|faculty_head']);
    }
    
    /**
     * Check if the current user is a class adviser
     */
    private function ensureClassAdviser()
    {
        $teacher = Auth::user();
        $teacherRecord = Teacher::where('user_id', $teacher->id)->first();
        
        if (!$teacherRecord) {
            abort(403, 'Teacher record not found.');
        }
        
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacherRecord->id)
            ->where('assignment_type', 'class_adviser')
            ->where('academic_year', $currentAcademicYear)
            ->where('status', 'active')
            ->first();
            
        if (!$advisoryAssignment) {
            abort(403, 'You must be a class adviser to access this feature.');
        }
        
        return $advisoryAssignment;
    }
    
    /**
     * Get advisory alert counts
     */
    public function getAdvisoryAlertCounts()
    {
        try {
            $teacher = Auth::user();
            $teacherRecord = Teacher::where('user_id', $teacher->id)->first();
            
            $counts = [
                'unreplied_reports' => 0,
                'scheduled_counseling' => 0,
            ];
            
            if ($teacherRecord) {
                // Count unreplied observation reports using the same logic as showObservationReport
                $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
                $counts['unreplied_reports'] = \App\Models\CaseMeeting::with(['student'])
                    ->whereIn('status', ['scheduled', 'pre_completed'])
                    ->where(function($query) use ($teacher, $teacherRecord, $currentAcademicYear) {
                        // Case 1: Direct adviser_id match
                        $query->where('adviser_id', $teacher->id)
                              // Case 2: OR check if teacher is class adviser for the student
                              ->orWhereHas('student', function($studentQuery) use ($teacherRecord, $currentAcademicYear) {
                                  $studentQuery->whereExists(function($advisoryQuery) use ($teacherRecord, $currentAcademicYear) {
                                      $advisoryQuery->select(DB::raw(1))
                                                  ->from('faculty_assignments')
                                                  ->whereColumn('faculty_assignments.grade_level', 'students.grade_level')
                                                  ->whereColumn('faculty_assignments.section', 'students.section')
                                                  ->where('faculty_assignments.teacher_id', $teacherRecord->id)
                                                  ->where('faculty_assignments.academic_year', $currentAcademicYear)
                                                  ->where('faculty_assignments.assignment_type', 'class_adviser')
                                                  ->where('faculty_assignments.status', 'active');
                                  });
                              });
                    })
                    ->where(function($query) {
                        // Only count those without both teacher_statement AND action_plan (not fully replied)
                        $query->whereNull('teacher_statement')
                              ->orWhereNull('action_plan');
                    })
                    ->count();
                
                // Count scheduled counseling sessions (only unviewed ones)
                $counts['scheduled_counseling'] = CounselingSession::where('recommended_by', $teacher->id)
                    ->where('status', 'scheduled')
                    ->where('teacher_notified', false)
                    ->count();
            }
            
            return response()->json([
                'success' => true,
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching advisory alert counts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display students in teacher's advisory class only
     */
    public function advisory()
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get teacher's advisory assignment (class adviser)
        $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
            ->where('assignment_type', 'class_adviser')
            ->where('academic_year', $currentAcademicYear)
            ->where('status', 'active')
            ->first();
        
        $students = collect();
        $className = '';
        
        if ($advisoryAssignment) {
            // Build class name
            $className = $advisoryAssignment->grade_level . ' - ' . $advisoryAssignment->section;
            if ($advisoryAssignment->strand) {
                $className .= ' - ' . $advisoryAssignment->strand;
                if ($advisoryAssignment->track) {
                    $className .= ' - ' . $advisoryAssignment->track;
                }
            }
            
            // Get students in advisory class (include quarterly and monthly payers)
            $studentsQuery = Student::where('grade_level', $advisoryAssignment->grade_level)
                                   ->where('section', $advisoryAssignment->section)
                                   ->where('academic_year', $currentAcademicYear)
                                   ->where('is_active', true)
                                   ->where('enrollment_status', 'enrolled');
            
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
        }
        
        return view('teacher.advisory', compact(
            'advisoryAssignment',
            'students', 
            'className',
            'currentAcademicYear'
        ));
    }

    /**
     * Get individual student grades for advisory
     */
    public function getStudentGrades(Student $student)
    {
        try {
            $teacher = Auth::user();
            $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
            
            // Verify this teacher is the student's adviser
            $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
                ->where('grade_level', $student->grade_level)
                ->where('section', $student->section)
                ->where('assignment_type', 'class_adviser')
                ->where('academic_year', $currentAcademicYear)
                ->where('status', 'active')
                ->first();
                
            if (!$advisoryAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not the adviser for this student.'
                ]);
            }
            
            // Get student's subjects based on grade level, strand, and track
            $subjects = Subject::where('grade_level', $student->grade_level)
                ->where('academic_year', $currentAcademicYear)
                ->where('is_active', true);
                
            if ($student->strand) {
                $subjects->where(function($query) use ($student) {
                    $query->whereNull('strand')
                          ->orWhere('strand', $student->strand);
                });
            }
            
            if ($student->track) {
                $subjects->where(function($query) use ($student) {
                    $query->whereNull('track')
                          ->orWhere('track', $student->track);
                });
            }
            
            $subjects = $subjects->get();
            
            // Get grades for each subject and quarter
            $gradesData = [];
            $quarters = ['1st', '2nd', '3rd', '4th'];
            
            foreach ($subjects as $subject) {
                $subjectGrades = [
                    'subject_name' => $subject->subject_name,
                    'quarters' => []
                ];
                
                foreach ($quarters as $quarter) {
                    $grade = Grade::where('student_id', $student->id)
                        ->where('subject_id', $subject->id)
                        ->where('quarter', $quarter)
                        ->where('academic_year', $currentAcademicYear)
                        ->first();
                        
                    $subjectGrades['quarters'][$quarter] = $grade ? $grade->grade : null;
                }
                
                $gradesData[] = $subjectGrades;
            }
            
            // Calculate averages per quarter
            $quarterAverages = [];
            foreach ($quarters as $quarter) {
                $quarterGrades = [];
                foreach ($gradesData as $subjectData) {
                    if ($subjectData['quarters'][$quarter] !== null) {
                        $quarterGrades[] = $subjectData['quarters'][$quarter];
                    }
                }
                $quarterAverages[$quarter] = !empty($quarterGrades) ? round(array_sum($quarterGrades) / count($quarterGrades), 2) : null;
            }
            
            $html = view('teacher.student-grades', [
                'student' => $student,
                'gradesData' => $gradesData,
                'quarterAverages' => $quarterAverages,
                'currentAcademicYear' => $currentAcademicYear
            ])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading student grades: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get all advisory students grades
     */
    public function getAllAdvisoryGrades()
    {
        try {
            $teacher = Auth::user();
            $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
            
            // Get teacher's advisory assignment
            $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
                ->where('assignment_type', 'class_adviser')
                ->where('academic_year', $currentAcademicYear)
                ->where('status', 'active')
                ->first();
                
            if (!$advisoryAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have an advisory class assigned.'
                ]);
            }
            
            // Get all students in advisory class (include quarterly and monthly payers)
            $students = Student::where('grade_level', $advisoryAssignment->grade_level)
                ->where('section', $advisoryAssignment->section)
                ->where('academic_year', $currentAcademicYear)
                ->where('enrollment_status', 'enrolled')
                ->where('is_active', true)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
                
            // Get subjects for this grade level/strand/track
            $subjects = Subject::where('grade_level', $advisoryAssignment->grade_level)
                ->where('academic_year', $currentAcademicYear)
                ->where('is_active', true);
                
            if ($advisoryAssignment->strand) {
                $subjects->where(function($query) use ($advisoryAssignment) {
                    $query->whereNull('strand')
                          ->orWhere('strand', $advisoryAssignment->strand);
                });
            }
            
            if ($advisoryAssignment->track) {
                $subjects->where(function($query) use ($advisoryAssignment) {
                    $query->whereNull('track')
                          ->orWhere('track', $advisoryAssignment->track);
                });
            }
            
            $subjects = $subjects->get();
            
            // Get grades for all students (1st quarter for now)
            $studentsData = [];
            
            foreach ($students as $student) {
                $studentGrades = [];
                $studentTotal = 0;
                $subjectCount = 0;
                
                foreach ($subjects as $subject) {
                    $grade = Grade::where('student_id', $student->id)
                        ->where('subject_id', $subject->id)
                        ->where('quarter', '1st')
                        ->where('academic_year', $currentAcademicYear)
                        ->first();
                        
                    if ($grade) {
                        $studentGrades[$subject->id] = $grade->grade;
                        $studentTotal += $grade->grade;
                        $subjectCount++;
                    } else {
                        $studentGrades[$subject->id] = null;
                    }
                }
                
                $studentAverage = $subjectCount > 0 ? round($studentTotal / $subjectCount, 2) : null;
                
                $studentsData[] = [
                    'student' => $student,
                    'grades' => $studentGrades,
                    'average' => $studentAverage
                ];
            }
            
            // Sort by average (highest first) and assign rankings
            $studentsData = collect($studentsData)->sortByDesc('average')->values()->all();
            foreach ($studentsData as $index => &$studentData) {
                $studentData['ranking'] = $studentData['average'] !== null ? $index + 1 : null;
            }
            
            // Calculate class statistics
            $allAverages = collect($studentsData)->pluck('average')->filter()->values();
            $classAverage = $allAverages->count() > 0 ? round($allAverages->avg(), 2) : 0;
            $highestGrade = $allAverages->count() > 0 ? $allAverages->max() : 0;
            $topStudent = collect($studentsData)->where('average', $highestGrade)->first();
            
            $className = $advisoryAssignment->grade_level . ' - ' . $advisoryAssignment->section;
            if ($advisoryAssignment->strand) {
                $className .= ' - ' . $advisoryAssignment->strand;
                if ($advisoryAssignment->track) {
                    $className .= ' - ' . $advisoryAssignment->track;
                }
            }
            
            $html = view('teacher.all-advisory-grades', [
                'studentsData' => $studentsData,
                'subjects' => $subjects,
                'className' => $className,
                'classAverage' => $classAverage,
                'highestGrade' => $highestGrade,
                'topStudent' => $topStudent,
                'totalStudents' => count($studentsData),
                'gradedStudents' => $allAverages->count(),
                'currentAcademicYear' => $currentAcademicYear
            ])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading advisory grades: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generate individual student report card PDF
     */
    public function generateStudentReportCard(Student $student)
    {
        // For now, return a simple response
        // In production, this would generate an actual PDF
        return response()->json([
            'success' => false,
            'message' => 'PDF generation not implemented yet. This would generate a report card for ' . $student->full_name
        ]);
    }
    
    /**
     * Generate all advisory students report cards PDF
     */
    public function generateAllReportCards()
    {
        // For now, return a simple response
        // In production, this would generate PDFs for all advisory students
        return response()->json([
            'success' => false,
            'message' => 'Bulk PDF generation not implemented yet. This would generate report cards for all advisory students.'
        ]);
    }

    /**
     * Show the form to recommend a student to counselling.
     */
    public function showRecommendForm()
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get teacher record
        $teacherRecord = \App\Models\Teacher::where('user_id', $teacher->id)->first();
        
        if (!$teacherRecord) {
            return view('teacher.recommend-counseling', [
                'students' => collect(),
                'message' => 'Teacher record not found.'
            ]);
        }
        
        // Get teacher's advisory assignment (class adviser)
        $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacherRecord->id)
            ->where('assignment_type', 'class_adviser')
            ->where('academic_year', $currentAcademicYear)
            ->where('status', 'active')
            ->first();
        
        $students = collect();
        $message = '';
        
        if ($advisoryAssignment) {
            // Get students in advisory class only
            $studentsQuery = Student::select('id', 'first_name', 'last_name', 'student_id')
                ->where('grade_level', $advisoryAssignment->grade_level)
                ->where('section', $advisoryAssignment->section)
                ->where('academic_year', $currentAcademicYear)
                ->where('is_active', true)
                ->where('enrollment_status', 'enrolled');
            
            // Add strand filter if assignment has strand
            if ($advisoryAssignment->strand) {
                $studentsQuery->where('strand', $advisoryAssignment->strand);
            }
            
            // Add track filter if assignment has track
            if ($advisoryAssignment->track) {
                $studentsQuery->where('track', $advisoryAssignment->track);
            }
            
            $students = $studentsQuery->orderBy('last_name', 'asc')
                ->orderBy('first_name', 'asc')
                ->get();
                
            // Build class name for display
            $className = $advisoryAssignment->grade_level . ' - ' . $advisoryAssignment->section;
            if ($advisoryAssignment->strand) {
                $className .= ' - ' . $advisoryAssignment->strand;
                if ($advisoryAssignment->track) {
                    $className .= ' - ' . $advisoryAssignment->track;
                }
            }
            
            $message = "Showing students from your advisory class: {$className}";
        } else {
            $message = 'You are not assigned as a class adviser for the current academic year.';
        }

        // Get scheduled counseling sessions recommended by this teacher (only unviewed ones)
        $scheduledSessions = \App\Models\CounselingSession::with(['student', 'counselor'])
            ->where('recommended_by', $teacher->id)
            ->where('status', 'scheduled')
            ->where('teacher_notified', false)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('teacher.recommend-counseling', compact('students', 'message', 'scheduledSessions'));
    }

    /**
     * Recommend a student to counselling.
     */
    public function recommendToCounseling(Request $request)
    {
        $validatedData = $request->validate([
            'student_id' => 'required|exists:students,id',
            'referral_academic' => 'nullable|array',
            'referral_academic_other' => 'nullable|string',
            'referral_social' => 'nullable|array',
            'referral_social_other' => 'nullable|string',
            'incident_description' => 'nullable|string',
        ]);

        $counselingSession = \App\Models\CounselingSession::create([
            'student_id' => $validatedData['student_id'],
            'recommended_by' => Auth::id(),
            'referral_academic' => isset($validatedData['referral_academic']) ? json_encode($validatedData['referral_academic']) : null,
            'referral_academic_other' => $validatedData['referral_academic_other'] ?? null,
            'referral_social' => isset($validatedData['referral_social']) ? json_encode($validatedData['referral_social']) : null,
            'referral_social_other' => $validatedData['referral_social_other'] ?? null,
            'incident_description' => $validatedData['incident_description'] ?? null,
            'status' => 'recommended',
        ]);

        // Create notification for guidance about teacher recommendation
        try {
            $teacherName = Auth::user()->name ?? 'Teacher';
            $student = \App\Models\Student::find($validatedData['student_id']);
            $studentName = $student ? $student->full_name : 'Student';
            
            // Get referral reasons
            $academicReasons = $validatedData['referral_academic'] ?? [];
            $socialReasons = $validatedData['referral_social'] ?? [];
            $allReasons = array_merge($academicReasons, $socialReasons);
            $reasonsText = !empty($allReasons) ? implode(', ', $allReasons) : 'General counseling need';
            
            \App\Models\Notice::createGlobal(
                "Student Recommended for Counseling",
                "Teacher {$teacherName} has recommended {$studentName} for counseling. Concerns: {$reasonsText}. Please review and schedule the counseling session.",
                null, // created_by will be null for system-generated notices
                null, // target_status
                null  // target_grade_level
            );
            
            \Log::info('Notification created for teacher counseling recommendation', [
                'counseling_session_id' => $counselingSession->id,
                'teacher_name' => $teacherName,
                'student_name' => $studentName,
                'reasons' => $reasonsText
            ]);
        } catch (\Exception $notificationError) {
            // Log notification error but don't fail the main operation
            \Log::error('Failed to create notification for teacher counseling recommendation', [
                'counseling_session_id' => $counselingSession->id,
                'error' => $notificationError->getMessage()
            ]);
        }

        return redirect()->route('teacher.dashboard')
            ->with('success', 'Student has been recommended for counseling. Guidance will review the recommendation.');
    }

    /**
     * Show the Observation Report page.
     * Route: teacher.observationreport
     */
    public function showObservationReport()
    {
        $currentUser = Auth::user();
        $teacherRecord = \App\Models\Teacher::where('user_id', $currentUser->id)->first();
        
        if (!$teacherRecord) {
            return view('teacher.observationreport', ['reports' => collect()]);
        }
        
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get only case meetings for students that this teacher is the class adviser of
        $reports = \App\Models\CaseMeeting::with(['student', 'violation', 'counselor'])
            ->whereIn('status', ['scheduled', 'pre_completed'])
            ->where(function($query) use ($currentUser, $teacherRecord, $currentAcademicYear) {
                // Case 1: Direct adviser_id match
                $query->where('adviser_id', $currentUser->id)
                      // Case 2: OR check if teacher is class adviser for the student
                      ->orWhereHas('student', function($studentQuery) use ($teacherRecord, $currentAcademicYear) {
                          $studentQuery->whereExists(function($advisoryQuery) use ($teacherRecord, $currentAcademicYear) {
                              $advisoryQuery->select(\DB::raw(1))
                                          ->from('faculty_assignments')
                                          ->whereColumn('faculty_assignments.grade_level', 'students.grade_level')
                                          ->whereColumn('faculty_assignments.section', 'students.section')
                                          ->where('faculty_assignments.teacher_id', $teacherRecord->id)
                                          ->where('faculty_assignments.academic_year', $currentAcademicYear)
                                          ->where('faculty_assignments.assignment_type', 'class_adviser')
                                          ->where('faculty_assignments.status', 'active');
                          });
                      });
            })
            ->orderByDesc('scheduled_date')
            ->get();

        return view('teacher.observationreport', compact('reports'));
    }

    /**
     * Handle teacher reply for observation report (update case meeting)
     * Only allows the assigned adviser to reply
     */
    public function submitObservationReply(Request $request, $caseMeetingId)
    {
        try {
            // Debug incoming request data
            \Log::info('Teacher observation reply - incoming request data', [
                'case_meeting_id' => $caseMeetingId,
                'has_teacher_statement' => $request->has('teacher_statement'),
                'teacher_statement_length' => strlen($request->get('teacher_statement', '')),
                'has_action_plan' => $request->has('action_plan'),
                'action_plan_length' => strlen($request->get('action_plan', '')),
                'has_token' => $request->has('_token'),
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'all_data' => $request->all()
            ]);

            $request->validate([
                'teacher_statement' => 'required|string',
                'action_plan' => 'required|string',
            ]);

            $caseMeeting = \App\Models\CaseMeeting::with(['student', 'adviser'])->findOrFail($caseMeetingId);
            $currentUser = Auth::user();
            
            // Check if current user is the assigned adviser for this case meeting
            if (!$caseMeeting->adviser_id || $caseMeeting->adviser_id !== $currentUser->id) {
                // If no adviser_id is set, check if user is the class adviser for this student
                if ($caseMeeting->student) {
                    $student = $caseMeeting->student;
                    $teacherRecord = \App\Models\Teacher::where('user_id', $currentUser->id)->first();
                    
                    if (!$teacherRecord) {
                        if ($request->ajax()) {
                            return response()->json(['message' => 'You are not authorized to reply to this case meeting.'], 403);
                        }
                        abort(403, 'You are not authorized to reply to this case meeting.');
                    }
                    
                    $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
                    $advisoryAssignment = \App\Models\FacultyAssignment::where('teacher_id', $teacherRecord->id)
                        ->where('grade_level', $student->grade_level)
                        ->where('section', $student->section)
                        ->where('academic_year', $currentAcademicYear)
                        ->where('assignment_type', 'class_adviser')
                        ->where('status', 'active')
                        ->first();
                        
                    if (!$advisoryAssignment) {
                        if ($request->ajax()) {
                            return response()->json(['message' => 'You are not the assigned adviser for this student.'], 403);
                        }
                        abort(403, 'You are not the assigned adviser for this student.');
                    }
                } else {
                    if ($request->ajax()) {
                        return response()->json(['message' => 'You are not authorized to reply to this case meeting.'], 403);
                    }
                    abort(403, 'You are not authorized to reply to this case meeting.');
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            // Log the attempt for debugging
            \Log::info('Attempting to submit teacher observation reply', [
                'case_meeting_id' => $caseMeetingId,
                'teacher_id' => $currentUser->id,
                'teacher_statement_length' => strlen($request->teacher_statement),
                'action_plan_length' => strlen($request->action_plan)
            ]);

            $caseMeeting->teacher_statement = $request->teacher_statement;
            $caseMeeting->action_plan = $request->action_plan;
            
            // Only set timestamp if the column exists in the table
            if (\Schema::hasColumn('case_meetings', 'teacher_reply_submitted_at')) {
                $caseMeeting->teacher_reply_submitted_at = now();
            }
            
            // Check if the case meeting can be saved
            if (!$caseMeeting->save()) {
                throw new \Exception('Failed to save case meeting - save() returned false');
            }

            // Create notification for guidance counselor about teacher reply
            try {
                $teacherName = $currentUser->name ?? 'Teacher';
                $studentName = $caseMeeting->student ? $caseMeeting->student->full_name : 'Student';
                
                \App\Models\Notice::createGlobal(
                    "Teacher Reply Received - Case Meeting",
                    "Teacher {$teacherName} has submitted their reply for the case meeting regarding {$studentName}. Please review the teacher's statement and action plan in the case meeting details.",
                    null, // created_by will be null for system-generated notices
                    null, // target_status
                    null  // target_grade_level
                );
                
                \Log::info('Notification created for teacher reply', [
                    'case_meeting_id' => $caseMeetingId,
                    'teacher_name' => $teacherName,
                    'student_name' => $studentName
                ]);
            } catch (\Exception $notificationError) {
                // Log notification error but don't fail the main operation
                \Log::error('Failed to create notification for teacher reply', [
                    'case_meeting_id' => $caseMeetingId,
                    'teacher_id' => $currentUser->id,
                    'error' => $notificationError->getMessage()
                ]);
            }

            \Log::info('Teacher observation reply submitted successfully', [
                'case_meeting_id' => $caseMeetingId,
                'teacher_id' => $currentUser->id
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your observation report reply has been successfully submitted. The guidance office will review your response.'
                ]);
            }

            return redirect()->back()->with('success', 'Your observation report reply has been successfully submitted. The guidance office will review your response.');
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error while submitting teacher observation reply', [
                'case_meeting_id' => $caseMeetingId,
                'teacher_id' => $currentUser->id,
                'error' => $e->getMessage(),
                'sql_code' => $e->getCode()
            ]);
            
            return redirect()->back()->with('error', 'Database error occurred. Please check if all required fields are properly filled and try again.');
            
        } catch (\Exception $e) {
            \Log::error('Failed to submit teacher observation reply', [
                'case_meeting_id' => $caseMeetingId,
                'teacher_id' => $currentUser->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // In development, show detailed error; in production, show generic message
            if (config('app.debug')) {
                return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
            } else {
                return redirect()->back()->with('error', 'Failed to submit your reply. Please try again or contact the administrator if the problem persists.');
            }
        }
    }

    /**
     * Mark advisory-specific alerts as viewed
     */
    public function markAlertViewed(Request $request)
    {
        $alertType = $request->input('alert_type');
        
        if ($alertType === 'observation_reports') {
            session(['observation_reports_alert_viewed' => true]);
        } elseif ($alertType === 'counseling') {
            session(['counseling_alert_viewed' => true]);
            
            // Mark all scheduled counseling sessions as notified for this adviser
            \App\Models\CounselingSession::where('recommended_by', Auth::id())
                ->where('status', 'scheduled')
                ->where('teacher_notified', false)
                ->update(['teacher_notified' => true]);
        }
        
        return response()->json(['success' => true]);
    }
}
