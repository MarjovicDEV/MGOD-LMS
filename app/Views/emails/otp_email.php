<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code - MGOD LMS</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .otp-box {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border: 2px dashed #3b82f6;
            border-radius: 10px;
            padding: 30px;
            margin: 30px 0;
        }
        .otp-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .otp-code {
            font-size: 48px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .expiry-note {
            font-size: 14px;
            color: #ef4444;
            margin-top: 15px;
            font-weight: 500;
        }
        .warning-box {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        .warning-box p {
            margin: 0;
            font-size: 14px;
            color: #991b1b;
            line-height: 1.5;
        }
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #1e40af;
            line-height: 1.5;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
            font-size: 14px;
            color: #6b7280;
        }
        .footer .app-name {
            font-weight: 600;
            color: #1e3a8a;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }
            .otp-code {
                font-size: 36px;
                letter-spacing: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🔐 Two-Factor Authentication</h1>
            <p>Secure Login Verification</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hello, <strong><?= esc($userName) ?></strong>!
            </div>

            <div class="message">
                You have requested to log in to your MGOD LMS account. To complete your login, please use the One-Time Password (OTP) below:
            </div>

            <!-- OTP Box -->
            <div class="otp-box">
                <div class="otp-label">Your OTP Code</div>
                <div class="otp-code"><?= esc($otpCode) ?></div>
                <div class="expiry-note">
                    ⏱️ This code expires in <?= esc($expiryMinutes) ?> minutes
                </div>
            </div>

            <!-- Security Warning -->
            <div class="warning-box">
                <p><strong>⚠️ Security Alert:</strong><br>
                Never share this code with anyone. MGOD LMS staff will never ask for your OTP code. If you didn't request this code, please ignore this email and secure your account immediately.</p>
            </div>

            <!-- Information Box -->
            <div class="info-box">
                <p><strong>ℹ️ Important Information:</strong></p>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; text-align: left;">
                    <li>This OTP can only be used once</li>
                    <li>Maximum 3 verification attempts allowed</li>
                    <li>Request a new code if this one expires</li>
                    <li>This is an automated security measure</li>
                </ul>
            </div>

            <div class="message" style="margin-top: 30px;">
                If you're having trouble logging in, please contact our support team for assistance.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="app-name">MGOD Learning Management System</p>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p style="margin-top: 15px; font-size: 12px;">
                © <?= date('Y') ?> MGOD LMS. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
