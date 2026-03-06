<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MGOD LMS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card mt-5 shadow">
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0">Login - MGOD LMS</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('show_resend')): ?>
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">📧 Email Not Verified</h6>
                                <p class="mb-2">Didn't receive the email?</p>
                                <form method="POST" action="<?= base_url('resend-verification') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="email" value="<?= session()->getFlashdata('user_email') ?? old('email') ?>">
                                    <button type="submit" class="btn btn-sm btn-warning">
                                        📨 Resend Verification Email
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= base_url('login') ?>">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= old('email') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Captcha Verification</label>
                                <div class="border rounded p-2 bg-white">
                                    <?php if (!empty($captchaImageUrl)): ?>
                                        <img id="captchaImage"
                                             src="<?= esc($captchaImageUrl) ?>"
                                             alt="Captcha Image"
                                             class="img-fluid w-100 rounded"
                                             style="max-height: 90px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-0">Captcha is unavailable. Please refresh the page.</div>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" id="captchaToken" name="captcha_token" value="<?= esc($captchaToken ?? '') ?>">
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <input type="text"
                                           class="form-control"
                                           id="captchaCode"
                                           name="captcha_code"
                                           placeholder="Enter captcha text"
                                           autocomplete="off"
                                           required>
                                    <button type="button" id="refreshCaptchaBtn" class="btn btn-outline-dark">Refresh</button>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark btn-lg">Login</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <p class="mb-0">Don't have an account? 
                                <a href="<?= base_url('register') ?>" class="text-decoration-none text-dark fw-bold">Register here</a>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="<?= base_url() ?>" class="text-decoration-none text-muted">
                        ← Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const refreshBtn = document.getElementById('refreshCaptchaBtn');
            const captchaImage = document.getElementById('captchaImage');
            const captchaToken = document.getElementById('captchaToken');
            const captchaCode = document.getElementById('captchaCode');

            if (!refreshBtn || !captchaToken) {
                return;
            }

            refreshBtn.addEventListener('click', async function () {
                try {
                    const response = await fetch('<?= base_url('captcha/refresh') ?>', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Failed to refresh captcha.');
                    }

                    captchaToken.value = data.token;
                    if (captchaImage) {
                        captchaImage.src = data.image_url;
                    }
                    if (captchaCode) {
                        captchaCode.value = '';
                    }
                } catch (error) {
                    alert('Unable to refresh captcha right now. Please try again.');
                }
            });
        });
    </script>
</body>
</html>