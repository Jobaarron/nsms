<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Student;
use App\Models\Enrollee;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\DB;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form
     */
    public function showForm()
    {
        return view('forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'user_type' => 'required|in:system_user,student,enrollee'
        ]);

        $identifier = $request->input('identifier');
        $userType = $request->input('user_type');

        try {
            switch ($userType) {
                case 'system_user':
                    return $this->handleSystemUserReset($identifier);
                case 'student':
                    return $this->handleStudentReset($identifier);
                case 'enrollee':
                    return $this->handleEnrolleeReset($identifier);
                default:
                    return back()->withErrors(['identifier' => 'Invalid user type.']);
            }
        } catch (\Exception $e) {
            \Log::error('Forgot password error: ' . $e->getMessage());
            return back()->withErrors(['identifier' => 'An error occurred. Please try again.']);
        }
    }

    /**
     * Handle system user (Admin, Teacher, etc.) password reset
     */
    private function handleSystemUserReset($identifier)
    {
        $user = User::where('email', $identifier)
            ->orWhere('name', 'like', '%' . $identifier . '%')
            ->first();

        if (!$user) {
            return back()->withErrors(['identifier' => 'No account found with that email or name.']);
        }

        // Generate reset token and code
        $resetCode = strtoupper(Str::random(6));
        $token = $resetCode . '.' . Str::random(64);

        // Store reset token in database with user type and ID
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'user_type' => 'system_user',
                'user_id' => $user->id,
                'created_at' => now()
            ]
        );

        // Send email with reset link
        try {
            Mail::to($user->email)->send(new ForgotPasswordMail(
                $user,
                $token,
                $resetCode,
                'system_user'
            ));

            return back()->with('success', 'Password reset link has been sent to your email. Check your inbox and spam folder.');
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
            return back()->withErrors(['identifier' => 'Failed to send email. Please try again later.']);
        }
    }

    /**
     * Handle student password reset
     */
    private function handleStudentReset($identifier)
    {
        $student = Student::where('student_id', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (!$student) {
            return back()->withErrors(['identifier' => 'No student account found with that ID or email.']);
        }

        // Generate reset token and code
        $resetCode = strtoupper(Str::random(6));
        $token = $resetCode . '.' . Str::random(64);
        $emailKey = $student->email ?? $student->student_id;

        // Store reset token in database with user type and ID
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $emailKey],
            [
                'token' => Hash::make($token),
                'user_type' => 'student',
                'user_id' => $student->id,
                'created_at' => now()
            ]
        );

        // Send email if available
        if ($student->email) {
            try {
                Mail::to($student->email)->send(new ForgotPasswordMail(
                    $student,
                    $token,
                    $resetCode,
                    'student'
                ));
                return back()->with('success', 'Password reset link has been sent to your email. Check your inbox and spam folder.');
            } catch (\Exception $e) {
                \Log::error('Email sending failed: ' . $e->getMessage());
                return back()->with('info', 'Reset code: ' . $resetCode . '. Use this code to reset your password.');
            }
        }

        return back()->with('info', 'Reset code: ' . $resetCode . '. Use this code to reset your password.');
    }

    /**
     * Handle enrollee password reset
     */
    private function handleEnrolleeReset($identifier)
    {
        $enrollee = Enrollee::where('application_id', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (!$enrollee) {
            return back()->withErrors(['identifier' => 'No enrollee account found with that ID or email.']);
        }

        // Generate reset token and code
        $resetCode = strtoupper(Str::random(6));
        $token = $resetCode . '.' . Str::random(64);
        $emailKey = $enrollee->email ?? $enrollee->application_id;

        // Store reset token in database with user type and ID
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $emailKey],
            [
                'token' => Hash::make($token),
                'user_type' => 'enrollee',
                'user_id' => $enrollee->id,
                'created_at' => now()
            ]
        );

        // Send email if available
        if ($enrollee->email) {
            try {
                Mail::to($enrollee->email)->send(new ForgotPasswordMail(
                    $enrollee,
                    $token,
                    $resetCode,
                    'enrollee'
                ));
                return back()->with('success', 'Password reset link has been sent to your email. Check your inbox and spam folder.');
            } catch (\Exception $e) {
                \Log::error('Email sending failed: ' . $e->getMessage());
                return back()->with('info', 'Reset code: ' . $resetCode . '. Use this code to reset your password.');
            }
        }

        return back()->with('info', 'Reset code: ' . $resetCode . '. Use this code to reset your password.');
    }

    /**
     * Show reset password form
     */
    public function showResetForm($token)
    {
        return view('reset-password', ['token' => $token]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'reset_code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $resetCode = strtoupper($request->input('reset_code'));
        $token = $request->input('token');
        $newPassword = Hash::make($request->input('password'));

        // Find the most recent reset record (only latest code is valid)
        $resetRecord = DB::table('password_reset_tokens')
            ->where('created_at', '>=', now()->subHours(1))
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['reset_code' => 'Invalid or expired reset code. Please request a new password reset.']);
        }

        // Verify the reset code by checking if it matches when hashed
        // The token stored is: Hash(resetCode . '.' . randomString)
        $fullToken = $resetCode . '.' . substr($token, strlen($resetCode) + 1);
        
        if (!Hash::check($fullToken, $resetRecord->token)) {
            return back()->withErrors(['reset_code' => 'Invalid reset code. Please check the code from your email.']);
        }

        try {
            // Get user type and ID from database
            $userType = $resetRecord->user_type;
            $userId = $resetRecord->user_id;

            // Update password based on user type stored in database
            switch ($userType) {
                case 'system_user':
                    User::where('id', $userId)->update(['password' => $newPassword]);
                    break;
                case 'student':
                    Student::where('id', $userId)->update(['password' => $newPassword]);
                    break;
                case 'enrollee':
                    Enrollee::where('id', $userId)->update(['password' => $newPassword]);
                    break;
                default:
                    return back()->withErrors(['reset_code' => 'Invalid user type.']);
            }

            // Delete ALL reset tokens for this email (invalidate previous codes)
            DB::table('password_reset_tokens')->where('email', $resetRecord->email)->delete();

            return redirect('/')->with('success', 'Password has been reset successfully. You can now log in with your new password.');
        } catch (\Exception $e) {
            \Log::error('Password reset error: ' . $e->getMessage());
            return back()->withErrors(['password' => 'An error occurred while resetting your password.']);
        }
    }
}
