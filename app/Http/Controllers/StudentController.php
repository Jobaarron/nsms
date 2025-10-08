<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        
        // Calculate total fees for the student
        $feeCalculation = Fee::calculateTotalFeesForGrade($student->grade_level);
        $totalAmount = $feeCalculation['total_amount'];
        
        // Check payment schedule status
        $paymentScheduleStatus = Payment::where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $student->id)
            ->first();
            
        // Get current subjects for the student (same logic as subjects view)
        $currentSubjects = Subject::where('grade_level', $student->grade_level)
            ->where('academic_year', $student->academic_year ?? '2024-2025')
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
            'payment_mode' => 'nullable|in:full,quarterly,monthly',
            'payment_notes' => 'nullable|string|max:1000'
        ]);

        // Set default payment mode if not provided
        $paymentMode = $request->payment_mode ?? 'full';
        
        // Set default payment method (will be determined by cashier)
        $paymentMethod = 'cash';

        try {
            Log::info('Starting enrollment submission for student: ' . $student->id);
            
            // Get enrollee data to fetch preferred_schedule
            $preferredScheduleDate = now()->addDays(7); // Default date
            
            try {
                $enrollee = $student->enrollee;
                if ($enrollee && $enrollee->preferred_schedule) {
                    $preferredScheduleDate = $enrollee->preferred_schedule;
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch enrollee data: ' . $e->getMessage());
                // Continue with default date
            }
            
            Log::info('Preferred schedule date: ' . $preferredScheduleDate);

            // Calculate total fees
            $feeCalculation = Fee::calculateTotalFeesForGrade($student->grade_level);
            $totalAmount = $feeCalculation['total_amount'];
            
            Log::info('Total amount calculated: ' . $totalAmount);

            // Create payment schedules based on payment mode
            Log::info('Creating payment schedules with mode: ' . $paymentMode);
            $this->createPaymentSchedules($student, $paymentMode, $totalAmount, $preferredScheduleDate, $paymentMethod, $request->payment_notes);
            
            Log::info('Payment schedules created successfully');

            // Update student with enrollment information
            Log::info('Updating student enrollment status');
            $student->update([
                'payment_mode' => $paymentMode,
                'total_fees_due' => $totalAmount,
                'enrollment_status' => 'payment_pending' // Changed from 'enrolled' to 'payment_pending'
            ]);
            
            Log::info('Student updated successfully');

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
                    'redirect_url' => '/student/dashboard'
                ]);
            }
            
            Log::info('Returning redirect response');
            return redirect()->route('student.dashboard')
                ->with('success', 'Payment schedule submitted successfully! Your schedule is now pending approval from the cashier\'s office.');
        } catch (\Exception $e) {
            Log::error('Enrollment submission failed: ' . $e->getMessage());
            
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
            try {
                Payment::create([
                    'transaction_id' => 'TXN-' . $student->student_id . '-' . time() . '-' . rand(100, 999),
                    'payable_type' => 'App\\Models\\Student',
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
            } catch (\Exception $e) {
                Log::error('Payment creation failed: ' . $e->getMessage());
                Log::error('Payment data: ' . json_encode([
                    'transaction_id' => 'TXN-' . $student->student_id . '-' . time() . '-' . rand(100, 999),
                    'payable_type' => 'App\\Models\\Student',
                    'payable_id' => $student->id,
                    'amount' => $schedule['amount'],
                    'scheduled_date' => $schedule['scheduled_date'],
                    'period_name' => $schedule['period_name'],
                    'payment_mode' => $paymentMode,
                    'payment_method' => $paymentMethod,
                    'status' => 'pending',
                    'confirmation_status' => 'pending',
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
            ->where('academic_year', $student->academic_year ?? '2024-2025')
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
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }
        
        return view('student.face-registration', compact('student'));
    }

    public function saveFaceRegistration(Request $request)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'face_images' => 'required|array|min:1',
            'face_images.*' => 'required|string',
            'source' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Deactivate existing face registrations
            $student->faceRegistrations()->update(['is_active' => false]);

            // Save the first (best) image as the active registration
            $faceImage = $request->face_images[0];
            
            FaceRegistration::create([
                'student_id' => $student->id,
                'face_image_data' => $faceImage,
                'face_image_mime_type' => 'image/jpeg',
                'source' => $request->source,
                'registered_at' => now(),
                'is_active' => true
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Face registration saved successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Face registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save face registration'
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