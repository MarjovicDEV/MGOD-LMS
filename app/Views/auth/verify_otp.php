<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - MGOD LMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <style>
        .otp-input {
            font-size: 24px;
            letter-spacing: 10px;
            text-align: center;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        .countdown {
            font-size: 16px;
            font-weight: bold;
            color: #dc3545;
        }
        .security-notice {
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card mt-5 shadow">
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0">🔐 Verify OTP - MGOD LMS</h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Email Info -->
                        <div class="alert alert-info text-center mb-4">
                            <p class="mb-0">We've sent a 6-digit code to:</p>
                            <strong><?= esc($email) ?></strong>
                        </div>                        <!-- Display Flash Messages -->
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <!-- OTP Form -->
                        <form method="POST" action="<?= base_url('verify-otp') ?>" id="otpForm">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label for="otp_code" class="form-label">Enter OTP Code *</label>
                                <input type="text" 
                                       class="form-control otp-input" 
                                       id="otp_code" 
                                       name="otp_code" 
                                       maxlength="6" 
                                       required 
                                       pattern="[0-9]{6}"
                                       autocomplete="off"
                                       placeholder="000000"
                                       autofocus>
                                <div class="form-text">Enter the 6-digit code from your email</div>
                            </div>

                            <!-- Countdown Timer -->
                            <div class="alert alert-warning text-center mb-3">
                                <small class="mb-0">⏱️ Code expires in: <span class="countdown" id="countdown">10:00</span></small>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-dark btn-lg">Verify OTP</button>
                            </div>
                        </form>

                        <!-- Resend OTP -->
                        <div class="text-center mb-3">
                            <p class="mb-2 text-muted">Didn't receive the code?</p>
                            <form method="POST" action="<?= base_url('resend-otp') ?>" id="resendForm" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-dark" id="resendBtn">
                                    📨 Resend OTP
                                </button>
                            </form>
                        </div>

                        <!-- Security Notice -->
                        <div class="alert alert-warning security-notice mb-3">
                            <div class="d-flex align-items-start">
                                <div class="me-2">⚠️</div>
                                <div>
                                    <strong>Security Notice:</strong><br>
                                    <small>Never share your OTP code with anyone. This code is for your security and should only be used once.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Back to Login -->
                        <div class="text-center">
                            <a href="<?= base_url('login') ?>" class="text-decoration-none text-muted">
                                ← Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // OTP Input - only allow numbers
        const otpInput = document.getElementById('otp_code');
        
        otpInput.addEventListener('input', function(e) {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Auto-submit when 6 digits are entered
            if (this.value.length === 6) {
                document.getElementById('otpForm').submit();
            }
        });

        // Countdown Timer (10 minutes)
        let timeLeft = 600; // 10 minutes in seconds
        const countdownElement = document.getElementById('countdown');
        const resendBtn = document.getElementById('resendBtn');

        function updateCountdown() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft === 0) {
                countdownElement.textContent = 'Expired';
                countdownElement.style.color = '#dc3545';
                otpInput.disabled = true;
                alert('OTP has expired. Please request a new code.');
            } else if (timeLeft <= 60) {
                countdownElement.style.color = '#dc3545'; // Red when less than 1 minute
            }

            timeLeft--;
        }

        // Update countdown every second
        setInterval(updateCountdown, 1000);
        updateCountdown(); // Initial call

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Focus on OTP input when page loads
        otpInput.focus();

        // Handle paste event
        otpInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = (e.clipboardData || window.clipboardData).getData('text');
            const numericData = pastedData.replace(/[^0-9]/g, '').substring(0, 6);
            this.value = numericData;
            
            // Auto-submit if 6 digits pasted
            if (numericData.length === 6) {
                document.getElementById('otpForm').submit();
            }
        });
    });
    </script>
</body>
</html>
