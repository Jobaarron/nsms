<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnrollStudentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Enrollee;
use App\Models\Fee;
use App\Mail\EnrolleeCredentialsMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    /**
     * Show the enrollment form.
     */
    public function create()
    {
        return view('enroll');
    }

    /**
     * Handle the enrollment submission.
     */
    public function store(EnrollStudentRequest $request): RedirectResponse
    {
        // 1) Validation is now handled by EnrollStudentRequest.
        $data = $request->validated();

        // The old inline validation is removed. The validated data is accessed via $request->validated().
        /*
        $data = $request->validate([
            'id_photo'           => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'documents'          => 'required|array|min:1',
            'documents.*'        => 'file|mimes:pdf,docx,jpeg,png,jpg|max:4096',
            
            // Enrollee Info
            'lrn'                => 'nullable|string|max:12',
            'student_type'       => 'required|in:new,transferee,returnee,continuing',
            
            // Name fields
            'first_name'         => 'required|string|max:50',
            'middle_name'        => 'nullable|string|max:50',
            'last_name'          => 'required|string|max:50',
            'suffix'             => 'nullable|string|max:10',
            
            // Personal Info
            'date_of_birth'      => 'required|date|before:today',
            'gender'             => 'required|in:male,female',
            'religion'           => 'nullable|string|max:100',
            
            // Contact Info
            'email'              => 'required|email|unique:enrollees,email',
            'contact_number'     => 'nullable|regex:/^09[0-9]{9}$/',
            
            // Address
            'address'            => 'required|string|max:255',
            'city'               => 'nullable|string|max:100',
            'province'           => 'nullable|string|max:100',
            'zip_code'           => 'nullable|string|max:10',
            
            // Academic Info Applied For
            'grade_level'        => 'required|in:Nursery,Junior Casa,Senior Casa,Grade 1,Grade 2,Grade 3,Grade 4,Grade 5,Grade 6,Grade 7,Grade 8,Grade 9,Grade 10,Grade 11,Grade 12',
            'strand'             => [
                'nullable',
                'required_if:grade_level,Grade 11,Grade 12',
                'string',
                'max:50',
            ],
            
            // Parent/Guardian Info
            'father_name'        => 'nullable|string|max:100',
            'father_occupation'  => 'nullable|string|max:100',
            'father_contact'     => 'nullable|regex:/^09[0-9]{9}$/',
            'mother_name'        => 'nullable|string|max:100',
            'mother_occupation'  => 'nullable|string|max:100',
            'mother_contact'     => 'nullable|regex:/^09[0-9]{9}$/',
            'guardian_name'      => 'required|string|max:100',
            'guardian_contact'   => [
                'required',
                'regex:/^09[0-9]{9}$/',
            ],
            
            // School Info
            'last_school_type'   => 'nullable|in:public,private',
            'last_school_name'   => 'nullable|string|max:100',
            
            // Medical & Payment
            'medical_history'    => 'nullable|string|max:1000',
            'payment_mode'       => 'required|in:cash,online payment,scholarship,voucher',
            'preferred_schedule' => 'nullable|date|after_or_equal:today',
        ]);
        */

        // 2) Store the ID photo as base64 data
        if ($request->hasFile('id_photo')) {
            $photo = $request->file('id_photo');
            $data['id_photo'] = base64_encode(file_get_contents($photo->getRealPath()));
            $data['id_photo_mime_type'] = $photo->getMimeType();
        }

        // 3) Store the multiple documents with metadata (consistent with EnrolleeController)
        $documents = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $doc) {
                $path = $doc->store('documents', 'public');
                $documents[] = [
                    'type' => strtoupper($doc->getClientOriginalExtension()) ?: 'Unknown',
                    'filename' => $doc->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $doc->getMimeType(),
                    'size' => $doc->getSize(),
                    'uploaded_at' => now()->toISOString(),
                    'status' => 'pending'
                ];
            }
        }
        $data['documents'] = $documents; // Store as array of objects with metadata

        // 4) Map grade_level to grade_level_applied for enrollee
        $data['grade_level_applied'] = $data['grade_level'];
        unset($data['grade_level']);
        
        // 5) Map strand to strand_applied for enrollee
        if (isset($data['strand'])) {
            $data['strand_applied'] = $data['strand'];
            unset($data['strand']);
        }

        // 6) Set enrollment defaults
        $data['enrollment_status'] = 'pending';
        $data['academic_year'] = date('Y') . '-' . (date('Y') + 1);
        $data['application_date'] = now();

        // 6.5) Calculate fees for the selected grade level
        try {
            $feeCalculation = Fee::calculateTotalFeesForGrade($data['grade_level_applied'], $data['academic_year']);
            $data['total_fees_due'] = $feeCalculation['total_amount'] ?? 0;
            $data['total_paid'] = 0;
            
            Log::info('Fee calculation successful for enrollment', [
                'grade_level' => $data['grade_level_applied'],
                'academic_year' => $data['academic_year'],
                'total_fees_due' => $data['total_fees_due'],
                'fee_count' => count($feeCalculation['fees'] ?? [])
            ]);
        } catch (\Exception $e) {
            Log::error('Fee calculation failed during enrollment', [
                'grade_level' => $data['grade_level_applied'],
                'academic_year' => $data['academic_year'],
                'error' => $e->getMessage()
            ]);
            
            // Set default values if fee calculation fails
            $data['total_fees_due'] = 0;
            $data['total_paid'] = 0;
        }

        // 7) Create enrollment application (Enrollee record)
        $enrollee = Enrollee::create($data);

        // 8) Send enrollment credentials email
        try {
            Mail::to($enrollee->email)->send(new EnrolleeCredentialsMail($enrollee));
            Log::info('Enrollment credentials email sent to: ' . $enrollee->email . ' with Application ID: ' . $enrollee->application_id);
        } catch (\Exception $e) {
            Log::error('Failed to send enrollment credentials email: ' . $e->getMessage());
        }

        // 9) Redirect back with success message
        return redirect()
            ->route('enroll.create')
            ->with('success', 'Enrollment application submitted successfully! Please check your email for your login credentials.');
    }

    /**
     * Calculate fees for a specific grade level (API endpoint)
     *
     * @param string $gradeLevel
     * @return JsonResponse
     */
    public function calculateFees($gradeLevel): JsonResponse
    {
        try {
            // Decode URL-encoded grade level (e.g., "Grade%201" becomes "Grade 1")
            $gradeLevel = urldecode($gradeLevel);
            
            // Get current academic year
            $academicYear = date('Y') . '-' . (date('Y') + 1);
            
            // Calculate fees for the grade level
            $feeCalculation = Fee::calculateTotalFeesForGrade($gradeLevel, $academicYear);
            
            // Add educational level information
            $feeCalculation['educational_level'] = Fee::getEducationalLevel($gradeLevel);
            $feeCalculation['grade_level'] = $gradeLevel;
            $feeCalculation['academic_year'] = $academicYear;
            
            return response()->json([
                'success' => true,
                'data' => $feeCalculation
            ]);
            
        } catch (\Exception $e) {
            Log::error('Fee calculation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate fees for the selected grade level.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
