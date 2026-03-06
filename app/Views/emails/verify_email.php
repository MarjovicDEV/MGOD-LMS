<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - MGOD LMS</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .email-header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .email-body {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }
        .email-body h2 {
            color: #667eea;
            font-size: 24px;
            margin-top: 0;
        }
        .email-body p {
            font-size: 16px;
            margin: 15px 0;
        }
        .verification-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .verification-box p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        .verify-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
            transition: transform 0.2s;
        }
        .verify-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .alternative-link {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            word-break: break-all;
        }
        .alternative-link p {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }
        .alternative-link a {
            color: #667eea;
            font-size: 12px;
            word-break: break-all;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            color: #666666;
            font-size: 14px;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-box p {
            margin: 0;
            font-size: 14px;
            color: #856404;
        }
        .info-list {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-list ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .info-list li {
            margin: 8px 0;
            font-size: 14px;
            color: #666;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 20px;
            }
            .email-header {
                padding: 30px 20px;
            }
            .email-body {
                padding: 30px 20px;
            }
            .verify-button {
                padding: 12px 30px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>📧 MGOD LMS</h1>
            <p>Learning Management System</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2>Welcome, <?= esc($userName) ?>! 🎉</h2>
            
            <p>Thank you for registering with <strong>MGOD Learning Management System</strong>. We're excited to have you on board!</p>
            
            <p>To complete your registration and activate your account, please verify your email address by clicking the button below:</p>

            <!-- Verification Button -->
            <div class="button-container">
                <a href="<?= esc($verificationLink) ?>" class="verify-button">
                    ✓ Verify Email Address
                </a>
            </div>

            <!-- Verification Info Box -->
            <div class="verification-box">
                <p><strong>📌 Important Information:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>This verification link will expire in <strong>24 hours</strong></li>
                    <li>Your account: <strong><?= esc($userEmail) ?></strong></li>
                    <li>Student ID: <strong><?= esc($studentId) ?></strong></li>
                </ul>
            </div>

            <!-- Alternative Link -->
            <div class="alternative-link">
                <p><strong>Button not working?</strong> Copy and paste this link into your browser:</p>
                <a href="<?= esc($verificationLink) ?>"><?= esc($verificationLink) ?></a>
            </div>

            <!-- Warning Box -->
            <div class="warning-box">
                <p>⚠️ <strong>Security Notice:</strong> If you did not create an account with MGOD LMS, please ignore this email. No account has been created yet.</p>
            </div>

            <!-- What's Next -->
            <div class="info-list">
                <p><strong>🚀 What's Next After Verification:</strong></p>
                <ul>
                    <li>Log in to your MGOD LMS account</li>
                    <li>Complete your student profile</li>
                    <li>Browse available courses</li>
                    <li>Connect with instructors and classmates</li>
                    <li>Start your learning journey!</li>
                </ul>
            </div>

            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>The MGOD LMS Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>MGOD Learning Management System</strong></p>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p style="font-size: 12px; color: #999; margin-top: 15px;">
                © <?= date('Y') ?> MGOD LMS. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
