<?php

namespace App\Http\Controllers;

use App\Models\Enrollee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EnrolleeController extends Controller
{
    /**
     * Show the enrollee login form
     */
    public function showLoginForm()
    {
        return view('enrollee.login');
    }

    /**
     * Handle enrollee login
     */
    public function login(Request $request)
    {
        $request->validate([
            'application_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('application_id', 'password');

        if (Auth::guard('enrollee')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('enrollee.dashboard'));
        }

        return back()->withErrors([
            'application_id' => 'The provided credentials do not match our records.',
        ])->onlyInput('application_id');
    }

    /**
     * Show the enrollee dashboard
     */
    public function index()
    {
        $enrollee = Auth::guard('enrollee')->user();
        return view('enrollee.index', compact('enrollee'));
    }

    /**
     * Show the enrollee application details
     */
    public function application()
    {
        $enrollee = Auth::guard('enrollee')->user();
        return view('enrollee.application', compact('enrollee'));
    }

    /**
     * Show the enrollee documents
     */
    public function documents()
    {
        $enrollee = Auth::guard('enrollee')->user();
        return view('enrollee.documents', compact('enrollee'));
    }

    /**
     * Upload a document
     */
    public function uploadDocument(Request $request)
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        // Only allow uploads for pending applications
        if ($enrollee->enrollment_status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documents can only be uploaded while your application is pending.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Documents can only be uploaded while your application is pending.');
        }

        try {
            $request->validate([
                'document_type' => 'required|string',
                'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:5120', // 5MB max
                'other_document_type' => 'required_if:document_type,other|string|max:255',
                'document_notes' => 'nullable|string|max:500'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            $file = $request->file('document_file');
            $documentType = $request->document_type === 'other' ? $request->other_document_type : $request->document_type;
            
            // Store file in public storage for easy access
            $path = $file->store('documents', 'public');
            
            // Get existing documents or initialize empty array
            $documents = is_array($enrollee->documents) ? $enrollee->documents : [];
            
            // Add new document
            $documents[] = [
                'type' => $documentType,
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_at' => now()->toISOString(),
                'status' => 'pending',
                'notes' => $request->document_notes
            ];
            
            // Update enrollee documents
            $enrollee->update(['documents' => $documents]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document uploaded successfully!',
                    'document' => end($documents)
                ]);
            }
            
            return redirect()->back()->with('success', 'Document uploaded successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Document upload error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload document. Please try again.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to upload document. Please try again.');
        }
    }

    /**
     * Show the enrollee payment information
     */
    public function payment()
    {
        $enrollee = Auth::guard('enrollee')->user();
        return view('enrollee.payment', compact('enrollee'));
    }

    /**
     * Process payment information
     */
    public function processPayment(Request $request)
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        // Only allow payment for approved applications
        if ($enrollee->enrollment_status !== 'approved' || $enrollee->is_paid) {
            return redirect()->back()->with('error', 'Payment is not available for your current status.');
        }

        $request->validate([
            'payment_method' => 'required|string|in:gcash,paymaya,bank_transfer,over_counter',
            'amount' => 'required|numeric|min:0',
            'payment_reference' => 'required|string|max:255'
        ]);

        try {
            $enrollee->update([
                'payment_mode' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'payment_date' => now(),
                // Note: is_paid should be updated by admin after verification
            ]);
            
            return redirect()->back()->with('success', 'Payment information submitted successfully! Please wait for verification.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process payment information. Please try again.');
        }
    }

    /**
     * Show the enrollee schedule
     */
    public function schedule()
    {
        $enrollee = Auth::guard('enrollee')->user();
        return view('enrollee.schedule', compact('enrollee'));
    }

    /**
     * Update preferred schedule
     */
    public function updateSchedule(Request $request)
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        // Only allow schedule changes for pending applications
        if ($enrollee->enrollment_status !== 'pending') {
            return redirect()->back()->with('error', 'Schedule can only be changed while your application is pending.');
        }

        $request->validate([
            'preferred_schedule' => 'required|date|after:today',
            'reason' => 'required|string|max:500'
        ]);

        try {
            $enrollee->update([
                'preferred_schedule' => $request->preferred_schedule,
                'admin_notes' => ($enrollee->admin_notes ?? '') . "\n\nSchedule change requested on " . now()->format('Y-m-d H:i:s') . ": " . $request->reason
            ]);
            
            return redirect()->back()->with('success', 'Schedule change request submitted successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update schedule. Please try again.');
        }
    }

    /**
     * Show the enrollee profile
     */
    public function profile()
    {
        $enrollee = Auth::guard('enrollee')->user();
        return view('enrollee.profile', compact('enrollee'));
    }

    /**
     * Update enrollee profile
     */
    public function updateProfile(Request $request)
    {
        $enrollee = Auth::guard('enrollee')->user();
        
        // Only allow profile updates for pending applications
        if ($enrollee->enrollment_status !== 'pending') {
            return redirect()->back()->with('error', 'Profile can only be updated while your application is pending.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'religion' => 'nullable|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'guardian_name' => 'required|string|max:255',
            'guardian_contact' => 'required|string|max:20',
            'medical_history' => 'nullable|string'
        ]);

        try {
            $enrollee->update($request->only([
                'first_name', 'middle_name', 'last_name', 'contact_number', 'religion',
                'address', 'city', 'province', 'zip_code', 'guardian_name', 
                'guardian_contact', 'medical_history'
            ]));
            
            return redirect()->back()->with('success', 'Profile updated successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update profile. Please try again.');
        }
    }

    /**
     * Logout enrollee
     */
    public function logout(Request $request)
    {
        Auth::guard('enrollee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('enrollee.login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Delete a document
     */
    public function deleteDocument(Request $request)
    {
        try {
            $enrollee = Auth::guard('enrollee')->user();
            
            // Only allow deletion for pending applications
            if ($enrollee->enrollment_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Documents can only be deleted for pending applications.'
                ], 403);
            }

            $documentIndex = $request->input('document_index');
            
            if (!is_numeric($documentIndex)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document index.'
                ], 400);
            }

            $documents = is_array($enrollee->documents) ? $enrollee->documents : [];
            
            if (!isset($documents[$documentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.'
                ], 404);
            }

            // Get the document path to delete from storage
            $documentPath = $documents[$documentIndex];
            if (is_string($documentPath)) {
                $fullPath = storage_path('app/public/' . $documentPath);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            // Remove document from array
            unset($documents[$documentIndex]);
            
            // Re-index the array to maintain proper indexing
            $documents = array_values($documents);
            
            // Update enrollee record
            $enrollee->documents = $documents;
            $enrollee->save();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Document deletion error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the document.'
            ], 500);
        }
    }
}
