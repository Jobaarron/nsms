<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CounselingSession;
use App\Models\Student;
use App\Models\FacultyAssignment;
use App\Models\Subject;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PdfController extends Controller
{
    /**
     * Get school time (Philippine Time)
     */
    private function schoolNow()
    {
        return Carbon::now('Asia/Manila');
    }

    /**
     * Generate dynamic counseling session PDF using TCPDF.
     */
    public function show(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            abort(404, 'Session ID is required.');
    }

        $session = CounselingSession::with(['student', 'counselor'])->find($sessionId);
        if (!$session) {
            abort(404, 'Counseling session not found.');
        }

    $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
    $templatePath = resource_path('assets/pdf-forms-generation/SEWO-CRFS-010 Counselling-Request-Forms.pdf');
    $pdf->setSourceFile($templatePath);
    $tplId = $pdf->importPage(1);
    $size = $pdf->getTemplateSize($tplId);
    // Set margins to zero for full-page overlay
    $pdf->SetMargins(0, 0, 0);
    // Detect orientation
    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
    // Add page with correct size and orientation
    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
    $pdf->SetFont('dejavusans', '', 10);
    $pdf->useTemplate($tplId);

    // Overlay dynamic data on template
    $pdf->SetFont('dejavusans', '', 10);

        // --- Upper form ---
        $pdf->SetXY(35, 38);
        $pdf->Write(0, $session->start_date ? $session->start_date->format('Y-m-d') : ($session->created_at ? $session->created_at->format('Y-m-d') : ''));
    $pdf->SetXY(128, 37 );
    $pdf->Write(0, $session->id ?? '');
    $pdf->SetXY(45, 47);
    $pdf->Write(0, $session->student->full_name ?? '');
    $pdf->SetXY(129, 46);
    $pdf->Write(0, $session->student->contact_number ?? '');
    $pdf->SetXY(52, 51);
    $pdf->Write(0, $session->student->grade_level ?? '');
    $pdf->SetXY(47, 56);
    $pdf->Write(0, $session->student->guardian_name ?? '');
    $pdf->SetXY(136, 55);
    $pdf->Write(0, $session->student->email ?? '');
    $pdf->SetXY(34, 60);
    $pdf->Write(0, $session->student->address ?? '');
    $pdf->SetXY(140, 59);
    $pdf->Write(0, $session->recommender->name ?? '');
        $referralAcademic = is_array($session->referral_academic) ? $session->referral_academic : json_decode($session->referral_academic, true);
        $referralAcademicOther = $session->referral_academic_other ?? '';
        $referralSocial = is_array($session->referral_social) ? $session->referral_social : json_decode($session->referral_social, true);
        $referralSocialOther = $session->referral_social_other ?? '';
        $check = '✓'; // Unicode checkmark
        $academicPositions = [
            'Attendance' => [22, 81],
            'Activity Sheets/Assignments' => [22, 85],
            'Exams' => [113, 82],
            'Quiz' => [113, 86],
            'Others' => [22, 90],
        ];
        foreach ($academicPositions as $option => [$x, $y]) {
            if (is_array($referralAcademic) && in_array($option, $referralAcademic)) {
                $pdf->SetXY($x, $y);
                $pdf->Write(0, $check);
            }
        }
        // Show 'Others (specify)' text for Academic
        if (!empty($referralAcademicOther)) {
            $pdf->SetXY(38, 90);
            $pdf->Write(0,$referralAcademicOther);
        }
        $socialPositions = [
            'Anger Management' => [22, 103],
            'Bullying' => [22, 107],
            'Social Skills/Friends' => [22, 112],
            'Negative Attitude' => [22,116],
            'Honesty' => [113, 103],
            'Self-esteem' => [113, 107],
            'Personal Hygiene' => [113, 112],
            'Adjustment' => [113, 116],
            'Family Conflict' => [113, 120],
            'Others' => [22, 120],
        ];
        foreach ($socialPositions as $option => [$x, $y]) {
            if (is_array($referralSocial) && in_array($option, $referralSocial)) {
                $pdf->SetXY($x, $y);
                $pdf->Write(0, $check);
            }
        }
        // Show 'Others (specify)' text for Social
        if (!empty($referralSocialOther)) {
            $pdf->SetXY(38, 120);
            $pdf->Write(0, $referralSocialOther);
        }
        $pdf->SetXY(47,129);
        $pdf->MultiCell(150, 6, $session->incident_description ?? '');

        // --- Lower form (copy for student/office) ---
        $yOffset = 150; // Adjust this value based on your template's layout
        $pdf->SetXY(35, 45 + $yOffset);
        $pdf->Write(0, $session->start_date ? $session->start_date->format('Y-m-d') : ($session->created_at ? $session->created_at->format('Y-m-d') : ''));
        $pdf->SetXY(128, 43 + $yOffset);
        $pdf->Write(0, $session->id ?? '');
        $pdf->SetXY(46, 53 + $yOffset);
        $pdf->Write(0, $session->student->full_name ?? '');
        $pdf->SetXY(129, 53 + $yOffset);
        $pdf->Write(0, $session->student->contact_number ?? '');
        $pdf->SetXY(52, 57 + $yOffset);
        $pdf->Write(0, $session->student->grade_level ?? '');
        $pdf->SetXY(47, 62 + $yOffset);
        $pdf->Write(0, $session->student->guardian_name ?? '');
        $pdf->SetXY(136, 62 + $yOffset);
        $pdf->Write(0, $session->student->email ?? '');
        $pdf->SetXY(34, 66 + $yOffset);
        $pdf->Write(0, $session->student->address ?? '');
        $pdf->SetXY(140, 66 + $yOffset);
        $pdf->Write(0, $session->recommender->name ?? '');

        // Add referral logic for lower part
        $referralAcademic = is_array($session->referral_academic) ? $session->referral_academic : json_decode($session->referral_academic, true);
        $referralAcademicOther = $session->referral_academic_other ?? '';
        $referralSocial = is_array($session->referral_social) ? $session->referral_social : json_decode($session->referral_social, true);
        $referralSocialOther = $session->referral_social_other ?? '';
        $check = '✓'; // Unicode checkmark
        $academicPositions = [
            'Attendance' => [22, 88],
            'Activity Sheets/Assignments' => [22, 92],
            'Exams' => [113, 88],
            'Quiz' => [113, 92],
            'Others' => [22, 97],
        ];
        foreach ($academicPositions as $option => [$x, $y]) {
            if (is_array($referralAcademic) && in_array($option, $referralAcademic)) {
                $pdf->SetXY($x, $y + $yOffset);
                $pdf->Write(0, $check);
            }
        }
        // Show 'Others (specify)' text for Academic (lower form)
        if (!empty($referralAcademicOther)) {
            $pdf->SetXY(38, 97 + $yOffset);
            $pdf->Write(0,$referralAcademicOther);
        }
        $socialPositions = [
            'Anger Management' => [22, 109],
            'Bullying' => [22, 114],
            'Social Skills/Friends' => [22, 118],
            'Negative Attitude' => [22, 122],
            'Honesty' => [113, 109],
            'Self-esteem' => [113, 114],
            'Personal Hygiene' => [113, 118],
            'Adjustment' => [113, 122],
            'Family Conflict' => [113, 127],
            'Others' => [22, 127],
        ];
        foreach ($socialPositions as $option => [$x, $y]) {
            if (is_array($referralSocial) && in_array($option, $referralSocial)) {
                $pdf->SetXY($x, $y + $yOffset);
                $pdf->Write(0, $check);
            }
        }
        // Show 'Others (specify)' text for Social (lower form)
        if (!empty($referralSocialOther)) {
            $pdf->SetXY(38, 127 + $yOffset);
            $pdf->Write(0,$referralSocialOther);
        }
        $pdf->SetXY(47,135 + $yOffset);
        $pdf->MultiCell(150, 6, $session->incident_description ?? '');

        // Return the PDF
        return response($pdf->Output('Counseling-Request-Form.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');
    }
    
        /**
         * Generate dynamic Student Narrative Report PDF using TCPDF.
         */
        public function studentNarrativePdf($studentId, $violationId)
        {
            $student = \App\Models\Student::findOrFail($studentId);
            $violation = $student->violations()->findOrFail($violationId);
            $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
            $templatePath = resource_path('assets/pdf-forms-generation/Student.pdf');
            $pdf->setSourceFile($templatePath);
            $tplId = $pdf->importPage(1);
            $size = $pdf->getTemplateSize($tplId);
            $pdf->SetMargins(0, 0, 0);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->useTemplate($tplId);

            // Overlay student data (adjust coordinates as needed for your template)
            $pdf->SetXY(24, 58); $pdf->Write(0, $student->full_name ?? '');
            $pdf->SetXY(141, 57); $pdf->Write(0, $student->grade_level ?? '');
            $pdf->SetXY(47, 56); $pdf->Write(0, $student->section ?? '');

            // Overlay specific violation date and time
            $pdf->SetXY(120, 40); $pdf->Write(0, $violation->violation_date ? $violation->violation_date->format('Y-m-d') : '');
            $pdf->SetXY(176, 40);
            if ($violation->violation_time) {
                $time = $violation->violation_time;
                if ($time instanceof \DateTimeInterface) {
                    $formattedTime = $time->format('h:i A');
                } else {
                    $formattedTime = date('h:i A', strtotime($time));
                }
                $pdf->Write(0, $formattedTime);
            } else {
                $pdf->Write(0, '');
            }
             
            // Overlay student printed name
            $pdf->SetXY(76, 292); $pdf->Write(0, $student->full_name ?? '');
            // Overlay student statement (adjust coordinates as needed)
            if (!empty($violation->student_statement)) {
                $pdf->SetXY(18, 84); // Example position, adjust as needed
                $pdf->MultiCell(160, 8, $violation->student_statement);
                }

                // Overlay incident feelings
                if (!empty($violation->incident_feelings)) {
                    $pdf->SetXY(18, 156); // Adjust Y as needed for spacing
                    $pdf->MultiCell(160, 8, $violation->incident_feelings);
                }

                // Overlay action plan
                if (!empty($violation->action_plan)) {
                    $pdf->SetXY(18, 219); // Adjust Y as needed for spacing
                    $pdf->MultiCell(160, 8, $violation->action_plan);
            }

            return response($pdf->Output('Student-Narrative-Report.pdf', 'S'))->header('Content-Type', 'application/pdf');
        }

            /**
     * Generate dynamic Case Meeting PDF using TCPDF.
     */
    public function caseMeetingAttachmentPdf($caseMeetingId)
    {
        $caseMeeting = \App\Models\CaseMeeting::with(['violation', 'student'])->findOrFail($caseMeetingId);
        $student = $caseMeeting->student ?? ($caseMeeting->violation->student ?? null);
        $violation = $caseMeeting->violation ?? null;
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $templatePath = resource_path('assets/pdf-forms-generation/CaseMeeting.pdf');
        if (!file_exists($templatePath)) {
            abort(404, 'Case Meeting PDF template not found.');
        }
        $pdf->setSourceFile($templatePath);
        $tplId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplId);
        $pdf->SetMargins(0, 0, 0);
        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->useTemplate($tplId);

        // Overlay case meeting and student data (adjust coordinates as needed for your template)
        if ($student) {
            $pdf->SetXY(24, 58); $pdf->Write(0, $student->full_name ?? '');
            $pdf->SetXY(141, 57); $pdf->Write(0, $student->grade_level ?? '');
            $pdf->SetXY(47, 56); $pdf->Write(0, $student->section ?? '');
        }
        if ($violation) {
            $pdf->SetXY(120, 40); $pdf->Write(0, $violation->violation_date ? $violation->violation_date->format('Y-m-d') : '');
            $pdf->SetXY(176, 40);
            if ($violation->violation_time) {
                $time = $violation->violation_time;
                if ($time instanceof \DateTimeInterface) {
                    $formattedTime = $time->format('h:i A');
                } else {
                    $formattedTime = date('h:i A', strtotime($time));
                }
                $pdf->Write(0, $formattedTime);
            } else {
                $pdf->Write(0, '');
            }
        }
        $pdf->SetXY(76, 292); $pdf->Write(0, $student->full_name ?? '');

          if (!empty($violation->student_statement)) {
                $pdf->SetXY(18, 84); // Example position, adjust as needed
                $pdf->MultiCell(160, 8, $violation->student_statement);
                }

                // Overlay incident feelings
                if (!empty($violation->incident_feelings)) {
                    $pdf->SetXY(18, 156); // Adjust Y as needed for spacing
                    $pdf->MultiCell(160, 8, $violation->incident_feelings);
                }

                // Overlay action plan
                if (!empty($violation->action_plan)) {
                    $pdf->SetXY(18, 219); // Adjust Y as needed for spacing
                    $pdf->MultiCell(160, 8, $violation->action_plan);
            }

            return response($pdf->Output('Student-Narrative-Report.pdf', 'S'))->header('Content-Type', 'application/pdf');
        }

    /**
     * Generate Teacher Observation Report PDF using TCPDF.
     *
     * @param int $caseMeetingId
     * @return \Illuminate\Http\Response
     */
    public function teacherObservationReportPdf($caseMeetingId)
    {
        $caseMeeting = \App\Models\CaseMeeting::with(['violation', 'student', 'adviser'])->findOrFail($caseMeetingId);
        
        // Check authorization - only admin, guidance staff, discipline staff or the assigned adviser can access this PDF
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        $canAccess = false;
        
        // Check if user is admin, guidance staff or discipline staff
        if ($currentUser && ($currentUser->isAdmin() || $currentUser->isGuidanceStaff() || $currentUser->isDisciplineStaff())) {
            $canAccess = true;
        }
        // Check if user is the assigned adviser
        elseif ($currentUser && $caseMeeting->adviser_id && $caseMeeting->adviser_id === $currentUser->id) {
            $canAccess = true;
        }
        // Check if user is the class adviser (fallback if adviser_id not set)
        elseif ($currentUser && $caseMeeting->student) {
            $student = $caseMeeting->student;
            $teacherRecord = \App\Models\Teacher::where('user_id', $currentUser->id)->first();
            
            if ($teacherRecord) {
                $advisoryAssignment = \App\Models\FacultyAssignment::where('teacher_id', $teacherRecord->id)
                    ->where('grade_level', $student->grade_level)
                    ->where('section', $student->section)
                    ->where('academic_year', $student->academic_year)
                    ->where('assignment_type', 'class_adviser')
                    ->where('status', 'active')
                    ->first();
                    
                if ($advisoryAssignment) {
                    $canAccess = true;
                }
            }
        }
        
        if (!$canAccess) {
            abort(403, 'You are not authorized to access this Teacher Observation Report.');
        }

        // Get adviser name - only use the actual class adviser, not any teacher
        $adviserName = null;
        if ($caseMeeting->adviser) {
            $adviserName = $caseMeeting->adviser->full_name ?? $caseMeeting->adviser->name ?? null;
        }
        
        // If no adviser is set in case meeting, try to find the class adviser for this student
        if (!$adviserName && $caseMeeting->student) {
            $student = $caseMeeting->student;
            $advisoryAssignment = \App\Models\FacultyAssignment::where('grade_level', $student->grade_level)
                ->where('section', $student->section)
                ->where('academic_year', $student->academic_year)
                ->where('assignment_type', 'class_adviser')
                ->where('status', 'active')
                ->with(['teacher.user'])
                ->first();
            
            if ($advisoryAssignment && $advisoryAssignment->teacher && $advisoryAssignment->teacher->user) {
                $adviserName = $advisoryAssignment->teacher->user->full_name ?? $advisoryAssignment->teacher->user->name ?? null;
            }
        }

        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $templatePath = resource_path('assets/pdf-forms-generation/Teacher-Observation-Report.pdf');
        if (!file_exists($templatePath)) {
            abort(404, 'Teacher Observation Report PDF template not found.');
        }
        $pdf->setSourceFile($templatePath);
        $tplId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplId);
        $pdf->SetMargins(0, 0, 0);
        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->useTemplate($tplId);

        // Overlay the required data (adjust coordinates as needed for your template)
        $pdf->SetXY(27, 62); // Adviser Name
        $pdf->Write(0, $adviserName ?? '');
        $pdf->SetXY(87, 292); // Adviser Name
        $pdf->Write(0, $adviserName ?? '');
        $pdf->SetXY(120, 45); // Scheduled Date
        $pdf->Write(0, $caseMeeting->scheduled_date ? (is_string($caseMeeting->scheduled_date) ? $caseMeeting->scheduled_date : $caseMeeting->scheduled_date->format('Y-m-d')) : '');
        $pdf->SetXY(174, 45); // Scheduled Time
        if ($caseMeeting->scheduled_time) {
            $time = $caseMeeting->scheduled_time;
            if ($time instanceof \DateTimeInterface) {
                $formattedTime = $time->format('h:i A');
            } else {
                $formattedTime = date('h:i A', strtotime($time));
            }
            $pdf->Write(0, $formattedTime);
        } else {
            $pdf->Write(0, '');
        }
        $pdf->SetXY(18, 90); // Teacher Statement
        $pdf->MultiCell(150, 8, $caseMeeting->teacher_statement ?? '');
        $pdf->SetXY(18, 199); // Action Plan
        $pdf->MultiCell(150, 8, $caseMeeting->action_plan ?? '');

        return response($pdf->Output('Teacher-Observation-Report.pdf', 'S'))->header('Content-Type', 'application/pdf');
    }

    /**
     * Generate Disciplinary Conference Summary Report PDF for students with disciplinary records using TCPDF.
     * Shows the FIRST (original) disciplinary case for each student, ordered chronologically.
     * @return \Illuminate\Http\Response
     */
    public function conferenceSummaryReportAllPdf()
    {
        // Only fetch students who have case meetings (disciplinary records)
        // Get the FIRST (original) case meeting per student, ordered chronologically
        $caseMeetings = \App\Models\CaseMeeting::with(['student'])
            ->whereHas('student') // Ensure student exists
            ->orderBy('id') // Order by case ID chronologically (oldest first)
            ->get()
            ->groupBy('student_id') // Group by student to get first case meeting per student
            ->map(function ($meetings) {
                return $meetings->first(); // Get the FIRST (original) case meeting for each student
            })
            ->sortBy(function ($caseMeeting) {
                return $caseMeeting->id; // Sort final results by case ID for consistent ordering
            });

        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $templatePath = resource_path('assets/pdf-forms-generation/Summary Report.pdf');
        if (!file_exists($templatePath)) {
            abort(404, 'Disciplinary Conference Summary Report PDF template not found.');
        }

        // Prepare the first page
        $pdf->setSourceFile($templatePath);
        $tplId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplId);
        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->useTemplate($tplId);

        // Fill in header information
        $currentDate = $this->schoolNow();
        $currentMonth = $currentDate->format('F'); // Full month name (January, February, etc.)
        $currentSchoolYear = $currentDate->format('Y') . '-' . ($currentDate->format('Y') + 1); // 2025-2026 format
        
        // Add MONTH/SCHOOL YEAR field (adjust coordinates as needed for your template)
        $pdf->SetXY(160, 45); // Adjust X,Y coordinates based on your template layout
        $pdf->Write(0, $currentMonth . ' / ' . $currentSchoolYear);

        // Table starting coordinates (adjust as needed for your template)
        $startY = 59; // Y coordinate of first row (after header)
        $rowHeight = 6; // Height of each row
        $maxRowsPerPage = 25; // Adjust based on your template
        $currentRow = 0;
        $sequentialNumber = 1; // Sequential numbering starting from 1

        foreach ($caseMeetings as $caseMeeting) {
            $student = $caseMeeting->student;
            
            // Skip if student data is missing
            if (!$student) {
                continue;
            }

            // Calculate Y position for this row
            $y = $startY + ($currentRow * $rowHeight);
            if ($y > ($size['height'] - 20)) { // If out of space, add new page
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($tplId);
                $y = $startY;
                $currentRow = 0;
            }

            // Write each column (adjust X positions and widths as needed)
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->SetXY(22, $y); // No.
            $pdf->Write(0, $sequentialNumber);
            $pdf->SetXY(30, $y); // Name
            $pdf->Write(0, $student->full_name ?? '');
            $pdf->SetXY(84, $y); // Grade/Section
            $pdf->Write(0, ($student->grade_level ?? '') . ' - ' . ($student->section ?? ''));
            $pdf->SetXY(122, $y); // DCR Case No.
            $pdf->Write(0, $sequentialNumber);
            // Issues/Concerns and Remarks left blank, but no border/cell

            $currentRow++;
            $sequentialNumber++; // Increment sequential number for next record
        }

        return response($pdf->Output('Disciplinary-Conference-Summary-Report-All.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');
    }
        /**
         * Generate Disciplinary Conference Reports PDF for all students' case meetings.
         * Fields: student_name, section, case_meeting id, admin_name, adviser_name, violation_date, violation_time, location, student_statement, teacher_statement, summary, follow_up_meeting
         * @return \Illuminate\Http\Response
         */
    public function DisciplinaryConReports($caseMeetingId)
    {

        // Always fetch $caseMeeting and $violation at the top
        $caseMeeting = \App\Models\CaseMeeting::with(['student', 'admin', 'violation', 'violation.teacher'])->findOrFail($caseMeetingId);
        $violation = isset($caseMeeting->violation) ? $caseMeeting->violation : null;

        // Determine if Student Narrative Report and Teacher Observation Report are available
        $hasStudentNarrative = false;
        $hasTeacherObservation = false;
        // Student Narrative: if student_statement, incident_feelings, or action_plan exists on violation
        if ($violation && (
            !empty($violation->student_statement) ||
            !empty($violation->incident_feelings) ||
            !empty($violation->action_plan)
        )) {
            $hasStudentNarrative = true;
        }
        // Teacher Observation: if teacher_statement or action_plan exists on caseMeeting
        if (!empty($caseMeeting->teacher_statement) || !empty($caseMeeting->action_plan)) {
            $hasTeacherObservation = true;
        }

        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
$templatePath = resource_path('assets/pdf-forms-generation/Disciplinary-Con-Report.pdf');      
  if (!file_exists($templatePath)) {
            abort(404, 'Disciplinary Conference Summary Report PDF template not found.');
        }

        $pageCount = $pdf->setSourceFile($templatePath);
        // Page 1: dynamic data
        $tplId1 = $pdf->importPage(1);
        $size1 = $pdf->getTemplateSize($tplId1);
        $orientation1 = ($size1['width'] > $size1['height']) ? 'L' : 'P';
        $pdf->AddPage($orientation1, [$size1['width'], $size1['height']]);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->useTemplate($tplId1);

        $student = $caseMeeting->student;
        $admin = $caseMeeting->admin ?? null;
        $adviser = $caseMeeting->adviser ?? null;

            // If adviser is not set, try to get from violation's teacher (like teacherObservationReportPdf)
            if (!$adviser && $violation && method_exists($violation, 'teacher')) {
                $teacher = $violation->teacher;
                if ($teacher) {
                    $adviser = $teacher;
                }
            }

            // Use SetXY/Write for each field, with specific coordinates (adjust as needed)
            $pdf->SetFont('dejavusans', '', 10.1);
            $pdf->SetXY(24, 58); $pdf->Write(0, $student->full_name ?? '');
            $pdf->SetXY(54, 126); $pdf->Write(0, $student->full_name ?? '');
            $pdf->SetXY(141, 57); $pdf->Write(0, ($student->grade_level ?? '') . ' -');
            $pdf->SetXY(162, 57); $pdf->Write(0, $student->section ?? '');
            $pdf->SetXY(175, 41); $pdf->Write(0, $caseMeeting->id);
            $pdf->SetXY(100, 56); $pdf->Write(0, $admin ? ($admin->full_name ?? $admin->name ?? '') : '');
            $pdf->SetXY(54, 102); $pdf->Write(0, $adviser ? ($adviser->full_name ?? $adviser->name ?? '') : '');
            // (Checklist moved to page 2 below)
            $pdf->SetFont('dejavusans', '', 10.1);
            $pdf->SetXY(100,160); $pdf->Write(0, $violation && $violation->violation_date ? (is_string($violation->violation_date) ? $violation->violation_date : $violation->violation_date->format('Y-m-d')) : '');
            $pdf->SetXY(153,160);
            if ($violation && $violation->violation_time) {
                $time = $violation->violation_time;
                if ($time instanceof \DateTimeInterface) {
                    $formattedTime = $time->format('h:i A');
                } else {
                    $formattedTime = date('h:i A', strtotime($time));
                }
                $pdf->Write(0, $formattedTime);
            } else {
                $pdf->Write(0, '');
            }
            $pdf->SetXY(33, 165); $pdf->Write(0, $caseMeeting->location ?? '');
            // Format Student's Explanation: first sentence inline, rest below
            $studentStatement = $violation->student_statement ?? '';
            $firstLine = $studentStatement;
            $remainingText = '';
            // Split at first period followed by space or end of string
            if (preg_match('/^(.+?\.[\s\n]|.+?$)(.*)/s', $studentStatement, $matches)) {
                $firstLine = trim($matches[1]);
                $remainingText = trim($matches[2]);
            }
            // Write label and first sentence inline
            $pdf->SetFont('dejavusans', 'B', 9.1);
            $pdf->SetXY(64, 227);
            $pdf->Write(0, '');
            $pdf->SetFont('dejavusans', '', 9.1);
            $pdf->Write(0, $firstLine);
            // Write the rest below, wrapped
            if (!empty($remainingText)) {
                $pdf->SetFont('dejavusans', '', 9);
                // Place the remaining text as a MultiCell below the first line, matching the underline area
                $pdf->SetXY(28, 232); // Adjust Y as needed to match template
                $pdf->MultiCell(160, 8, $remainingText); // Adjust width/height as needed
                $pdf->SetFont('dejavusans', '', 10.1);
            }
            // Format Teacher's Statement: first sentence inline, rest below
            $teacherStatement = $caseMeeting->teacher_statement ?? '';
            $teacherFirstLine = $teacherStatement;
            $teacherRemainingText = '';
            // Split at first period followed by space or end of string
            if (preg_match('/^(.+?\.[\s\n]|.+?$)(.*)/s', $teacherStatement, $matches)) {
                $teacherFirstLine = trim($matches[1]);
                $teacherRemainingText = trim($matches[2]);
            }
            // Write label and first sentence inline (adjust X/Y as needed)
            $pdf->SetFont('dejavusans', 'B', 9.1);
            $pdf->SetXY(65, 262); // Adjust X to match your template's label position
            $pdf->Write(0, '');
            $pdf->SetFont('dejavusans', '', 9.1);
            $pdf->Write(0, $teacherFirstLine);
            // Write the rest below, wrapped
            if (!empty($teacherRemainingText)) {
                $pdf->SetFont('dejavusans', '', 9);
                $pdf->SetXY(28, 266); // Adjust Y as needed to match template
                $pdf->MultiCell(160, 8, $teacherRemainingText);
                $pdf->SetFont('dejavusans', '', 10.1);
            }
            // Do not write summary and follow_up_meeting on page 1

            // Page 2: template, then write summary, follow_up_meeting, and checklist, plus all agreed actions/interventions
            if ($pageCount > 1) {
                $tplId2 = $pdf->importPage(2);
                $size2 = $pdf->getTemplateSize($tplId2);
                $orientation2 = ($size2['width'] > $size2['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation2, [$size2['width'], $size2['height']]);
                $pdf->useTemplate($tplId2);
                // Write summary and follow_up_meeting on page 2
                $pdf->SetFont('dejavusans', '', 10.1);
                $pdf->SetXY(18, 30); $pdf->MultiCell(169, 8, $caseMeeting->summary ?? '');
                $pdf->SetXY(100, 90); $pdf->MultiCell(60, 8, $caseMeeting->follow_up_meeting ?? '');

                // Overlay check icons for report availability (now on page 2)
                $check = '✓';
                $pdf->SetFont('dejavusans', '', 12);
                if ($hasStudentNarrative) {
                    $pdf->SetXY(31, 210); $pdf->Write(0, $check);
                }
                if ($hasTeacherObservation) {
                    $pdf->SetXY(31, 215); $pdf->Write(0, $check);
                }
                $hasDisciplinaryConReport = (isset($caseMeeting->status) && strtolower($caseMeeting->status) === 'active');
                if ($hasDisciplinaryConReport) {
                    $pdf->SetXY(31, 220); $pdf->Write(0, $check);
                    $pdf->SetXY(38, 220); $pdf->Write(0, 'Disciplinary Conference Report');
                }

                // --- AGREED ACTIONS/INTERVENTIONS SECTION ---
                $pdf->SetFont('dejavusans', 'B', 11);
                $pdf->SetXY(18, 110); $pdf->Write(0, '');
                $pdf->SetFont('dejavusans', '', 10);
                // Written Reflection
                $pdf->SetXY(31, 88); $pdf->Write(0, !empty($caseMeeting->written_reflection) ? $check : '');
                if (!empty($caseMeeting->written_reflection_due)) {
                    $pdf->SetXY(150, 92); $pdf->Write(0, ($caseMeeting->written_reflection_due instanceof \DateTimeInterface) ? $caseMeeting->written_reflection_due->format('Y-m-d') : $caseMeeting->written_reflection_due);
                }
                // Mentor Name
                if (!empty($caseMeeting->mentor_name)) {
                    $pdf->SetXY(134, 103); $pdf->Write(0, $caseMeeting->mentor_name);
                }
                // Mentorship Counseling
                $pdf->SetXY(31, 99); $pdf->Write(0, !empty($caseMeeting->mentorship_counseling) ? $check : '');
                // Parent Teacher Communication
                $pdf->SetXY(31, 110); $pdf->Write(0, !empty($caseMeeting->parent_teacher_communication) ? $check : '');
                if (!empty($caseMeeting->parent_teacher_date)) {
                    $pdf->SetXY(126, 115); $pdf->Write(0, ($caseMeeting->parent_teacher_date instanceof \DateTimeInterface) ? $caseMeeting->parent_teacher_date->format('Y-m-d') : $caseMeeting->parent_teacher_date);
                }
                // Restorative Justice Activity
                $pdf->SetXY(31,121); $pdf->Write(0, !empty($caseMeeting->restorative_justice_activity) ? $check : '');
                if (!empty($caseMeeting->restorative_justice_date)) {
                    $pdf->SetXY(120, 126); $pdf->Write(0, ($caseMeeting->restorative_justice_date instanceof \DateTimeInterface) ? $caseMeeting->restorative_justice_date->format('Y-m-d') : $caseMeeting->restorative_justice_date);
                }
                // Follow Up Meeting
                $pdf->SetXY(31, 133); $pdf->Write(0, !empty($caseMeeting->follow_up_meeting) ? $check : '');
                if (!empty($caseMeeting->follow_up_meeting_date)) {
                    $pdf->SetXY(100, 137); $pdf->Write(0, ($caseMeeting->follow_up_meeting_date instanceof \DateTimeInterface) ? $caseMeeting->follow_up_meeting_date->format('Y-m-d') : $caseMeeting->follow_up_meeting_date);
                }
                // Community Service
                $pdf->SetXY(31, 144); $pdf->Write(0, !empty($caseMeeting->community_service) ? $check : '');
                if (!empty($caseMeeting->community_service_date)) {
                    $pdf->SetXY(50, 153); $pdf->Write(0, ($caseMeeting->community_service_date instanceof \DateTimeInterface) ? $caseMeeting->community_service_date->format('Y-m-d') : $caseMeeting->community_service_date);
                }
                if (!empty($caseMeeting->community_service_area)) {
                    $pdf->SetXY(110, 152); $pdf->Write(0, $caseMeeting->community_service_area);
                }
                // Suspension
                $pdf->SetXY(31, 160); $pdf->Write(0, !empty($caseMeeting->suspension) ? $check : '');
                $pdf->SetXY(93, 160); $pdf->Write(0, !empty($caseMeeting->suspension_3days) ? $check : '');
                $pdf->SetXY(109, 160); $pdf->Write(0, !empty($caseMeeting->suspension_5days) ? $check : '');
                if (!empty($caseMeeting->suspension_other_days)) {
                    $pdf->SetXY(130, 159); $pdf->Write(0, $caseMeeting->suspension_other_days . '');
                }
                if (!empty($caseMeeting->suspension_start)) {
                    $pdf->SetXY(110, 164); $pdf->Write(0, ($caseMeeting->suspension_start instanceof \DateTimeInterface) ? $caseMeeting->suspension_start->format('Y-m-d') : $caseMeeting->suspension_start);
                }
                if (!empty($caseMeeting->suspension_end)) {
                    $pdf->SetXY(40, 168); $pdf->Write(0, ($caseMeeting->suspension_end instanceof \DateTimeInterface) ? $caseMeeting->suspension_end->format('Y-m-d') : $caseMeeting->suspension_end);
                }
                if (!empty($caseMeeting->suspension_return)) {
                    $pdf->SetXY(89, 173); $pdf->Write(0, ($caseMeeting->suspension_return instanceof \DateTimeInterface) ? $caseMeeting->suspension_return->format('Y-m-d') : $caseMeeting->suspension_return);
                }
                // Expulsion
                $pdf->SetXY(31, 180); $pdf->Write(0, !empty($caseMeeting->expulsion) ? $check : '');
                if (!empty($caseMeeting->expulsion_date)) {
                    $pdf->SetXY(160, 193); $pdf->Write(0, ($caseMeeting->expulsion_date instanceof \DateTimeInterface) ? $caseMeeting->expulsion_date->format('Y-m-d') : $caseMeeting->expulsion_date);
                }
            }

            return response($pdf->Output('Disciplinary-Conference-Reports.pdf', 'S'))
                ->header('Content-Type', 'application/pdf');
        }
            /**
     * Show static receipt PDF with dynamic overlay fields.
     * Fields: application_id, student_id, transaction_id, student full name, timestamp, appointment date, event
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function showReceipt(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        if (!$transactionId) {
            abort(404, 'Transaction ID is required.');
        }

        // Find payment by transaction_id
        $payment = \App\Models\Payment::where('transaction_id', $transactionId)->first();
        if (!$payment) {
            abort(404, "Payment with transaction ID '{$transactionId}' not found. Please verify the transaction ID is correct.");
        }

        // Get Enrollee and Student
        $enrollee = null;
        $student = null;
        if ($payment->payable_type === 'App\\Models\\Enrollee') {
            $enrollee = $payment->payable;
            $student = $enrollee->student;
        } elseif ($payment->payable_type === 'App\\Models\\Student') {
            $student = $payment->payable;
            $enrollee = $student->enrollee;
        }

        // Fallbacks
        $applicationId = $enrollee ? $enrollee->application_id : '';
        $studentId = $student ? $student->student_id : '';
        $studentName = $student ? ($student->full_name ?? ($student->first_name . ' ' . $student->last_name)) : '';
        $timestamp = $payment->paid_at ? $payment->paid_at->format('Y-m-d h:i A') : ($payment->created_at ? $payment->created_at->format('Y-m-d h:i A') : '');
    // Use scheduled_date from payment table for appointment date
    $appointmentDate = $payment->scheduled_date ? (\Carbon\Carbon::parse($payment->scheduled_date)->format('Y-m-d')) : '';
        $event = $payment->period_name ?? '';

        // Load static PDF
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $templatePath = resource_path('assets/pdf-forms-generation/Receipt.pdf');
        if (!file_exists($templatePath)) {
            abort(404, 'Receipt PDF template not found.');
        }
        $pdf->setSourceFile($templatePath);
        $tplId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplId);
        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->useTemplate($tplId);

        // Overlay fields (adjust coordinates as needed for your template)
        // $pdf->SetXY(120, 82); // Application ID
        // $pdf->Write(0, '' . $applicationId);
        $pdf->SetXY(95, 89); // Student ID
        $pdf->Write(0, '' . $studentId);
        $pdf->SetXY(95, 69); // Transaction ID
        $pdf->Write(0, '' . $transactionId);
    $pdf->SetXY(95, 98); // Student Name
    $pdf->Write(0, $studentName);
        $pdf->SetXY(95, 79); // Timestamp
        $pdf->Write(0, '' . $timestamp);
        $pdf->SetXY(95, 108); // Appointment Date
        // Determine time of day if scheduled_time exists
        $appointmentTimeOfDay = '';
        if (!empty($payment->scheduled_time)) {
            $time = $payment->scheduled_time;
            if ($time instanceof \DateTimeInterface) {
                $hour = (int)$time->format('H');
            } else {
                $hour = (int)date('H', strtotime($time));
            }
            if ($hour >= 5 && $hour < 12) {
                $appointmentTimeOfDay = 'MORNING';
            } elseif ($hour >= 12 && $hour < 13) {
                $appointmentTimeOfDay = 'NOON';
            } elseif ($hour >= 13 && $hour < 18) {
                $appointmentTimeOfDay = 'AFTERNOON';
            } else {
                $appointmentTimeOfDay = 'EVENING';
            }
        }
        $appointmentDateDisplay = $appointmentDate;
        if ($appointmentDate && $appointmentTimeOfDay) {
            $appointmentDateDisplay .= ' (' . $appointmentTimeOfDay . ')';
        }
        $pdf->Write(0, '' . $appointmentDateDisplay);
        $pdf->SetXY(95, 117); // Event
        $pdf->Write(0, '' . $event);

        return response($pdf->Output('Receipt.pdf', 'S'))->header('Content-Type', 'application/pdf');
    }
        /**
     * Generate dynamic cashier receipt PDF using TCPDF.
     * Path: storage/app/public/receipt/cashier_receipt.pdf
     * Fields: date, year, lastname, firstname, middleinitial, gradelevel, entrancefee, Miscellaneous Fee, Tuition Fee, Others fee, totalfee, amount in words, cashier name
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function showCashierReceipt(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        if (!$transactionId) {
            abort(404, 'Transaction ID is required.');
        }

        // Find payment by transaction_id
        $payment = \App\Models\Payment::where('transaction_id', $transactionId)->first();
        if (!$payment) {
            abort(404, "Payment with transaction ID '{$transactionId}' not found. Please verify the transaction ID is correct.");
        }

        // Get related models
        $student = null;
        $enrollee = null;
        if ($payment->payable_type === 'App\\Models\\Enrollee') {
            $enrollee = $payment->payable;
            $student = $enrollee->student;
        } elseif ($payment->payable_type === 'App\\Models\\Student') {
            $student = $payment->payable;
            $enrollee = $student->enrollee;
        }

        // Date and year
        $date = $payment->paid_at ? $payment->paid_at->format('Y-m-d') : ($payment->created_at ? $payment->created_at->format('Y-m-d') : '');
        $year = $payment->paid_at ? $payment->paid_at->format('Y') : ($payment->created_at ? $payment->created_at->format('Y') : '');

        // Student info
        $lastname = $student ? ($student->last_name ?? '') : '';
        $firstname = $student ? ($student->first_name ?? '') : '';
        $middleinitial = $student && !empty($student->middle_name) ? strtoupper(substr($student->middle_name, 0, 1)) : '';
        $gradelevel = $student ? ($student->grade_level ?? '') : '';

        // Fee breakdown: fetch 'others' fee from Fee model for the student's grade and academic year
        $entranceFee = $payment->entrance_fee ?? '';
        $miscFee = $payment->miscellaneous_fee ?? '';
        $tuitionFee = $payment->tuition_fee ?? '';
        $othersFee = $payment->others_fee ?? '';
        $totalFee = $payment->total_fee ?? $payment->amount ?? '';

        // If not directly on payment, try to get from related models (e.g., Enrollee or Student)
        if ($entranceFee === '' && $enrollee && isset($enrollee->entrance_fee)) {
            $entranceFee = $enrollee->entrance_fee;
        }
        if ($miscFee === '' && $enrollee && isset($enrollee->miscellaneous_fee)) {
            $miscFee = $enrollee->miscellaneous_fee;
        }
        if ($tuitionFee === '' && $enrollee && isset($enrollee->tuition_fee)) {
            $tuitionFee = $enrollee->tuition_fee;
        }
        if ($totalFee === '' && $enrollee && isset($enrollee->total_fee)) {
            $totalFee = $enrollee->total_fee;
        }

        // Fetch 'tuition' and 'others' fee from Fee model for the student's grade and academic year
        // Consider payment method to calculate appropriate installment amount
        if ($student && !empty($student->grade_level)) {
            $academicYear = $student->academic_year ?? $payment->period_name ?? null;
            $feeBreakdown = \App\Models\Fee::calculateTotalFeesForGrade($student->grade_level, $academicYear);
            
            // Always display full tuition fee amount (not installment-specific amounts)
            if (!empty($feeBreakdown['breakdown']['tuition']) && $feeBreakdown['breakdown']['tuition'] != 0) {
                $fullTuitionFee = $feeBreakdown['breakdown']['tuition'];
                
                // Always use full tuition fee amount regardless of payment method
                $tuitionFee = $fullTuitionFee;
            }
            
            if (!empty($feeBreakdown['breakdown']['other']) && $feeBreakdown['breakdown']['other'] != 0) {
                $othersFee = $feeBreakdown['breakdown']['other'];
            }
        }

        // Amount in words (use a helper if available, else simple fallback)
        if (!function_exists('convert_number_to_words')) {
            function convert_number_to_words($number) {
                // Simple PHP number to words (English, for demonstration)
                $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
                return ucfirst($f->format($number));
            }
        }
    $amountInWords = $totalFee !== '' ? strtoupper(convert_number_to_words($totalFee) . ' PESOS ONLY') : '';

        // Cashier name (assume Payment has cashier_id or user_id, or fallback to current user)
        $cashierName = '';
        if ($payment->processed_by) {
            $cashier = \App\Models\Cashier::find($payment->processed_by);
            $cashierName = $cashier ? $cashier->full_name : '';
        }

        // Load cashier receipt PDF template
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $templatePath = resource_path('assets/pdf-forms-generation/cashier_receipt.pdf');
        if (!file_exists($templatePath)) {
            abort(404, 'Cashier Receipt PDF template not found.');
        }
        $pdf->setSourceFile($templatePath);
        $tplId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplId);
        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->useTemplate($tplId);

        // Overlay fields (adjust coordinates as needed for your template)
        $pdf->SetXY(125,44); // Date
        $pdf->Write(0, $date);
        // $pdf->SetXY(60, 30); // Year
        // $pdf->Write(0, $year);
        $pdf->SetXY(55, 62); // Lastname
        $pdf->Write(0, $lastname);
        $pdf->SetXY(90, 62); // Firstname
        $pdf->Write(0, $firstname);
        $pdf->SetXY(125, 62); // Middle Initial
        $pdf->Write(0, $middleinitial);
        $pdf->SetXY(149, 62); // Grade Level
        $pdf->Write(0, $gradelevel);
        $pdf->SetXY(30, 60); // Entrance Fee
        $pdf->Write(0, $entranceFee);
        $pdf->SetXY(30, 70); // Miscellaneous Fee
        $pdf->Write(0, $miscFee);
        $pdf->SetXY(75, 139); // Tuition Fee
        $pdf->Write(0, 'Tuition Fee');
        $pdf->SetXY(142, 139); // Tuition Fee
        $pdf->Write(0, '21,500.00'); // Always display full payment amount
        $pdf->SetXY(30, 90); // Others Fee
         $pdf->Write(0, $othersFee);
        $pdf->SetXY(143, 147); // Total Fee
        $pdf->Write(0, $totalFee);
        $pdf->SetXY(57, 161); // Amount in Words
        $pdf->Write(0, $amountInWords);
        $pdf->SetXY(120, 185); // Cashier Name
        $pdf->Write(0, $cashierName);

        return response($pdf->Output('Cashier-Receipt.pdf', 'S'))->header('Content-Type', 'application/pdf');
    }




public function generateReportCardPdf(Student $student)
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
            abort(403, 'You are not the adviser for this student.');
        }

        // Get student's subjects
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

        // Load PDF template
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $templatePath = resource_path('assets/pdf-forms-generation/Report Card HS.pdf');
        if (!file_exists($templatePath)) {
            abort(404, 'Report Card PDF template not found.');
        }
        $pageCount = $pdf->setSourceFile($templatePath);
        // Always show both pages (even if only one is needed)
        for ($pageNum = 1; $pageNum <= min(2, $pageCount); $pageNum++) {
            $tplId = $pdf->importPage($pageNum);
            $size = $pdf->getTemplateSize($tplId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            if ($pageNum === 1) {
                $pdf->SetFont('dejavusans', '', 10);
                $pdf->useTemplate($tplId);
                // Overlay grades and averages on page 1, show '-' if null
                $pdf->SetXY(53, 39); $pdf->Write(0, isset($gradesData[0]['quarters']['1st']) && $gradesData[0]['quarters']['1st'] !== null ? ((intval($gradesData[0]['quarters']['1st']) == floatval($gradesData[0]['quarters']['1st'])) ? intval($gradesData[0]['quarters']['1st']) : number_format($gradesData[0]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 39); $pdf->Write(0, isset($gradesData[0]['quarters']['2nd']) && $gradesData[0]['quarters']['2nd'] !== null ? ((intval($gradesData[0]['quarters']['2nd']) == floatval($gradesData[0]['quarters']['2nd'])) ? intval($gradesData[0]['quarters']['2nd']) : number_format($gradesData[0]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 39); $pdf->Write(0, isset($gradesData[0]['quarters']['3rd']) && $gradesData[0]['quarters']['3rd'] !== null ? ((intval($gradesData[0]['quarters']['3rd']) == floatval($gradesData[0]['quarters']['3rd'])) ? intval($gradesData[0]['quarters']['3rd']) : number_format($gradesData[0]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(87, 39); $pdf->Write(0, isset($gradesData[0]['quarters']['4th']) && $gradesData[0]['quarters']['4th'] !== null ? ((intval($gradesData[0]['quarters']['4th']) == floatval($gradesData[0]['quarters']['4th'])) ? intval($gradesData[0]['quarters']['4th']) : number_format($gradesData[0]['quarters']['4th'], 1)) : '');

                $pdf->SetXY(53, 45); $pdf->Write(0, isset($gradesData[1]['quarters']['1st']) && $gradesData[1]['quarters']['1st'] !== null ? ((intval($gradesData[1]['quarters']['1st']) == floatval($gradesData[1]['quarters']['1st'])) ? intval($gradesData[1]['quarters']['1st']) : number_format($gradesData[1]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 45); $pdf->Write(0, isset($gradesData[1]['quarters']['2nd']) && $gradesData[1]['quarters']['2nd'] !== null ? ((intval($gradesData[1]['quarters']['2nd']) == floatval($gradesData[1]['quarters']['2nd'])) ? intval($gradesData[1]['quarters']['2nd']) : number_format($gradesData[1]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 45); $pdf->Write(0, isset($gradesData[1]['quarters']['3rd']) && $gradesData[1]['quarters']['3rd'] !== null ? ((intval($gradesData[1]['quarters']['3rd']) == floatval($gradesData[1]['quarters']['3rd'])) ? intval($gradesData[1]['quarters']['3rd']) : number_format($gradesData[1]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(86, 45); $pdf->Write(0, isset($gradesData[1]['quarters']['4th']) && $gradesData[1]['quarters']['4th'] !== null ? ((intval($gradesData[1]['quarters']['4th']) == floatval($gradesData[1]['quarters']['4th'])) ? intval($gradesData[1]['quarters']['4th']) : number_format($gradesData[1]['quarters']['4th'], 1)) : '');

                $pdf->SetXY(53, 53); $pdf->Write(0, isset($gradesData[2]['quarters']['1st']) && $gradesData[2]['quarters']['1st'] !== null ? ((intval($gradesData[2]['quarters']['1st']) == floatval($gradesData[2]['quarters']['1st'])) ? intval($gradesData[2]['quarters']['1st']) : number_format($gradesData[2]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 53); $pdf->Write(0, isset($gradesData[2]['quarters']['2nd']) && $gradesData[2]['quarters']['2nd'] !== null ? ((intval($gradesData[2]['quarters']['2nd']) == floatval($gradesData[2]['quarters']['2nd'])) ? intval($gradesData[2]['quarters']['2nd']) : number_format($gradesData[2]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 53); $pdf->Write(0, isset($gradesData[2]['quarters']['3rd']) && $gradesData[2]['quarters']['3rd'] !== null ? ((intval($gradesData[2]['quarters']['3rd']) == floatval($gradesData[2]['quarters']['3rd'])) ? intval($gradesData[2]['quarters']['3rd']) : number_format($gradesData[2]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(86, 53); $pdf->Write(0, isset($gradesData[2]['quarters']['4th']) && $gradesData[2]['quarters']['4th'] !== null ? ((intval($gradesData[2]['quarters']['4th']) == floatval($gradesData[2]['quarters']['4th'])) ? intval($gradesData[2]['quarters']['4th']) : number_format($gradesData[2]['quarters']['4th'], 1)) : '');

                $pdf->SetXY(53, 61); $pdf->Write(0, isset($gradesData[3]['quarters']['1st']) && $gradesData[3]['quarters']['1st'] !== null ? ((intval($gradesData[3]['quarters']['1st']) == floatval($gradesData[3]['quarters']['1st'])) ? intval($gradesData[3]['quarters']['1st']) : number_format($gradesData[3]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 61); $pdf->Write(0, isset($gradesData[3]['quarters']['2nd']) && $gradesData[3]['quarters']['2nd'] !== null ? ((intval($gradesData[3]['quarters']['2nd']) == floatval($gradesData[3]['quarters']['2nd'])) ? intval($gradesData[3]['quarters']['2nd']) : number_format($gradesData[3]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 61); $pdf->Write(0, isset($gradesData[3]['quarters']['3rd']) && $gradesData[3]['quarters']['3rd'] !== null ? ((intval($gradesData[3]['quarters']['3rd']) == floatval($gradesData[3]['quarters']['3rd'])) ? intval($gradesData[3]['quarters']['3rd']) : number_format($gradesData[3]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(86, 61); $pdf->Write(0, isset($gradesData[3]['quarters']['4th']) && $gradesData[3]['quarters']['4th'] !== null ? ((intval($gradesData[3]['quarters']['4th']) == floatval($gradesData[3]['quarters']['4th'])) ? intval($gradesData[3]['quarters']['4th']) : number_format($gradesData[3]['quarters']['4th'], 1)) : '');

                $pdf->SetXY(53, 68); $pdf->Write(0, isset($gradesData[4]['quarters']['1st']) && $gradesData[4]['quarters']['1st'] !== null ? ((intval($gradesData[4]['quarters']['1st']) == floatval($gradesData[4]['quarters']['1st'])) ? intval($gradesData[4]['quarters']['1st']) : number_format($gradesData[4]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 68); $pdf->Write(0, isset($gradesData[4]['quarters']['2nd']) && $gradesData[4]['quarters']['2nd'] !== null ? ((intval($gradesData[4]['quarters']['2nd']) == floatval($gradesData[4]['quarters']['2nd'])) ? intval($gradesData[4]['quarters']['2nd']) : number_format($gradesData[4]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 68); $pdf->Write(0, isset($gradesData[4]['quarters']['3rd']) && $gradesData[4]['quarters']['3rd'] !== null ? ((intval($gradesData[4]['quarters']['3rd']) == floatval($gradesData[4]['quarters']['3rd'])) ? intval($gradesData[4]['quarters']['3rd']) : number_format($gradesData[4]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(86, 68); $pdf->Write(0, isset($gradesData[4]['quarters']['4th']) && $gradesData[4]['quarters']['4th'] !== null ? ((intval($gradesData[4]['quarters']['4th']) == floatval($gradesData[4]['quarters']['4th'])) ? intval($gradesData[4]['quarters']['4th']) : number_format($gradesData[4]['quarters']['4th'], 1)) : '');

                $pdf->SetXY(53, 77); $pdf->Write(0, isset($gradesData[5]['quarters']['1st']) && $gradesData[5]['quarters']['1st'] !== null ? ((intval($gradesData[5]['quarters']['1st']) == floatval($gradesData[5]['quarters']['1st'])) ? intval($gradesData[5]['quarters']['1st']) : number_format($gradesData[5]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 77); $pdf->Write(0, isset($gradesData[5]['quarters']['2nd']) && $gradesData[5]['quarters']['2nd'] !== null ? ((intval($gradesData[5]['quarters']['2nd']) == floatval($gradesData[5]['quarters']['2nd'])) ? intval($gradesData[5]['quarters']['2nd']) : number_format($gradesData[5]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 77); $pdf->Write(0, isset($gradesData[5]['quarters']['3rd']) && $gradesData[5]['quarters']['3rd'] !== null ? ((intval($gradesData[5]['quarters']['3rd']) == floatval($gradesData[5]['quarters']['3rd'])) ? intval($gradesData[5]['quarters']['3rd']) : number_format($gradesData[5]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(86, 77); $pdf->Write(0, isset($gradesData[5]['quarters']['4th']) && $gradesData[5]['quarters']['4th'] !== null ? ((intval($gradesData[5]['quarters']['4th']) == floatval($gradesData[5]['quarters']['4th'])) ? intval($gradesData[5]['quarters']['4th']) : number_format($gradesData[5]['quarters']['4th'], 1)) : '');

                $pdf->SetXY(53, 91); $pdf->Write(0, isset($gradesData[6]['quarters']['1st']) && $gradesData[6]['quarters']['1st'] !== null ? ((intval($gradesData[6]['quarters']['1st']) == floatval($gradesData[6]['quarters']['1st'])) ? intval($gradesData[6]['quarters']['1st']) : number_format($gradesData[6]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 91); $pdf->Write(0, isset($gradesData[6]['quarters']['2nd']) && $gradesData[6]['quarters']['2nd'] !== null ? ((intval($gradesData[6]['quarters']['2nd']) == floatval($gradesData[6]['quarters']['2nd'])) ? intval($gradesData[6]['quarters']['2nd']) : number_format($gradesData[6]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 91); $pdf->Write(0, isset($gradesData[6]['quarters']['3rd']) && $gradesData[6]['quarters']['3rd'] !== null ? ((intval($gradesData[6]['quarters']['3rd']) == floatval($gradesData[6]['quarters']['3rd'])) ? intval($gradesData[6]['quarters']['3rd']) : number_format($gradesData[6]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(86, 91); $pdf->Write(0, isset($gradesData[6]['quarters']['4th']) && $gradesData[6]['quarters']['4th'] !== null ? ((intval($gradesData[6]['quarters']['4th']) == floatval($gradesData[6]['quarters']['4th'])) ? intval($gradesData[6]['quarters']['4th']) : number_format($gradesData[6]['quarters']['4th'], 1)) : '');

                // Compute and display MAPEH grade (average of subjects 6, 8, 9, 10 for each quarter)
                if (isset($gradesData[6], $gradesData[8], $gradesData[9], $gradesData[10])) {
                    $quarters = ['1st', '2nd', '3rd', '4th'];
                    foreach ($quarters as $qIndex => $quarter) {
                        $sum = 0;
                        $count = 0;
                        foreach ([6, 8, 9, 10] as $idx) {
                            $grade = isset($gradesData[$idx]['quarters'][$quarter]) ? $gradesData[$idx]['quarters'][$quarter] : null;
                            if ($grade !== null) {
                                $sum += $grade;
                                $count++;
                            }
                        }
                        $mapehGrade = $count > 0 ? round($sum / $count, 2) : '';
                        // Place the MAPEH grade below subject 6 (adjust Y as needed)
                        $x = 52 + ($qIndex * 12);
                        $y = 85; // Y value just below subject 6
                        $pdf->SetXY($x, $y);
                        $pdf->Write(0, $mapehGrade !== '' ? ((intval($mapehGrade) == floatval($mapehGrade)) ? intval($mapehGrade) : number_format($mapehGrade, 1)) : '');
                    }
                    // Label the row
                    $pdf->SetXY(40, 97); $pdf->Write(0, '');
                }
 
                $pdf->SetXY(53, 123); $pdf->Write(0, isset($gradesData[7]['quarters']['1st']) && $gradesData[7]['quarters']['1st'] !== null ? ((intval($gradesData[7]['quarters']['1st']) == floatval($gradesData[7]['quarters']['1st'])) ? intval($gradesData[7]['quarters']['1st']) : number_format($gradesData[7]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 123); $pdf->Write(0, isset($gradesData[7]['quarters']['2nd']) && $gradesData[7]['quarters']['2nd'] !== null ? ((intval($gradesData[7]['quarters']['2nd']) == floatval($gradesData[7]['quarters']['2nd'])) ? intval($gradesData[7]['quarters']['2nd']) : number_format($gradesData[7]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 123); $pdf->Write(0, isset($gradesData[7]['quarters']['3rd']) && $gradesData[7]['quarters']['3rd'] !== null ? ((intval($gradesData[7]['quarters']['3rd']) == floatval($gradesData[7]['quarters']['3rd'])) ? intval($gradesData[7]['quarters']['3rd']) : number_format($gradesData[7]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(87, 123); $pdf->Write(0, isset($gradesData[7]['quarters']['4th']) && $gradesData[7]['quarters']['4th'] !== null ? ((intval($gradesData[7]['quarters']['4th']) == floatval($gradesData[7]['quarters']['4th'])) ? intval($gradesData[7]['quarters']['4th']) : number_format($gradesData[7]['quarters']['4th'], 1)) : '');

                // Fetch grades for subjects 8, 9, 10 if they exist
                if (isset($gradesData[8])) {
                    $pdf->SetXY(53, 99); $pdf->Write(0, isset($gradesData[8]['quarters']['1st']) && $gradesData[8]['quarters']['1st'] !== null ? ((intval($gradesData[8]['quarters']['1st']) == floatval($gradesData[8]['quarters']['1st'])) ? intval($gradesData[8]['quarters']['1st']) : number_format($gradesData[8]['quarters']['1st'], 1)) : '');
                    $pdf->SetXY(65, 99); $pdf->Write(0, isset($gradesData[8]['quarters']['2nd']) && $gradesData[8]['quarters']['2nd'] !== null ? ((intval($gradesData[8]['quarters']['2nd']) == floatval($gradesData[8]['quarters']['2nd'])) ? intval($gradesData[8]['quarters']['2nd']) : number_format($gradesData[8]['quarters']['2nd'], 1)) : '');
                    $pdf->SetXY(75, 99); $pdf->Write(0, isset($gradesData[8]['quarters']['3rd']) && $gradesData[8]['quarters']['3rd'] !== null ? ((intval($gradesData[8]['quarters']['3rd']) == floatval($gradesData[8]['quarters']['3rd'])) ? intval($gradesData[8]['quarters']['3rd']) : number_format($gradesData[8]['quarters']['3rd'], 1)) : '');
                    $pdf->SetXY(87, 99); $pdf->Write(0, isset($gradesData[8]['quarters']['4th']) && $gradesData[8]['quarters']['4th'] !== null ? ((intval($gradesData[8]['quarters']['4th']) == floatval($gradesData[8]['quarters']['4th'])) ? intval($gradesData[8]['quarters']['4th']) : number_format($gradesData[8]['quarters']['4th'], 1)) : '');
                }
                if (isset($gradesData[9])) {
                    $pdf->SetXY(53, 106); $pdf->Write(0, isset($gradesData[9]['quarters']['1st']) && $gradesData[9]['quarters']['1st'] !== null ? ((intval($gradesData[9]['quarters']['1st']) == floatval($gradesData[9]['quarters']['1st'])) ? intval($gradesData[9]['quarters']['1st']) : number_format($gradesData[9]['quarters']['1st'], 1)) : '');
                    $pdf->SetXY(65, 106); $pdf->Write(0, isset($gradesData[9]['quarters']['2nd']) && $gradesData[9]['quarters']['2nd'] !== null ? ((intval($gradesData[9]['quarters']['2nd']) == floatval($gradesData[9]['quarters']['2nd'])) ? intval($gradesData[9]['quarters']['2nd']) : number_format($gradesData[9]['quarters']['2nd'], 1)) : '');
                    $pdf->SetXY(75, 106); $pdf->Write(0, isset($gradesData[9]['quarters']['3rd']) && $gradesData[9]['quarters']['3rd'] !== null ? ((intval($gradesData[9]['quarters']['3rd']) == floatval($gradesData[9]['quarters']['3rd'])) ? intval($gradesData[9]['quarters']['3rd']) : number_format($gradesData[9]['quarters']['3rd'], 1)) : '');
                    $pdf->SetXY(87, 106); $pdf->Write(0, isset($gradesData[9]['quarters']['4th']) && $gradesData[9]['quarters']['4th'] !== null ? ((intval($gradesData[9]['quarters']['4th']) == floatval($gradesData[9]['quarters']['4th'])) ? intval($gradesData[9]['quarters']['4th']) : number_format($gradesData[9]['quarters']['4th'], 1)) : '');
                }
                if (isset($gradesData[10])) {
                    $pdf->SetXY(53, 113); $pdf->Write(0, isset($gradesData[10]['quarters']['1st']) && $gradesData[10]['quarters']['1st'] !== null ? ((intval($gradesData[10]['quarters']['1st']) == floatval($gradesData[10]['quarters']['1st'])) ? intval($gradesData[10]['quarters']['1st']) : number_format($gradesData[10]['quarters']['1st'], 1)) : '');
                    $pdf->SetXY(65, 113); $pdf->Write(0, isset($gradesData[10]['quarters']['2nd']) && $gradesData[10]['quarters']['2nd'] !== null ? ((intval($gradesData[10]['quarters']['2nd']) == floatval($gradesData[10]['quarters']['2nd'])) ? intval($gradesData[10]['quarters']['2nd']) : number_format($gradesData[10]['quarters']['2nd'], 1)) : '');
                    $pdf->SetXY(75, 113); $pdf->Write(0, isset($gradesData[10]['quarters']['3rd']) && $gradesData[10]['quarters']['3rd'] !== null ? ((intval($gradesData[10]['quarters']['3rd']) == floatval($gradesData[10]['quarters']['3rd'])) ? intval($gradesData[10]['quarters']['3rd']) : number_format($gradesData[10]['quarters']['3rd'], 1)) : '');
                    $pdf->SetXY(87, 113); $pdf->Write(0, isset($gradesData[10]['quarters']['4th']) && $gradesData[10]['quarters']['4th'] !== null ? ((intval($gradesData[10]['quarters']['4th']) == floatval($gradesData[10]['quarters']['4th'])) ? intval($gradesData[10]['quarters']['4th']) : number_format($gradesData[10]['quarters']['4th'], 1)) : '');
                }

                // Compute and display General Average (DepEd JHS)
                $finalGrades = [];
                foreach ($gradesData as $subjectData) {
                    // Compute subject final grade as the average of all available quarters
                    $subjectQuarters = array_filter($subjectData['quarters'], function($g) { return $g !== null; });
                    if (count($subjectQuarters) > 0) {
                        $finalGrades[] = array_sum($subjectQuarters) / count($subjectQuarters);
                    }
                }
                $generalAverage = count($finalGrades) > 0 ? round(array_sum($finalGrades) / count($finalGrades), 2) : '';
                // Place the General Average below the last subject (adjust Y as needed)
                $pdf->SetXY(40, 120); $pdf->Write(0, '');
                $pdf->SetXY(113, 144); $pdf->Write(0, $generalAverage !== '' ? ((intval($generalAverage) == floatval($generalAverage)) ? intval($generalAverage) : number_format($generalAverage, 1)) : '');
            } else if ($pageNum === 2) {
                $pdf->SetFont('dejavusans', '', 11);
                $pdf->useTemplate($tplId);
                // Overlay LRN, student name, age, gradelevel, school year, gender, section, adviser name
                $adviserName = $advisoryAssignment->teacher->full_name ?? ($advisoryAssignment->teacher->name ?? '');
                $schoolYear = $currentAcademicYear;
                $age = $student->age ?? '';
                $pdf->SetXY(225, 17); $pdf->Write(0, '' . ($student->lrn ?? ''));
                $pdf->SetXY(159,163); $pdf->Write(0, $student->full_name ?? '');
                $pdf->SetXY(155, 173); $pdf->Write(0, '' . $age);
                $pdf->SetXY(160, 182); $pdf->Write(0, '' . ($student->grade_level ?? ''));
                $pdf->SetXY(168, 191); $pdf->Write(0, '' . $schoolYear);
                $pdf->SetXY(220, 173); $pdf->Write(0, '' . ($student->gender ?? ''));
                $pdf->SetXY(220, 183); $pdf->Write(0, '' . ($student->section ?? ''));
                $pdf->SetXY(76, 98); $pdf->Write(0, '' . $adviserName);
            }
        }
        return response($pdf->Output('Report-Card.pdf', 'S'))->header('Content-Type', 'application/pdf');
    } catch (\Exception $e) {
        return response('Error generating report card: ' . $e->getMessage(), 500);
    }
}  

/**
 * Generate Grade 11 Report Card PDF for senior high school students
 */
public function generateGrade11ReportCardPdf(Student $student)
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
            abort(403, 'You are not the adviser for this student.');
        }

        // Get student's subjects with specific Grade 11 filtering
        $subjects = Subject::where('grade_level', $student->grade_level)
            ->where('academic_year', $currentAcademicYear)
            ->where('is_active', true);

        // Grade 11 specific subject filtering by strand and track
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

        // Load PDF template for Grade 11 (Senior High School)
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $templatePath = resource_path('assets/pdf-forms-generation/STEM 11.pdf');
        if (!file_exists($templatePath)) {
            abort(404, 'STEM Grade 11 Report Card PDF template not found.');
        }
        $pageCount = $pdf->setSourceFile($templatePath);
        // Always show both pages (even if only one is needed)
        for ($pageNum = 1; $pageNum <= min(2, $pageCount); $pageNum++) {
            $tplId = $pdf->importPage($pageNum);
            $size = $pdf->getTemplateSize($tplId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            if ($pageNum === 1) {
                $pdf->SetFont('dejavusans', '', 11);
                $pdf->useTemplate($tplId);
                // Overlay student information for Grade 11 on page 1
                $adviserName = $advisoryAssignment->teacher->full_name ?? ($advisoryAssignment->teacher->name ?? '');
                $schoolYear = $currentAcademicYear;
                $age = $student->age ?? '';
                
                // Student Information (adjust coordinates for Grade 11 template)
                $pdf->SetXY(162, 161); $pdf->Write(0, $student->full_name ?? '');
                $pdf->SetXY(162, 170); $pdf->Write(0, '' . $age);
                $pdf->SetXY(160, 178); $pdf->Write(0, '11');
                $pdf->SetXY(220, 170); $pdf->Write(0, '' . ($student->gender ?? ''));
                
                // Grade 11 specific fields
                $pdf->SetXY(87, 58); $pdf->Write(0, '' . $adviserName);
                $pdf->SetXY(200, 178); $pdf->Write(0, '' . ($student->strand ?? ''));
                $pdf->SetXY(220, 98); $pdf->Write(0, '' . ($student->track ?? '-'));
            } else if ($pageNum === 2) {
                $pdf->SetFont('dejavusans', '', 10);
                $pdf->useTemplate($tplId);
                // Overlay grades and averages on page 2, show '-' if null
                // Grade 11 STEM Semester-Based Subject Mapping:
                // FIRST SEMESTER (1st & 2nd Quarter) vs SECOND SEMESTER (3rd & 4th Quarter)
                // 
                // Core Subjects:
                // [0] Oral Communication (1st/2nd) → [7] Reading and Writing Skills (3rd/4th)  
                // [1] General Mathematics (1st/2nd) → [8] 21st Century Literature (3rd/4th)
                // [2] Earth and Life Science (1st/2nd) → [9] Statistics and Probability (3rd/4th)
                // [3] Komunikasyon at Pananaliksik sa Wika (1st/2nd) → [10] Pagbasa at Pagsusuri ng Iba't – ibang Teksto (3rd/4th)
                // [4] Personal Development (1st/2nd) → [11] Physical Education and Health 2 (3rd/4th)
                // [5] Understanding Culture, Society, and Politics (1st/2nd) → [12] Research in Daily Life 1 (3rd/4th)
                // [6] Physical Education and Health 1 (1st/2nd) → [13] Empowerment Technologies (3rd/4th)
                // 
                // Specialized Subjects:
                // [7] Pre-Calculus (1st/2nd) → [14] Basic Calculus (3rd/4th)
                // [8] General Chemistry 1 (1st/2nd) → [15] General Chemistry 2 (3rd/4th)
                
                // Position 1: Oral Communication (1st/2nd) | Reading and Writing Skills (3rd/4th)
                $pdf->SetXY(76, 41); $pdf->Write(0, isset($gradesData[0]['quarters']['1st']) && $gradesData[0]['quarters']['1st'] !== null ? ((intval($gradesData[0]['quarters']['1st']) == floatval($gradesData[0]['quarters']['1st'])) ? intval($gradesData[0]['quarters']['1st']) : number_format($gradesData[0]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(89, 41); $pdf->Write(0, isset($gradesData[0]['quarters']['2nd']) && $gradesData[0]['quarters']['2nd'] !== null ? ((intval($gradesData[0]['quarters']['2nd']) == floatval($gradesData[0]['quarters']['2nd'])) ? intval($gradesData[0]['quarters']['2nd']) : number_format($gradesData[0]['quarters']['2nd'], 1)) : '');
                // Semester 1 Final Grade for Position 1
                $sem1Grades = array_filter([isset($gradesData[0]['quarters']['1st']) ? $gradesData[0]['quarters']['1st'] : null, isset($gradesData[0]['quarters']['2nd']) ? $gradesData[0]['quarters']['2nd'] : null], function($g) { return $g !== null; });
                $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
                $pdf->SetXY(102, 41); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
                $pdf->SetXY(75, 120); $pdf->Write(0, isset($gradesData[7]['quarters']['3rd']) && $gradesData[7]['quarters']['3rd'] !== null ? ((intval($gradesData[7]['quarters']['3rd']) == floatval($gradesData[7]['quarters']['3rd'])) ? intval($gradesData[7]['quarters']['3rd']) : number_format($gradesData[7]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(89, 120); $pdf->Write(0, isset($gradesData[7]['quarters']['4th']) && $gradesData[7]['quarters']['4th'] !== null ? ((intval($gradesData[7]['quarters']['4th']) == floatval($gradesData[7]['quarters']['4th'])) ? intval($gradesData[7]['quarters']['4th']) : number_format($gradesData[7]['quarters']['4th'], 1)) : '');
                // Semester 2 Final Grade for Position 1
                $sem2Grades = array_filter([isset($gradesData[7]['quarters']['3rd']) ? $gradesData[7]['quarters']['3rd'] : null, isset($gradesData[7]['quarters']['4th']) ? $gradesData[7]['quarters']['4th'] : null], function($g) { return $g !== null; });
                $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
                $pdf->SetXY(102, 120); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

                // Position 2: General Mathematics (1st/2nd) | 21st Century Literature (3rd/4th)
                $pdf->SetXY(76, 46); $pdf->Write(0, isset($gradesData[1]['quarters']['1st']) && $gradesData[1]['quarters']['1st'] !== null ? ((intval($gradesData[1]['quarters']['1st']) == floatval($gradesData[1]['quarters']['1st'])) ? intval($gradesData[1]['quarters']['1st']) : number_format($gradesData[1]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(89, 46); $pdf->Write(0, isset($gradesData[1]['quarters']['2nd']) && $gradesData[1]['quarters']['2nd'] !== null ? ((intval($gradesData[1]['quarters']['2nd']) == floatval($gradesData[1]['quarters']['2nd'])) ? intval($gradesData[1]['quarters']['2nd']) : number_format($gradesData[1]['quarters']['2nd'], 1)) : '');
                $sem1Grades = array_filter([isset($gradesData[1]['quarters']['1st']) ? $gradesData[1]['quarters']['1st'] : null, isset($gradesData[1]['quarters']['2nd']) ? $gradesData[1]['quarters']['2nd'] : null], function($g) { return $g !== null; });
                $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
                $pdf->SetXY(102, 46); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
                $pdf->SetXY(75, 125); $pdf->Write(0, isset($gradesData[8]['quarters']['3rd']) && $gradesData[8]['quarters']['3rd'] !== null ? ((intval($gradesData[8]['quarters']['3rd']) == floatval($gradesData[8]['quarters']['3rd'])) ? intval($gradesData[8]['quarters']['3rd']) : number_format($gradesData[8]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(89, 125); $pdf->Write(0, isset($gradesData[8]['quarters']['4th']) && $gradesData[8]['quarters']['4th'] !== null ? ((intval($gradesData[8]['quarters']['4th']) == floatval($gradesData[8]['quarters']['4th'])) ? intval($gradesData[8]['quarters']['4th']) : number_format($gradesData[8]['quarters']['4th'], 1)) : '');
                $sem2Grades = array_filter([isset($gradesData[8]['quarters']['3rd']) ? $gradesData[8]['quarters']['3rd'] : null, isset($gradesData[8]['quarters']['4th']) ? $gradesData[8]['quarters']['4th'] : null], function($g) { return $g !== null; });
                $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
                $pdf->SetXY(102, 125); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

                // Position 3: Earth and Life Science (1st/2nd) | Statistics and Probability (3rd/4th)
                $pdf->SetXY(76, 51); $pdf->Write(0, isset($gradesData[2]['quarters']['1st']) && $gradesData[2]['quarters']['1st'] !== null ? ((intval($gradesData[2]['quarters']['1st']) == floatval($gradesData[2]['quarters']['1st'])) ? intval($gradesData[2]['quarters']['1st']) : number_format($gradesData[2]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(89, 51); $pdf->Write(0, isset($gradesData[2]['quarters']['2nd']) && $gradesData[2]['quarters']['2nd'] !== null ? ((intval($gradesData[2]['quarters']['2nd']) == floatval($gradesData[2]['quarters']['2nd'])) ? intval($gradesData[2]['quarters']['2nd']) : number_format($gradesData[2]['quarters']['2nd'], 1)) : '');
                $sem1Grades = array_filter([isset($gradesData[2]['quarters']['1st']) ? $gradesData[2]['quarters']['1st'] : null, isset($gradesData[2]['quarters']['2nd']) ? $gradesData[2]['quarters']['2nd'] : null], function($g) { return $g !== null; });
                $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
                $pdf->SetXY(102, 51); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
                $pdf->SetXY(75, 131); $pdf->Write(0, isset($gradesData[9]['quarters']['3rd']) && $gradesData[9]['quarters']['3rd'] !== null ? ((intval($gradesData[9]['quarters']['3rd']) == floatval($gradesData[9]['quarters']['3rd'])) ? intval($gradesData[9]['quarters']['3rd']) : number_format($gradesData[9]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(89, 131); $pdf->Write(0, isset($gradesData[9]['quarters']['4th']) && $gradesData[9]['quarters']['4th'] !== null ? ((intval($gradesData[9]['quarters']['4th']) == floatval($gradesData[9]['quarters']['4th'])) ? intval($gradesData[9]['quarters']['4th']) : number_format($gradesData[9]['quarters']['4th'], 1)) : '');
                $sem2Grades = array_filter([isset($gradesData[9]['quarters']['3rd']) ? $gradesData[9]['quarters']['3rd'] : null, isset($gradesData[9]['quarters']['4th']) ? $gradesData[9]['quarters']['4th'] : null], function($g) { return $g !== null; });
                $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
                $pdf->SetXY(102, 131); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

                // Position 4: Komunikasyon at Pananaliksik sa Wika (1st/2nd) | Pagbasa at Pagsusuri ng Iba't – ibang Teksto (3rd/4th)
                $pdf->SetXY(76, 56); $pdf->Write(0, isset($gradesData[3]['quarters']['1st']) && $gradesData[3]['quarters']['1st'] !== null ? ((intval($gradesData[3]['quarters']['1st']) == floatval($gradesData[3]['quarters']['1st'])) ? intval($gradesData[3]['quarters']['1st']) : number_format($gradesData[3]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(89, 57); $pdf->Write(0, isset($gradesData[3]['quarters']['2nd']) && $gradesData[3]['quarters']['2nd'] !== null ? ((intval($gradesData[3]['quarters']['2nd']) == floatval($gradesData[3]['quarters']['2nd'])) ? intval($gradesData[3]['quarters']['2nd']) : number_format($gradesData[3]['quarters']['2nd'], 1)) : '');
                $sem1Grades = array_filter([isset($gradesData[3]['quarters']['1st']) ? $gradesData[3]['quarters']['1st'] : null, isset($gradesData[3]['quarters']['2nd']) ? $gradesData[3]['quarters']['2nd'] : null], function($g) { return $g !== null; });
                $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
                $pdf->SetXY(102, 56); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
                $pdf->SetXY(75, 134); $pdf->Write(0, isset($gradesData[10]['quarters']['3rd']) && $gradesData[10]['quarters']['3rd'] !== null ? ((intval($gradesData[10]['quarters']['3rd']) == floatval($gradesData[10]['quarters']['3rd'])) ? intval($gradesData[10]['quarters']['3rd']) : number_format($gradesData[10]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(89, 134); $pdf->Write(0, isset($gradesData[10]['quarters']['4th']) && $gradesData[10]['quarters']['4th'] !== null ? ((intval($gradesData[10]['quarters']['4th']) == floatval($gradesData[10]['quarters']['4th'])) ? intval($gradesData[10]['quarters']['4th']) : number_format($gradesData[10]['quarters']['4th'], 1)) : '');
                $sem2Grades = array_filter([isset($gradesData[10]['quarters']['3rd']) ? $gradesData[10]['quarters']['3rd'] : null, isset($gradesData[10]['quarters']['4th']) ? $gradesData[10]['quarters']['4th'] : null], function($g) { return $g !== null; });
                $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
                $pdf->SetXY(102, 134); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

                // Position 5: Personal Development (1st/2nd) | Physical Education and Health 2 (3rd/4th)
                $pdf->SetXY(76, 62); $pdf->Write(0, isset($gradesData[4]['quarters']['1st']) && $gradesData[4]['quarters']['1st'] !== null ? ((intval($gradesData[4]['quarters']['1st']) == floatval($gradesData[4]['quarters']['1st'])) ? intval($gradesData[4]['quarters']['1st']) : number_format($gradesData[4]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(89, 62); $pdf->Write(0, isset($gradesData[4]['quarters']['2nd']) && $gradesData[4]['quarters']['2nd'] !== null ? ((intval($gradesData[4]['quarters']['2nd']) == floatval($gradesData[4]['quarters']['2nd'])) ? intval($gradesData[4]['quarters']['2nd']) : number_format($gradesData[4]['quarters']['2nd'], 1)) : '');
                $sem1Grades = array_filter([isset($gradesData[4]['quarters']['1st']) ? $gradesData[4]['quarters']['1st'] : null, isset($gradesData[4]['quarters']['2nd']) ? $gradesData[4]['quarters']['2nd'] : null], function($g) { return $g !== null; });
                $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
                $pdf->SetXY(102, 62); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
                $pdf->SetXY(75, 142); $pdf->Write(0, isset($gradesData[11]['quarters']['3rd']) && $gradesData[11]['quarters']['3rd'] !== null ? ((intval($gradesData[11]['quarters']['3rd']) == floatval($gradesData[11]['quarters']['3rd'])) ? intval($gradesData[11]['quarters']['3rd']) : number_format($gradesData[11]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(89, 142); $pdf->Write(0, isset($gradesData[11]['quarters']['4th']) && $gradesData[11]['quarters']['4th'] !== null ? ((intval($gradesData[11]['quarters']['4th']) == floatval($gradesData[11]['quarters']['4th'])) ? intval($gradesData[11]['quarters']['4th']) : number_format($gradesData[11]['quarters']['4th'], 1)) : '');
                $sem2Grades = array_filter([isset($gradesData[11]['quarters']['3rd']) ? $gradesData[11]['quarters']['3rd'] : null, isset($gradesData[11]['quarters']['4th']) ? $gradesData[11]['quarters']['4th'] : null], function($g) { return $g !== null; });
                $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
                $pdf->SetXY(102, 142); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

                // Position 6: Understanding Culture, Society, and Politics (1st/2nd) | Research in Daily Life 1 (3rd/4th)
                $pdf->SetXY(76, 67); $pdf->Write(0, isset($gradesData[5]['quarters']['1st']) && $gradesData[5]['quarters']['1st'] !== null ? ((intval($gradesData[5]['quarters']['1st']) == floatval($gradesData[5]['quarters']['1st'])) ? intval($gradesData[5]['quarters']['1st']) : number_format($gradesData[5]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(89, 67); $pdf->Write(0, isset($gradesData[5]['quarters']['2nd']) && $gradesData[5]['quarters']['2nd'] !== null ? ((intval($gradesData[5]['quarters']['2nd']) == floatval($gradesData[5]['quarters']['2nd'])) ? intval($gradesData[5]['quarters']['2nd']) : number_format($gradesData[5]['quarters']['2nd'], 1)) : '');
                $sem1Grades = array_filter([isset($gradesData[5]['quarters']['1st']) ? $gradesData[5]['quarters']['1st'] : null, isset($gradesData[5]['quarters']['2nd']) ? $gradesData[5]['quarters']['2nd'] : null], function($g) { return $g !== null; });
                $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
                $pdf->SetXY(102, 67); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
                $pdf->SetXY(75, 151); $pdf->Write(0, isset($gradesData[12]['quarters']['3rd']) && $gradesData[12]['quarters']['3rd'] !== null ? ((intval($gradesData[12]['quarters']['3rd']) == floatval($gradesData[12]['quarters']['3rd'])) ? intval($gradesData[12]['quarters']['3rd']) : number_format($gradesData[12]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(89, 151); $pdf->Write(0, isset($gradesData[12]['quarters']['4th']) && $gradesData[12]['quarters']['4th'] !== null ? ((intval($gradesData[12]['quarters']['4th']) == floatval($gradesData[12]['quarters']['4th'])) ? intval($gradesData[12]['quarters']['4th']) : number_format($gradesData[12]['quarters']['4th'], 1)) : '');
                $sem2Grades = array_filter([isset($gradesData[12]['quarters']['3rd']) ? $gradesData[12]['quarters']['3rd'] : null, isset($gradesData[12]['quarters']['4th']) ? $gradesData[12]['quarters']['4th'] : null], function($g) { return $g !== null; });
                $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
                $pdf->SetXY(102, 151); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

                // Position 7: Physical Education and Health 1 (1st/2nd) | Empowerment Technologies (3rd/4th)
                $pdf->SetXY(76, 72); $pdf->Write(0, isset($gradesData[6]['quarters']['1st']) && $gradesData[6]['quarters']['1st'] !== null ? ((intval($gradesData[6]['quarters']['1st']) == floatval($gradesData[6]['quarters']['1st'])) ? intval($gradesData[6]['quarters']['1st']) : number_format($gradesData[6]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(89, 72); $pdf->Write(0, isset($gradesData[6]['quarters']['2nd']) && $gradesData[6]['quarters']['2nd'] !== null ? ((intval($gradesData[6]['quarters']['2nd']) == floatval($gradesData[6]['quarters']['2nd'])) ? intval($gradesData[6]['quarters']['2nd']) : number_format($gradesData[6]['quarters']['2nd'], 1)) : '');
                $sem1Grades = array_filter([isset($gradesData[6]['quarters']['1st']) ? $gradesData[6]['quarters']['1st'] : null, isset($gradesData[6]['quarters']['2nd']) ? $gradesData[6]['quarters']['2nd'] : null], function($g) { return $g !== null; });
                $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
                $pdf->SetXY(102, 72); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
                $pdf->SetXY(75, 157); $pdf->Write(0, isset($gradesData[13]['quarters']['3rd']) && $gradesData[13]['quarters']['3rd'] !== null ? ((intval($gradesData[13]['quarters']['3rd']) == floatval($gradesData[13]['quarters']['3rd'])) ? intval($gradesData[13]['quarters']['3rd']) : number_format($gradesData[13]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(89, 157); $pdf->Write(0, isset($gradesData[13]['quarters']['4th']) && $gradesData[13]['quarters']['4th'] !== null ? ((intval($gradesData[13]['quarters']['4th']) == floatval($gradesData[13]['quarters']['4th'])) ? intval($gradesData[13]['quarters']['4th']) : number_format($gradesData[13]['quarters']['4th'], 1)) : '');
                $sem2Grades = array_filter([isset($gradesData[13]['quarters']['3rd']) ? $gradesData[13]['quarters']['3rd'] : null, isset($gradesData[13]['quarters']['4th']) ? $gradesData[13]['quarters']['4th'] : null], function($g) { return $g !== null; });
                $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
                $pdf->SetXY(102, 157); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

                // Position 8: Pre-Calculus (1st/2nd) | Basic Calculus (3rd/4th)
                $pdf->SetXY(76, 82); $pdf->Write(0, isset($gradesData[7]['quarters']['1st']) && $gradesData[7]['quarters']['1st'] !== null ? ((intval($gradesData[7]['quarters']['1st']) == floatval($gradesData[7]['quarters']['1st'])) ? intval($gradesData[7]['quarters']['1st']) : number_format($gradesData[7]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(89, 81); $pdf->Write(0, isset($gradesData[7]['quarters']['2nd']) && $gradesData[7]['quarters']['2nd'] !== null ? ((intval($gradesData[7]['quarters']['2nd']) == floatval($gradesData[7]['quarters']['2nd'])) ? intval($gradesData[7]['quarters']['2nd']) : number_format($gradesData[7]['quarters']['2nd'], 1)) : '');
                $sem1Grades = array_filter([isset($gradesData[7]['quarters']['1st']) ? $gradesData[7]['quarters']['1st'] : null, isset($gradesData[7]['quarters']['2nd']) ? $gradesData[7]['quarters']['2nd'] : null], function($g) { return $g !== null; });
                $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
                $pdf->SetXY(102, 82); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
                $pdf->SetXY(75, 163); $pdf->Write(0, isset($gradesData[14]['quarters']['3rd']) && $gradesData[14]['quarters']['3rd'] !== null ? ((intval($gradesData[14]['quarters']['3rd']) == floatval($gradesData[14]['quarters']['3rd'])) ? intval($gradesData[14]['quarters']['3rd']) : number_format($gradesData[14]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(89, 163); $pdf->Write(0, isset($gradesData[14]['quarters']['4th']) && $gradesData[14]['quarters']['4th'] !== null ? ((intval($gradesData[14]['quarters']['4th']) == floatval($gradesData[14]['quarters']['4th'])) ? intval($gradesData[14]['quarters']['4th']) : number_format($gradesData[14]['quarters']['4th'], 1)) : '');
                $sem2Grades = array_filter([isset($gradesData[14]['quarters']['3rd']) ? $gradesData[14]['quarters']['3rd'] : null, isset($gradesData[14]['quarters']['4th']) ? $gradesData[14]['quarters']['4th'] : null], function($g) { return $g !== null; });
                $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
                $pdf->SetXY(102, 163); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

                // Position 9: General Chemistry 1 (1st/2nd) | General Chemistry 2 (3rd/4th)
                $pdf->SetXY(76, 87); $pdf->Write(0, isset($gradesData[8]['quarters']['1st']) && $gradesData[8]['quarters']['1st'] !== null ? ((intval($gradesData[8]['quarters']['1st']) == floatval($gradesData[8]['quarters']['1st'])) ? intval($gradesData[8]['quarters']['1st']) : number_format($gradesData[8]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(89, 87); $pdf->Write(0, isset($gradesData[8]['quarters']['2nd']) && $gradesData[8]['quarters']['2nd'] !== null ? ((intval($gradesData[8]['quarters']['2nd']) == floatval($gradesData[8]['quarters']['2nd'])) ? intval($gradesData[8]['quarters']['2nd']) : number_format($gradesData[8]['quarters']['2nd'], 1)) : '');
                $sem1Grades = array_filter([isset($gradesData[8]['quarters']['1st']) ? $gradesData[8]['quarters']['1st'] : null, isset($gradesData[8]['quarters']['2nd']) ? $gradesData[8]['quarters']['2nd'] : null], function($g) { return $g !== null; });
                $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
                $pdf->SetXY(102, 87); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
                $pdf->SetXY(75, 168); $pdf->Write(0, isset($gradesData[15]['quarters']['3rd']) && $gradesData[15]['quarters']['3rd'] !== null ? ((intval($gradesData[15]['quarters']['3rd']) == floatval($gradesData[15]['quarters']['3rd'])) ? intval($gradesData[15]['quarters']['3rd']) : number_format($gradesData[15]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(89, 168); $pdf->Write(0, isset($gradesData[15]['quarters']['4th']) && $gradesData[15]['quarters']['4th'] !== null ? ((intval($gradesData[15]['quarters']['4th']) == floatval($gradesData[15]['quarters']['4th'])) ? intval($gradesData[15]['quarters']['4th']) : number_format($gradesData[15]['quarters']['4th'], 1)) : '');
                $sem2Grades = array_filter([isset($gradesData[15]['quarters']['3rd']) ? $gradesData[15]['quarters']['3rd'] : null, isset($gradesData[15]['quarters']['4th']) ? $gradesData[15]['quarters']['4th'] : null], function($g) { return $g !== null; });
                $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
                $pdf->SetXY(102, 168); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

                // Calculate General Average for First Semester (1st & 2nd quarters)
                $firstSemGrades = [];
                for ($i = 0; $i <= 8; $i++) {
                    if (isset($gradesData[$i])) {
                        $q1 = isset($gradesData[$i]['quarters']['1st']) ? $gradesData[$i]['quarters']['1st'] : null;
                        $q2 = isset($gradesData[$i]['quarters']['2nd']) ? $gradesData[$i]['quarters']['2nd'] : null;
                        $semGrades = array_filter([$q1, $q2], function($g) { return $g !== null; });
                        if (count($semGrades) == 2) {
                            $firstSemGrades[] = array_sum($semGrades) / count($semGrades);
                        }
                    }
                }
                $firstSemAverage = count($firstSemGrades) > 0 ? round(array_sum($firstSemGrades) / count($firstSemGrades), 2) : null;
                $pdf->SetXY(110, 93); $pdf->Write(0, $firstSemAverage !== null ? ((intval($firstSemAverage) == floatval($firstSemAverage)) ? intval($firstSemAverage) : number_format($firstSemAverage, 1)) : '');

                // Calculate General Average for Second Semester (3rd & 4th quarters)
                $secondSemGrades = [];
                for ($i = 7; $i <= 15; $i++) {
                    if (isset($gradesData[$i])) {
                        $q3 = isset($gradesData[$i]['quarters']['3rd']) ? $gradesData[$i]['quarters']['3rd'] : null;
                        $q4 = isset($gradesData[$i]['quarters']['4th']) ? $gradesData[$i]['quarters']['4th'] : null;
                        $semGrades = array_filter([$q3, $q4], function($g) { return $g !== null; });
                        if (count($semGrades) == 2) {
                            $secondSemGrades[] = array_sum($semGrades) / count($semGrades);
                        }
                    }
                }
                $secondSemAverage = count($secondSemGrades) > 0 ? round(array_sum($secondSemGrades) / count($secondSemGrades), 2) : null;
                $pdf->SetXY(110, 174); $pdf->Write(0, $secondSemAverage !== null ? ((intval($secondSemAverage) == floatval($secondSemAverage)) ? intval($secondSemAverage) : number_format($secondSemAverage, 1)) : '');
            }
        }
        return response($pdf->Output('Grade-11-Report-Card.pdf', 'S'))->header('Content-Type', 'application/pdf');
    } catch (\Exception $e) {
        return response('Error generating Grade 11 report card: ' . $e->getMessage(), 500);
    }
}

/**
 * Print all report cards for students in teacher's sections based on grade level
 */
public function printAllReportCards(Request $request)
{
    try {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        // Get all sections where this teacher is the class adviser
        $advisoryAssignments = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
            ->where('assignment_type', 'class_adviser')
            ->where('academic_year', $currentAcademicYear)
            ->where('status', 'active')
            ->with(['teacher'])
            ->get();

        if ($advisoryAssignments->isEmpty()) {
            return response('You are not assigned as a class adviser for any section.', 403);
        }

        // Initialize PDF merger
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $hasPages = false;

        foreach ($advisoryAssignments as $assignment) {
            // Get all students in this section
            $students = Student::where('grade_level', $assignment->grade_level)
                ->where('section', $assignment->section)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            if ($students->isEmpty()) {
                continue;
            }

            foreach ($students as $student) {
                try {
                    // Determine which report card function to use based on grade level
                    $studentPdfContent = null;
                    
                    if (in_array($student->grade_level, ['Grade 1', 'Grade 2'])) {
                        // Use Elementary Report Card for Grade 1-2
                        $studentPdfContent = $this->generateElementaryReportCardContent($student);
                        
                    } elseif ($student->grade_level === 'Grade 11') {
                        // Use Grade 11 STEM Report Card
                        $studentPdfContent = $this->generateGrade11ReportCardContent($student);
                        
                    } elseif (in_array($student->grade_level, ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'])) {
                        // Use High School Report Card for Grades 7-10
                        $studentPdfContent = $this->generateReportCardContent($student);
                        
                    } else {
                        // For other grades, use the generic high school template
                        $studentPdfContent = $this->generateReportCardContent($student);
                    }

                    // Add student's pages to the main PDF
                    if ($studentPdfContent) {
                        $this->addPdfContentToMain($pdf, $studentPdfContent);
                        $hasPages = true;
                    }

                } catch (\Exception $e) {
                    \Log::error('Error generating report card for student', [
                        'student_id' => $student->id,
                        'student_name' => $student->full_name,
                        'grade_level' => $student->grade_level,
                        'section' => $student->section,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other students even if one fails
                    continue;
                }
            }
        }

        if (!$hasPages) {
            return response('No report cards could be generated. Please check if students have grades entered.', 404);
        }

        $filename = 'All-Report-Cards-' . $teacher->teacher->full_name . '-' . date('Y-m-d') . '.pdf';
        return response($pdf->Output($filename, 'S'))->header('Content-Type', 'application/pdf');

    } catch (\Exception $e) {
        \Log::error('Error in printAllReportCards', [
            'teacher_id' => $teacher->teacher->id ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response('Error generating all report cards: ' . $e->getMessage(), 500);
    }
}

/**
 * Helper method: Generate Elementary Report Card content (Grade 1-2)
 */
private function generateElementaryReportCardContent(Student $student)
{
    $teacher = Auth::user();
    $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

    // Verify adviser assignment
    $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
        ->where('grade_level', $student->grade_level)
        ->where('section', $student->section)
        ->where('assignment_type', 'class_adviser')
        ->where('academic_year', $currentAcademicYear)
        ->where('status', 'active')
        ->first();

    if (!$advisoryAssignment) {
        return null;
    }

    // Get subjects and grades (same logic as generateElementaryReportCardPdf)
    $subjects = Subject::where('grade_level', $student->grade_level)
        ->where('academic_year', $currentAcademicYear)
        ->where('is_active', true)
        ->get();

    // Order subjects for elementary report card display
    $expectedOrder = [
        'Mother Tongue (MTB-MLE)',
        'Filipino',
        'English',
        'Mathematics',
        'Araling Panlipunan (AP)',
        'Science',
        'Music',
        'Arts',
        'Physical Education',
        'Health',
        'Edukasyon sa Pagpapakatao (EsP / Values)'
    ];
    
    // Create ordered grades data
    $gradesData = [];
    $quarters = ['1st', '2nd', '3rd', '4th'];
    
    foreach ($expectedOrder as $expectedSubjectName) {
        $subject = $subjects->firstWhere('subject_name', $expectedSubjectName);
        
        $subjectGrades = [
            'subject_name' => $expectedSubjectName,
            'quarters' => []
        ];
        
        foreach ($quarters as $quarter) {
            if ($subject) {
                $grade = Grade::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('quarter', $quarter)
                    ->where('academic_year', $currentAcademicYear)
                    ->first();
                $subjectGrades['quarters'][$quarter] = $grade ? $grade->grade : null;
            } else {
                $subjectGrades['quarters'][$quarter] = null;
            }
        }
        $gradesData[] = $subjectGrades;
    }

    // Create PDF content
    $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
    $templatePath = resource_path('assets/pdf-forms-generation/Report-Card- Gr.  1-2 NEW.pdf');
    
    if (!file_exists($templatePath)) {
        return null;
    }
    
    $pageCount = $pdf->setSourceFile($templatePath);
    
    // Generate pages (same logic as original function but return content)
    for ($pageNum = 1; $pageNum <= min(2, $pageCount); $pageNum++) {
        $tplId = $pdf->importPage($pageNum);
        $size = $pdf->getTemplateSize($tplId);
        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        
        if ($pageNum === 1) {
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->useTemplate($tplId);
            
            // Overlay grades (using same coordinate logic as original function)
            // Subject 0 - Mother Tongue
            $pdf->SetXY(53, 45); $pdf->Write(0, (isset($gradesData[0]) && isset($gradesData[0]['quarters']['1st']) && $gradesData[0]['quarters']['1st'] !== null) ? ((intval($gradesData[0]['quarters']['1st']) == floatval($gradesData[0]['quarters']['1st'])) ? intval($gradesData[0]['quarters']['1st']) : number_format($gradesData[0]['quarters']['1st'], 1)) : '');
            $pdf->SetXY(65, 45); $pdf->Write(0, (isset($gradesData[0]) && isset($gradesData[0]['quarters']['2nd']) && $gradesData[0]['quarters']['2nd'] !== null) ? ((intval($gradesData[0]['quarters']['2nd']) == floatval($gradesData[0]['quarters']['2nd'])) ? intval($gradesData[0]['quarters']['2nd']) : number_format($gradesData[0]['quarters']['2nd'], 1)) : '');
            $pdf->SetXY(75, 45); $pdf->Write(0, (isset($gradesData[0]) && isset($gradesData[0]['quarters']['3rd']) && $gradesData[0]['quarters']['3rd'] !== null) ? ((intval($gradesData[0]['quarters']['3rd']) == floatval($gradesData[0]['quarters']['3rd'])) ? intval($gradesData[0]['quarters']['3rd']) : number_format($gradesData[0]['quarters']['3rd'], 1)) : '');
            $pdf->SetXY(87, 45); $pdf->Write(0, (isset($gradesData[0]) && isset($gradesData[0]['quarters']['4th']) && $gradesData[0]['quarters']['4th'] !== null) ? ((intval($gradesData[0]['quarters']['4th']) == floatval($gradesData[0]['quarters']['4th'])) ? intval($gradesData[0]['quarters']['4th']) : number_format($gradesData[0]['quarters']['4th'], 1)) : '');

            // Continue with other subjects...
            for ($i = 1; $i <= 10; $i++) {
                if (isset($gradesData[$i])) {
                    $y = 45 + ($i * 7); // Adjust Y position for each subject
                    
                    $pdf->SetXY(53, $y); $pdf->Write(0, isset($gradesData[$i]['quarters']['1st']) && $gradesData[$i]['quarters']['1st'] !== null ? ((intval($gradesData[$i]['quarters']['1st']) == floatval($gradesData[$i]['quarters']['1st'])) ? intval($gradesData[$i]['quarters']['1st']) : number_format($gradesData[$i]['quarters']['1st'], 1)) : '');
                    $pdf->SetXY(65, $y); $pdf->Write(0, isset($gradesData[$i]['quarters']['2nd']) && $gradesData[$i]['quarters']['2nd'] !== null ? ((intval($gradesData[$i]['quarters']['2nd']) == floatval($gradesData[$i]['quarters']['2nd'])) ? intval($gradesData[$i]['quarters']['2nd']) : number_format($gradesData[$i]['quarters']['2nd'], 1)) : '');
                    $pdf->SetXY(75, $y); $pdf->Write(0, isset($gradesData[$i]['quarters']['3rd']) && $gradesData[$i]['quarters']['3rd'] !== null ? ((intval($gradesData[$i]['quarters']['3rd']) == floatval($gradesData[$i]['quarters']['3rd'])) ? intval($gradesData[$i]['quarters']['3rd']) : number_format($gradesData[$i]['quarters']['3rd'], 1)) : '');
                    $pdf->SetXY(87, $y); $pdf->Write(0, isset($gradesData[$i]['quarters']['4th']) && $gradesData[$i]['quarters']['4th'] !== null ? ((intval($gradesData[$i]['quarters']['4th']) == floatval($gradesData[$i]['quarters']['4th'])) ? intval($gradesData[$i]['quarters']['4th']) : number_format($gradesData[$i]['quarters']['4th'], 1)) : '');
                }
            }
            
            // General average
            $finalGrades = [];
            foreach ($gradesData as $subjectData) {
                $subjectQuarters = array_filter($subjectData['quarters'], function($g) { return $g !== null; });
                if (count($subjectQuarters) > 0) {
                    $finalGrades[] = array_sum($subjectQuarters) / count($subjectQuarters);
                }
            }
            $generalAverage = count($finalGrades) > 0 ? round(array_sum($finalGrades) / count($finalGrades), 2) : '';
            $pdf->SetXY(113, 130); $pdf->Write(0, $generalAverage !== '' ? ((intval($generalAverage) == floatval($generalAverage)) ? intval($generalAverage) : number_format($generalAverage, 1)) : '');
            
        } else if ($pageNum === 2) {
            $pdf->SetFont('dejavusans', '', 11);
            $pdf->useTemplate($tplId);
            
            // Student information on page 2
            $adviserName = $advisoryAssignment->teacher->full_name ?? ($advisoryAssignment->teacher->name ?? '');
            $schoolYear = $currentAcademicYear;
            $age = $student->age ?? '';
            
            $pdf->SetXY(225, 17); $pdf->Write(0, '' . ($student->lrn ?? ''));
            $pdf->SetXY(159, 163); $pdf->Write(0, $student->full_name ?? '');
            $pdf->SetXY(155, 173); $pdf->Write(0, '' . $age);
            $pdf->SetXY(160, 182); $pdf->Write(0, '' . ($student->grade_level ?? ''));
            $pdf->SetXY(168, 191); $pdf->Write(0, '' . $schoolYear);
            $pdf->SetXY(220, 173); $pdf->Write(0, '' . ($student->gender ?? ''));
            $pdf->SetXY(220, 183); $pdf->Write(0, '' . ($student->section ?? ''));
            $pdf->SetXY(76, 98); $pdf->Write(0, '' . $adviserName);
        }
    }
    
    return $pdf->Output('', 'S'); // Return PDF content as string
}

/**
 * Helper method: Generate High School Report Card content (Grades 7-10)
 */
private function generateReportCardContent(Student $student)
{
    $teacher = Auth::user();
    $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

    // Verify adviser assignment
    $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
        ->where('grade_level', $student->grade_level)
        ->where('section', $student->section)
        ->where('assignment_type', 'class_adviser')
        ->where('academic_year', $currentAcademicYear)
        ->where('status', 'active')
        ->first();

    if (!$advisoryAssignment) {
        return null;
    }

    // Get subjects (same logic as original function)
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

    // Create PDF
    $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
    $templatePath = resource_path('assets/pdf-forms-generation/Report Card HS.pdf');
    if (!file_exists($templatePath)) {
        return null;
    }
    
    $pageCount = $pdf->setSourceFile($templatePath);
    
    for ($pageNum = 1; $pageNum <= min(2, $pageCount); $pageNum++) {
        $tplId = $pdf->importPage($pageNum);
        $size = $pdf->getTemplateSize($tplId);
        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        
        if ($pageNum === 1) {
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->useTemplate($tplId);
            
            // Overlay grades (using coordinate logic from original function)
            for ($i = 0; $i <= 10 && $i < count($gradesData); $i++) {
                $y = 39 + ($i * 8); // Base Y position + offset for each subject
                
                if ($i == 6) $y = 91; // Special positioning for certain subjects
                if ($i == 7) $y = 123;
                if ($i == 8) $y = 99;
                if ($i == 9) $y = 106;
                if ($i == 10) $y = 113;
                
                $pdf->SetXY(53, $y); $pdf->Write(0, isset($gradesData[$i]['quarters']['1st']) && $gradesData[$i]['quarters']['1st'] !== null ? ((intval($gradesData[$i]['quarters']['1st']) == floatval($gradesData[$i]['quarters']['1st'])) ? intval($gradesData[$i]['quarters']['1st']) : number_format($gradesData[$i]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, $y); $pdf->Write(0, isset($gradesData[$i]['quarters']['2nd']) && $gradesData[$i]['quarters']['2nd'] !== null ? ((intval($gradesData[$i]['quarters']['2nd']) == floatval($gradesData[$i]['quarters']['2nd'])) ? intval($gradesData[$i]['quarters']['2nd']) : number_format($gradesData[$i]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, $y); $pdf->Write(0, isset($gradesData[$i]['quarters']['3rd']) && $gradesData[$i]['quarters']['3rd'] !== null ? ((intval($gradesData[$i]['quarters']['3rd']) == floatval($gradesData[$i]['quarters']['3rd'])) ? intval($gradesData[$i]['quarters']['3rd']) : number_format($gradesData[$i]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(87, $y); $pdf->Write(0, isset($gradesData[$i]['quarters']['4th']) && $gradesData[$i]['quarters']['4th'] !== null ? ((intval($gradesData[$i]['quarters']['4th']) == floatval($gradesData[$i]['quarters']['4th'])) ? intval($gradesData[$i]['quarters']['4th']) : number_format($gradesData[$i]['quarters']['4th'], 1)) : '');
            }
            
            // General average
            $finalGrades = [];
            foreach ($gradesData as $subjectData) {
                $subjectQuarters = array_filter($subjectData['quarters'], function($g) { return $g !== null; });
                if (count($subjectQuarters) > 0) {
                    $finalGrades[] = array_sum($subjectQuarters) / count($subjectQuarters);
                }
            }
            $generalAverage = count($finalGrades) > 0 ? round(array_sum($finalGrades) / count($finalGrades), 2) : '';
            $pdf->SetXY(113, 144); $pdf->Write(0, $generalAverage !== '' ? ((intval($generalAverage) == floatval($generalAverage)) ? intval($generalAverage) : number_format($generalAverage, 1)) : '');
            
        } else if ($pageNum === 2) {
            $pdf->SetFont('dejavusans', '', 11);
            $pdf->useTemplate($tplId);
            
            // Student information
            $adviserName = $advisoryAssignment->teacher->full_name ?? ($advisoryAssignment->teacher->name ?? '');
            $schoolYear = $currentAcademicYear;
            $age = $student->age ?? '';
            
            $pdf->SetXY(225, 17); $pdf->Write(0, '' . ($student->lrn ?? ''));
            $pdf->SetXY(159, 163); $pdf->Write(0, $student->full_name ?? '');
            $pdf->SetXY(155, 173); $pdf->Write(0, '' . $age);
            $pdf->SetXY(160, 182); $pdf->Write(0, '' . ($student->grade_level ?? ''));
            $pdf->SetXY(168, 191); $pdf->Write(0, '' . $schoolYear);
            $pdf->SetXY(220, 173); $pdf->Write(0, '' . ($student->gender ?? ''));
            $pdf->SetXY(220, 183); $pdf->Write(0, '' . ($student->section ?? ''));
            $pdf->SetXY(76, 98); $pdf->Write(0, '' . $adviserName);
        }
    }
    
    return $pdf->Output('', 'S');
}

/**
 * Helper method: Generate Grade 11 Report Card content
 */
private function generateGrade11ReportCardContent(Student $student)
{
    $teacher = Auth::user();
    $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

    // Verify adviser assignment
    $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
        ->where('grade_level', $student->grade_level)
        ->where('section', $student->section)
        ->where('assignment_type', 'class_adviser')
        ->where('academic_year', $currentAcademicYear)
        ->where('status', 'active')
        ->first();

    if (!$advisoryAssignment) {
        return null;
    }

    // Get subjects (same logic as original Grade 11 function)
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

    // Create PDF
    $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
    $templatePath = resource_path('assets/pdf-forms-generation/STEM 11.pdf');
    if (!file_exists($templatePath)) {
        return null;
    }
    
    $pageCount = $pdf->setSourceFile($templatePath);
    
    for ($pageNum = 1; $pageNum <= min(2, $pageCount); $pageNum++) {
        $tplId = $pdf->importPage($pageNum);
        $size = $pdf->getTemplateSize($tplId);
        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        
        if ($pageNum === 1) {
            $pdf->SetFont('dejavusans', '', 11);
            $pdf->useTemplate($tplId);
            
            // Student Information overlay (from original Grade 11 function)
            $adviserName = $advisoryAssignment->teacher->full_name ?? ($advisoryAssignment->teacher->name ?? '');
            $schoolYear = $currentAcademicYear;
            $age = $student->age ?? '';
            
            $pdf->SetXY(162, 161); $pdf->Write(0, $student->full_name ?? '');
            $pdf->SetXY(162, 170); $pdf->Write(0, '' . $age);
            $pdf->SetXY(160, 178); $pdf->Write(0, '11');
            $pdf->SetXY(220, 170); $pdf->Write(0, '' . ($student->gender ?? ''));
            $pdf->SetXY(87, 58); $pdf->Write(0, '' . $adviserName);
            $pdf->SetXY(200, 178); $pdf->Write(0, '' . ($student->strand ?? ''));
            $pdf->SetXY(220, 98); $pdf->Write(0, '' . ($student->track ?? '-'));
            
        } else if ($pageNum === 2) {
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->useTemplate($tplId);
            
            // Overlay grades using semester-based mapping (from original Grade 11 function)
            // Position 1: Oral Communication (1st/2nd) | Reading and Writing Skills (3rd/4th)
            $pdf->SetXY(76, 41); $pdf->Write(0, isset($gradesData[0]['quarters']['1st']) && $gradesData[0]['quarters']['1st'] !== null ? ((intval($gradesData[0]['quarters']['1st']) == floatval($gradesData[0]['quarters']['1st'])) ? intval($gradesData[0]['quarters']['1st']) : number_format($gradesData[0]['quarters']['1st'], 1)) : '');
            $pdf->SetXY(89, 41); $pdf->Write(0, isset($gradesData[0]['quarters']['2nd']) && $gradesData[0]['quarters']['2nd'] !== null ? ((intval($gradesData[0]['quarters']['2nd']) == floatval($gradesData[0]['quarters']['2nd'])) ? intval($gradesData[0]['quarters']['2nd']) : number_format($gradesData[0]['quarters']['2nd'], 1)) : '');
            $sem1Grades = array_filter([isset($gradesData[0]['quarters']['1st']) ? $gradesData[0]['quarters']['1st'] : null, isset($gradesData[0]['quarters']['2nd']) ? $gradesData[0]['quarters']['2nd'] : null], function($g) { return $g !== null; });
            $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
            $pdf->SetXY(102, 41); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
            $pdf->SetXY(75, 120); $pdf->Write(0, isset($gradesData[7]['quarters']['3rd']) && $gradesData[7]['quarters']['3rd'] !== null ? ((intval($gradesData[7]['quarters']['3rd']) == floatval($gradesData[7]['quarters']['3rd'])) ? intval($gradesData[7]['quarters']['3rd']) : number_format($gradesData[7]['quarters']['3rd'], 1)) : '');
            $pdf->SetXY(89, 120); $pdf->Write(0, isset($gradesData[7]['quarters']['4th']) && $gradesData[7]['quarters']['4th'] !== null ? ((intval($gradesData[7]['quarters']['4th']) == floatval($gradesData[7]['quarters']['4th'])) ? intval($gradesData[7]['quarters']['4th']) : number_format($gradesData[7]['quarters']['4th'], 1)) : '');
            $sem2Grades = array_filter([isset($gradesData[7]['quarters']['3rd']) ? $gradesData[7]['quarters']['3rd'] : null, isset($gradesData[7]['quarters']['4th']) ? $gradesData[7]['quarters']['4th'] : null], function($g) { return $g !== null; });
            $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
            $pdf->SetXY(102, 120); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

            // Position 2: General Mathematics (1st/2nd) | 21st Century Literature (3rd/4th)
            $pdf->SetXY(76, 46); $pdf->Write(0, isset($gradesData[1]['quarters']['1st']) && $gradesData[1]['quarters']['1st'] !== null ? ((intval($gradesData[1]['quarters']['1st']) == floatval($gradesData[1]['quarters']['1st'])) ? intval($gradesData[1]['quarters']['1st']) : number_format($gradesData[1]['quarters']['1st'], 1)) : '');
            $pdf->SetXY(89, 46); $pdf->Write(0, isset($gradesData[1]['quarters']['2nd']) && $gradesData[1]['quarters']['2nd'] !== null ? ((intval($gradesData[1]['quarters']['2nd']) == floatval($gradesData[1]['quarters']['2nd'])) ? intval($gradesData[1]['quarters']['2nd']) : number_format($gradesData[1]['quarters']['2nd'], 1)) : '');
            $sem1Grades = array_filter([isset($gradesData[1]['quarters']['1st']) ? $gradesData[1]['quarters']['1st'] : null, isset($gradesData[1]['quarters']['2nd']) ? $gradesData[1]['quarters']['2nd'] : null], function($g) { return $g !== null; });
            $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
            $pdf->SetXY(102, 46); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
            $pdf->SetXY(75, 125); $pdf->Write(0, isset($gradesData[8]['quarters']['3rd']) && $gradesData[8]['quarters']['3rd'] !== null ? ((intval($gradesData[8]['quarters']['3rd']) == floatval($gradesData[8]['quarters']['3rd'])) ? intval($gradesData[8]['quarters']['3rd']) : number_format($gradesData[8]['quarters']['3rd'], 1)) : '');
            $pdf->SetXY(89, 125); $pdf->Write(0, isset($gradesData[8]['quarters']['4th']) && $gradesData[8]['quarters']['4th'] !== null ? ((intval($gradesData[8]['quarters']['4th']) == floatval($gradesData[8]['quarters']['4th'])) ? intval($gradesData[8]['quarters']['4th']) : number_format($gradesData[8]['quarters']['4th'], 1)) : '');
            $sem2Grades = array_filter([isset($gradesData[8]['quarters']['3rd']) ? $gradesData[8]['quarters']['3rd'] : null, isset($gradesData[8]['quarters']['4th']) ? $gradesData[8]['quarters']['4th'] : null], function($g) { return $g !== null; });
            $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
            $pdf->SetXY(102, 125); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

            // Position 3: Earth and Life Science (1st/2nd) | Statistics and Probability (3rd/4th)
            $pdf->SetXY(76, 51); $pdf->Write(0, isset($gradesData[2]['quarters']['1st']) && $gradesData[2]['quarters']['1st'] !== null ? ((intval($gradesData[2]['quarters']['1st']) == floatval($gradesData[2]['quarters']['1st'])) ? intval($gradesData[2]['quarters']['1st']) : number_format($gradesData[2]['quarters']['1st'], 1)) : '');
            $pdf->SetXY(89, 51); $pdf->Write(0, isset($gradesData[2]['quarters']['2nd']) && $gradesData[2]['quarters']['2nd'] !== null ? ((intval($gradesData[2]['quarters']['2nd']) == floatval($gradesData[2]['quarters']['2nd'])) ? intval($gradesData[2]['quarters']['2nd']) : number_format($gradesData[2]['quarters']['2nd'], 1)) : '');
            $sem1Grades = array_filter([isset($gradesData[2]['quarters']['1st']) ? $gradesData[2]['quarters']['1st'] : null, isset($gradesData[2]['quarters']['2nd']) ? $gradesData[2]['quarters']['2nd'] : null], function($g) { return $g !== null; });
            $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
            $pdf->SetXY(102, 51); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
            $pdf->SetXY(75, 131); $pdf->Write(0, isset($gradesData[9]['quarters']['3rd']) && $gradesData[9]['quarters']['3rd'] !== null ? ((intval($gradesData[9]['quarters']['3rd']) == floatval($gradesData[9]['quarters']['3rd'])) ? intval($gradesData[9]['quarters']['3rd']) : number_format($gradesData[9]['quarters']['3rd'], 1)) : '');
            $pdf->SetXY(89, 131); $pdf->Write(0, isset($gradesData[9]['quarters']['4th']) && $gradesData[9]['quarters']['4th'] !== null ? ((intval($gradesData[9]['quarters']['4th']) == floatval($gradesData[9]['quarters']['4th'])) ? intval($gradesData[9]['quarters']['4th']) : number_format($gradesData[9]['quarters']['4th'], 1)) : '');
            $sem2Grades = array_filter([isset($gradesData[9]['quarters']['3rd']) ? $gradesData[9]['quarters']['3rd'] : null, isset($gradesData[9]['quarters']['4th']) ? $gradesData[9]['quarters']['4th'] : null], function($g) { return $g !== null; });
            $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
            $pdf->SetXY(102, 131); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

            // Position 4: Komunikasyon at Pananaliksik sa Wika (1st/2nd) | Pagbasa at Pagsusuri ng Iba't – ibang Teksto (3rd/4th)
            $pdf->SetXY(76, 56); $pdf->Write(0, isset($gradesData[3]['quarters']['1st']) && $gradesData[3]['quarters']['1st'] !== null ? ((intval($gradesData[3]['quarters']['1st']) == floatval($gradesData[3]['quarters']['1st'])) ? intval($gradesData[3]['quarters']['1st']) : number_format($gradesData[3]['quarters']['1st'], 1)) : '');
            $pdf->SetXY(89, 57); $pdf->Write(0, isset($gradesData[3]['quarters']['2nd']) && $gradesData[3]['quarters']['2nd'] !== null ? ((intval($gradesData[3]['quarters']['2nd']) == floatval($gradesData[3]['quarters']['2nd'])) ? intval($gradesData[3]['quarters']['2nd']) : number_format($gradesData[3]['quarters']['2nd'], 1)) : '');
            $sem1Grades = array_filter([isset($gradesData[3]['quarters']['1st']) ? $gradesData[3]['quarters']['1st'] : null, isset($gradesData[3]['quarters']['2nd']) ? $gradesData[3]['quarters']['2nd'] : null], function($g) { return $g !== null; });
            $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
            $pdf->SetXY(102, 56); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
            $pdf->SetXY(75, 134); $pdf->Write(0, isset($gradesData[10]['quarters']['3rd']) && $gradesData[10]['quarters']['3rd'] !== null ? ((intval($gradesData[10]['quarters']['3rd']) == floatval($gradesData[10]['quarters']['3rd'])) ? intval($gradesData[10]['quarters']['3rd']) : number_format($gradesData[10]['quarters']['3rd'], 1)) : '');
            $pdf->SetXY(89, 134); $pdf->Write(0, isset($gradesData[10]['quarters']['4th']) && $gradesData[10]['quarters']['4th'] !== null ? ((intval($gradesData[10]['quarters']['4th']) == floatval($gradesData[10]['quarters']['4th'])) ? intval($gradesData[10]['quarters']['4th']) : number_format($gradesData[10]['quarters']['4th'], 1)) : '');
            $sem2Grades = array_filter([isset($gradesData[10]['quarters']['3rd']) ? $gradesData[10]['quarters']['3rd'] : null, isset($gradesData[10]['quarters']['4th']) ? $gradesData[10]['quarters']['4th'] : null], function($g) { return $g !== null; });
            $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
            $pdf->SetXY(102, 134); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

            // Position 5: Personal Development (1st/2nd) | Physical Education and Health 2 (3rd/4th)
            $pdf->SetXY(76, 62); $pdf->Write(0, isset($gradesData[4]['quarters']['1st']) && $gradesData[4]['quarters']['1st'] !== null ? ((intval($gradesData[4]['quarters']['1st']) == floatval($gradesData[4]['quarters']['1st'])) ? intval($gradesData[4]['quarters']['1st']) : number_format($gradesData[4]['quarters']['1st'], 1)) : '');
            $pdf->SetXY(89, 62); $pdf->Write(0, isset($gradesData[4]['quarters']['2nd']) && $gradesData[4]['quarters']['2nd'] !== null ? ((intval($gradesData[4]['quarters']['2nd']) == floatval($gradesData[4]['quarters']['2nd'])) ? intval($gradesData[4]['quarters']['2nd']) : number_format($gradesData[4]['quarters']['2nd'], 1)) : '');
            $sem1Grades = array_filter([isset($gradesData[4]['quarters']['1st']) ? $gradesData[4]['quarters']['1st'] : null, isset($gradesData[4]['quarters']['2nd']) ? $gradesData[4]['quarters']['2nd'] : null], function($g) { return $g !== null; });
            $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
            $pdf->SetXY(102, 62); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
            $pdf->SetXY(75, 142); $pdf->Write(0, isset($gradesData[11]['quarters']['3rd']) && $gradesData[11]['quarters']['3rd'] !== null ? ((intval($gradesData[11]['quarters']['3rd']) == floatval($gradesData[11]['quarters']['3rd'])) ? intval($gradesData[11]['quarters']['3rd']) : number_format($gradesData[11]['quarters']['3rd'], 1)) : '');
            $pdf->SetXY(89, 142); $pdf->Write(0, isset($gradesData[11]['quarters']['4th']) && $gradesData[11]['quarters']['4th'] !== null ? ((intval($gradesData[11]['quarters']['4th']) == floatval($gradesData[11]['quarters']['4th'])) ? intval($gradesData[11]['quarters']['4th']) : number_format($gradesData[11]['quarters']['4th'], 1)) : '');
            $sem2Grades = array_filter([isset($gradesData[11]['quarters']['3rd']) ? $gradesData[11]['quarters']['3rd'] : null, isset($gradesData[11]['quarters']['4th']) ? $gradesData[11]['quarters']['4th'] : null], function($g) { return $g !== null; });
            $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
            $pdf->SetXY(102, 142); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

            // Position 6: Understanding Culture, Society, and Politics (1st/2nd) | Research in Daily Life 1 (3rd/4th)
            $pdf->SetXY(76, 67); $pdf->Write(0, isset($gradesData[5]['quarters']['1st']) && $gradesData[5]['quarters']['1st'] !== null ? ((intval($gradesData[5]['quarters']['1st']) == floatval($gradesData[5]['quarters']['1st'])) ? intval($gradesData[5]['quarters']['1st']) : number_format($gradesData[5]['quarters']['1st'], 1)) : '');
            $pdf->SetXY(89, 67); $pdf->Write(0, isset($gradesData[5]['quarters']['2nd']) && $gradesData[5]['quarters']['2nd'] !== null ? ((intval($gradesData[5]['quarters']['2nd']) == floatval($gradesData[5]['quarters']['2nd'])) ? intval($gradesData[5]['quarters']['2nd']) : number_format($gradesData[5]['quarters']['2nd'], 1)) : '');
            $sem1Grades = array_filter([isset($gradesData[5]['quarters']['1st']) ? $gradesData[5]['quarters']['1st'] : null, isset($gradesData[5]['quarters']['2nd']) ? $gradesData[5]['quarters']['2nd'] : null], function($g) { return $g !== null; });
            $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
            $pdf->SetXY(102, 67); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
            $pdf->SetXY(75, 151); $pdf->Write(0, isset($gradesData[12]['quarters']['3rd']) && $gradesData[12]['quarters']['3rd'] !== null ? ((intval($gradesData[12]['quarters']['3rd']) == floatval($gradesData[12]['quarters']['3rd'])) ? intval($gradesData[12]['quarters']['3rd']) : number_format($gradesData[12]['quarters']['3rd'], 1)) : '');
            $pdf->SetXY(89, 151); $pdf->Write(0, isset($gradesData[12]['quarters']['4th']) && $gradesData[12]['quarters']['4th'] !== null ? ((intval($gradesData[12]['quarters']['4th']) == floatval($gradesData[12]['quarters']['4th'])) ? intval($gradesData[12]['quarters']['4th']) : number_format($gradesData[12]['quarters']['4th'], 1)) : '');
            $sem2Grades = array_filter([isset($gradesData[12]['quarters']['3rd']) ? $gradesData[12]['quarters']['3rd'] : null, isset($gradesData[12]['quarters']['4th']) ? $gradesData[12]['quarters']['4th'] : null], function($g) { return $g !== null; });
            $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
            $pdf->SetXY(102, 151); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

            // Position 7: Physical Education and Health 1 (1st/2nd) | Empowerment Technologies (3rd/4th)
            $pdf->SetXY(76, 72); $pdf->Write(0, isset($gradesData[6]['quarters']['1st']) && $gradesData[6]['quarters']['1st'] !== null ? ((intval($gradesData[6]['quarters']['1st']) == floatval($gradesData[6]['quarters']['1st'])) ? intval($gradesData[6]['quarters']['1st']) : number_format($gradesData[6]['quarters']['1st'], 1)) : '');
            $pdf->SetXY(89, 72); $pdf->Write(0, isset($gradesData[6]['quarters']['2nd']) && $gradesData[6]['quarters']['2nd'] !== null ? ((intval($gradesData[6]['quarters']['2nd']) == floatval($gradesData[6]['quarters']['2nd'])) ? intval($gradesData[6]['quarters']['2nd']) : number_format($gradesData[6]['quarters']['2nd'], 1)) : '');
            $sem1Grades = array_filter([isset($gradesData[6]['quarters']['1st']) ? $gradesData[6]['quarters']['1st'] : null, isset($gradesData[6]['quarters']['2nd']) ? $gradesData[6]['quarters']['2nd'] : null], function($g) { return $g !== null; });
            $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
            $pdf->SetXY(102, 72); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
            $pdf->SetXY(75, 157); $pdf->Write(0, isset($gradesData[13]['quarters']['3rd']) && $gradesData[13]['quarters']['3rd'] !== null ? ((intval($gradesData[13]['quarters']['3rd']) == floatval($gradesData[13]['quarters']['3rd'])) ? intval($gradesData[13]['quarters']['3rd']) : number_format($gradesData[13]['quarters']['3rd'], 1)) : '');
            $pdf->SetXY(89, 157); $pdf->Write(0, isset($gradesData[13]['quarters']['4th']) && $gradesData[13]['quarters']['4th'] !== null ? ((intval($gradesData[13]['quarters']['4th']) == floatval($gradesData[13]['quarters']['4th'])) ? intval($gradesData[13]['quarters']['4th']) : number_format($gradesData[13]['quarters']['4th'], 1)) : '');
            $sem2Grades = array_filter([isset($gradesData[13]['quarters']['3rd']) ? $gradesData[13]['quarters']['3rd'] : null, isset($gradesData[13]['quarters']['4th']) ? $gradesData[13]['quarters']['4th'] : null], function($g) { return $g !== null; });
            $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
            $pdf->SetXY(102, 157); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

            // Position 8: Pre-Calculus (1st/2nd) | Basic Calculus (3rd/4th)
            $pdf->SetXY(76, 82); $pdf->Write(0, isset($gradesData[7]['quarters']['1st']) && $gradesData[7]['quarters']['1st'] !== null ? ((intval($gradesData[7]['quarters']['1st']) == floatval($gradesData[7]['quarters']['1st'])) ? intval($gradesData[7]['quarters']['1st']) : number_format($gradesData[7]['quarters']['1st'], 1)) : '');
            $pdf->SetXY(89, 81); $pdf->Write(0, isset($gradesData[7]['quarters']['2nd']) && $gradesData[7]['quarters']['2nd'] !== null ? ((intval($gradesData[7]['quarters']['2nd']) == floatval($gradesData[7]['quarters']['2nd'])) ? intval($gradesData[7]['quarters']['2nd']) : number_format($gradesData[7]['quarters']['2nd'], 1)) : '');
            $sem1Grades = array_filter([isset($gradesData[7]['quarters']['1st']) ? $gradesData[7]['quarters']['1st'] : null, isset($gradesData[7]['quarters']['2nd']) ? $gradesData[7]['quarters']['2nd'] : null], function($g) { return $g !== null; });
            $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
            $pdf->SetXY(102, 82); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
            $pdf->SetXY(75, 163); $pdf->Write(0, isset($gradesData[14]['quarters']['3rd']) && $gradesData[14]['quarters']['3rd'] !== null ? ((intval($gradesData[14]['quarters']['3rd']) == floatval($gradesData[14]['quarters']['3rd'])) ? intval($gradesData[14]['quarters']['3rd']) : number_format($gradesData[14]['quarters']['3rd'], 1)) : '');
            $pdf->SetXY(89, 163); $pdf->Write(0, isset($gradesData[14]['quarters']['4th']) && $gradesData[14]['quarters']['4th'] !== null ? ((intval($gradesData[14]['quarters']['4th']) == floatval($gradesData[14]['quarters']['4th'])) ? intval($gradesData[14]['quarters']['4th']) : number_format($gradesData[14]['quarters']['4th'], 1)) : '');
            $sem2Grades = array_filter([isset($gradesData[14]['quarters']['3rd']) ? $gradesData[14]['quarters']['3rd'] : null, isset($gradesData[14]['quarters']['4th']) ? $gradesData[14]['quarters']['4th'] : null], function($g) { return $g !== null; });
            $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
            $pdf->SetXY(102, 163); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');

            // Position 9: General Chemistry 1 (1st/2nd) | General Chemistry 2 (3rd/4th)
            $pdf->SetXY(76, 87); $pdf->Write(0, isset($gradesData[8]['quarters']['1st']) && $gradesData[8]['quarters']['1st'] !== null ? ((intval($gradesData[8]['quarters']['1st']) == floatval($gradesData[8]['quarters']['1st'])) ? intval($gradesData[8]['quarters']['1st']) : number_format($gradesData[8]['quarters']['1st'], 1)) : '');
            $pdf->SetXY(89, 87); $pdf->Write(0, isset($gradesData[8]['quarters']['2nd']) && $gradesData[8]['quarters']['2nd'] !== null ? ((intval($gradesData[8]['quarters']['2nd']) == floatval($gradesData[8]['quarters']['2nd'])) ? intval($gradesData[8]['quarters']['2nd']) : number_format($gradesData[8]['quarters']['2nd'], 1)) : '');
            $sem1Grades = array_filter([isset($gradesData[8]['quarters']['1st']) ? $gradesData[8]['quarters']['1st'] : null, isset($gradesData[8]['quarters']['2nd']) ? $gradesData[8]['quarters']['2nd'] : null], function($g) { return $g !== null; });
            $sem1Avg = count($sem1Grades) == 2 ? round(array_sum($sem1Grades) / count($sem1Grades), 2) : null;
            $pdf->SetXY(102, 87); $pdf->Write(0, $sem1Avg !== null ? ((intval($sem1Avg) == floatval($sem1Avg)) ? intval($sem1Avg) : number_format($sem1Avg, 1)) : '');
            $pdf->SetXY(75, 168); $pdf->Write(0, isset($gradesData[15]['quarters']['3rd']) && $gradesData[15]['quarters']['3rd'] !== null ? ((intval($gradesData[15]['quarters']['3rd']) == floatval($gradesData[15]['quarters']['3rd'])) ? intval($gradesData[15]['quarters']['3rd']) : number_format($gradesData[15]['quarters']['3rd'], 1)) : '');
            $pdf->SetXY(89, 168); $pdf->Write(0, isset($gradesData[15]['quarters']['4th']) && $gradesData[15]['quarters']['4th'] !== null ? ((intval($gradesData[15]['quarters']['4th']) == floatval($gradesData[15]['quarters']['4th'])) ? intval($gradesData[15]['quarters']['4th']) : number_format($gradesData[15]['quarters']['4th'], 1)) : '');
            $sem2Grades = array_filter([isset($gradesData[15]['quarters']['3rd']) ? $gradesData[15]['quarters']['3rd'] : null, isset($gradesData[15]['quarters']['4th']) ? $gradesData[15]['quarters']['4th'] : null], function($g) { return $g !== null; });
            $sem2Avg = count($sem2Grades) == 2 ? round(array_sum($sem2Grades) / count($sem2Grades), 2) : null;
            $pdf->SetXY(102, 168); $pdf->Write(0, $sem2Avg !== null ? ((intval($sem2Avg) == floatval($sem2Avg)) ? intval($sem2Avg) : number_format($sem2Avg, 1)) : '');
            
            // Calculate General Average for First Semester (1st & 2nd quarters)
            $firstSemGrades = [];
            for ($i = 0; $i <= 8; $i++) {
                if (isset($gradesData[$i])) {
                    $q1 = isset($gradesData[$i]['quarters']['1st']) ? $gradesData[$i]['quarters']['1st'] : null;
                    $q2 = isset($gradesData[$i]['quarters']['2nd']) ? $gradesData[$i]['quarters']['2nd'] : null;
                    $semGrades = array_filter([$q1, $q2], function($g) { return $g !== null; });
                    if (count($semGrades) > 0) {
                        $firstSemGrades[] = array_sum($semGrades) / count($semGrades);
                    }
                }
            }
            $firstSemAverage = count($firstSemGrades) > 0 ? round(array_sum($firstSemGrades) / count($firstSemGrades), 2) : null;
            $pdf->SetXY(110, 93); $pdf->Write(0, $firstSemAverage !== null ? ((intval($firstSemAverage) == floatval($firstSemAverage)) ? intval($firstSemAverage) : number_format($firstSemAverage, 1)) : '');

            // Calculate General Average for Second Semester (3rd & 4th quarters)
            $secondSemGrades = [];
            for ($i = 7; $i <= 15; $i++) {
                if (isset($gradesData[$i])) {
                    $q3 = isset($gradesData[$i]['quarters']['3rd']) ? $gradesData[$i]['quarters']['3rd'] : null;
                    $q4 = isset($gradesData[$i]['quarters']['4th']) ? $gradesData[$i]['quarters']['4th'] : null;
                    $semGrades = array_filter([$q3, $q4], function($g) { return $g !== null; });
                    if (count($semGrades) > 0) {
                        $secondSemGrades[] = array_sum($semGrades) / count($semGrades);
                    }
                }
            }
            $secondSemAverage = count($secondSemGrades) > 0 ? round(array_sum($secondSemGrades) / count($secondSemGrades), 2) : null;
            $pdf->SetXY(110, 174); $pdf->Write(0, $secondSemAverage !== null ? ((intval($secondSemAverage) == floatval($secondSemAverage)) ? intval($secondSemAverage) : number_format($secondSemAverage, 1)) : '');
        }
    }
    
    return $pdf->Output('', 'S');
}

/**
 * Helper method: Add PDF content to main PDF
 */
private function addPdfContentToMain($mainPdf, $pdfContent)
{
    // Create temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'report_card_') . '.pdf';
    file_put_contents($tempFile, $pdfContent);
    
    try {
        // Import pages from temporary PDF
        $pageCount = $mainPdf->setSourceFile($tempFile);
        
        for ($pageNum = 1; $pageNum <= $pageCount; $pageNum++) {
            $tplId = $mainPdf->importPage($pageNum);
            $size = $mainPdf->getTemplateSize($tplId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $mainPdf->AddPage($orientation, [$size['width'], $size['height']]);
            $mainPdf->useTemplate($tplId);
        }
        
    } finally {
        // Clean up temporary file
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
}

/**
 * Generate Elementary Report Card PDF for Grade 1-2 students
 */
public function generateElementaryReportCardPdf(Student $student)
{
    try {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        // Verify this teacher is the student's adviser for elementary students
        $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
            ->where('grade_level', $student->grade_level)
            ->where('section', $student->section)
            ->where('assignment_type', 'class_adviser')
            ->where('academic_year', $currentAcademicYear)
            ->where('status', 'active')
            ->first();

        if (!$advisoryAssignment) {
            abort(403, 'You are not the adviser for this student.');
        }

        // Get student's subjects
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

        // Order subjects for elementary report card display
        $expectedOrder = [
            'Mother Tongue (MTB-MLE)',
            'Filipino',
            'English',
            'Mathematics',
            'Araling Panlipunan (AP)',
            'Science',
            'Music',
            'Arts',
            'Physical Education',
            'Health',
            'Edukasyon sa Pagpapakatao (EsP / Values)'
        ];
        
        // Create ordered grades data
        $gradesData = [];
        $quarters = ['1st', '2nd', '3rd', '4th'];
        
        foreach ($expectedOrder as $expectedSubjectName) {
            $subject = $subjects->firstWhere('subject_name', $expectedSubjectName);
            
            $subjectGrades = [
                'subject_name' => $expectedSubjectName,
                'quarters' => []
            ];
            
            foreach ($quarters as $quarter) {
                if ($subject) {
                    $grade = Grade::where('student_id', $student->id)
                        ->where('subject_id', $subject->id)
                        ->where('quarter', $quarter)
                        ->where('academic_year', $currentAcademicYear)
                        ->first();
                    $subjectGrades['quarters'][$quarter] = $grade ? $grade->grade : null;
                } else {
                    $subjectGrades['quarters'][$quarter] = null;
                }
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

        // Load Elementary PDF template for Grade 1-2
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $templatePath = resource_path('assets/pdf-forms-generation/Report-Card- Gr.  1-2 NEW.pdf');
        if (!file_exists($templatePath)) {
            abort(404, 'Elementary Report Card PDF template not found.');
        }
        $pageCount = $pdf->setSourceFile($templatePath);
        
        // Always show both pages (even if only one is needed)
        for ($pageNum = 1; $pageNum <= min(2, $pageCount); $pageNum++) {
            $tplId = $pdf->importPage($pageNum);
            $size = $pdf->getTemplateSize($tplId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            
            if ($pageNum === 1) {
                $pdf->SetFont('dejavusans', '', 10);
                $pdf->useTemplate($tplId);
                
                // Overlay grades for elementary subjects (adjust coordinates for Grade 1-2 template)
                // Note: Coordinates will need to be adjusted based on actual template layout
                
                // Subject 0 - Mother Tongue
                $pdf->SetXY(53, 45); $pdf->Write(0, (isset($gradesData[0]) && isset($gradesData[0]['quarters']['1st']) && $gradesData[0]['quarters']['1st'] !== null) ? ((intval($gradesData[0]['quarters']['1st']) == floatval($gradesData[0]['quarters']['1st'])) ? intval($gradesData[0]['quarters']['1st']) : number_format($gradesData[0]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 45); $pdf->Write(0, (isset($gradesData[0]) && isset($gradesData[0]['quarters']['2nd']) && $gradesData[0]['quarters']['2nd'] !== null) ? ((intval($gradesData[0]['quarters']['2nd']) == floatval($gradesData[0]['quarters']['2nd'])) ? intval($gradesData[0]['quarters']['2nd']) : number_format($gradesData[0]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 45); $pdf->Write(0, (isset($gradesData[0]) && isset($gradesData[0]['quarters']['3rd']) && $gradesData[0]['quarters']['3rd'] !== null) ? ((intval($gradesData[0]['quarters']['3rd']) == floatval($gradesData[0]['quarters']['3rd'])) ? intval($gradesData[0]['quarters']['3rd']) : number_format($gradesData[0]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(87, 45); $pdf->Write(0, (isset($gradesData[0]) && isset($gradesData[0]['quarters']['4th']) && $gradesData[0]['quarters']['4th'] !== null) ? ((intval($gradesData[0]['quarters']['4th']) == floatval($gradesData[0]['quarters']['4th'])) ? intval($gradesData[0]['quarters']['4th']) : number_format($gradesData[0]['quarters']['4th'], 1)) : '');

                // Subject 1 - Filipino
                $pdf->SetXY(53, 48); $pdf->Write(0, isset($gradesData[1]['quarters']['1st']) && $gradesData[1]['quarters']['1st'] !== null ? ((intval($gradesData[1]['quarters']['1st']) == floatval($gradesData[1]['quarters']['1st'])) ? intval($gradesData[1]['quarters']['1st']) : number_format($gradesData[1]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 48); $pdf->Write(0, isset($gradesData[1]['quarters']['2nd']) && $gradesData[1]['quarters']['2nd'] !== null ? ((intval($gradesData[1]['quarters']['2nd']) == floatval($gradesData[1]['quarters']['2nd'])) ? intval($gradesData[1]['quarters']['2nd']) : number_format($gradesData[1]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 48); $pdf->Write(0, isset($gradesData[1]['quarters']['3rd']) && $gradesData[1]['quarters']['3rd'] !== null ? ((intval($gradesData[1]['quarters']['3rd']) == floatval($gradesData[1]['quarters']['3rd'])) ? intval($gradesData[1]['quarters']['3rd']) : number_format($gradesData[1]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(87, 48); $pdf->Write(0, isset($gradesData[1]['quarters']['4th']) && $gradesData[1]['quarters']['4th'] !== null ? ((intval($gradesData[1]['quarters']['4th']) == floatval($gradesData[1]['quarters']['4th'])) ? intval($gradesData[1]['quarters']['4th']) : number_format($gradesData[1]['quarters']['4th'], 1)) : '');

                // Subject 2 - English
                $pdf->SetXY(53, 52); $pdf->Write(0, isset($gradesData[2]['quarters']['1st']) && $gradesData[2]['quarters']['1st'] !== null ? ((intval($gradesData[2]['quarters']['1st']) == floatval($gradesData[2]['quarters']['1st'])) ? intval($gradesData[2]['quarters']['1st']) : number_format($gradesData[2]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 52); $pdf->Write(0, isset($gradesData[2]['quarters']['2nd']) && $gradesData[2]['quarters']['2nd'] !== null ? ((intval($gradesData[2]['quarters']['2nd']) == floatval($gradesData[2]['quarters']['2nd'])) ? intval($gradesData[2]['quarters']['2nd']) : number_format($gradesData[2]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 52); $pdf->Write(0, isset($gradesData[2]['quarters']['3rd']) && $gradesData[2]['quarters']['3rd'] !== null ? ((intval($gradesData[2]['quarters']['3rd']) == floatval($gradesData[2]['quarters']['3rd'])) ? intval($gradesData[2]['quarters']['3rd']) : number_format($gradesData[2]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(86, 52); $pdf->Write(0, isset($gradesData[2]['quarters']['4th']) && $gradesData[2]['quarters']['4th'] !== null ? ((intval($gradesData[2]['quarters']['4th']) == floatval($gradesData[2]['quarters']['4th'])) ? intval($gradesData[2]['quarters']['4th']) : number_format($gradesData[2]['quarters']['4th'], 1)) : '');

                // Subject 3 - Mathematics
                $pdf->SetXY(53, 59); $pdf->Write(0, isset($gradesData[3]['quarters']['1st']) && $gradesData[3]['quarters']['1st'] !== null ? ((intval($gradesData[3]['quarters']['1st']) == floatval($gradesData[3]['quarters']['1st'])) ? intval($gradesData[3]['quarters']['1st']) : number_format($gradesData[3]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 59); $pdf->Write(0, isset($gradesData[3]['quarters']['2nd']) && $gradesData[3]['quarters']['2nd'] !== null ? ((intval($gradesData[3]['quarters']['2nd']) == floatval($gradesData[3]['quarters']['2nd'])) ? intval($gradesData[3]['quarters']['2nd']) : number_format($gradesData[3]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 59); $pdf->Write(0, isset($gradesData[3]['quarters']['3rd']) && $gradesData[3]['quarters']['3rd'] !== null ? ((intval($gradesData[3]['quarters']['3rd']) == floatval($gradesData[3]['quarters']['3rd'])) ? intval($gradesData[3]['quarters']['3rd']) : number_format($gradesData[3]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(86, 59); $pdf->Write(0, isset($gradesData[3]['quarters']['4th']) && $gradesData[3]['quarters']['4th'] !== null ? ((intval($gradesData[3]['quarters']['4th']) == floatval($gradesData[3]['quarters']['4th'])) ? intval($gradesData[3]['quarters']['4th']) : number_format($gradesData[3]['quarters']['4th'], 1)) : '');

                // Subject 4 - Araling Panlipunan
                $pdf->SetXY(53, 66); $pdf->Write(0, isset($gradesData[4]['quarters']['1st']) && $gradesData[4]['quarters']['1st'] !== null ? ((intval($gradesData[4]['quarters']['1st']) == floatval($gradesData[4]['quarters']['1st'])) ? intval($gradesData[4]['quarters']['1st']) : number_format($gradesData[4]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 66); $pdf->Write(0, isset($gradesData[4]['quarters']['2nd']) && $gradesData[4]['quarters']['2nd'] !== null ? ((intval($gradesData[4]['quarters']['2nd']) == floatval($gradesData[4]['quarters']['2nd'])) ? intval($gradesData[4]['quarters']['2nd']) : number_format($gradesData[4]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 66); $pdf->Write(0, isset($gradesData[4]['quarters']['3rd']) && $gradesData[4]['quarters']['3rd'] !== null ? ((intval($gradesData[4]['quarters']['3rd']) == floatval($gradesData[4]['quarters']['3rd'])) ? intval($gradesData[4]['quarters']['3rd']) : number_format($gradesData[4]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(86, 66); $pdf->Write(0, isset($gradesData[4]['quarters']['4th']) && $gradesData[4]['quarters']['4th'] !== null ? ((intval($gradesData[4]['quarters']['4th']) == floatval($gradesData[4]['quarters']['4th'])) ? intval($gradesData[4]['quarters']['4th']) : number_format($gradesData[4]['quarters']['4th'], 1)) : '');

                // Subject 5 - Science
                $pdf->SetXY(53, 73); $pdf->Write(0, isset($gradesData[5]['quarters']['1st']) && $gradesData[5]['quarters']['1st'] !== null ? ((intval($gradesData[5]['quarters']['1st']) == floatval($gradesData[5]['quarters']['1st'])) ? intval($gradesData[5]['quarters']['1st']) : number_format($gradesData[5]['quarters']['1st'], 1)) : '');
                $pdf->SetXY(65, 73); $pdf->Write(0, isset($gradesData[5]['quarters']['2nd']) && $gradesData[5]['quarters']['2nd'] !== null ? ((intval($gradesData[5]['quarters']['2nd']) == floatval($gradesData[5]['quarters']['2nd'])) ? intval($gradesData[5]['quarters']['2nd']) : number_format($gradesData[5]['quarters']['2nd'], 1)) : '');
                $pdf->SetXY(75, 73); $pdf->Write(0, isset($gradesData[5]['quarters']['3rd']) && $gradesData[5]['quarters']['3rd'] !== null ? ((intval($gradesData[5]['quarters']['3rd']) == floatval($gradesData[5]['quarters']['3rd'])) ? intval($gradesData[5]['quarters']['3rd']) : number_format($gradesData[5]['quarters']['3rd'], 1)) : '');
                $pdf->SetXY(86, 73); $pdf->Write(0, isset($gradesData[5]['quarters']['4th']) && $gradesData[5]['quarters']['4th'] !== null ? ((intval($gradesData[5]['quarters']['4th']) == floatval($gradesData[5]['quarters']['4th'])) ? intval($gradesData[5]['quarters']['4th']) : number_format($gradesData[5]['quarters']['4th'], 1)) : '');

                // Add more subjects as needed for elementary curriculum
                if (isset($gradesData[6])) {
                    // Subject 6 - Music
                    $pdf->SetXY(53, 80); $pdf->Write(0, isset($gradesData[6]['quarters']['1st']) && $gradesData[6]['quarters']['1st'] !== null ? ((intval($gradesData[6]['quarters']['1st']) == floatval($gradesData[6]['quarters']['1st'])) ? intval($gradesData[6]['quarters']['1st']) : number_format($gradesData[6]['quarters']['1st'], 1)) : '');
                    $pdf->SetXY(65, 80); $pdf->Write(0, isset($gradesData[6]['quarters']['2nd']) && $gradesData[6]['quarters']['2nd'] !== null ? ((intval($gradesData[6]['quarters']['2nd']) == floatval($gradesData[6]['quarters']['2nd'])) ? intval($gradesData[6]['quarters']['2nd']) : number_format($gradesData[6]['quarters']['2nd'], 1)) : '');
                    $pdf->SetXY(75, 80); $pdf->Write(0, isset($gradesData[6]['quarters']['3rd']) && $gradesData[6]['quarters']['3rd'] !== null ? ((intval($gradesData[6]['quarters']['3rd']) == floatval($gradesData[6]['quarters']['3rd'])) ? intval($gradesData[6]['quarters']['3rd']) : number_format($gradesData[6]['quarters']['3rd'], 1)) : '');
                    $pdf->SetXY(86, 80); $pdf->Write(0, isset($gradesData[6]['quarters']['4th']) && $gradesData[6]['quarters']['4th'] !== null ? ((intval($gradesData[6]['quarters']['4th']) == floatval($gradesData[6]['quarters']['4th'])) ? intval($gradesData[6]['quarters']['4th']) : number_format($gradesData[6]['quarters']['4th'], 1)) : '');
                }

                if (isset($gradesData[7])) {
                    // Subject 7 - Arts
                    $pdf->SetXY(53, 87); $pdf->Write(0, isset($gradesData[7]['quarters']['1st']) && $gradesData[7]['quarters']['1st'] !== null ? ((intval($gradesData[7]['quarters']['1st']) == floatval($gradesData[7]['quarters']['1st'])) ? intval($gradesData[7]['quarters']['1st']) : number_format($gradesData[7]['quarters']['1st'], 1)) : '');
                    $pdf->SetXY(65, 87); $pdf->Write(0, isset($gradesData[7]['quarters']['2nd']) && $gradesData[7]['quarters']['2nd'] !== null ? ((intval($gradesData[7]['quarters']['2nd']) == floatval($gradesData[7]['quarters']['2nd'])) ? intval($gradesData[7]['quarters']['2nd']) : number_format($gradesData[7]['quarters']['2nd'], 1)) : '');
                    $pdf->SetXY(75, 87); $pdf->Write(0, isset($gradesData[7]['quarters']['3rd']) && $gradesData[7]['quarters']['3rd'] !== null ? ((intval($gradesData[7]['quarters']['3rd']) == floatval($gradesData[7]['quarters']['3rd'])) ? intval($gradesData[7]['quarters']['3rd']) : number_format($gradesData[7]['quarters']['3rd'], 1)) : '');
                    $pdf->SetXY(86, 87); $pdf->Write(0, isset($gradesData[7]['quarters']['4th']) && $gradesData[7]['quarters']['4th'] !== null ? ((intval($gradesData[7]['quarters']['4th']) == floatval($gradesData[7]['quarters']['4th'])) ? intval($gradesData[7]['quarters']['4th']) : number_format($gradesData[7]['quarters']['4th'], 1)) : '');
                }

                if (isset($gradesData[8])) {
                    // Subject 8 - Physical Education
                    $pdf->SetXY(53, 94); $pdf->Write(0, isset($gradesData[8]['quarters']['1st']) && $gradesData[8]['quarters']['1st'] !== null ? ((intval($gradesData[8]['quarters']['1st']) == floatval($gradesData[8]['quarters']['1st'])) ? intval($gradesData[8]['quarters']['1st']) : number_format($gradesData[8]['quarters']['1st'], 1)) : '');
                    $pdf->SetXY(65, 94); $pdf->Write(0, isset($gradesData[8]['quarters']['2nd']) && $gradesData[8]['quarters']['2nd'] !== null ? ((intval($gradesData[8]['quarters']['2nd']) == floatval($gradesData[8]['quarters']['2nd'])) ? intval($gradesData[8]['quarters']['2nd']) : number_format($gradesData[8]['quarters']['2nd'], 1)) : '');
                    $pdf->SetXY(75, 94); $pdf->Write(0, isset($gradesData[8]['quarters']['3rd']) && $gradesData[8]['quarters']['3rd'] !== null ? ((intval($gradesData[8]['quarters']['3rd']) == floatval($gradesData[8]['quarters']['3rd'])) ? intval($gradesData[8]['quarters']['3rd']) : number_format($gradesData[8]['quarters']['3rd'], 1)) : '');
                    $pdf->SetXY(87, 94); $pdf->Write(0, isset($gradesData[8]['quarters']['4th']) && $gradesData[8]['quarters']['4th'] !== null ? ((intval($gradesData[8]['quarters']['4th']) == floatval($gradesData[8]['quarters']['4th'])) ? intval($gradesData[8]['quarters']['4th']) : number_format($gradesData[8]['quarters']['4th'], 1)) : '');
                }

                if (isset($gradesData[9])) {
                    // Subject 9 - Health
                    $pdf->SetXY(53, 101); $pdf->Write(0, isset($gradesData[9]['quarters']['1st']) && $gradesData[9]['quarters']['1st'] !== null ? ((intval($gradesData[9]['quarters']['1st']) == floatval($gradesData[9]['quarters']['1st'])) ? intval($gradesData[9]['quarters']['1st']) : number_format($gradesData[9]['quarters']['1st'], 1)) : '');
                    $pdf->SetXY(65, 101); $pdf->Write(0, isset($gradesData[9]['quarters']['2nd']) && $gradesData[9]['quarters']['2nd'] !== null ? ((intval($gradesData[9]['quarters']['2nd']) == floatval($gradesData[9]['quarters']['2nd'])) ? intval($gradesData[9]['quarters']['2nd']) : number_format($gradesData[9]['quarters']['2nd'], 1)) : '');
                    $pdf->SetXY(75, 101); $pdf->Write(0, isset($gradesData[9]['quarters']['3rd']) && $gradesData[9]['quarters']['3rd'] !== null ? ((intval($gradesData[9]['quarters']['3rd']) == floatval($gradesData[9]['quarters']['3rd'])) ? intval($gradesData[9]['quarters']['3rd']) : number_format($gradesData[9]['quarters']['3rd'], 1)) : '');
                    $pdf->SetXY(87, 101); $pdf->Write(0, isset($gradesData[9]['quarters']['4th']) && $gradesData[9]['quarters']['4th'] !== null ? ((intval($gradesData[9]['quarters']['4th']) == floatval($gradesData[9]['quarters']['4th'])) ? intval($gradesData[9]['quarters']['4th']) : number_format($gradesData[9]['quarters']['4th'], 1)) : '');
                }

                if (isset($gradesData[10])) {
                    // Subject 10 - ESP (Edukasyon sa Pagpapakatao)
                    $pdf->SetXY(53, 108); $pdf->Write(0, isset($gradesData[10]['quarters']['1st']) && $gradesData[10]['quarters']['1st'] !== null ? ((intval($gradesData[10]['quarters']['1st']) == floatval($gradesData[10]['quarters']['1st'])) ? intval($gradesData[10]['quarters']['1st']) : number_format($gradesData[10]['quarters']['1st'], 1)) : '');
                    $pdf->SetXY(65, 108); $pdf->Write(0, isset($gradesData[10]['quarters']['2nd']) && $gradesData[10]['quarters']['2nd'] !== null ? ((intval($gradesData[10]['quarters']['2nd']) == floatval($gradesData[10]['quarters']['2nd'])) ? intval($gradesData[10]['quarters']['2nd']) : number_format($gradesData[10]['quarters']['2nd'], 1)) : '');
                    $pdf->SetXY(75, 108); $pdf->Write(0, isset($gradesData[10]['quarters']['3rd']) && $gradesData[10]['quarters']['3rd'] !== null ? ((intval($gradesData[10]['quarters']['3rd']) == floatval($gradesData[10]['quarters']['3rd'])) ? intval($gradesData[10]['quarters']['3rd']) : number_format($gradesData[10]['quarters']['3rd'], 1)) : '');
                    $pdf->SetXY(87, 108); $pdf->Write(0, isset($gradesData[10]['quarters']['4th']) && $gradesData[10]['quarters']['4th'] !== null ? ((intval($gradesData[10]['quarters']['4th']) == floatval($gradesData[10]['quarters']['4th'])) ? intval($gradesData[10]['quarters']['4th']) : number_format($gradesData[10]['quarters']['4th'], 1)) : '');
                }

                // Compute and display General Average for elementary
                $finalGrades = [];
                foreach ($gradesData as $subjectData) {
                    // Compute subject final grade as the average of all available quarters
                    $subjectQuarters = array_filter($subjectData['quarters'], function($g) { return $g !== null; });
                    if (count($subjectQuarters) > 0) {
                        $finalGrades[] = array_sum($subjectQuarters) / count($subjectQuarters);
                    }
                }
                $generalAverage = count($finalGrades) > 0 ? round(array_sum($finalGrades) / count($finalGrades), 2) : '';
                
                // Place the General Average (adjust coordinates for elementary template)
                $pdf->SetXY(113, 130); $pdf->Write(0, $generalAverage !== '' ? ((intval($generalAverage) == floatval($generalAverage)) ? intval($generalAverage) : number_format($generalAverage, 1)) : '');
                
            } else if ($pageNum === 2) {
                $pdf->SetFont('dejavusans', '', 11);
                $pdf->useTemplate($tplId);
                
                // Overlay student information on page 2 (adjust coordinates for elementary template)
                $adviserName = $advisoryAssignment->teacher->full_name ?? ($advisoryAssignment->teacher->name ?? '');
                $schoolYear = $currentAcademicYear;
                $age = $student->age ?? '';
                
                // Student information overlay (coordinates may need adjustment based on template)
                $pdf->SetXY(225, 17); $pdf->Write(0, '' . ($student->lrn ?? ''));
                $pdf->SetXY(159, 163); $pdf->Write(0, $student->full_name ?? '');
                $pdf->SetXY(155, 173); $pdf->Write(0, '' . $age);
                $pdf->SetXY(160, 182); $pdf->Write(0, '' . ($student->grade_level ?? ''));
                $pdf->SetXY(168, 191); $pdf->Write(0, '' . $schoolYear);
                $pdf->SetXY(220, 173); $pdf->Write(0, '' . ($student->gender ?? ''));
                $pdf->SetXY(220, 183); $pdf->Write(0, '' . ($student->section ?? ''));
                $pdf->SetXY(76, 98); $pdf->Write(0, '' . $adviserName);
            }
        }
        
        return response($pdf->Output('Elementary-Report-Card.pdf', 'S'))->header('Content-Type', 'application/pdf');
        
    } catch (\Exception $e) {
        return response('Error generating elementary report card: ' . $e->getMessage(), 500);
    }
}

}
