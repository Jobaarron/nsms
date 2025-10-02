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
            // File Uploads
            'id_photo'           => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'documents'          => 'required|array|min:1',
            'documents.*'        => 'file|mimes:pdf,docx,jpeg,png,jpg|max:4096',

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
            'payment_mode'       => 'required|in:cash,online payment,installment',
            'preferred_schedule' => 'nullable|date|after_or_equal:today',
        ];
    }
}
