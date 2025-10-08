<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\Student;
use App\Models\Violation;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\FaceRegistration;
use Illuminate\Support\Facades\Log;
use App\Models\Subject;
use App\Models\Fee;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }
        
        return view('student.index', compact('student'));
    }

    public function showLoginForm()
    {
        return view('student.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string',
            'password' => 'required',
        ]);

        $credentials = $request->only('student_id', 'password');

        if (Auth::guard('student')->attempt($credentials)) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('student.dashboard'));
        }

        return back()->withErrors([
            'student_id' => 'The provided credentials do not match our records.',
        ])->onlyInput('student_id');
    }

    public function violations()
    {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Get all violations for the current student, ordered by most recent first
        $violations = Violation::where('student_id', $student->id)
            ->with(['reportedBy', 'resolvedBy']) // Load relationships if needed
            ->orderBy('violation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Set effective_severity and escalated for all violations
        // Group minor violations by title to count occurrences
        $minorCountsByTitle = [];
        foreach ($violations as $violation) {
            if ($violation->severity === 'minor') {
                $minorCountsByTitle[$violation->title] = ($minorCountsByTitle[$violation->title] ?? 0) + 1;
            }
        }

        foreach ($violations as $violation) {
            if ($violation->severity === 'minor') {
                $countForTitle = $minorCountsByTitle[$violation->title] ?? 0;
                if ($countForTitle >= 3) {
                    $violation->effective_severity = 'major';
                    $violation->escalated = true;
                    $violation->escalation_reason = '3rd minor offense - treated as major';
                } else {
                    $violation->effective_severity = 'minor';
                    $violation->escalated = false;
                }
            } elseif ($violation->severity === 'major' || $violation->severity === 'severe') {
                $violation->effective_severity = $violation->severity;
                $violation->escalated = false;
            }
        }

        return view('student.violations', compact('student', 'violations'));
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('student.login');
    }


   public function registerStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'lrn' => 'required|string|unique:students,lrn',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'contact_number' => 'required|string|max:20',
            'email' => 'nullable|email|unique:students,email',
            'address' => 'required|string|max:255',
            'grade_level' => 'required|string|max:50',
            'section' => 'required|string|max:50',
            'guardian_name' => 'required|string|max:255',
            'guardian_contact' => 'required|string|max:20',
            'id_photo' => 'required|string',
            'id_photo_mime_type' => 'required|string|in:image/jpeg,image/png',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $studentData = [
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?? null,
                'last_name' => $request->last_name,
                'suffix' => $request->suffix ?? null,
                'lrn' => $request->lrn,
                'gender' => $request->gender,
                'date_of_birth' => Carbon::parse($request->date_of_birth)->format('Y-m-d'),
                'contact_number' => $request->contact_number,
                'email' => $request->email ?? null,
                'address' => $request->address,
                'city' => $request->city ?? null,
                'province' => $request->province ?? null,
                'zip_code' => $request->zip_code ?? null,
                'grade_level' => $request->grade_level,
                'section' => $request->section,
                'father_name' => $request->father_name ?? null,
                'father_contact' => $request->father_contact ?? null,
                'mother_name' => $request->mother_name ?? null,
                'mother_contact' => $request->mother_contact ?? null,
                'guardian_name' => $request->guardian_name,
                'guardian_contact' => $request->guardian_contact,
                'id_photo' => $request->id_photo,
                'id_photo_mime_type' => $request->id_photo_mime_type,
                'enrollment_status' => 'pending',
                'academic_year' => $request->academic_year ?? '2024-2025',
                'student_type' => $request->student_type ?? 'new',
                'password' => null, // Explicitly set to null
            ];

            $student = Student::create($studentData);

            // Automatically create face registration if ID photo exists
            if ($request->id_photo) {
                FaceRegistration::create([
                    'student_id' => $student->id,
                    'face_image_data' => $request->id_photo,
                    'face_image_mime_type' => $request->id_photo_mime_type,
                    'source' => 'registration_form',
                    'registered_at' => now(),
                    'is_active' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Student registered successfully',
                'student' => $student
            ], 201);

        } catch (\Exception $e) {
            Log::error('Student registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function enrollment()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }
        
        return view('student.enrollment', compact('student'));
    }

    public function submitEnrollment(Request $request)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }

        // Check if student already has payment schedules that are not rejected
        $existingSchedules = Payment::where('payable_type', Student::class)
            ->where('payable_id', $student->id)
            ->whereNotIn('confirmation_status', ['rejected', 'declined'])
            ->exists();
            
        if ($existingSchedules) {
            return back()->withErrors([
                'error' => 'You have already submitted a payment schedule. You can only re-submit if your previous schedule was rejected or declined.'
            ]);
        }

        $request->validate([
            'payment_mode' => 'required|in:full,quarterly,monthly',
            'payment_method' => 'required|in:cash,bank_transfer,online_payment'
        ]);

        try {
            // Get enrollee data to fetch preferred_schedule
            $enrollee = $student->enrollee;
            $preferredScheduleDate = $enrollee ? $enrollee->preferred_schedule : now()->addDays(7);
            
            // If no preferred schedule, use a default date
            if (!$preferredScheduleDate) {
                $preferredScheduleDate = now()->addDays(7);
            }

            // Calculate total fees
            $feeCalculation = Fee::calculateTotalFeesForGrade($student->grade_level);
            $totalAmount = $feeCalculation['total_amount'];

            // Create payment schedules based on payment mode
            $this->createPaymentSchedules($student, $request->payment_mode, $totalAmount, $preferredScheduleDate, $request->payment_method, $request->payment_notes);

            // Update student with enrollment information
            $student->update([
                'payment_mode' => $request->payment_mode,
                'total_fees_due' => $totalAmount,
                'enrollment_status' => 'enrolled'
            ]);

            return redirect()->route('student.dashboard')
                ->with('success', 'Payment schedule submitted successfully! Your schedule is now pending approval from the cashier\'s office.');
        } catch (\Exception $e) {
            Log::error('Enrollment submission failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to complete enrollment. Please try again.']);
        }
    }

    private function createPaymentSchedules($student, $paymentMode, $totalAmount, $baseDate, $paymentMethod, $notes = null)
    {
        $schedules = [];
        
        switch ($paymentMode) {
            case 'full':
                $schedules[] = [
                    'period_name' => 'Full Payment',
                    'amount' => $totalAmount,
                    'scheduled_date' => $baseDate,
                ];
                break;
                
            case 'quarterly':
                $quarterlyAmount = $totalAmount / 4;
                $quarters = ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
                
                for ($i = 0; $i < 4; $i++) {
                    $schedules[] = [
                        'period_name' => $quarters[$i],
                        'amount' => $quarterlyAmount,
                        'scheduled_date' => Carbon::parse($baseDate)->addMonths($i * 3),
                    ];
                }
                break;
                
            case 'monthly':
                $monthlyAmount = $totalAmount / 10;
                $months = ['June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March'];
                
                for ($i = 0; $i < 10; $i++) {
                    $schedules[] = [
                        'period_name' => $months[$i],
                        'amount' => $monthlyAmount,
                        'scheduled_date' => Carbon::parse($baseDate)->addMonths($i),
                    ];
                }
                break;
        }

        // Create payment records
        foreach ($schedules as $schedule) {
            Payment::create([
                'transaction_id' => 'TXN-' . $student->student_id . '-' . time() . '-' . rand(100, 999),
                'payable_type' => Student::class,
                'payable_id' => $student->id,
                'amount' => $schedule['amount'],
                'scheduled_date' => $schedule['scheduled_date'],
                'period_name' => $schedule['period_name'],
                'payment_mode' => $paymentMode,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'confirmation_status' => 'pending',
                'notes' => $notes,
            ]);
        }
    }

    public function subjects()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }
        
        return view('student.subjects', compact('student'));
    }

    public function payments()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }
        
        // Get all payment records for this student
        $paymentSchedules = Payment::where('payable_type', Student::class)
            ->where('payable_id', $student->id)
            ->orderBy('scheduled_date', 'asc')
            ->get();
        
        // Get payment history (confirmed payments)
        $paymentHistory = Payment::where('payable_type', Student::class)
            ->where('payable_id', $student->id)
            ->where('confirmation_status', 'confirmed')
            ->orderBy('confirmed_at', 'desc')
            ->get();
        
        // Calculate fee breakdown and totals
        $feeCalculation = Fee::calculateTotalFeesForGrade($student->grade_level);
        $totalFeesAmount = $feeCalculation['total_amount'];
        
        // Calculate total paid (confirmed payments)
        $totalPaid = $paymentHistory->sum('amount_received') ?: $paymentHistory->sum('amount');
        
        // Calculate balance due
        $balanceDue = $totalFeesAmount - $totalPaid;
        
        // Update student totals if they don't match
        if ($student->total_fees_due != $totalFeesAmount || $student->total_paid != $totalPaid) {
            $student->update([
                'total_fees_due' => $totalFeesAmount,
                'total_paid' => $totalPaid,
                'is_paid' => $balanceDue <= 0
            ]);
        }
        
        return view('student.payments', compact('student', 'paymentSchedules', 'paymentHistory', 'totalFeesAmount', 'totalPaid', 'balanceDue'));
    }

    public function updatePaymentMode(Request $request)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }

        // Check if student has submitted payment schedules
        $hasSubmittedSchedule = Payment::where('payable_type', Student::class)
            ->where('payable_id', $student->id)
            ->exists();

        if ($hasSubmittedSchedule) {
            return back()->withErrors([
                'error' => 'Cannot change payment mode. You have already submitted a payment schedule. Please contact the cashier\'s office to make changes.'
            ]);
        }

        $request->validate([
            'payment_mode' => 'required|in:full,quarterly,monthly'
        ]);

        try {
            $student->update([
                'payment_mode' => $request->payment_mode
            ]);

            return back()->with('success', 'Payment mode updated successfully!');
        } catch (\Exception $e) {
            Log::error('Payment mode update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update payment mode. Please try again.']);
        }
    }

    public function faceRegistration()
    {
        try {
            $student = Auth::guard('student')->user();
            if (!$student) {
                return redirect()->route('student.login');
            }
            return view('student.face-registration', compact('student'));
        } catch (\Exception $e) {
            \Log::error('Error loading face registration page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('errors.500', ['message' => 'An error occurred loading the face registration page.'], 500);
        }
    }
 public function saveFaceRegistration(Request $request)
{
    try {
        $studentId = auth()->user()->id ?? $request->input('student_id');

        // Validate
        if (!$request->has('face_encoding')) {
            \Log::error('Face registration failed: Missing face encoding.', ['request' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Missing face encoding.'
            ], 400);
        }

        // Check if already registered
        $alreadyRegistered = \App\Models\FaceRegistration::where('student_id', $studentId)
            ->where('is_active', true)
            ->exists();
        if ($alreadyRegistered) {
            \Log::info('Face registration attempt for already registered student.', ['student_id' => $studentId]);
            return response()->json([
                'success' => false,
                'message' => 'This system is already registered.'
            ], 409);
        }

        // Only store base64, not full data URL
        $faceImageData = $request->input('face_image_data');
        if ($faceImageData && str_starts_with($faceImageData, 'data:')) {
            $faceImageData = explode(',', $faceImageData, 2)[1] ?? null;
        }

        // Log the incoming data for debugging (do not log image data for privacy)
        \Log::info('Creating face registration', [
            'student_id' => $studentId,
            'has_face_encoding' => $request->has('face_encoding'),
            'has_face_image_data' => !empty($faceImageData),
            'face_image_mime_type' => $request->input('face_image_mime_type'),
            'confidence_score' => $request->input('confidence_score'),
            'face_landmarks' => $request->input('face_landmarks'),
            'source' => $request->input('source', 'camera_capture'),
        ]);

        \App\Models\FaceRegistration::create([
            'student_id' => $studentId,
            'face_encoding' => $request->input('face_encoding'),
            'confidence_score' => $request->input('confidence_score'),
            'face_landmarks' => $request->input('face_landmarks'),
            'face_image_data' => $faceImageData,
            'face_image_mime_type' => $request->input('face_image_mime_type'),
            'source' => $request->input('source', 'camera_capture'),
            'registered_at' => now(),
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Face registered successfully!'
        ]);
    } catch (\Exception $e) {
        \Log::error('Face registration server error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}


    public function deleteFaceRegistration()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $student->faceRegistrations()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Face registration removed successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Face registration deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove face registration'
            ], 500);
        }
    }
}