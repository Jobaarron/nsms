<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - Nicolites School</title>
    <style>
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .mobile-padding { padding: 20px !important; }
            .mobile-font { font-size: 16px !important; }
            .mobile-title { font-size: 24px !important; }
            .receipt-table { width: 100% !important; }
            .receipt-label { display: block !important; width: 100% !important; margin-bottom: 5px !important; }
            .receipt-value { display: block !important; width: 100% !important; margin-bottom: 15px !important; }
        }
    </style>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #e8f5e8; margin: 0; padding: 20px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" class="container" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; max-width: 600px;">
        
        <!-- Header -->
        <tr>
            <td style="background-color: #014421; color: white; padding: 40px 30px; text-align: center;" class="mobile-padding">
                <h1 style="font-size: 32px; font-weight: 700; margin: 0; color: white;" class="mobile-title">
                    💳 Payment Receipt
                </h1>
                <p style="font-size: 18px; margin: 10px 0 0 0; color: #d0d8c3;" class="mobile-font">
                    Nicolites Montessori School
                </p>
            </td>
        </tr>

        <!-- Main Content -->
        <tr>
            <td style="padding: 40px 30px;" class="mobile-padding">
                
                <!-- Confirmation Message -->
                <p style="font-size: 18px; color: #012d17; margin-bottom: 30px; line-height: 1.6;" class="mobile-font">
                    Your payment has been successfully confirmed by our cashier. Your receipt is now available for download.
                </p>

                <!-- Payment Status -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #e8f5e8; border: 1px solid #4caf50; border-radius: 8px; border-left: 4px solid #2e7d32; margin: 25px 0;">
                    <tr>
                        <td style="padding: 25px;">
                            <div style="font-size: 20px; font-weight: 600; color: #2e7d32; margin-bottom: 15px;">
                                ✅ Payment Status: <span style="color: #4caf50;">CONFIRMED</span>
                            </div>
                            <p style="margin: 0; color: #2e7d32; font-size: 15px;">
                                Your payment has been received and approved. You can now download your receipt.
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Receipt Details Box -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; border-left: 4px solid #1976d2; margin: 25px 0;">
                    <tr>
                        <td style="padding: 25px;">
                            <div style="font-size: 20px; font-weight: 600; color: #1565c0; margin-bottom: 20px;">
                                📋 Receipt Details
                            </div>
                            
                            <!-- Transaction ID -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="receipt-table" style="margin-bottom: 15px;">
                                <tr>
                                    <td style="font-weight: 600; color: #1565c0; width: 140px; vertical-align: top; padding: 8px 15px 8px 0;" class="receipt-label">Transaction ID:</td>
                                    <td style="color: #0d47a1; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #90caf9; font-size: 16px; font-weight: 600;" class="receipt-value">
                                        {{ $payment->transaction_id }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Amount Paid -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="receipt-table" style="margin-bottom: 15px;">
                                <tr>
                                    <td style="font-weight: 600; color: #1565c0; width: 140px; vertical-align: top; padding: 8px 15px 8px 0;" class="receipt-label">Amount Paid:</td>
                                    <td style="color: #0d47a1; font-family: Courier, monospace; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #90caf9; font-size: 18px; font-weight: 600;" class="receipt-value">
                                        ₱{{ number_format($payment->amount_received ?? $payment->amount, 2) }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Payment Date -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="receipt-table" style="margin-bottom: 15px;">
                                <tr>
                                    <td style="font-weight: 600; color: #1565c0; width: 140px; vertical-align: top; padding: 8px 15px 8px 0;" class="receipt-label">Date Confirmed:</td>
                                    <td style="color: #0d47a1; padding: 8px 12px; font-size: 15px;" class="receipt-value">
                                        {{ $payment->confirmed_at ? $payment->confirmed_at->format('F d, Y \a\t h:i A') : 'N/A' }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Payment Method -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="receipt-table" style="margin-bottom: 15px;">
                                <tr>
                                    <td style="font-weight: 600; color: #1565c0; width: 140px; vertical-align: top; padding: 8px 15px 8px 0;" class="receipt-label">Payment Method:</td>
                                    <td style="color: #0d47a1; padding: 8px 12px; font-size: 15px;" class="receipt-value">
                                        {{ ucfirst($payment->payment_method ?? 'N/A') }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Period (if applicable) -->
                            @if($payment->period_name)
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="receipt-table">
                                <tr>
                                    <td style="font-weight: 600; color: #1565c0; width: 140px; vertical-align: top; padding: 8px 15px 8px 0;" class="receipt-label">Period:</td>
                                    <td style="color: #0d47a1; padding: 8px 12px; font-size: 15px;" class="receipt-value">
                                        {{ $payment->period_name }}
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Download Receipt Button -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                    <tr>
                        <td align="center">
                            <a href="{{ url('/receipt?transaction_id=' . $payment->transaction_id) }}" style="display: inline-block; background-color: #1976d2; color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; font-size: 18px;">
                                📄 View Receipt
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- Important Notes -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #fff3e0; border: 1px solid #ff9800; border-radius: 6px; border-left: 4px solid #f57c00; margin: 25px 0;">
                    <tr>
                        <td style="padding: 15px;">
                            <p style="margin: 0; color: #e65100; font-size: 15px;">
                                ⚠️ <strong>Important:</strong> Keep this email for your records. You can access your receipt anytime using the link above.
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Support Message -->
                <p style="color: #2d6a3e; font-size: 15px; margin: 30px 0 0 0; padding-top: 20px; border-top: 1px solid #d0d8c3;">
                    If you have any questions about this payment or need assistance, please contact our cashier's office or visit the school.
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
                    © 2025 Nicolites School. All rights reserved.
                </p>
            </td>
        </tr>

    </table>
</body>
</html>
