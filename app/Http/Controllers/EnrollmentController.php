<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Mail\StudentWelcomeMail;
use Illuminate\Support\Facades\Mail;


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
            'id_photo'           => 'required|image|mimes:jpeg,png|max:2048',
            'documents'          => 'required|array',
            'documents.*'        => 'file|mimes:pdf,docx,jpeg,png|max:4096',
            'first_name'         => 'required|string|max:50',
            'middle_name'        => 'nullable|string|max:50',
            'last_name'          => 'required|string|max:50',
            'dob'                => 'required|date|before_or_equal:today',
            'religion'           => 'nullable|string|max:100',
            'email'              => 'required|email|unique:students,email',
            'address'            => 'required|string|max:255',
            // only allow these grade values:
            'grade_applied'      => [
                'required',
                'in:Nursery,Kinder 1,Kinder 2,Grade 1,Grade 2,Grade 3,Grade 4,Grade 5,Grade 6,Grade 7,Grade 8,Grade 9,Grade 10,Grade 11,Grade 12'
            ],
            // strand is required only if grade is 11 or 12
            'strand'             => [
                'nullable',
                'required_if:grade_applied,Grade 11,Grade 12',
                'string',
                'max:50',
            ],
            'guardian_name'      => 'required|string|max:100',
            'guardian_contact'   => [
                'required',
                'regex:/^09[0-9]{9}$/',
            ],
            'last_school_type'   => 'nullable|in:Public,Private',
            'last_school_name'   => 'nullable|string|max:100',
            'medical_history'    => 'nullable|string|max:255',
            'payment_mode'       => 'required|string|in:Cash',
            'preferred_schedule' => 'nullable|date|after_or_equal:today',
        ]);

        // 2) Store the ID photo
        $data['id_photo'] = $request->file('id_photo')->store('id_photos','public');
        $docs = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $doc) {
                $docs[] = $doc->store('documents','public');
            }
        }

        // 3) Store the multiple documents
        $paths = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $doc) {
                $paths[] = $doc->store('documents', 'public');
                
            }
        }
        $rawPassword = Str::random(10);
        Hash::make($rawPassword);
        $data['password'] = $rawPassword;
        
        $data['documents'] = json_encode($paths);

        // 4) Persist to the database
        $student = Student::create($data);
        
        Mail::to($student->email)->send(new StudentWelcomeMail($student, $rawPassword));

        // 5) Redirect back with a success message
        return redirect()
            ->route('enroll.create')
            ->with('success', 'Enrollment successful!');
    }}
