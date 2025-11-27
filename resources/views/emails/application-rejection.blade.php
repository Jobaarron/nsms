<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: white;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 5px;
            margin-top: 20px;
        }
        .alert {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .info-box {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
        h2 {
            color: #dc3545;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }
        .reason-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 12px;
            border-radius: 4px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Application Rejection Notice</h1>
            <p>Nicolites School of Science</p>
        </div>

        <div class="content">
            <p>Dear {{ $studentName }},</p>

            <div class="alert">
                <strong>⚠️ Important Notice:</strong> We regret to inform you that your admission application has been declined.
            </div>

            <h2>Application Details</h2>
            <div class="info-box">
                <p><strong>Application ID:</strong> {{ $applicationId }}</p>
                <p><strong>Rejection Date:</strong> {{ $rejectedDate }}</p>
                <p><strong>Status:</strong> Declined</p>
            </div>

            <h2>Reason for Rejection</h2>
            <div class="reason-box">
                <p>{{ $rejectionReason }}</p>
            </div>

            <h2>Important Information</h2>
            <p>Please note the following:</p>
            <ul>
                <li>Your application has been permanently declined and cannot be resubmitted through this portal.</li>
                <li>All your personal data and application materials will be deleted from our system within 3 days in accordance with our data retention policy.</li>
                <li>If you believe this declined is in error, please contact the admissions office immediately.</li>
                <li>You may reapply for admission in the next academic year if you wish.</li>
            </ul>

            <h2>Next Steps</h2>
            <p>If you have any questions or concerns regarding this rejection, please:</p>
            <ul>
                <li>Contact the Admissions Office directly</li>
                <li>Email: nmscurriculumdirector@gmail.com</li>
                <li>Phone: (043) 416-0149</li>
                <li>Visit us during office hours: Monday-Friday, 7:00 AM - 5:00 PM</li>
            </ul>

            <p>We appreciate your interest in Nicolites School of Science and wish you success in your future educational endeavors.</p>

            <p>Sincerely,<br>
            <strong>Admissions Office</strong><br>
            Nicolites School of Science</p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} Nicolites School of Science. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
