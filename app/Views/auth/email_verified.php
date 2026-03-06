<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - MGOD LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .success-animation {
            animation: scaleIn 0.5s ease-in-out;
        }
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        .checkmark-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        .checkmark {
            color: white;
            font-size: 60px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card mt-5 shadow success-animation">
                    <div class="card-body p-5 text-center">
                        <div class="checkmark-circle">
                            <div class="checkmark">✓</div>
                        </div>
                        
                        <h2 class="mb-3">Email Verified Successfully!</h2>
                        <p class="lead mb-4">
                            Your email address has been verified. You can now access all features of MGOD LMS.
                        </p>

                        <div class="alert alert-success" role="alert">
                            <h6 class="alert-heading">🎉 What's Next?</h6>
                            <hr>
                            <ul class="list-unstyled mb-0 text-start">
                                <li>✅ Your account is now fully activated</li>
                                <li>✅ You can log in with your credentials</li>
                                <li>✅ Access courses and learning materials</li>
                                <li>✅ Connect with instructors and classmates</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <a href="<?= base_url('login') ?>" class="btn btn-primary btn-lg">
                                🔐 Go to Login
                            </a>
                            <a href="<?= base_url() ?>" class="btn btn-outline-secondary">
                                🏠 Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
