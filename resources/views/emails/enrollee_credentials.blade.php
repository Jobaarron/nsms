<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Application Submitted - Nicolites Portal: School Management System</title>
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
                    📝 Admission Application Submitted
                </h1>
                <p style="font-size: 18px; margin: 10px 0 0 0; color: #d0d8c3;" class="mobile-font">
                    Nicolites Portal: School Management System
                </p>
            </td>
        </tr>

        <!-- Main Content -->
        <tr>
            <td style="padding: 40px 30px;" class="mobile-padding">
                
                <!-- Welcome Message -->
                <p style="font-size: 18px; color: #012d17; margin-bottom: 30px; line-height: 1.6;" class="mobile-font">
                    Hello, {{ $enrollee->first_name }}!<br>
                    Thank you for submitting your admission application to Nicolites School. Your application has been successfully received and is now being processed.
                </p>

                <!-- Application Status -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #e3f2fd; border: 1px solid #90caf9; border-radius: 8px; border-left: 4px solid #1976d2; margin: 25px 0;">
                    <tr>
                        <td style="padding: 25px;">
                            <div style="font-size: 20px; font-weight: 600; color: #1565c0; margin-bottom: 15px;">
                                📋 Application Status: <span style="color: #ff9800;">PENDING REVIEW</span>
                            </div>
                            <p style="margin: 0; color: #1565c0; font-size: 15px;">
                                Your application is currently under review by our admissions team. You will receive an email notification once a decision has been made.
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Credentials Box -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #e8f5e8; border: 1px solid #d0d8c3; border-radius: 8px; border-left: 4px solid #2d6a3e; margin: 25px 0;">
                    <tr>
                        <td style="padding: 25px;">
                            <div style="font-size: 20px; font-weight: 600; color: #014421; margin-bottom: 15px;">
                                🔐 Your Application Credentials
                            </div>
                            <p style="margin: 0 0 15px 0; color: #2d6a3e; font-size: 15px;">
                                Use these credentials to track your application status and access the applicant portal:
                            </p>
                            
                            <!-- Application ID -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="credential-table" style="margin-bottom: 12px;">
                                <tr>
                                    <td style="font-weight: 600; color: #014421; width: 120px; vertical-align: top; padding: 8px 15px 8px 0;" class="credential-label">Applicant ID:</td>
                                    <td style="color: #012d17; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #d0d8c3; font-size: 18px; font-weight: 600;" class="credential-value">
                                        {{ $applicationId }}
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Password -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="credential-table">
                                <tr>
                                    <td style="font-weight: 600; color: #014421; width: 120px; vertical-align: top; padding: 8px 15px 8px 0;" class="credential-label">Password:</td>
                                    <td style="color: #012d17; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #d0d8c3; font-size: 18px; font-weight: 600;" class="credential-value">
                                        {{ $password }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Security Warning -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #fff8e1; border: 1px solid #ffcc02; border-radius: 6px; border-left: 4px solid #ff9800; margin: 25px 0;">
                    <tr>
                        <td style="padding: 15px;">
                            <p style="margin: 0; color: #e65100; font-size: 15px;">
                                ⚠️ <strong>Security Reminder:</strong> Please keep your credentials safe and do not share them with anyone. You will need these to access your application status.
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Login Button -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                    <tr>
                        <td align="center">
                            <a href="{{ url('/enrollee/login') }}" style="display: inline-block; background-color: #014421; color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; font-size: 18px;">
                                🔗 Access Applicant Portal
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- Next Steps -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f3e5f5; border: 1px solid #ce93d8; border-radius: 8px; border-left: 4px solid #9c27b0; margin: 25px 0;">
                    <tr>
                        <td style="padding: 25px;">
                            <div style="font-size: 18px; font-weight: 600; color: #7b1fa2; margin-bottom: 15px;">
                                📅 What Happens Next?
                            </div>
                            <ul style="margin: 0; padding-left: 20px; color: #7b1fa2;">
                                <li style="margin-bottom: 8px;">Our admissions team will review your application within 3-5 business days</li>
                                <li style="margin-bottom: 8px;">You will receive an email notification with the admission decision</li>
                                <li style="margin-bottom: 8px;">If approved, you will receive further instructions for enrollment completion</li>
                                <li>You can track your application status anytime using the enrollee portal</li>
                            </ul>
                        </td>
                    </tr>
                </table>

                <!-- Support Message -->
                <p style="color: #2d6a3e; font-size: 15px; margin: 30px 0 0 0; padding-top: 20px; border-top: 1px solid #d0d8c3;">
                    If you have any questions about your application or need assistance, please don't hesitate to contact our admissions office.
                </p>

            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #012d17; color: white; padding: 30px; text-align: center;" class="mobile-padding">
                <div style="font-size: 20px; font-weight: 700; margin-bottom: 10px; color: #d0d8c3;">
                    Nicolites Montessori
                </div>
                <p style="color: #d0d8c3; margin: 0 0 20px 0; font-size: 14px;">
                    Nicolites Portal: School Management System
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
