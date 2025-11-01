<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CounselingSession;

class PdfController extends Controller
{
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

    // Debug: log sessionId and session data
    \Log::info('PDF sessionId:', ['session_id' => $sessionId]);
    \Log::info('Session data for PDF:', ['session' => $session]);

    $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
    $templatePath = storage_path('app/public/counseling-form/SEWO-CRFS-010 Counselling-Request-Forms.pdf');
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

        // Debug: log student data
        \Log::info('Student data for PDF:', ['student' => $session->student]);

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
            $templatePath = storage_path('app/public/Student-narrative-report/Student.pdf');
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
            $pdf->SetXY(176, 40); $pdf->Write(0, $violation->violation_time ?? '');
             
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
        $templatePath = storage_path('app/public/Case-Meeting-Report/CaseMeeting.pdf');
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
            $pdf->SetXY(176, 40); $pdf->Write(0, $violation->violation_time ?? '');
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
        $caseMeeting = \App\Models\CaseMeeting::with(['violation', 'student'])->findOrFail($caseMeetingId);

        // Attempt to get teacher name and user_id from the violation's teacher, fallback to null
        $teacherName = null;
        $teacherUserId = null;
        if ($caseMeeting->violation && method_exists($caseMeeting->violation, 'teacher')) {
            $teacher = $caseMeeting->violation->teacher;
            if ($teacher) {
                $teacherName = $teacher->full_name ?? $teacher->name ?? null;
                // $teacherUserId = $teacher->user_id ?? null;
            }
        }

        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $templatePath = storage_path('app/public/Teacher-Report/Teacher-Observation-Report.pdf');
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
        $pdf->SetXY(27, 62); // Teacher Name
        $pdf->Write(0, $teacherName ?? '');
        $pdf->SetXY(87, 292); // Teacher Name
        $pdf->Write(0, $teacherName ?? '');
        // Optionally, overlay the teacher user_id (for demonstration, place at 40, 50)
        if ($teacherUserId) {
            $pdf->SetXY(40, 50); // Teacher user_id
            $pdf->Write(0, 'User ID: ' . $teacherUserId);
        }
        $pdf->SetXY(120, 45); // Scheduled Date
        $pdf->Write(0, $caseMeeting->scheduled_date ? (is_string($caseMeeting->scheduled_date) ? $caseMeeting->scheduled_date : $caseMeeting->scheduled_date->format('Y-m-d')) : '');
        $pdf->SetXY(174, 45); // Scheduled Time
        $pdf->Write(0, $caseMeeting->scheduled_time ? (is_string($caseMeeting->scheduled_time) ? $caseMeeting->scheduled_time : $caseMeeting->scheduled_time->format('H:i')) : '');
        $pdf->SetXY(18, 90); // Teacher Statement
        $pdf->MultiCell(150, 8, $caseMeeting->teacher_statement ?? '');
        $pdf->SetXY(18, 199); // Action Plan
        $pdf->MultiCell(150, 8, $caseMeeting->action_plan ?? '');

        return response($pdf->Output('Teacher-Observation-Report.pdf', 'S'))->header('Content-Type', 'application/pdf');
    }

    /**
     * Generate Disciplinary Conference Summary Report PDF for all students using TCPDF.
     * @return \Illuminate\Http\Response
     */
    public function conferenceSummaryReportAllPdf()
    {
        $students = \App\Models\Student::all();
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $templatePath = storage_path('app/public/Discplinary-Summary-Report/Summary Report.pdf');
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

        // Table starting coordinates (adjust as needed for your template)
        $startY = 59; // Y coordinate of first row (after header)
        $rowHeight = 6; // Height of each row
        $maxRowsPerPage = 25; // Adjust based on your template
        $currentRow = 0;

        foreach ($students as $index => $student) {
            // Fetch the latest CaseMeeting for this student (or adjust as needed)
            $caseMeeting = \App\Models\CaseMeeting::where('student_id', $student->id)->orderByDesc('id')->first();
            $caseMeetingId = $caseMeeting ? $caseMeeting->id : '';

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
            $pdf->Write(0, $caseMeetingId);
            $pdf->SetXY(30, $y); // Name
            $pdf->Write(0, $student->full_name ?? '');
            $pdf->SetXY(84, $y); // Grade/Section
            $pdf->Write(0, ($student->grade_level ?? '') . ' - ' . ($student->section ?? ''));
            $pdf->SetXY(122, $y); // DCR Case No. (repeat CaseMeeting id)
            $pdf->Write(0, $caseMeetingId);
            // Issues/Concerns and Remarks left blank, but no border/cell

            $currentRow++;
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
        $templatePath = 'C:/Users/anony/Documents/nsms/storage/app/public/Disciplinary-Con-Report/Disciplinary-Con-Report.pdf';
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
                    // Try to parse string to time
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

            // Page 2: template, then write summary, follow_up_meeting, and checklist
            if ($pageCount > 1) {
                $tplId2 = $pdf->importPage(2);
                $size2 = $pdf->getTemplateSize($tplId2);
                $orientation2 = ($size2['width'] > $size2['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation2, [$size2['width'], $size2['height']]);
                $pdf->useTemplate($tplId2);
                // Now write summary and follow_up_meeting on page 2
                $pdf->SetFont('dejavusans', '', 10.1);
                $pdf->SetXY(18, 30); $pdf->MultiCell(169, 8, $caseMeeting->summary ?? '');
                $pdf->SetXY(100, 90); $pdf->MultiCell(60, 8, $caseMeeting->follow_up_meeting ?? '');
                // Overlay check icons for report availability (now on page 2)
                $check = '✓';
                $pdf->SetFont('dejavusans', '', 12);
                if ($hasStudentNarrative) {
                    $pdf->SetXY(31, 210); // Adjust position as needed for Student Narrative check on page 2
                    $pdf->Write(0, $check);
                }
                if ($hasTeacherObservation) {
                    $pdf->SetXY(31, 215); // Adjust position as needed for Teacher Observation check on page 2
                    $pdf->Write(0, $check);
                }
                // Disciplinary Conference Report checklist
                $hasDisciplinaryConReport = (isset($caseMeeting->status) && strtolower($caseMeeting->status) === 'active');
                if ($hasDisciplinaryConReport) {
                    $pdf->SetXY(31, 220); // Adjust position as needed for Disciplinary Conference Report check on page 2
                    $pdf->Write(0, $check);
                    $pdf->SetXY(38, 220);
                    $pdf->Write(0, 'Disciplinary Conference Report');
                }
            }

            return response($pdf->Output('Disciplinary-Conference-Reports.pdf', 'S'))
                ->header('Content-Type', 'application/pdf');
        }
    }
