<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request - Nicolites Portal</title>
    <style>
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .mobile-padding { padding: 20px !important; }
            .mobile-font { font-size: 16px !important; }
            .mobile-title { font-size: 24px !important; }
            .credential-table { width: 100% !important; }
            .credential-label { display: block !important; width: 100% !important; margin-bottom: 5px !important; }
            .credential-value { display: block !important; width: 100% !important; margin-bottom: 15px !important; }
        }
    </style>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #e8f5e8; margin: 0; padding: 20px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" class="container" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; max-width: 600px;">
        
        <!-- Header -->
        <tr>
            <td style="background-color: #014421; color: white; padding: 40px 30px; text-align: center;" class="mobile-padding">
                <h1 style="font-size: 32px; font-weight: 700; margin: 0; color: white;" class="mobile-title">
                    🔐 Password Reset Request
                </h1>
                <p style="font-size: 18px; margin: 10px 0 0 0; color: #d0d8c3;" class="mobile-font">
                    Nicolites Portal
                </p>
            </td>
        </tr>

        <!-- Main Content -->
        <tr>
            <td style="padding: 40px 30px;" class="mobile-padding">
                
                <!-- Welcome Message -->
                <p style="font-size: 18px; color: #012d17; margin-bottom: 30px; line-height: 1.6;" class="mobile-font">
                    Hello,<br>
                    We received a request to reset the password for your account at <strong>Nicolites Montessori School</strong>.
                </p>

                <!-- Account Information -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #e8f5e8; border: 1px solid #d0d8c3; border-radius: 8px; border-left: 4px solid #2d6a3e; margin: 25px 0;">
                    <tr>
                        <td style="padding: 25px;">
                            <div style="font-size: 20px; font-weight: 600; color: #014421; margin-bottom: 15px;">
                                📋 Account Information
                            </div>
                            
                            @if($userType === 'system_user')
                                <!-- System User Info -->
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="credential-table" style="margin-bottom: 12px;">
                                    <tr>
                                        <td style="font-weight: 600; color: #014421; width: 80px; vertical-align: top; padding: 8px 15px 8px 0;" class="credential-label">Email:</td>
                                        <td style="color: #012d17; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #d0d8c3;" class="credential-value">
                                            {{ $user->email }}
                                        </td>
                                    </tr>
                                </table>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="credential-table">
                                    <tr>
                                        <td style="font-weight: 600; color: #014421; width: 80px; vertical-align: top; padding: 8px 15px 8px 0;" class="credential-label">Name:</td>
                                        <td style="color: #012d17; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #d0d8c3;" class="credential-value">
                                            {{ $user->name }}
                                        </td>
                                    </tr>
                                </table>
                            @elseif($userType === 'student')
                                <!-- Student Info -->
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="credential-table" style="margin-bottom: 12px;">
                                    <tr>
                                        <td style="font-weight: 600; color: #014421; width: 100px; vertical-align: top; padding: 8px 15px 8px 0;" class="credential-label">Student ID:</td>
                                        <td style="color: #012d17; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #d0d8c3;" class="credential-value">
                                            {{ $user->student_id }}
                                        </td>
                                    </tr>
                                </table>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="credential-table">
                                    <tr>
                                        <td style="font-weight: 600; color: #014421; width: 100px; vertical-align: top; padding: 8px 15px 8px 0;" class="credential-label">Name:</td>
                                        <td style="color: #012d17; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #d0d8c3;" class="credential-value">
                                            {{ $user->first_name ?? 'N/A' }} {{ $user->last_name ?? 'N/A' }}
                                        </td>
                                    </tr>
                                </table>
                            @elseif($userType === 'enrollee')
                                <!-- Enrollee Info -->
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="credential-table" style="margin-bottom: 12px;">
                                    <tr>
                                        <td style="font-weight: 600; color: #014421; width: 120px; vertical-align: top; padding: 8px 15px 8px 0;" class="credential-label">Application ID:</td>
                                        <td style="color: #012d17; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #d0d8c3;" class="credential-value">
                                            {{ $user->application_id }}
                                        </td>
                                    </tr>
                                </table>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="credential-table">
                                    <tr>
                                        <td style="font-weight: 600; color: #014421; width: 120px; vertical-align: top; padding: 8px 15px 8px 0;" class="credential-label">Name:</td>
                                        <td style="color: #012d17; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #d0d8c3;" class="credential-value">
                                            {{ $user->first_name ?? 'N/A' }} {{ $user->last_name ?? 'N/A' }}
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Reset Code Box -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #e8f5e8; border: 1px solid #d0d8c3; border-radius: 8px; border-left: 4px solid #2d6a3e; margin: 25px 0;">
                    <tr>
                        <td style="padding: 25px; text-align: center;">
                            <div style="font-size: 14px; font-weight: 600; color: #2d6a3e; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px;">
                                Your Reset Code
                            </div>
                            <div style="font-size: 32px; font-weight: 700; color: #014421; letter-spacing: 3px; font-family: Courier, monospace;">
                                {{ $resetCode }}
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Instructions -->
                <p style="color: #2d6a3e; font-size: 15px; margin: 25px 0; font-weight: 600;">
                    How to Reset Your Password:
                </p>
                <ol style="color: #012d17; font-size: 15px; line-height: 1.8;">
                    <li>Click the button below or visit the password reset page</li>
                    <li>Enter the reset code shown above: <strong>{{ $resetCode }}</strong></li>
                    <li>Create a new password (at least 8 characters with uppercase, lowercase, and numbers)</li>
                    <li>Confirm your new password</li>
                    <li>Click "Reset Password" to complete the process</li>
                </ol>

                <!-- Reset Button -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                    <tr>
                        <td align="center">
                            <a href="{{ $resetLink }}" style="display: inline-block; background-color: #2d6a3e; color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; font-size: 18px;">
                                🔑 Reset Your Password
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- Alternative Link -->
                <p style="color: #666; font-size: 12px; text-align: center; margin: 20px 0;">
                    Or copy and paste this link in your browser:<br>
                    <code style="background: #f0f0f0; padding: 8px 12px; border-radius: 4px; display: inline-block; margin-top: 8px; word-break: break-all; color: #012d17;">
                        {{ $resetLink }}
                    </code>
                </p>

                <!-- Security Warning -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #fff8e1; border: 1px solid #ffcc02; border-radius: 6px; border-left: 4px solid #ff9800; margin: 25px 0;">
                    <tr>
                        <td style="padding: 15px;">
                            <p style="margin: 0; color: #e65100; font-size: 15px;">
                                ⚠️ <strong>Important:</strong> This reset link will expire in <strong>1 hour</strong>. If you didn't request a password reset, please ignore this email or contact our support team immediately.
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Security Tips -->
                <p style="color: #2d6a3e; font-size: 15px; margin: 25px 0; font-weight: 600;">
                    Security Tips:
                </p>
                <ul style="color: #012d17; font-size: 15px; line-height: 1.8;">
                    <li>Never share your reset code with anyone</li>
                    <li>Use a strong, unique password</li>
                    <li>Don't use the same password as other accounts</li>
                    <li>If you didn't request this reset, change your password immediately</li>
                </ul>

                <!-- Support Message -->
                <p style="color: #2d6a3e; font-size: 15px; margin: 30px 0 0 0; padding-top: 20px; border-top: 1px solid #d0d8c3;">
                    If you have any questions or need assistance, please don't hesitate to contact our support team.
                </p>

            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #012d17; color: white; padding: 30px; text-align: center;" class="mobile-padding">
                <div style="font-size: 20px; font-weight: 700; margin-bottom: 10px; color: #d0d8c3;">
                    Nicolites Portal
                </div>
                <p style="color: #d0d8c3; margin: 0 0 20px 0; font-size: 14px;">
                    Password Reset Service
                </p>
                <div style="border-top: 1px solid #d0d8c3; margin: 20px 0; opacity: 0.3;"></div>
                <p style="color: #d0d8c3; margin: 0; font-size: 14px;">
                    © 2025 Nicolites Montessori School. All rights reserved.
                </p>
            </td>
        </tr>

    </table>
</body>
</html>
