<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MGOD LMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-white text-dark">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url() ?>">MGOD LMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url() ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('about') ?>">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= base_url('contact') ?>">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('login') ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('register') ?>">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-4">Contact Us</h3>
                    <p class="lead mb-5">Get in touch with our MGOD LMS team.</p>
                    
                    <div class="mb-3">
                        <h6 class="fw-bold">Administrator</h6>
                        <p class="mb-1">Marjovic Prato Alejado</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">Email</h6>
                        <p class="mb-1">
                            <a href="mailto:marjovic_alejado@lms.com" class="text-decoration-none text-dark">
                                marjovic_alejado@lms.com
                            </a>
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">Phone</h6>
                        <p class="mb-1">
                            <a href="tel:+639391520886" class="text-decoration-none text-dark">
                                +639391520886
                            </a>
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">Address</h6>
                        <p class="mb-1">Buayan, General Santos City, Philippines</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>