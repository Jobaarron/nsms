<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Mail\StudentWelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

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
    public function store(Request $request): RedirectResponse
    {
        // 1) Validate all fields (including files)
        $data = $request->validate([
            'id_photo'           => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'documents'          => 'required|array|min:1',
            'documents.*'        => 'file|mimes:pdf,docx,jpeg,png,jpg|max:4096',
            
            // Student Info
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
            'email'              => 'required|email|unique:students,email',
            'contact_number'     => 'nullable|regex:/^09[0-9]{9}$/',
            
            // Address
            'address'            => 'required|string|max:255',
            'city'               => 'nullable|string|max:100',
            'province'           => 'nullable|string|max:100',
            'zip_code'           => 'nullable|string|max:10',
            
            // Academic Info
            'grade_level'        => 'required|in:Nursery,Kinder 1,Kinder 2,Grade 1,Grade 2,Grade 3,Grade 4,Grade 5,Grade 6,Grade 7,Grade 8,Grade 9,Grade 10,Grade 11,Grade 12',
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
            'payment_mode'       => 'required|in:cash,online payment',
            'preferred_schedule' => 'nullable|date|after_or_equal:today',
        ]);

        // 2) Store the ID photo as base64 data
        if ($request->hasFile('id_photo')) {
            $photo = $request->file('id_photo');
            $data['id_photo'] = base64_encode(file_get_contents($photo->getRealPath()));
            $data['id_photo_mime_type'] = $photo->getMimeType();
        }

        // 3) Store the multiple documents
        $documentPaths = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $doc) {
                $documentPaths[] = $doc->store('documents', 'public');
            }
        }
        $data['documents'] = json_encode($documentPaths);

        // 4) Generate password and hash it properly - MOVED BEFORE Student::create()
        $rawPassword = Str::random(12);
        $data['password'] = Hash::make($rawPassword);

        // 5) Persist to the database
        $student = Student::create($data);

        // 6) ASSIGN STUDENT ROLE
        try {
            $studentRole = Role::firstOrCreate([
                'name' => 'student',
                'guard_name' => 'web'
            ]);

            $student->assignRole('student');
            
        } catch (\Exception $e) {
            Log::error('Error assigning student role: ' . $e->getMessage());
        }

        // 7) Send welcome email (with error handling)
        try {
            Mail::to($student->email)->send(new StudentWelcomeMail($student, $rawPassword));
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
        }

        // 8) Redirect back with success message
        return redirect()
            ->route('enroll.create')
            ->with('success', 'Enrollment successful! Email sent to ' . $student->email);
    }
}
