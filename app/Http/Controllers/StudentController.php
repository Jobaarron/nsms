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

        $request->validate([
            'payment_mode' => 'required|in:full,quarterly,monthly'
        ]);

        try {
            // Calculate total fees
            $feeCalculation = Fee::calculateTotalFeesForGrade($student->grade_level);
            $totalAmount = $feeCalculation['total_amount'];

            // Update student with enrollment information
            $student->update([
                'payment_mode' => $request->payment_mode,
                'total_fees_due' => $totalAmount,
                'enrollment_status' => 'enrolled'
            ]);

            return redirect()->route('student.dashboard')
                ->with('success', 'Enrollment completed successfully! You can now proceed to payment.');
        } catch (\Exception $e) {
            Log::error('Enrollment submission failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to complete enrollment. Please try again.']);
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
        
        return view('student.payments', compact('student'));
    }

    public function updatePaymentMode(Request $request)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
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