<!DOCTYPE html>
<html lang="en">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="csrf-hash" content="<?= csrf_hash() ?>">
    <!-- Cache Control - Force fresh load -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= $title ?? 'MGOD LMS' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
          crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" 
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" 
          crossorigin="anonymous">
    <style>
        :root {
            --brand-primary: #2563eb;
            --brand-soft: #eff6ff;
            --surface-base: #eff6ff;
            --surface-light: #f8fafc;
            --text-dark: #0f172a;
            --text-muted: #475569;
            --border-soft: #d9e5fb;
            --hover-soft: #f1f5f9;
        }

        html,
        body {
            font-size: 18px;
        }

        body {
            background-color: var(--surface-light) !important;
            color: var(--text-dark);
        }

        .lms-navbar {
            background-color: var(--surface-base) !important;
            border-bottom: 1px solid var(--border-soft);
        }

        .lms-navbar .navbar-brand {
            color: var(--text-dark) !important;
            font-size: 1.45rem !important;
            letter-spacing: 0.2px;
        }

        .lms-navbar .nav-link,
        .lms-navbar .nav-link,
        .lms-navbar .navbar-toggler {
            color: var(--text-dark) !important;
        }

        .lms-navbar .nav-link {
            border-radius: 8px;
            font-size: 0.875rem;
            padding-top: 0.45rem;
            padding-bottom: 0.45rem;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .lms-navbar .nav-link:hover,
        .lms-navbar .nav-link:focus {
            background-color: var(--hover-soft);
            color: var(--text-dark) !important;
        }

        .lms-navbar .navbar-nav .nav-link.active {
            background-color: var(--brand-soft);
            color: var(--brand-primary) !important;
        }

        .lms-navbar .dropdown-menu {
            background-color: #ffffff;
            border: 1px solid var(--border-soft);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        .lms-navbar .dropdown-item {
            color: var(--text-dark);
            font-size: 0.85rem;
            padding: 0.5rem 0.9rem;
        }

        .lms-navbar .dropdown-item:hover,
        .lms-navbar .dropdown-item:focus {
            background-color: var(--hover-soft);
            color: var(--text-dark);
        }

        .lms-navbar .dropdown-item.active,
        .lms-navbar .dropdown-item:active {
            background-color: var(--brand-soft);
            color: var(--brand-primary);
        }

        .role-chip {
            background-color: var(--brand-soft) !important;
            color: var(--brand-primary) !important;
            border: 1px solid #bfdbfe;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .avatar-chip {
            background-color: var(--brand-soft) !important;
            color: var(--brand-primary) !important;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .lms-navbar .navbar-toggler {
            border-color: rgba(15, 23, 42, 0.16) !important;
        }

        .lms-navbar .navbar-toggler-icon {
            filter: none;
        }

        .lms-navbar #notificationBadge {
            background-color: var(--brand-primary) !important;
            color: #ffffff !important;
        }

        .lms-navbar .btn-outline-light {
            color: var(--brand-primary) !important;
            border-color: #bfdbfe !important;
            background-color: #ffffff;
            font-size: 0.84rem;
        }

        .lms-navbar .btn-outline-light:hover,
        .lms-navbar .btn-outline-light:focus {
            color: var(--brand-primary) !important;
            background-color: var(--brand-soft) !important;
            border-color: #93c5fd !important;
        }

        .lms-navbar .dropdown-item i {
            display: none;
        }

        .lms-navbar #notificationsList i {
            display: none;
        }

        .lms-navbar #notificationsList .alert {
            background-color: #ffffff !important;
            border-left: 3px solid var(--brand-primary) !important;
            color: var(--text-dark);
        }

        .lms-navbar #notificationsList .btn-primary {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            font-size: 0.78rem;
        }

        .lms-navbar #notificationsList .btn-outline-secondary {
            color: var(--text-muted);
            border-color: var(--border-soft);
            font-size: 0.78rem;
        }

        .lms-role-view {
            --rv-surface: #ffffff;
            --rv-surface-soft: #f8fbff;
            --rv-border: #dbe4ef;
            --rv-text: #0f172a;
            --rv-text-soft: #475569;
            --rv-hover: #f4f7fb;
            background-color: #f8fafc;
            color: var(--rv-text);
        }

        .lms-role-view .role-hero {
            background-color: var(--rv-surface-soft) !important;
            border: 1px solid var(--rv-border);
        }

        .lms-role-view .role-stats .card,
        .lms-role-view .role-section-card {
            border: 1px solid var(--rv-border) !important;
            background: var(--rv-surface) !important;
        }

        .lms-role-view .role-action-bar {
            border: 1px solid var(--rv-border);
            background: var(--rv-surface);
        }

        .lms-role-view .role-empty-state {
            border: 1px dashed var(--rv-border);
            background: var(--rv-surface-soft);
        }

        body.role-admin {
            --rv-accent: #2563eb;
        }

        body.role-student {
            --rv-accent: #0ea5e9;
        }

        body.role-teacher {
            --rv-accent: #059669;
        }

        /* ========================================
           LMS Dashboard Styling - Clean Minimal Design
           Applied to: Student pages for consistent UX
           ======================================== */
        .lms-dashboard {
            --brand-primary: #2563eb;
            --brand-soft: #eef4ff;
            --dashboard-bg: #f8fafc;
            --text-main: #0f172a;
            --text-soft: #475569;
            --surface-soft: #ffffff;
            --border-soft: #dbe4ef;
            --hover-soft: #eef2f7;
            background-color: var(--dashboard-bg);
            color: var(--text-main);
        }

        .lms-dashboard h1,
        .lms-dashboard h2,
        .lms-dashboard h3,
        .lms-dashboard h4,
        .lms-dashboard h5,
        .lms-dashboard h6,
        .lms-dashboard p,
        .lms-dashboard .card-title,
        .lms-dashboard .card-text,
        .lms-dashboard .fw-bold,
        .lms-dashboard .fw-semibold {
            color: var(--text-main);
            letter-spacing: -0.01em;
        }

        .lms-dashboard h2 {
            font-size: 1.875rem;
        }

        .lms-dashboard h5 {
            font-size: 1.125rem;
        }

        .lms-dashboard h6 {
            font-size: 0.95rem;
        }

        .lms-dashboard p {
            font-size: 0.98rem;
        }

        .lms-dashboard small,
        .lms-dashboard .text-muted {
            color: var(--text-soft) !important;
        }

        .lms-dashboard .text-white {
            color: var(--text-main) !important;
        }

        /* Hide emoji display elements but preserve button icons */
        .lms-dashboard .display-4 {
            display: none !important;
        }
        
        /* Preserve icons in buttons and action elements */
        .lms-dashboard .btn i,
        .lms-dashboard .btn-group i,
        .lms-dashboard a i,
        .lms-dashboard button i {
            display: inline-block !important;
        }

        .lms-dashboard .card {
            border-radius: 12px;
            border: 1px solid var(--border-soft) !important;
            background-color: var(--surface-soft);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .lms-dashboard .card-header {
            border-bottom: 1px solid var(--border-soft);
            background-color: var(--surface-soft);
        }

        .lms-dashboard .card-body.bg-primary {
            background-color: var(--surface-soft) !important;
            border: 1px solid var(--border-soft);
        }

        .lms-dashboard .card.text-white {
            background-color: var(--surface-soft) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-soft) !important;
        }

        .lms-dashboard .card.text-white::before {
            content: none;
        }

        .lms-dashboard .card.text-white small {
            color: var(--text-soft) !important;
        }

        .lms-dashboard .display-5 {
            font-size: 2.25rem;
            line-height: 1;
            margin-bottom: 0.35rem;
        }

        /* Neutralize all Bootstrap color classes to soft professional tones */
        .lms-dashboard .card.text-white.bg-primary,
        .lms-dashboard .card.text-white.bg-success,
        .lms-dashboard .card.text-white.bg-info,
        .lms-dashboard .card.text-white.bg-warning,
        .lms-dashboard .card.text-white.bg-danger,
        .lms-dashboard .card.text-white.bg-secondary,
        .lms-dashboard .badge.bg-primary,
        .lms-dashboard .badge.bg-success,
        .lms-dashboard .badge.bg-info,
        .lms-dashboard .badge.bg-warning,
        .lms-dashboard .badge.bg-danger,
        .lms-dashboard .badge.bg-secondary {
            background-color: var(--surface-soft) !important;
            border-color: var(--border-soft) !important;
            color: var(--text-main) !important;
        }

        .lms-dashboard .bg-success.bg-opacity-10 {
            background-color: #ecfdf3 !important;
            border: 1px solid #d1f2de;
        }

        .lms-dashboard .bg-warning.bg-opacity-10 {
            background-color: #fff8e8 !important;
            border: 1px solid #fae8bd;
        }

        .lms-dashboard .bg-info.bg-opacity-10 {
            background-color: #eef6ff !important;
            border: 1px solid #d7e8ff;
        }

        .lms-dashboard .bg-primary.bg-opacity-10 {
            background-color: #eef4ff !important;
            border: 1px solid #d7e5ff;
        }

        .lms-dashboard .bg-danger.bg-opacity-10,
        .lms-dashboard .bg-secondary.bg-opacity-10 {
            background-color: #f5f7fb !important;
            border: 1px solid #e5ebf3;
        }

        .lms-dashboard .progress-bar {
            background-color: var(--brand-primary) !important;
        }

        .lms-dashboard .text-primary,
        .lms-dashboard .text-info {
            color: #1d4ed8 !important;
        }

        .lms-dashboard .text-success {
            color: #15803d !important;
        }

        .lms-dashboard .text-warning {
            color: #b45309 !important;
        }

        .lms-dashboard .text-danger {
            color: #b91c1c !important;
        }

        .lms-dashboard .btn-primary {
            background-color: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
            color: #ffffff !important;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .lms-dashboard .btn-primary:hover,
        .lms-dashboard .btn-primary:focus {
            background-color: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
        }

        .lms-dashboard .btn-outline-primary,
        .lms-dashboard .btn-outline-success,
        .lms-dashboard .btn-outline-info,
        .lms-dashboard .btn-outline-warning,
        .lms-dashboard .btn-outline-danger,
        .lms-dashboard .btn-outline-secondary {
            color: var(--text-soft) !important;
            border-color: var(--border-soft) !important;
            background-color: var(--surface-soft);
            font-size: 0.85rem;
        }

        .lms-dashboard .btn-outline-primary:hover,
        .lms-dashboard .btn-outline-success:hover,
        .lms-dashboard .btn-outline-info:hover,
        .lms-dashboard .btn-outline-warning:hover,
        .lms-dashboard .btn-outline-danger:hover,
        .lms-dashboard .btn-outline-secondary:hover {
            color: var(--text-main) !important;
            background-color: var(--hover-soft) !important;
            border-color: var(--text-soft) !important;
        }

        .lms-dashboard .border-warning,
        .lms-dashboard .border-success,
        .lms-dashboard .border-info,
        .lms-dashboard .border-danger,
        .lms-dashboard .border-primary {
            border-color: var(--border-soft) !important;
        }

        .lms-dashboard .alert-success,
        .lms-dashboard .alert-info,
        .lms-dashboard .alert-warning,
        .lms-dashboard .alert-danger {
            background-color: var(--surface-soft);
            border-color: var(--border-soft);
            color: var(--text-main);
        }

        .lms-dashboard .bg-light {
            background-color: var(--surface-soft) !important;
            color: var(--text-main) !important;
        }

        .lms-dashboard .opacity-75 {
            opacity: 1 !important;
            color: var(--text-soft) !important;
        }

        /* Responsive adjustments for mobile/tablet */
        @media (max-width: 768px) {
            .lms-dashboard {
                font-size: 13px;
            }

            .lms-dashboard h2 {
                font-size: 1.5rem;
            }

            .lms-dashboard .display-5 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<?php
$session = \Config\Services::session();
$userRole = $session->get('role');
$isLoggedIn = $session->get('isLoggedIn');
$allowedRoles = ['admin', 'teacher', 'student'];
$normalizedRole = strtolower((string) $userRole);
$roleClass = ($isLoggedIn && in_array($normalizedRole, $allowedRoles, true))
    ? 'role-' . $normalizedRole
    : 'role-guest';

$request = \Config\Services::request();
$currentAction = $request->getGet('action');
$currentUri = uri_string();
?>
<body class="bg-light <?= esc($roleClass, 'attr') ?>">
    
    <nav class="navbar navbar-expand-lg navbar-light lms-navbar shadow-sm">
        <div class="container">            
            <a class="navbar-brand fw-bold fs-4" href="<?= $isLoggedIn ? base_url($userRole . '/dashboard') : base_url() ?>">
                MGOD LMS
                <?php if ($isLoggedIn): ?>
                    <span class="badge role-chip ms-2 rounded-pill">
                        <?= ucfirst($userRole) ?>
                    </span>
                <?php endif; ?>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">                  
                <ul class="navbar-nav align-items-lg-center ms-auto">
                    <?php if ($isLoggedIn): ?>                       
                         <li class="nav-item">
                            <?php
                            // Create role-based dashboard URL
                            $dashboardUrl = base_url($userRole . '/dashboard');
                            $isDashboardActive = (strpos($currentUri, $userRole . '/dashboard') !== false) || ($currentUri === 'dashboard');
                            ?>
                            <a class="nav-link px-3 fw-bold <?= $isDashboardActive ? 'active' : '' ?>" href="<?= $dashboardUrl ?>">
                                Dashboard
                            </a>
                        </li>
                        
                        <?php if ($userRole === 'admin'): ?>
                            <!-- Admin Navigation -->
                            
                            <!-- Users Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 fw-bold <?= (strpos($currentUri, 'admin/manage_users') !== false || strpos($currentUri, 'admin/manage_departments') !== false || strpos($currentUri, 'admin/manage_terms') !== false) ? 'active' : '' ?>" 
                                   href="#" 
                                   id="usersDropdown" 
                                   role="button" 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false">
                                    Users
                                </a>
                                <ul class="dropdown-menu shadow" aria-labelledby="usersDropdown">
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_users') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_users') ?>">
                                            <i class="fas fa-users me-2"></i>Manage Users
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_departments') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_departments') ?>">
                                            <i class="fas fa-building me-2"></i>Manage Departments
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_terms') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_terms') ?>">
                                            <i class="fas fa-calendar-alt me-2"></i>Manage Terms
                                        </a>
                                    </li>
                                </ul>
                            </li>                            
                            <!-- Courses Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 fw-bold <?= (strpos($currentUri, 'admin/manage_courses') !== false || strpos($currentUri, 'admin/manage_prerequisite') !== false || strpos($currentUri, 'admin/manage_offerings') !== false || strpos($currentUri, 'admin/manage_course_instructors') !== false || strpos($currentUri, 'admin/course/') !== false) ? 'active' : '' ?>" 
                                   href="#" 
                                   id="coursesDropdown" 
                                   role="button" 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false">
                                    Courses
                                </a>
                                <ul class="dropdown-menu shadow" aria-labelledby="coursesDropdown">
                                    <li>
                                        <a class="dropdown-item <?= ($currentUri === 'admin/manage_courses' || (strpos($currentUri, 'admin/manage_courses') !== false && strpos($currentUri, 'schedule') === false && strpos($currentUri, 'instructor') === false)) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_courses') ?>">
                                            <i class="fas fa-book me-2"></i>Manage Courses
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_prerequisites') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_prerequisites') ?>">
                                            <i class="fas fa-link me-2"></i>Manage Courses Prerequisite
                                        </a>
                                    </li>                                    
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_offerings') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_offerings') ?>">
                                            <i class="fas fa-calendar-check me-2"></i>Manage Course Offerings
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/course/') !== false && strpos($currentUri, '/upload') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_offerings') ?>">
                                            <i class="fas fa-folder-open me-2"></i>Course Materials
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_courses_schedule') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_courses_schedule') ?>">
                                            <i class="fas fa-calendar me-2"></i>Manage Courses Schedule
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_course_instructors') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_course_instructors') ?>">
                                            <i class="fas fa-chalkboard-teacher me-2"></i>Assign Teacher to Courses
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            
                            <!-- Enrollments Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 fw-bold <?= (strpos($currentUri, 'admin/manage_programs') !== false || strpos($currentUri, 'admin/manage_curriculum') !== false || strpos($currentUri, 'admin/manage_enrollments') !== false) ? 'active' : '' ?>" 
                                   href="#" 
                                   id="enrollmentsDropdown" 
                                   role="button" 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false">
                                    Enrollments
                                </a>
                                <ul class="dropdown-menu shadow" aria-labelledby="enrollmentsDropdown">
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_programs') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_programs') ?>">
                                            <i class="fas fa-graduation-cap me-2"></i>Manage Programs
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_curriculum') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_curriculum') ?>">
                                            <i class="fas fa-file-alt me-2"></i>Manage Programs Curriculum
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_enrollments') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_enrollments') ?>">
                                            <i class="fas fa-user-check me-2"></i>Manage Enrollments
                                        </a>
                                    </li>
                                </ul>
                            </li>
                              <!-- Assignments Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 fw-bold <?= (strpos($currentUri, 'admin/manage_assignment_types') !== false || strpos($currentUri, 'admin/manage_grading_periods') !== false || strpos($currentUri, 'admin/manage_grade_components') !== false) ? 'active' : '' ?>" 
                                   href="#" 
                                   id="assignmentsDropdown" 
                                   role="button" 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false">
                                    Assignments
                                </a>
                                <ul class="dropdown-menu shadow" aria-labelledby="assignmentsDropdown">
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_assignment_types') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_assignment_types') ?>">
                                            <i class="fas fa-tasks me-2"></i>Manage Assignment Types
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_grading_periods') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_grading_periods') ?>">
                                            <i class="fas fa-clock me-2"></i>Manage Grading Periods
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_grade_components') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_grade_components') ?>">
                                            <i class="fas fa-chart-bar me-2"></i>Manage Grade Components
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/manage_assignments') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/manage_assignments') ?>">
                                            <i class="fas fa-tasks me-2"></i>Manage Assignments
                                        </a>
                                    </li>
                                    
                                </ul>
                            </li>

                            <!-- Gradebook Dropdown (Admin) -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 fw-bold <?= (strpos($currentUri, 'admin/gradebook') !== false) ? 'active' : '' ?>" 
                                   href="#" 
                                   id="adminGradebookDropdown" 
                                   role="button" 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false">
                                    Gradebook
                                </a>
                                <ul class="dropdown-menu shadow" aria-labelledby="adminGradebookDropdown">
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/gradebook/overview') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/gradebook/overview') ?>">
                                            <i class="fas fa-tachometer-alt me-2"></i>System Overview
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/gradebook/analytics') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/gradebook/analytics') ?>">
                                            <i class="fas fa-chart-line me-2"></i>Grade Analytics
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'admin/gradebook/audit') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('admin/gradebook/audit') ?>">
                                            <i class="fas fa-history me-2"></i>Audit Trail
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            
                            <?php elseif ($userRole === 'teacher'): ?>
                            <!-- Teacher Navigation -->
                            
                            <!-- Courses Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 fw-bold <?= (strpos($currentUri, 'teacher/courses') !== false || strpos($currentUri, 'teacher/all_courses') !== false) ? 'active' : '' ?>" 
                                   href="#" 
                                   id="teacherCoursesDropdown" 
                                   role="button" 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false">
                                    Courses
                                </a>                                
                                <ul class="dropdown-menu shadow" aria-labelledby="teacherCoursesDropdown">
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'teacher/courses') !== false && strpos($currentUri, 'all_courses') === false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('teacher/courses') ?>">
                                            <i class="fas fa-chalkboard-teacher me-2"></i>Courses I Teach
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'teacher/course/') !== false && strpos($currentUri, '/upload') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('teacher/courses') ?>">
                                            <i class="fas fa-folder-open me-2"></i>Course Materials
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            
                            <!-- Enrollment Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 fw-bold <?= (strpos($currentUri, 'teacher/enroll_student') !== false || strpos($currentUri, 'teacher/enrolled_students') !== false) ? 'active' : '' ?>" 
                                   href="#" 
                                   id="teacherEnrollmentDropdown" 
                                   role="button" 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false">
                                    Enrollment
                                </a>
                                <ul class="dropdown-menu shadow" aria-labelledby="teacherEnrollmentDropdown">
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'teacher/enroll_student') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('teacher/enroll_student') ?>">
                                            <i class="fas fa-user-plus me-2"></i>Enroll a Student
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'teacher/enrolled_students') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('teacher/enrolled_students') ?>">
                                            <i class="fas fa-users me-2"></i>List of Enrolled Students
                                        </a>
                                    </li>
                                </ul>
                            </li>
                              <!-- Assignments Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 fw-bold <?= (strpos($currentUri, 'teacher/assignments') !== false || strpos($currentUri, 'teacher/submissions') !== false) ? 'active' : '' ?>" 
                                   href="#" 
                                   id="teacherAssignmentsDropdown" 
                                   role="button" 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false">
                                    Assignments
                                </a>
                                <ul class="dropdown-menu shadow" aria-labelledby="teacherAssignmentsDropdown">
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'teacher/assignments') !== false && strpos($currentUri, 'create') === false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('teacher/assignments') ?>">
                                            <i class="fas fa-list me-2"></i>Manage Assignments
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'teacher/submissions') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('teacher/submissions') ?>">
                                            <i class="fas fa-inbox me-2"></i>View Submissions
                                        </a>
                                    </li>
                                </ul>
                            </li>
                              <!-- Gradebook -->
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold <?= (strpos($currentUri, 'teacher/gradebook') !== false) ? 'active' : '' ?>" 
                                   href="<?= base_url('teacher/gradebook') ?>">
                                    Gradebook
                                </a>
                            </li>


                                  <?php elseif ($userRole === 'student'): ?>
                        <!-- Student Navigation -->                            <!-- My Courses Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 fw-bold <?= (strpos($currentUri, 'student/courses') !== false || strpos($currentUri, 'student/materials') !== false || strpos($currentUri, 'student/course/') !== false) ? 'active' : '' ?>" 
                                   href="#" 
                                   id="studentCoursesDropdown" 
                                   role="button" 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false">
                                    My Courses
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="studentCoursesDropdown">
                                    <li>
                                        <a class="dropdown-item <?= ($currentUri === 'student/courses') ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('student/courses') ?>">
                                            <i class="fas fa-graduation-cap me-2"></i>Enrolled Courses
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'student/materials') !== false || strpos($currentUri, 'student/course/') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('student/materials') ?>">
                                            <i class="fas fa-folder-open me-2"></i>Course Materials
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold <?= (strpos($currentUri, 'student/assignments') !== false) ? 'active' : '' ?>" href="<?= base_url('student/assignments') ?>">
                                    Assignments
                                </a>
                            </li>
                            <!-- Grades Dropdown (Student) -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 fw-bold <?= (strpos($currentUri, 'student/grades') !== false || strpos($currentUri, 'student/gradebook') !== false) ? 'active' : '' ?>" 
                                   href="#" 
                                   id="studentGradesDropdown" 
                                   role="button" 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false">
                                    Grades
                                </a>
                                <ul class="dropdown-menu shadow" aria-labelledby="studentGradesDropdown">
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'student/grades') !== false && strpos($currentUri, 'gradebook') === false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('student/grades') ?>">
                                            <i class="fas fa-chart-bar me-2"></i>Grade Summary
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= (strpos($currentUri, 'student/gradebook') !== false) ? 'active fw-bold' : '' ?>" 
                                           href="<?= base_url('student/gradebook') ?>">
                                            <i class="fas fa-book-open me-2"></i>Detailed Gradebook
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold <?= (strpos($currentUri, 'student/schedule') !== false) ? 'active' : '' ?>" href="<?= base_url('student/schedule') ?>">
                                    Schedule
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Public Navigation -->
                        <li class="nav-item">
                            <a class="nav-link px-3 fw-bold" href="<?= base_url() ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 fw-bold" href="<?= base_url('about') ?>">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 fw-bold" href="<?= base_url('contact') ?>">Contact</a>
                        </li>
                    <?php endif; ?>
                </ul>                
                <ul class="navbar-nav align-items-lg-center ms-lg-3">
                    <?php if ($isLoggedIn): ?>
                        <!-- Notifications Dropdown with jQuery/Bootstrap Integration -->
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell fs-5"></i>
                                <!-- Badge to show unread count - initially hidden -->
                                <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                                    0
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            </a>
                            <!-- Dropdown menu to list notifications - initially empty -->
                            <ul class="dropdown-menu dropdown-menu-end shadow" id="notificationsList" aria-labelledby="notificationsDropdown" style="min-width: 350px; max-height: 450px; overflow-y: auto;">
                                <!-- Notifications will be loaded here dynamically via jQuery -->
                                <li class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mb-0 small text-muted mt-2">Loading notifications...</p>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Logged In User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="badge avatar-chip me-2 rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                    <?= strtoupper(substr($session->get('name'), 0, 1)) ?>
                                </span>
                                <?= esc($session->get('name')) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <h6 class="dropdown-header text-center">
                                        <span class="badge bg-primary rounded-pill">
                                            <?= ucfirst($userRole) ?>
                                        </span>
                                    </h6>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item fw-semibold" href="#">Profile</a></li>
                                <li><a class="dropdown-item fw-semibold" href="#">Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger fw-bold" href="<?= base_url('logout') ?>">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Public User Menu -->
                        <li class="nav-item">
                            <a class="nav-link fw-bold px-3" href="<?= base_url('login') ?>">
                                Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light rounded-pill ms-2 px-3 fw-bold" href="<?= base_url('register') ?>">
                                Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    </div>    
    <!-- Include jQuery before Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
            crossorigin="anonymous"></script>    
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
            crossorigin="anonymous"></script>
    
    <!-- Meta tag for base URL (used by notifications.js) -->
    <meta name="base-url" content="<?= base_url() ?>">
      <?php if ($isLoggedIn): ?>
    <!-- Notification System - Enhanced with Real-time Updates -->
    <script src="<?= base_url('public/js/notifications.js') ?>"></script>
    <script>
        $(document).ready(function() {
            // Step 6.1: Load notifications on page load
            loadNotifications();
            
            // Reload notifications when dropdown is opened
            $('#notificationsDropdown').on('click', function() {
                loadNotifications();
            });
            
            // Step 6.2: Auto-refresh notifications every 60 seconds (real-time updates simulation)
            setInterval(loadNotifications, 60000); // 60000ms = 60 seconds
        });
        
        /**
         * Load notifications using jQuery AJAX
         * Uses $.get() to call /notifications endpoint
         */
        function loadNotifications() {
            $.get('<?= base_url('notifications') ?>', function(data) {
                if (data.success) {
                    // Update badge count
                    updateNotificationBadge(data.unread_count);
                    
                    // Populate dropdown menu with notifications
                    populateNotifications(data.notifications);
                }
            }).fail(function(xhr, status, error) {
                console.error('Failed to load notifications:', error);
                $('#notificationsList').html(`
                    <li class="text-center py-3">
                        <i class="fas fa-exclamation-triangle text-danger fs-3"></i>
                        <p class="mb-0 small text-danger mt-2">Failed to load notifications</p>
                    </li>
                `);
            });
        }
        
        /**
         * Update notification badge count
         * If count is 0, hide badge; otherwise show it
         */
        function updateNotificationBadge(count) {
            const badge = $('#notificationBadge');
            
            if (count > 0) {
                // Show badge with count
                badge.text(count > 99 ? '99+' : count);
                badge.show();
            } else {
                // Hide badge when count is 0
                badge.hide();
            }
        }
          /**
         * Populate dropdown menu with notification list
         * Uses Bootstrap alert classes for styling
         */
        function populateNotifications(notifications) {
            const list = $('#notificationsList');
            list.empty();
            
            if (notifications.length === 0) {
                // No notifications - show empty state
                list.html(`
                    <li class="px-3 py-4 text-center">
                        <i class="fas fa-bell-slash text-muted fs-2 mb-2"></i>
                        <p class="mb-0 text-muted">No notifications</p>
                        <small class="text-muted">You're all caught up!</small>
                    </li>
                `);
                return;
            }
            
            // Add each notification to the list
            notifications.forEach(function(notification, index) {
                const alertClass = notification.is_unread ? 'alert-info' : 'alert-light';
                const boldClass = notification.is_unread ? 'fw-bold' : '';
                const iconColor = notification.is_unread ? 'text-primary' : 'text-muted';
                
                const notificationHtml = `
                    <li id="notification-${notification.id}" class="border-bottom">
                        <div class="alert ${alertClass} mb-0 rounded-0 border-0" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle ${iconColor} me-2 mt-1"></i>
                                <div class="flex-grow-1">
                                    <p class="mb-1 small ${boldClass}">
                                        ${escapeHtml(notification.message)}
                                    </p>
                                    <small class="text-muted d-block">
                                        <i class="fas fa-clock me-1"></i>
                                        ${formatToPhilippineTime(notification.created_at || notification.formatted_date)}
                                    </small>
                                    <div class="mt-2">
                                        ${notification.is_unread ? `
                                            <button class="btn btn-sm btn-primary me-1 mark-read-btn" 
                                                    data-id="${notification.id}"
                                                    onclick="markAsRead(${notification.id})">
                                                <i class="fas fa-check me-1"></i>
                                                Mark as Read
                                            </button>
                                        ` : `
                                            <span class="badge bg-success me-1">
                                                <i class="fas fa-check-circle"></i> Read
                                            </span>
                                        `}
                                        <button class="btn btn-sm btn-outline-secondary dismiss-btn" 
                                                data-id="${notification.id}"
                                                onclick="dismissNotification(${notification.id})">
                                            <i class="fas fa-times me-1"></i>
                                            Dismiss
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                `;
                
                list.append(notificationHtml);
            });
        }
        
        /**
         * Mark notification as read
         * Uses $.post() to call /notifications/mark_read/{id} endpoint
         * Upon success, removes notification from list and updates badge
         */
        function markAsRead(notificationId) {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const csrfHash = $('meta[name="csrf-hash"]').attr('content');
            
            // Disable the button to prevent double-clicks
            $(`button[data-id="${notificationId}"]`).prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm me-1"></span>
                Marking...
            `);
            
            $.post(
                '<?= base_url('notifications/mark_read/') ?>' + notificationId,
                JSON.stringify({ [csrfToken]: csrfHash }),
                function(data) {
                    if (data.success) {
                        // Remove notification from list with fade effect
                        $(`#notification-${notificationId}`).fadeOut(300, function() {
                            $(this).remove();
                            
                            // Update badge count
                            updateNotificationBadge(data.unread_count);
                            
                            // If no notifications left, show empty state
                            if ($('#notificationsList li').length === 0) {
                                $('#notificationsList').html(`
                                    <li class="px-3 py-4 text-center">
                                        <i class="fas fa-bell-slash text-muted fs-2 mb-2"></i>
                                        <p class="mb-0 text-muted">No notifications</p>
                                        <small class="text-muted">You're all caught up!</small>
                                    </li>
                                `);
                            }
                        });
                        
                        // Show success toast (optional)
                        showToast('Success', 'Notification marked as read', 'success');
                    } else {
                        // Show error message
                        alert('Failed to mark notification as read: ' + data.message);
                        // Re-enable button
                        $(`button[data-id="${notificationId}"]`).prop('disabled', false).html(`
                            <i class="fas fa-check me-1"></i> Mark as Read
                        `);
                    }
                },
                'json'
            ).fail(function(xhr, status, error) {
                console.error('Error marking notification as read:', error);
                alert('An error occurred while marking the notification as read');
                // Re-enable button
                $(`button[data-id="${notificationId}"]`).prop('disabled', false).html(`
                    <i class="fas fa-check me-1"></i> Mark as Read
                `);
            });
        }
        
        /**
         * Dismiss notification (hide it from view)
         * Uses $.post() to call /notifications/hide/{id} endpoint
         * Upon success, removes notification from list and updates badge
         */
        function dismissNotification(notificationId) {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const csrfHash = $('meta[name="csrf-hash"]').attr('content');
            
            // Disable the button to prevent double-clicks
            $(`button.dismiss-btn[data-id="${notificationId}"]`).prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm me-1"></span>
                Dismissing...
            `);
            
            $.post(
                '<?= base_url('notifications/hide/') ?>' + notificationId,
                JSON.stringify({ [csrfToken]: csrfHash }),
                function(data) {
                    if (data.success) {
                        // Remove notification from list with fade effect
                        $(`#notification-${notificationId}`).fadeOut(300, function() {
                            $(this).remove();
                            
                            // Update badge count
                            updateNotificationBadge(data.unread_count);
                            
                            // If no notifications left, show empty state
                            if ($('#notificationsList li').length === 0) {
                                $('#notificationsList').html(`
                                    <li class="px-3 py-4 text-center">
                                        <i class="fas fa-bell-slash text-muted fs-2 mb-2"></i>
                                        <p class="mb-0 text-muted">No notifications</p>
                                        <small class="text-muted">You're all caught up!</small>
                                    </li>
                                `);
                            }
                        });
                        
                        // Show success toast (optional)
                        showToast('Success', 'Notification dismissed', 'success');
                    } else {
                        // Show error message
                        alert('Failed to dismiss notification: ' + data.message);
                        // Re-enable button
                        $(`button.dismiss-btn[data-id="${notificationId}"]`).prop('disabled', false).html(`
                            <i class="fas fa-times me-1"></i> Dismiss
                        `);
                    }
                },
                'json'
            ).fail(function(xhr, status, error) {
                console.error('Error dismissing notification:', error);
                alert('An error occurred while dismissing the notification');
                // Re-enable button
                $(`button.dismiss-btn[data-id="${notificationId}"]`).prop('disabled', false).html(`
                    <i class="fas fa-times me-1"></i> Dismiss
                `);
            });
        }
        
        /**
         * Helper function to escape HTML and prevent XSS
         */
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        /**
         * Optional: Show toast notification
         */
        function showToast(title, message, type = 'info') {
            // Simple console log for now - can be enhanced with Bootstrap toasts
            console.log(`[${type.toUpperCase()}] ${title}: ${message}`);
        }

        function formatToPhilippineTime(dateString) {
            if (!dateString) return '';
            // If dateString is "YYYY-MM-DD HH:MM:SS", convert to "YYYY-MM-DDTHH:MM:SSZ" (treat as UTC)
            let isoString = dateString.replace(' ', 'T');
            if (!isoString.endsWith('Z') && !isoString.includes('+')) {
                isoString += 'Z';
            }
            const date = new Date(isoString);
            // Format for Asia/Manila
            const options = {
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            };
            return date.toLocaleString('en-PH', options);
        }
    </script>
    <?php endif; ?>
    <!-- Initialize Bootstrap Tooltips Globally -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all tooltips on page load
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover focus',
                delay: { show: 500, hide: 100 },
                placement: 'top'
            });
        });
    });
    </script>
</body>
</html>
