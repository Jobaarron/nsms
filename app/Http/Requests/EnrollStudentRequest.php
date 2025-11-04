<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // File Uploads (optional since handled by AJAX)
            'id_photo'           => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB limit
            'documents'          => 'nullable|array',
            'documents.*'        => 'file|mimes:pdf,jpeg,png,jpg|max:8192', // 8MB limit

            // Enrollee Info
            'lrn'                => 'nullable|string|max:12|unique:enrollees,lrn',
            'student_type'       => 'required|in:new,transferee,old',

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
            'contact_number'     => ['nullable', 'regex:/^(09|\+639)\d{9}$/'],

            // Address
            'address'            => 'required|string|max:255',
            'city'               => 'nullable|string|max:100',
            'province'           => 'nullable|string|max:100',
            'zip_code'           => 'nullable|string|max:10',

            // Academic Info Applied For (using form field names)
            'grade_level'        => 'required|string',
            'strand'             => [
                'nullable',
                'required_if:grade_level,Grade 11,Grade 12',
                'string',
                'max:50',
            ],
            'track_applied'      => [
                'nullable',
                'required_if:strand,TVL',
                'in:ICT,HE',
                'string',
            ],

            // Parent/Guardian Info
            'father_name'        => 'nullable|string|max:100',
            'father_occupation'  => 'nullable|string|max:100',
            'father_contact'     => ['nullable', 'regex:/^(09|\+639)\d{9}$/'],
            'mother_name'        => 'nullable|string|max:100',
            'mother_occupation'  => 'nullable|string|max:100',
            'mother_contact'     => ['nullable', 'regex:/^(09|\+639)\d{9}$/'],
            'guardian_name'      => 'required|string|max:100',
            'guardian_contact'   => ['required', 'regex:/^(09|\+639)\d{9}$/'],

            // Previous School Info
            'last_school_type'   => 'nullable|in:public,private',
            'last_school_name'   => 'nullable|string|max:100',

            // Medical & Payment
            'medical_history'    => 'nullable|string|max:1000',
            'payment_mode'       => 'nullable|in:cash,online payment,installment', // Made optional - will be handled in student portal
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if files are uploaded via AJAX (stored in session)
            $tempFiles = session('temp_enrollment_files', []);
            
            $hasIdPhoto = false;
            $hasDocuments = false;
            
            foreach ($tempFiles as $fileData) {
                if ($fileData['type'] === 'id_photo') {
                    $hasIdPhoto = true;
                } elseif ($fileData['type'] === 'documents') {
                    $hasDocuments = true;
                }
            }
            
            // Validate that required files are uploaded
            if (!$hasIdPhoto && !$this->hasFile('id_photo')) {
                $validator->errors()->add('id_photo', 'ID Photo is required.');
            }
            
            if (!$hasDocuments && (!$this->hasFile('documents') || empty($this->file('documents')))) {
                $validator->errors()->add('documents', 'At least one document is required.');
            }
        });
    }
}
