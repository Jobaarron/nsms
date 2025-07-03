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
            'id_photo'           => 'required|image|mimes:jpeg,png|max:2048',
            'documents'          => 'required|array',
            'documents.*'        => 'file|mimes:pdf,docx,jpeg,png|max:4096',
            'first_name'         => 'required|string|max:50',
            'middle_name'        => 'nullable|string|max:50',
            'last_name'          => 'required|string|max:50',
            'dob'                => 'required|date',
            'religion'           => 'nullable|string|max:100',
            'email'              => 'required|email|unique:students,email',
            'address'            => 'required|string',
            'grade_applied'      => 'required|string',
            'strand'             => 'nullable|string',
            'guardian_name'      => 'required|string',
            'guardian_contact'   => 'required|string',
            'last_school_type'   => 'nullable|in:Public,Private',
            'last_school_name'   => 'nullable|string',
            'medical_history'    => 'nullable|string',
            'payment_mode'       => 'required|string',
            'preferred_schedule' => 'nullable|date',
        ];
    }
}
