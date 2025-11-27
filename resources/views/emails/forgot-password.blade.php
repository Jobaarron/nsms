<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background-color: #014421;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #014421;
            font-size: 18px;
            margin-top: 0;
            border-bottom: 2px solid #014421;
            padding-bottom: 10px;
        }
        .reset-code {
            background-color: #e8f5e8;
            border-left: 4px solid #2d6a3e;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        .reset-code-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .reset-code-value {
            font-size: 24px;
            font-weight: 700;
            color: #014421;
            letter-spacing: 2px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .reset-button {
            display: inline-block;
            background-color: #2d6a3e;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .reset-button:hover {
            transform: translateY(-2px);
        }
        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .info-box strong {
            color: #856404;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .footer a {
            color: #2d6a3e;
            text-decoration: none;
        }
        .user-info {
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 14px;
            border-left: 4px solid #2d6a3e;
        }
        .user-info strong {
            color: #014421;
        }
        ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        ul li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🔐 Password Reset Request</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Hello,</h2>
            
            <p>We received a request to reset the password for your account at <strong>Nicolites Montessori School</strong>.</p>

            <!-- User Info -->
            <div class="user-info">
                <strong>Account Information:</strong><br>
                @if($userType === 'system_user')
                    <strong>Email:</strong> {{ $user->email }}<br>
                    <strong>Name:</strong> {{ $user->name }}
                @elseif($userType === 'student')
                    <strong>Student ID:</strong> {{ $user->student_id }}<br>
                    <strong>Name:</strong> {{ $user->first_name ?? 'N/A' }} {{ $user->last_name ?? 'N/A' }}
                @elseif($userType === 'enrollee')
                    <strong>Application ID:</strong> {{ $user->application_id }}<br>
                    <strong>Name:</strong> {{ $user->first_name ?? 'N/A' }} {{ $user->last_name ?? 'N/A' }}
                @endif
            </div>

            <!-- Reset Code -->
            <div class="reset-code">
                <div class="reset-code-label">Your Reset Code:</div>
                <div class="reset-code-value">{{ $resetCode }}</div>
            </div>

            <!-- Instructions -->
            <h2>How to Reset Your Password:</h2>
            <ol>
                <li>Click the button below or visit the password reset page</li>
                <li>Enter the reset code shown above: <strong>{{ $resetCode }}</strong></li>
                <li>Create a new password (at least 8 characters with uppercase, lowercase, and numbers)</li>
                <li>Confirm your new password</li>
                <li>Click "Reset Password" to complete the process</li>
            </ol>

            <!-- Reset Button -->
            <div class="button-container">
                <a href="{{ $resetLink }}" class="reset-button">Reset Your Password</a>
            </div>

            <!-- Alternative Link -->
            <p style="text-align: center; font-size: 12px; color: #666;">
                Or copy and paste this link in your browser:<br>
                <code style="background: #f0f0f0; padding: 5px 10px; border-radius: 3px; display: inline-block; margin-top: 5px; word-break: break-all;">
                    {{ $resetLink }}
                </code>
            </p>

            <!-- Warning Box -->
            <div class="info-box">
                <strong>⚠️ Important:</strong> This reset link will expire in <strong>1 hour</strong>. If you didn't request a password reset, please ignore this email or contact our support team immediately.
            </div>

            <!-- Security Tips -->
            <h2>Security Tips:</h2>
            <ul>
                <li>Never share your reset code with anyone</li>
                <li>Use a strong, unique password</li>
                <li>Don't use the same password as other accounts</li>
                <li>If you didn't request this reset, change your password immediately</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>Nicolites Montessori School</strong><br>
                San Roque St., Brgy 4, Nasugbu, Batangas 4231<br>
                <a href="tel:+63431600149">(043) 416-0149</a> | 
                <a href="mailto:admissions@nicolites.edu.ph">admissions@nicolites.edu.ph</a>
            </p>
            <p style="margin-top: 15px; border-top: 1px solid #ddd; padding-top: 15px;">
                This is an automated email. Please do not reply to this message.<br>
                © {{ date('Y') }} Nicolites Montessori School. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
