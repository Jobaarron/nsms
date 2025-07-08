<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Nicolites Portal</title>
    @vite(['resources/sass/app.scss','resources/js/app.js'])
    @vite('resources/css/email.css')
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    
</head>
<body>
    <div style="padding: 20px 0;">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <h1><i class="ri-graduation-cap-line"></i> Welcome to Nicolites Portal</h1>
                <div class="subtitle">Your Student Management System</div>
            </div>
            
            <!-- Body -->
            <div class="email-body">
                <div class="welcome-message">
                    <strong>Hello {{ $student->first_name }}!</strong><br>
                    We're excited to welcome you to the Nicolites Portal. Your student account has been successfully created and is ready to use.
                </div>
                
                <!-- Credentials Card -->
                <div class="credentials-card">
                    <div class="credentials-title">
                        <i class="ri-key-2-line"></i>
                        Your Login Credentials
                    </div>
                    
                    <div class="credential-item">
                        <div class="credential-label">
                            <i class="ri-mail-line"></i> Email:
                        </div>
                        <div class="credential-value">{{ $student->email }}</div>
                    </div>
                    
                    <div class="credential-item">
                        <div class="credential-label">
                            <i class="ri-lock-line"></i> Password:
                        </div>
                        <div class="credential-value">{{ $rawPassword }}</div>
                    </div>
                </div>
                
                <!-- Security Notice -->
                <div class="security-notice">
                    <p>
                        <i class="ri-shield-check-line"></i>
                        <strong>Security Reminder:</strong> Please change your password after your first login for enhanced security.
                    </p>
                </div>
                
                <!-- Login Button -->
                <div class="text-center">
                    <a href="{{ url('/student/login') }}" class="login-button">
                        <i class="ri-login-box-line"></i> Access Student Portal
                    </a>
                </div>
                
                <div class="support-text">
                    <p>
                        If you have any questions or need assistance, please don't hesitate to contact our support team.
                    </p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <div class="footer-brand">Nicolites Portal</div>
                <p class="footer-text">Student Management System</p>
                
                <div class="social-links">
                    <a href="#" aria-label="Facebook">
                        <i class="ri-facebook-fill"></i>
                    </a>
                    {{-- <a href="#" aria-label="Instagram">
                        <i class="ri-instagram-line"></i>
                    </a>
                    <a href="#" aria-label="Twitter">
                        <i class="ri-twitter-fill"></i>
                    </a> --}}
                </div>
                
                <div class="divider"></div>
                <p class="footer-text">
                    © {{ date('Y') }} Nicolites Portal. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
