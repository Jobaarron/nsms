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
        $pdf->SetXY(47,129);
        $pdf->MultiCell(150, 6, $session->incident_description ?? '');

        // --- Lower form (copy for student/office) ---
        $yOffset = 150; // Adjust this value based on your template's layout
        $pdf->SetXY(35, 45 + $yOffset);
        $pdf->Write(0, $session->start_date ? $session->start_date->format('Y-m-d') : ($session->created_at ? $session->created_at->format('Y-m-d') : ''));
        $pdf->SetXY(128, 43 + $yOffset);
        $pdf->Write(0, $session->id ?? '');
        $pdf->SetXY(46, 52 + $yOffset);
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

        // Add more overlays as needed for case meeting details
        if (!empty($caseMeeting->notes)) {
            $pdf->SetXY(18, 84);
            $pdf->MultiCell(160, 8, $caseMeeting->notes);
        }

        return response($pdf->Output('Case-Meeting-Report.pdf', 'S'))->header('Content-Type', 'application/pdf');
    }

}
