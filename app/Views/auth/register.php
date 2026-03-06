<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MGOD LMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card mt-5 shadow">
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0">Register - MGOD LMS</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Display Flash Messages -->
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

                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>                        
                        <!-- Registration Form -->
                        <form method="POST" action="<?= base_url('register') ?>">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= old('first_name') ?>" required pattern="[A-Za-zñÑ\s]+" 
                                       title="First name can only contain letters and spaces">
                                <div class="form-text">Enter your first name</div>
                            </div>

                            <div class="mb-3">
                                <label for="middle_name" class="form-label">Middle Name *</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" 
                                       value="<?= old('middle_name') ?>" required pattern="[A-Za-zñÑ\s]+" 
                                       title="Middle name can only contain letters and spaces">
                                <div class="form-text">Enter your middle name</div>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= old('last_name') ?>" required pattern="[A-Za-zñÑ\s]+" 
                                       title="Last name can only contain letters and spaces">
                                <div class="form-text">Enter your last name</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= old('email') ?>" required pattern="[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}">
                                <div class="form-text">Enter a valid email address</div>
                            </div>

                            <div class="mb-3">
                                <label for="year_level_id" class="form-label">Year Level *</label>
                                <select class="form-select" id="year_level_id" name="year_level_id" required>
                                    <option value="" selected disabled>Select your year level</option>
                                    <?php if (isset($yearLevels) && !empty($yearLevels)): ?>
                                        <?php foreach ($yearLevels as $yearLevel): ?>
                                            <option value="<?= $yearLevel['id'] ?>" 
                                                <?= old('year_level_id') == $yearLevel['id'] ? 'selected' : '' ?>>
                                                <?= esc($yearLevel['year_level_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No year levels available</option>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text">Select your current year level</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Password must be at least 6 characters long</div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                                <div class="form-text">Re-enter your password to confirm</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark btn-lg">Register</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <p class="mb-0">Already have an account? 
                                <a href="<?= base_url('login') ?>" class="text-decoration-none text-dark fw-bold">Login here</a>
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
    </div>    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
      <!-- Enhanced Form Validation -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Name field validation
        const firstNameField = document.getElementById('first_name');
        const middleNameField = document.getElementById('middle_name');
        const lastNameField = document.getElementById('last_name');
        const emailField = document.getElementById('email');
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('password_confirm');
        
        // Function to validate name fields
        function validateNameField(field, minLength = 2) {
            field.addEventListener('input', function(e) {
                const value = e.target.value;
                const validPattern = /^[A-Za-zñÑ\s]*$/;
                
                // Remove invalid characters as user types
                if (!validPattern.test(value)) {
                    e.target.value = value.replace(/[^A-Za-zñÑ\s]/g, '');
                }
                
                // Visual feedback
                if (e.target.value.length >= minLength && validPattern.test(e.target.value)) {
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                } else if (e.target.value.length > 0) {
                    e.target.classList.remove('is-valid');
                    e.target.classList.add('is-invalid');
                } else {
                    e.target.classList.remove('is-valid', 'is-invalid');
                }
            });
        }
        
        // Apply validation to all name fields
        validateNameField(firstNameField, 2);
        validateNameField(middleNameField, 1);
        validateNameField(lastNameField, 2);
        
        // Email validation
        emailField.addEventListener('input', function(e) {
            const value = e.target.value;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // Visual feedback for email
            if (emailPattern.test(value)) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else if (value.length > 0) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
            }
        });
        
        // Password validation
        passwordField.addEventListener('input', function(e) {
            const value = e.target.value;
            
            // Visual feedback for password
            if (value.length >= 6) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else if (value.length > 0) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
            }
            
            // Also validate confirm password if it has a value
            if (confirmPasswordField.value.length > 0) {
                validatePasswordConfirm();
            }
        });
        
        // Password confirmation validation
        confirmPasswordField.addEventListener('input', validatePasswordConfirm);
        
        function validatePasswordConfirm() {
            const password = passwordField.value;
            const confirmPassword = confirmPasswordField.value;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    confirmPasswordField.classList.remove('is-invalid');
                    confirmPasswordField.classList.add('is-valid');
                } else {
                    confirmPasswordField.classList.remove('is-valid');
                    confirmPasswordField.classList.add('is-invalid');
                }
            } else {
                confirmPasswordField.classList.remove('is-valid', 'is-invalid');
            }
        }
    });
    </script>
</body>
</html>