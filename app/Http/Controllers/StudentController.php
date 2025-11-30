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
use App\Models\ClassSchedule;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentConfirmedMail;

class StudentController extends Controller
{
    /**
     * Get current academic year using Philippine academic year logic
     */
    private static function getCurrentAcademicYear()
    {
        $currentYear = date('Y');
        $currentMonth = date('n'); // 1-12
        
        if ($currentMonth >= 1 && $currentMonth <= 5) {
            // January to May - second half of academic year
            return ($currentYear - 1) . '-' . $currentYear;
        } else {
            // June to December - first half of academic year
            return $currentYear . '-' . ($currentYear + 1);
        }
    }
    public function index()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }
        
        // Refresh payment totals to ensure they're up-to-date
        $this->refreshStudentPaymentTotals($student);
        
        // Reload the student to get updated values
        $student->refresh();
        
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
        $allViolations = Violation::where('student_id', $student->id)
            ->with(['reportedBy', 'resolvedBy']) // Load relationships if needed
            ->orderBy('violation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Filter violations similar to discipline portal logic
        $studentsWithGroupedViolations = $allViolations->where('title', 'Multiple Minor Violations - Escalated to Major')
                                                       ->pluck('student_id')
                                                       ->unique()
                                                       ->toArray();

        // Filter violations: exclude individual minor violations if grouped violations exist
        $violations = $allViolations->filter(function ($violation) use ($studentsWithGroupedViolations) {
            // Always show major violations and grouped violations
            if ($violation->severity === 'major' || $violation->title === 'Multiple Minor Violations - Escalated to Major') {
                return true;
            }
            
            // For minor violations, only show if student doesn't have a grouped violation
            return !in_array($violation->student_id, $studentsWithGroupedViolations);
        });

        // Set effective_severity and other properties for display
        foreach ($violations as $violation) {
            if ($violation->title === 'Multiple Minor Violations - Escalated to Major') {
                $violation->effective_severity = 'major';
                $violation->escalated = true;
            } else {
                $violation->effective_severity = $violation->severity;
                $violation->escalated = $violation->is_escalated ?? false;
            }
        }

        // Separate violations by effective severity for the view
        $minorViolations = $violations->where('effective_severity', 'minor');
        $majorViolations = $violations->whereIn('effective_severity', ['major', 'severe']);

        return view('student.violations', compact('student', 'violations', 'minorViolations', 'majorViolations'));
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
                'academic_year' => $request->academic_year ?? self::getCurrentAcademicYear(),
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
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment schedule submitted successfully! Please wait for cashier approval to complete your enrollment.',
                'redirect_url' => route('student.payments')
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
        
        // Redirect fully enrolled students to dashboard
        if ($student->enrollment_status === 'enrolled') {
            return redirect()->route('student.dashboard')
                ->with('info', 'You are already fully enrolled. Access your enrollment details from the dashboard.');
        }
        
        // Calculate total fees for the student
        // Use Philippine academic year logic if student doesn't have one set
        if ($student->academic_year) {
            $academicYear = $student->academic_year;
        } else {
            $academicYear = self::getCurrentAcademicYear();
        }
        $feeCalculation = Fee::calculateTotalFeesForGrade($student->grade_level, $academicYear);
        $totalAmount = $feeCalculation['total_amount'];
        
        // Check payment schedule status
        $paymentScheduleStatus = Payment::where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $student->id)
            ->first();
            
        // Get current subjects for the student (same logic as subjects view)
        $currentSubjects = Subject::where('grade_level', $student->grade_level)
            ->where('academic_year', $student->academic_year ?? self::getCurrentAcademicYear())
            ->where('is_active', true)
            ->when(in_array($student->grade_level, ['Grade 11', 'Grade 12']), function($query) use ($student) {
                return $query->where(function($q) use ($student) {
                    $q->whereNull('strand') // Core subjects for all strands
                      ->orWhere('strand', $student->strand);
                });
            })
            ->when($student->track, function($query) use ($student) {
                return $query->where(function($q) use ($student) {
                    $q->whereNull('track') // Subjects for all tracks in the strand
                      ->orWhere('track', $student->track);
                });
            })
            ->orderBy('subject_name')
            ->get();
        
        return view('student.enrollment', compact('student', 'totalAmount', 'paymentScheduleStatus', 'currentSubjects'));
    }

    public function submitEnrollment(Request $request)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }

        // Check if student already has payment schedules that are not rejected
        $existingSchedules = Payment::where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $student->id)
            ->whereNotIn('confirmation_status', ['rejected', 'declined'])
            ->exists();
            
        if ($existingSchedules) {
            $errorMessage = 'You have already submitted a payment schedule. You can only re-submit if your previous schedule was rejected or declined.';
            
            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 400);
            }
            
            return back()->withErrors(['error' => $errorMessage]);
        }

        // If resubmitting after rejection, delete the old rejected payment schedules
        Payment::where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $student->id)
            ->whereIn('confirmation_status', ['rejected', 'declined'])
            ->delete();

        $request->validate([
            'payment_method' => 'nullable|in:full,quarterly,monthly',
            'payment_notes' => 'nullable|string|max:1000'
        ]);

        // Set default payment schedule if not provided
        $paymentSchedule = $request->payment_method ?? 'full';

        try {
            Log::info('Starting enrollment submission for student: ' . $student->id);
            
            // Set default payment schedule date
            $preferredScheduleDate = now()->addDays(7); // Default date
            
            Log::info('Preferred schedule date: ' . $preferredScheduleDate);

            // Calculate total fees
            $academicYear = $student->academic_year ?? (date('Y') . '-' . (date('Y') + 1));
            $feeCalculation = Fee::calculateTotalFeesForGrade($student->grade_level, $academicYear);
            $totalAmount = $feeCalculation['total_amount'];
            
            Log::info('Total amount calculated: ' . $totalAmount);


            // Create payment schedules and get the first payment's transaction_id
            Log::info('Creating payment schedules with method: ' . $paymentSchedule);
            $transactionId = null;
            $schedules = [];
            // Ensure baseDate is a Carbon instance
            $baseDate = $preferredScheduleDate instanceof \Carbon\Carbon ? $preferredScheduleDate : \Carbon\Carbon::parse($preferredScheduleDate);
            switch ($paymentSchedule) {
                case 'full':
                    $schedules[] = [
                        'period_name' => 'Full Payment',
                        'amount' => $totalAmount,
                        'scheduled_date' => $baseDate->format('Y-m-d'),
                    ];
                    break;
                case 'quarterly':
                    $quarterlyAmount = $totalAmount / 4;
                    $quarters = ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
                    for ($i = 0; $i < 4; $i++) {
                        $schedules[] = [
                            'period_name' => $quarters[$i],
                            'amount' => $quarterlyAmount,
                            'scheduled_date' => $baseDate->copy()->addMonths($i * 3)->format('Y-m-d'),
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
                            'scheduled_date' => $baseDate->copy()->addMonths($i)->format('Y-m-d'),
                        ];
                    }
                    break;
            }
            foreach ($schedules as $idx => $schedule) {
                try {
                    $payment = Payment::create([
                        'transaction_id' => Payment::generateTransactionId($student->student_id),
                        'payable_type' => 'App\\Models\\Student',
                        'payable_id' => $student->id,
                        'fee_id' => null,
                        'amount' => $schedule['amount'],
                        'scheduled_date' => $schedule['scheduled_date'],
                        'period_name' => $schedule['period_name'],
                        'payment_method' => $paymentSchedule,
                        'status' => 'pending',
                        'confirmation_status' => 'pending',
                        'processed_by' => null,
                        'notes' => $request->payment_notes,
                    ]);
                    if ($idx === 0) {
                        $transactionId = $payment->transaction_id;
                    }
                } catch (\Exception $e) {
                    Log::error('Payment creation failed: ' . $e->getMessage());
                    Log::error('Payment creation stack trace: ' . $e->getTraceAsString());
                    Log::error('Payment data: ' . json_encode([
                        'transaction_id' => Payment::generateTransactionId($student->student_id),
                        'payable_type' => 'App\\Models\\Student',
                        'payable_id' => $student->id,
                        'fee_id' => null,
                        'amount' => $schedule['amount'],
                        'scheduled_date' => $schedule['scheduled_date'],
                        'period_name' => $schedule['period_name'],
                        'payment_method' => $paymentSchedule,
                        'status' => 'pending',
                        'confirmation_status' => 'pending',
                        'processed_by' => null,
                        'notes' => $request->payment_notes,
                    ]));
                    throw $e;
                }
            }
            Log::info('Payment schedules created successfully');

            // Update student with fee information but keep pre_registered status until payment is approved
            Log::info('Updating student fee information');
            $student->update([
                'total_fees_due' => $totalAmount,
                // Keep enrollment_status as 'pre_registered' until cashier approves payment schedule
            ]);
            
            Log::info('Student updated successfully');

            // Send payment confirmation email with the first payment's details
            try {
                if ($transactionId) {
                    // Get the first payment record to send in email
                    $firstPayment = Payment::where('transaction_id', $transactionId)->first();
                    if ($firstPayment) {
                        Log::info('Sending payment confirmation email to: ' . $student->user->email);
                        Mail::send(new PaymentConfirmedMail($firstPayment, $student));
                        Log::info('Payment confirmation email sent successfully');
                    }
                }
            } catch (\Exception $emailError) {
                Log::error('Failed to send payment confirmation email: ' . $emailError->getMessage());
                // Don't fail the enrollment if email fails - just log it
            }

            // Check if this is an AJAX request
            Log::info('Checking request type - AJAX headers: ' . json_encode([
                'expectsJson' => $request->expectsJson(),
                'ajax' => $request->ajax(),
                'X-Requested-With' => $request->header('X-Requested-With')
            ]));
            
            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                Log::info('Returning JSON response');
                return response()->json([
                    'success' => true,
                    'message' => 'Payment schedule submitted successfully! Your schedule is now pending approval from the cashier\'s office.',
                    'redirect_url' => '/student/dashboard',
                    'transaction_id' => $transactionId
                ]);
            }
            
            Log::info('Returning redirect response');
            return redirect()->route('student.dashboard')
                ->with('success', 'Payment schedule submitted successfully! Your schedule is now pending approval from the cashier\'s office.');
        } catch (\Exception $e) {
            Log::error('Enrollment submission failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            
            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to complete enrollment. Please try again.'
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to complete enrollment. Please try again.']);
        }
    }

    private function createPaymentSchedules($student, $paymentMethod, $totalAmount, $baseDate, $notes = null)
    {
        $schedules = [];
        
        // Ensure baseDate is a Carbon instance
        if (!$baseDate instanceof \Carbon\Carbon) {
            $baseDate = \Carbon\Carbon::parse($baseDate);
        }
        
        switch ($paymentMethod) {
            case 'full':
                $schedules[] = [
                    'period_name' => 'Full Payment',
                    'amount' => $totalAmount,
                    'scheduled_date' => $baseDate->format('Y-m-d'),
                ];
                break;
                
            case 'quarterly':
                $quarterlyAmount = $totalAmount / 4;
                $quarters = ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
                
                for ($i = 0; $i < 4; $i++) {
                    $schedules[] = [
                        'period_name' => $quarters[$i],
                        'amount' => $quarterlyAmount,
                        'scheduled_date' => $baseDate->copy()->addMonths($i * 3)->format('Y-m-d'),
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
                        'scheduled_date' => $baseDate->copy()->addMonths($i)->format('Y-m-d'),
                    ];
                }
                break;
        }

        // Create payment records
        foreach ($schedules as $schedule) {
            try {
                Payment::create([
                    'transaction_id' => Payment::generateTransactionId($student->student_id),
                    'payable_type' => 'App\\Models\\Student',
                    'payable_id' => $student->id,
                    'fee_id' => null, // Explicitly set to null since this is a payment schedule, not tied to specific fee
                    'amount' => $schedule['amount'],
                    'scheduled_date' => $schedule['scheduled_date'],
                    'period_name' => $schedule['period_name'],
                    'payment_method' => $paymentMethod, // Now stores payment schedule: full, quarterly, monthly
                    'status' => 'pending',
                    'confirmation_status' => 'pending',
                    'processed_by' => null, // Explicitly set to null until processed by cashier
                    'notes' => $notes,
                ]);
            } catch (\Exception $e) {
                Log::error('Payment creation failed: ' . $e->getMessage());
                Log::error('Payment creation stack trace: ' . $e->getTraceAsString());
                Log::error('Payment data: ' . json_encode([
                    'transaction_id' => Payment::generateTransactionId($student->student_id),
                    'payable_type' => 'App\\Models\\Student',
                    'payable_id' => $student->id,
                    'fee_id' => null,
                    'amount' => $schedule['amount'],
                    'scheduled_date' => $schedule['scheduled_date'],
                    'period_name' => $schedule['period_name'],
                    'payment_method' => $paymentMethod, // Now stores payment schedule: full, quarterly, monthly
                    'status' => 'pending',
                    'confirmation_status' => 'pending',
                    'processed_by' => null,
                    'notes' => $notes,
                ]));
                throw $e;
            }
        }
    }

    public function subjects()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }

        // Determine current grading period/semester based on date
        $currentMonth = now()->month;
        $currentGradingPeriod = $this->getCurrentGradingPeriod($currentMonth);
        $currentSemester = $this->getCurrentSemester($currentMonth);
        
        // Get subjects based on student's grade level
        $query = Subject::where('grade_level', $student->grade_level)
            ->where('academic_year', $student->academic_year ?? self::getCurrentAcademicYear())
            ->where('is_active', true);
            
        // Add strand and track filters for senior high school
        if (in_array($student->grade_level, ['Grade 11', 'Grade 12'])) {
            $query->where(function($q) use ($student) {
                $q->whereNull('strand') // Core subjects for all strands
                  ->orWhere('strand', $student->strand);
            });
            
            if ($student->track) {
                $query->where(function($q) use ($student) {
                    $q->whereNull('track') // Subjects for all tracks in the strand
                      ->orWhere('track', $student->track);
                });
            }
        }
        
        $allSubjects = $query->orderBy('strand', 'asc')
            ->orderBy('subject_name', 'asc')
            ->get();
            
        // Organize subjects by current period
        $currentSubjects = collect();
        $otherSubjects = collect();
        
        foreach ($allSubjects as $subject) {
            if (in_array($student->grade_level, ['Grade 11', 'Grade 12'])) {
                // Senior High School - organize by semester
                if ($subject->semester === $currentSemester) {
                    $currentSubjects->push($subject);
                } else {
                    $otherSubjects->push($subject);
                }
            } else {
                // Elementary/Junior High - all subjects are current (quarterly system)
                $currentSubjects->push($subject);
            }
        }
        
        return view('student.subjects', compact(
            'student', 
            'currentSubjects', 
            'otherSubjects', 
            'currentGradingPeriod', 
            'currentSemester'
        ));
    }
    
    private function getCurrentGradingPeriod($month)
    {
        if ($month >= 6 && $month <= 8) {
            return '1st Quarter';
        } elseif ($month >= 9 && $month <= 11) {
            return '2nd Quarter';
        } elseif ($month >= 12 || $month <= 2) {
            return '3rd Quarter';
        } else {
            return '4th Quarter';
        }
    }
    
    private function getCurrentSemester($month)
    {
        if ($month >= 6 && $month <= 11) {
            return 'First Semester';
        } else {
            return 'Second Semester';
        }
    }

    public function payments()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }
        
        // Refresh payment totals to ensure they're up-to-date
        $this->refreshStudentPaymentTotals($student);
        
        // Reload the student to get updated values
        $student->refresh();
        
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
        $academicYear = $student->academic_year ?? (date('Y') . '-' . (date('Y') + 1));
        $feeCalculation = Fee::calculateTotalFeesForGrade($student->grade_level, $academicYear);
        $totalFeesAmount = $feeCalculation['total_amount'];
        
        // Calculate total paid (confirmed payments) - use amount_received if available
        $totalPaid = $paymentHistory->sum(function($payment) {
            return $payment->amount_received ?? $payment->amount;
        });
        
        // Calculate balance due
        $balanceDue = $totalFeesAmount - $totalPaid;
        
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

        // Ensure face_encoding is always stored as an array
        $faceEncoding = $request->input('face_encoding');
        if (is_string($faceEncoding)) {
            $decoded = json_decode($faceEncoding, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $faceEncoding = $decoded;
            }
        }
        $faceRegistration = \App\Models\FaceRegistration::create([
            'student_id' => $studentId,
            'face_encoding' => $faceEncoding,
            'confidence_score' => $request->input('confidence_score'),
            'face_landmarks' => $request->input('face_landmarks'),
            'face_image_data' => $faceImageData,
            'face_image_mime_type' => $request->input('face_image_mime_type'),
            'source' => $request->input('source', 'camera_capture'),
            'registered_at' => now(),
            'is_active' => true
        ]);

        // Return the face image data URL for immediate UI update
        $faceImageDataUrl = null;
        if ($faceRegistration->face_image_data && $faceRegistration->face_image_mime_type) {
            $faceImageDataUrl = 'data:' . $faceRegistration->face_image_mime_type . ';base64,' . $faceRegistration->face_image_data;
        }

        return response()->json([
            'success' => true,
            'message' => 'Face registered successfully!',
            'face_image_data_url' => $faceImageDataUrl,
            'registration_date' => $faceRegistration->registered_at->format('M d, Y'),
            'source' => ucfirst(str_replace('_', ' ', $faceRegistration->source))
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

    /**
     * Refresh student payment totals from confirmed payments
     */
    private function refreshStudentPaymentTotals($student)
    {
        // Calculate total paid amount from all confirmed payments
        $totalPaid = Payment::where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $student->id)
            ->where('confirmation_status', 'confirmed')
            ->get()
            ->sum(function($payment) {
                return $payment->amount_received ?? $payment->amount;
            });
        
        // Calculate total fees due if not set
        if (!$student->total_fees_due) {
            $academicYear = $student->academic_year ?? (date('Y') . '-' . (date('Y') + 1));
            $feeCalculation = Fee::calculateTotalFeesForGrade($student->grade_level, $academicYear);
            $totalFeesDue = $feeCalculation['total_amount'];
        } else {
            $totalFeesDue = $student->total_fees_due;
        }
        
        // Check if payment is complete
        $isFullyPaid = $totalPaid >= $totalFeesDue;
        
        // Update student record
        $student->update([
            'total_paid' => $totalPaid,
            'total_fees_due' => $totalFeesDue,
            'is_paid' => $isFullyPaid,
            'payment_completed_at' => $isFullyPaid && !$student->payment_completed_at ? now() : $student->payment_completed_at
        ]);
        
        Log::info("Student payment totals refreshed - ID: {$student->id}, Total Paid: {$totalPaid}, Total Due: {$totalFeesDue}, Fully Paid: " . ($isFullyPaid ? 'Yes' : 'No'));
    }

    public function submitViolationReply(Request $request, Violation $violation)
    {
        $request->validate([
            'incident' => 'required|string',
            'feeling' => 'required|string',
            'action_plan' => 'required|string',
        ]);

        $violation->student_statement = $request->incident;
        $violation->incident_feelings = $request->feeling;
        $violation->action_plan = $request->action_plan;
        $violation->save();

        return redirect()->route('student.violations')->with('info', 'Your reply has been submitted successfully.');
    }

    public function uploadViolationAttachment(Request $request, Violation $violation)
    {
        $student = Auth::guard('student')->user();

        if (!$student || $violation->student_id != $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        // Check if violation has approved disciplinary action
        if (!$violation->disciplinary_action) {
            return response()->json([
                'success' => false,
                'message' => 'Attachment can only be uploaded for approved disciplinary actions.'
            ], 400);
        }

        $request->validate([
            'attachment' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB max
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $student->student_id . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('violation_attachments', $fileName, 'public');

            $violation->update([
                'student_attachment_path' => $filePath,
                'student_attachment_description' => $request->description,
                'student_attachment_uploaded_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attachment uploaded successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading violation attachment', [
                'violation_id' => $violation->id,
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error uploading attachment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadViolationAttachment(Violation $violation)
    {
        $student = Auth::guard('student')->user();

        if (!$student || $violation->student_id != $student->id) {
            abort(403, 'Unauthorized access.');
        }

        if (!$violation->student_attachment_path || !Storage::disk('public')->exists($violation->student_attachment_path)) {
            abort(404, 'Attachment not found.');
        }

        return Storage::disk('public')->download($violation->student_attachment_path);
    }

    public function markAlertViewed(Request $request)
    {
        $alertType = $request->input('alert_type');
        
        if ($alertType === 'payments') {
            session(['payments_alert_viewed' => true]);
        } elseif ($alertType === 'grades') {
            session(['grades_alert_viewed' => true]);
        } elseif ($alertType === 'violations') {
            session(['violations_alert_viewed' => true]);
        }
        
        return response()->json(['success' => true]);
    }

    public function getAlertCounts()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        try {
            // Get due payments count (due within 7 days)
            $duePaymentsCount = Payment::getDuePaymentsCountForStudent($student->id);
            
            // Get new grades count (updated within 24 hours)
            $newGradesCount = Grade::getNewGradesCountForStudent($student->id);
            
            // Get new violations count (created within 24 hours)
            $newViolationsCount = Violation::getNewViolationsCountForStudent($student->id);
            
            return response()->json([
                'success' => true,
                'counts' => [
                    'payments' => $duePaymentsCount,
                    'grades' => $newGradesCount,
                    'violations' => $newViolationsCount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching alert counts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching alerts',
                'counts' => [
                    'payments' => 0,
                    'grades' => 0,
                    'violations' => 0
                ]
            ], 500);
        }
    }
}