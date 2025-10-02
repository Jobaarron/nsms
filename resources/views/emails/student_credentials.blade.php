<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal Access - Nicolites School</title>
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
                    🎓 Welcome to Nicolites School!
                </h1>
                <p style="font-size: 18px; margin: 10px 0 0 0; color: #d0d8c3;" class="mobile-font">
                    Student Portal Access
                </p>
            </td>
        </tr>

        <!-- Main Content -->
        <tr>
            <td style="padding: 40px 30px;" class="mobile-padding">
                
                <!-- Welcome Message -->
                <p style="font-size: 18px; color: #012d17; margin-bottom: 30px; line-height: 1.6;" class="mobile-font">
                    Congratulations, {{ $student->first_name }}!<br>
                    Your enrollment has been approved and your student account has been successfully created. You are now officially a student of Nicolites School!
                </p>

                <!-- Enrollment Status -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #e8f5e8; border: 1px solid #4caf50; border-radius: 8px; border-left: 4px solid #2e7d32; margin: 25px 0;">
                    <tr>
                        <td style="padding: 25px;">
                            <div style="font-size: 20px; font-weight: 600; color: #2e7d32; margin-bottom: 15px;">
                                ✅ Enrollment Status: <span style="color: #4caf50;">APPROVED & ENROLLED</span>
                            </div>
                            <p style="margin: 0; color: #2e7d32; font-size: 15px;">
                                Welcome to {{ $student->grade_level }}{{ $student->strand ? ' - ' . $student->strand : '' }}{{ $student->track ? ' (' . $student->track . ')' : '' }} for Academic Year {{ $student->academic_year }}.
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Student Credentials Box -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; border-left: 4px solid #1976d2; margin: 25px 0;">
                    <tr>
                        <td style="padding: 25px;">
                            <div style="font-size: 20px; font-weight: 600; color: #1565c0; margin-bottom: 15px;">
                                🔐 Your Student Portal Credentials
                            </div>
                            <p style="margin: 0 0 15px 0; color: #1976d2; font-size: 15px;">
                                Use these credentials to access the student portal and manage your academic information:
                            </p>
                            
                            <!-- Student ID -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="credential-table" style="margin-bottom: 12px;">
                                <tr>
                                    <td style="font-weight: 600; color: #1565c0; width: 120px; vertical-align: top; padding: 8px 15px 8px 0;" class="credential-label">Student ID:</td>
                                    <td style="color: #0d47a1; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #90caf9; font-size: 18px; font-weight: 600;" class="credential-value">
                                        {{ $studentId }}
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Password -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="credential-table">
                                <tr>
                                    <td style="font-weight: 600; color: #1565c0; width: 120px; vertical-align: top; padding: 8px 15px 8px 0;" class="credential-label">Password:</td>
                                    <td style="color: #0d47a1; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #90caf9; font-size: 18px; font-weight: 600;" class="credential-value">
                                        {{ $password }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Security Warning -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #fff3e0; border: 1px solid #ff9800; border-radius: 6px; border-left: 4px solid #f57c00; margin: 25px 0;">
                    <tr>
                        <td style="padding: 15px;">
                            <p style="margin: 0; color: #e65100; font-size: 15px;">
                                ⚠️ <strong>Security Reminder:</strong> Please keep your credentials safe and change your password after your first login. Do not share your credentials with anyone.
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Login Button -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                    <tr>
                        <td align="center">
                            <a href="{{ url('/student/login') }}" style="display: inline-block; background-color: #1976d2; color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; font-size: 18px;">
                                🚀 Access Student Portal
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- Academic Information -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f3e5f5; border: 1px solid #ce93d8; border-radius: 8px; border-left: 4px solid #9c27b0; margin: 25px 0;">
                    <tr>
                        <td style="padding: 25px;">
                            <div style="font-size: 18px; font-weight: 600; color: #7b1fa2; margin-bottom: 15px;">
                                📚 Your Academic Information
                            </div>
                            <ul style="margin: 0; padding-left: 20px; color: #7b1fa2;">
                                <li style="margin-bottom: 8px;"><strong>Grade Level:</strong> {{ $student->grade_level }}</li>
                                @if($student->strand)
                                <li style="margin-bottom: 8px;"><strong>Strand:</strong> {{ $student->strand }}</li>
                                @endif
                                @if($student->track)
                                <li style="margin-bottom: 8px;"><strong>Track:</strong> {{ $student->track }}</li>
                                @endif
                                <li style="margin-bottom: 8px;"><strong>Academic Year:</strong> {{ $student->academic_year }}</li>
                                <li><strong>Student Type:</strong> {{ ucfirst($student->student_type) }}</li>
                            </ul>
                        </td>
                    </tr>
                </table>

                <!-- What's Available -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #e8f5e8; border: 1px solid #4caf50; border-radius: 8px; border-left: 4px solid #2e7d32; margin: 25px 0;">
                    <tr>
                        <td style="padding: 25px;">
                            <div style="font-size: 18px; font-weight: 600; color: #2e7d32; margin-bottom: 15px;">
                                🎯 Available in Student Portal
                            </div>
                            <ul style="margin: 0; padding-left: 20px; color: #2e7d32;">
                                <li style="margin-bottom: 8px;">View your class schedule and subjects</li>
                                <li style="margin-bottom: 8px;">Check grades and academic progress</li>
                                <li style="margin-bottom: 8px;">Access student payments and fees</li>
                                <li style="margin-bottom: 8px;">View guidance notes and counseling records</li>
                                <li style="margin-bottom: 8px;">Update your profile information</li>
                                <li>Communicate with teachers and school administration</li>
                            </ul>
                        </td>
                    </tr>
                </table>

                <!-- Support Message -->
                <p style="color: #2d6a3e; font-size: 15px; margin: 30px 0 0 0; padding-top: 20px; border-top: 1px solid #d0d8c3;">
                    If you have any questions about your student account or need assistance accessing the portal, please contact our IT support or visit the registrar's office.
                </p>

            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #012d17; color: white; padding: 30px; text-align: center;" class="mobile-padding">
                <div style="font-size: 20px; font-weight: 700; margin-bottom: 10px; color: #d0d8c3;">
                    Nicolites School
                </div>
                <p style="color: #d0d8c3; margin: 0 0 20px 0; font-size: 14px;">
                    Student Management System
                </p>
                <div style="border-top: 1px solid #d0d8c3; margin: 20px 0; opacity: 0.3;"></div>
                <p style="color: #d0d8c3; margin: 0; font-size: 14px;">
                    © 2025 Nicolites School. All rights reserved.
                </p>
            </td>
        </tr>

    </table>
</body>
</html>
