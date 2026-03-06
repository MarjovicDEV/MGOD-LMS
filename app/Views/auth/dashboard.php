<?= $this->include('templates/header') ?>

<!-- Unified Dashboard View - This single file handles all user roles (Admin, Teacher, Student) -->
<!-- Uses conditional PHP statements to show different content based on user's role -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        
        <!-- Dynamic Header Section - Changes based on user role -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <!-- Admin Header -->
                        <?php if ($user['role'] === 'admin'): ?>
                            <h2 class="mb-2 fw-bold">📊 Admin Dashboard</h2>
                            <p class="mb-0 opacity-75">Welcome back, <?= esc($user['name']) ?>! Manage your learning management system with powerful tools.</p>
                        <!-- Teacher Header -->
                        <?php elseif ($user['role'] === 'teacher'): ?>
                            <h2 class="mb-2 fw-bold">👨‍🏫 Teacher Dashboard</h2>
                            <p class="mb-0 opacity-75">Welcome back, <?= esc($user['name']) ?>! Manage your courses and students with ease.</p>
                        <!-- Student Header -->
                        <?php elseif ($user['role'] === 'student'): ?>
                            <h2 class="mb-2 fw-bold">🎓 Student Dashboard</h2>
                            <p class="mb-0 opacity-75">Welcome back, <?= esc($user['name']) ?>! Continue your learning journey and achieve your goals.</p>
                        <!-- Default Header for unknown roles -->
                        <?php else: ?>
                            <h2 class="mb-2 fw-bold">🏠 Dashboard</h2>
                            <p class="mb-0 opacity-75">Welcome back, <?= esc($user['name']) ?>!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards Section - Different stats based on user role -->
        <div class="row mb-4">
            
            <!-- ADMIN STATISTICS CARDS -->
            <?php if ($user['role'] === 'admin'): ?>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">👥</div>
                        <div class="display-5 fw-bold"><?= $totalUsers ?></div>
                        <div class="fw-semibold">Total Users</div>
                        <small class="opacity-75">Active in system</small>
                    </div>
                </div>                
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📚</div>
                        <div class="display-5 fw-bold"><?= $totalCourses ?? '0' ?></div>
                        <div class="fw-semibold">Total Courses</div>
                        <small class="opacity-75"><?= ($activeCourses ?? '0') ?> active, <?= ($draftCourses ?? '0') ?> draft</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">👨‍🏫</div>
                        <div class="display-5 fw-bold"><?= $totalTeachers ?></div>
                        <div class="fw-semibold">Teachers</div>
                        <small class="opacity-75">Creating content</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">🎓</div>
                        <div class="display-5 fw-bold"><?= $totalStudents ?></div>
                        <div class="fw-semibold">Students</div>
                        <small class="opacity-75">Learning actively</small>
                    </div>
                </div>            
                <!-- TEACHER STATISTICS CARDS -->
            <?php elseif ($user['role'] === 'teacher'): ?>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📚</div>
                        <div class="display-5 fw-bold"><?= $totalCourses ?? '0' ?></div>
                        <div class="fw-semibold">My Courses</div>
                        <small class="opacity-75"><?= ($activeCourses ?? '0') ?> active courses</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">👥</div>
                        <div class="display-5 fw-bold"><?= $totalStudents ?? '0' ?></div>
                        <div class="fw-semibold">My Students</div>
                        <small class="opacity-75">Enrolled in my courses</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📝</div>
                        <div class="display-5 fw-bold"><?= $pendingAssignments ?? '0' ?></div>
                        <div class="fw-semibold">Pending</div>
                        <small class="opacity-75">To be graded</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📊</div>
                        <div class="display-5 fw-bold"><?= $averageGrade ?? '0' ?>%</div>
                        <div class="fw-semibold">Avg Grade</div>
                        <small class="opacity-75">Class average</small>
                    </div>
                </div>

            <!-- STUDENT STATISTICS CARDS -->
            <?php elseif ($user['role'] === 'student'): ?>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📚</div>
                        <div class="display-5 fw-bold"><?= $enrolledCourses ?? '0' ?></div>
                        <div class="fw-semibold">Enrolled Courses</div>
                        <small class="opacity-75">Active learning paths</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">✅</div>
                        <div class="display-5 fw-bold"><?= $completedAssignments ?? '0' ?></div>
                        <div class="fw-semibold">Completed</div>
                        <small class="opacity-75">Assignments finished</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">⏰</div>
                        <div class="display-5 fw-bold"><?= $pendingAssignments ?? '0' ?></div>
                        <div class="fw-semibold">Pending</div>
                        <small class="opacity-75">Awaiting completion</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📊</div>
                        <div class="display-5 fw-bold"><?= $averageGrade ?? '0' ?>%</div>
                        <div class="fw-semibold">Average Grade</div>
                        <small class="opacity-75">Overall performance</small>
                    </div>
                </div>
            <?php endif; ?>        
        </div>        
        <!-- Additional Content Section - Role-specific content -->
        <div class="row">
              <!-- ADMIN ADDITIONAL CONTENT -->
            <?php if ($user['role'] === 'admin'): ?>
                <!-- Course Statistics Breakdown -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">📚 Course Overview</h5>
                            <small class="text-muted">Course distribution by status</small>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-3 bg-success bg-opacity-10 rounded-3">
                                        <div class="me-3">
                                            <span class="badge bg-success rounded-circle p-2">✅</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-success"><?= $activeCourses ?? '0' ?></div>
                                            <small class="text-muted">Active Courses</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-3 bg-warning bg-opacity-10 rounded-3">
                                        <div class="me-3">
                                            <span class="badge bg-warning rounded-circle p-2">📝</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-warning"><?= $draftCourses ?? '0' ?></div>
                                            <small class="text-muted">Draft Courses</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-3 bg-info bg-opacity-10 rounded-3">
                                        <div class="me-3">
                                            <span class="badge bg-info rounded-circle p-2">🎯</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-info"><?= $completedCourses ?? '0' ?></div>
                                            <small class="text-muted">Completed</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-3 bg-primary bg-opacity-10 rounded-3">
                                        <div class="me-3">
                                            <span class="badge bg-primary rounded-circle p-2">📚</span>
                                        </div>                                        <div>
                                            <div class="fw-bold text-primary"><?= $totalCourses ?? '0' ?></div>
                                            <small class="text-muted">Total Courses</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Actions for Admin -->
                            <div class="mt-4">
                                <h6 class="fw-semibold mb-3">🚀 Quick Actions</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="<?= base_url('admin/manage_courses') ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-book me-1"></i>Manage Courses
                                    </a>
                                    <a href="<?= base_url('admin/manage_users') ?>" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-users me-1"></i>Manage Users
                                    </a>
                                    <a href="<?= base_url('admin/manage_courses') ?>" class="btn btn-outline-success btn-sm" 
                                       title="Go to Manage Courses to upload materials for any course">
                                        <i class="fas fa-upload me-1"></i>Upload Materials
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Section -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">⏰ Recent Activity</h5>
                            <small class="text-muted">Latest system activities</small>
                        </div>
                        <div class="card-body pt-3">
                            <?php if (!empty($recentActivities)): ?>   
                                
                                <div class="activity-feed" style="max-height: 400px; overflow-y: auto;">
                                    <?php foreach ($recentActivities as $activity): ?>
                                        <div class="activity-item d-flex align-items-start mb-3 pb-3 border-bottom">
                                            <div class="activity-icon me-3 mt-1">
                                                <span class="badge rounded-circle p-2" style="font-size: 1.2em;">
                                                    <?= $activity['icon'] ?>
                                                </span>
                                            </div>
                                            <div class="activity-content flex-grow-1">
                                                <div class="activity-title fw-semibold text-dark mb-1">
                                                    <?= $activity['title'] ?>
                                                </div>
                                                <div class="activity-description text-muted small mb-1">
                                                    <?= $activity['description'] ?>
                                                </div>                                                <div class="activity-time text-muted" style="font-size: 0.75rem;">
                                                    <?php
                                                    $timeAgo = time() - strtotime($activity['time']);
                                                    if ($timeAgo < 60) {
                                                        echo 'Just now';
                                                    } elseif ($timeAgo < 3600) {
                                                        echo floor($timeAgo / 60) . ' minutes ago';
                                                    } elseif ($timeAgo < 86400) {
                                                        echo floor($timeAgo / 3600) . ' hours ago';
                                                    } elseif ($timeAgo < 2592000) {
                                                        echo floor($timeAgo / 86400) . ' days ago';
                                                    } else {
                                                        echo date('M j, Y', strtotime($activity['time']));
                                                    }
                                                    ?>
                                                </div>
                                            </div>                                            <div class="activity-badge">
                                                <?php                                                // Activity type colors for different activity types
                                                $activityTypeColors = [
                                                    'user_registration' => 'success',   // Green for new registrations
                                                    'user_creation' => 'info',          // Blue for admin-created users
                                                    'user_update' => 'warning',         // Yellow for updates
                                                    'user_deletion' => 'danger',        // Red for deletions
                                                    'course_creation' => 'primary',     // Blue for course creation
                                                    'course_update' => 'warning',       // Yellow for course updates  
                                                    'course_deletion' => 'danger',      // Red for course deletions
                                                    'course_assignment' => 'success',   // Green for teacher course assignments
                                                    'course_unassignment' => 'info'     // Blue for teacher course unassignments
                                                ];
                                                
                                                // Role colors for role badges
                                                $roleColors = [
                                                    'admin' => 'danger',
                                                    'teacher' => 'primary', 
                                                    'student' => 'success'
                                                ];
                                                  // Get colors
                                                $activityColor = $activityTypeColors[$activity['type']] ?? 'secondary';
                                                $roleColor = isset($activity['user_role']) ? ($roleColors[$activity['user_role']] ?? 'secondary') : 'info';
                                                ?>
                                                <div class="d-flex flex-column gap-1">
                                                    <!-- Activity Type Badge -->
                                                    <span class="badge bg-<?= $activityColor ?> rounded-pill small">
                                                        <?php                                                        $activityLabels = [
                                                            'user_registration' => 'Registration',
                                                            'user_creation' => 'User Created',
                                                            'user_update' => 'User Updated',
                                                            'user_deletion' => 'User Deleted',
                                                            'course_creation' => 'Course Created',
                                                            'course_update' => 'Course Updated',
                                                            'course_deletion' => 'Course Deleted',
                                                            'course_assignment' => 'Course Assigned',
                                                            'course_unassignment' => 'Course Unassigned'
                                                        ];
                                                        echo $activityLabels[$activity['type']] ?? 'Activity';
                                                        ?>
                                                    </span>
                                                    <!-- Role/Type Badge -->
                                                    <?php if (isset($activity['user_role'])): ?>
                                                        <span class="badge bg-<?= $roleColor ?> rounded-pill small">
                                                            <?= ucfirst($activity['user_role']) ?>
                                                        </span>
                                                    <?php elseif (isset($activity['course_code'])): ?>
                                                        <span class="badge bg-<?= $roleColor ?> rounded-pill small">
                                                            <?= esc($activity['course_code']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-<?= $roleColor ?> rounded-pill small">
                                                            System
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if (count($recentActivities) >= 8): ?>
                                    <div class="text-center mt-3">
                                        <small class="text-muted">Showing latest 8 activities</small>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <div class="mb-3">
                                        <span style="font-size: 3rem; opacity: 0.3;">⏰</span>
                                    </div>
                                    <p class="mb-0">No recent activities to display</p>
                                    <small>User activities will appear here as they occur</small>
                                </div>                            
                                <?php endif; ?>
                        </div>
                    </div>
                </div>
                      <!-- TEACHER ADDITIONAL CONTENT -->
            <?php elseif ($user['role'] === 'teacher'): ?>
                <!-- Course Management Section -->
                <div class="col-md-8 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">                                <div>
                                    <h5 class="mb-0 fw-bold text-dark">📚 Course Management</h5>
                                    <small class="text-muted">View and manage your assigned courses</small>
                                </div>
                                <a href="<?= base_url('teacher/courses') ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View All Courses
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <!-- Quick Course Stats -->
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <div class="me-3">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= $totalCourses ?? 0 ?></div>
                                            <small class="text-muted">My Courses</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <div class="me-3">
                                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-users"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= $totalStudents ?? 0 ?></div>
                                            <small class="text-muted">My Students</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                              <!-- Quick Actions -->
                            <div class="mt-4">
                                <h6 class="fw-semibold mb-3">🚀 Quick Actions</h6>
                                <div class="d-flex flex-wrap gap-2">                                    
                                    <a href="<?= base_url('teacher/courses') ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-book me-1"></i>View My Courses
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Teacher Pending Enrollment Approvals Section -->
                <div class="col-12 mb-4" id="teacherPendingApprovalsSection" style="display: none;">
                    <div class="card border-0 shadow-sm border-warning">
                        <div class="card-body p-4">
                            <h3 class="mb-3 fw-bold text-warning">
                                <i class="fas fa-user-clock me-2"></i>Pending Student Enrollments
                            </h3>
                            <p class="text-muted mb-3">Students requesting to enroll in your courses:</p>
                            <div id="teacherPendingApprovalsList">
                                <!-- Pending approvals will be loaded here by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- STUDENT ADDITIONAL CONTENT -->
            <?php elseif ($user['role'] === 'student'): ?>
                <!-- Pending Enrollment Approvals Section -->
                <div class="col-12 mb-4" id="pendingApprovalsSection" style="display: none;">
                    <div class="card border-0 shadow-sm border-warning">
                        <div class="card-body p-4">
                            <h3 class="mb-3 fw-bold text-warning">
                                <i class="fas fa-clock me-2"></i>Pending Enrollment Approvals
                            </h3>
                            <p class="text-muted mb-3">The following enrollments require your approval:</p>
                            <div id="pendingApprovalsList">
                                <!-- Pending approvals will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enrolled Courses Section -->
                <div class="col-12 mb-4">                    
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark">📖 My Enrolled Courses</h5>
                                    <small class="text-muted">Continue your learning journey</small>
                                </div>
                                <a href="<?= base_url('student/courses') ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View All Courses
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <?php if (!empty($enrolledCoursesData)): ?>
                                <div class="row g-3">
                                    <?php foreach ($enrolledCoursesData as $course): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title fw-bold text-primary mb-0"><?= esc($course['course_title'] ?? 'Untitled Course') ?></h6>
                                                        <span class="badge bg-success rounded-pill small">Enrolled</span>
                                                    </div>
                                                    <p class="text-muted small mb-2">
                                                        <?= esc($course['course_code'] ?? '') ?> 
                                                        <?php if (!empty($course['section'])): ?>• Section <?= esc($course['section']) ?><?php endif; ?>
                                                        <?php if (!empty($course['instructor_name'])): ?>• <?= esc($course['instructor_name']) ?><?php endif; ?>
                                                    </p>
                                                    <p class="card-text text-muted small mb-3">
                                                        <?= esc($course['term_name'] ?? '') ?> • <?= esc($course['semester_name'] ?? '') ?> <?= esc($course['academic_year'] ?? '') ?>
                                                    </p>
                                                    
                                                    <!-- Progress Bar -->
                                                    <div class="mb-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <small class="text-muted">Progress</small>
                                                            <small class="fw-bold"><?= $course['progress'] ?? 0 ?>%</small>
                                                        </div>
                                                        <div class="progress" style="height: 6px;">
                                                            <div class="progress-bar" role="progressbar" style="width: <?= $course['progress'] ?? 0 ?>%" aria-valuenow="<?= $course['progress'] ?? 0 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar"></i> Enrolled: <?= !empty($course['enrollment_date']) ? date('M j, Y', strtotime($course['enrollment_date'])) : 'N/A' ?>
                                                        </small>
                                                        <a href="<?= base_url('student/courses') ?>" class="btn btn-sm btn-outline-primary">Continue</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <span style="font-size: 3rem; opacity: 0.3;">📚</span>
                                    </div>
                                    <p class="mb-0">No enrolled courses yet</p>
                                    <small class="text-muted">Browse available courses below to start learning!</small>
                                </div>                            
                            <?php endif; ?> 
                        </div>
                    </div>
                </div>

                <!-- Available Courses Section -->
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">🎯 Available Courses</h5>
                            <small class="text-muted">Courses from your program curriculum</small>
                        </div>
                        <div class="card-body pt-3">
                            <?php if (!empty($availableCoursesData)): ?>
                                <div class="row g-3">
                                    <?php foreach ($availableCoursesData as $course): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border-0 shadow-sm course-card" data-course-id="<?= $course['id'] ?>">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title fw-bold text-dark mb-0"><?= esc($course['course_title'] ?? $course['title'] ?? 'Untitled Course') ?></h6>
                                                        <?php 
                                                        // Course type badge color
                                                        $typeColors = [
                                                            'major' => 'primary',
                                                            'minor' => 'secondary',
                                                            'general_education' => 'success'
                                                        ];
                                                        $courseType = $course['course_type'] ?? 'major';
                                                        $badgeColor = $typeColors[$courseType] ?? 'info';
                                                        $typeLabels = [
                                                            'major' => 'Major',
                                                            'minor' => 'Minor',
                                                            'general_education' => 'GE'
                                                        ];
                                                        $typeLabel = $typeLabels[$courseType] ?? ucfirst($courseType);
                                                        ?>
                                                        <span class="badge bg-<?= $badgeColor ?> rounded-pill small"><?= $typeLabel ?></span>
                                                    </div>
                                                    <p class="text-muted small mb-2">
                                                        <?= esc($course['course_code'] ?? '') ?> 
                                                        <?php if (!empty($course['section'])): ?>• Sec <?= esc($course['section']) ?><?php endif; ?>
                                                        • <?= esc($course['instructor_name'] ?? 'TBA') ?>
                                                    </p>
                                                    <p class="card-text text-muted small mb-3"><?= esc(substr($course['description'] ?? '', 0, 80)) ?>...</p>
                                                    
                                                    <!-- Course Details -->
                                                    <div class="mb-3">
                                                        <div class="row text-center">
                                                            <div class="col-4">
                                                                <small class="text-muted d-block">Credits</small>
                                                                <strong class="small"><?= $course['credits'] ?? 0 ?></strong>
                                                            </div>
                                                            <div class="col-4">
                                                                <small class="text-muted d-block">Slots</small>
                                                                <strong class="small text-<?= ($course['available_slots'] ?? 0) > 5 ? 'success' : 'warning' ?>"><?= $course['available_slots'] ?? 0 ?></strong>
                                                            </div>
                                                            <div class="col-4">
                                                                <small class="text-muted d-block">Year</small>
                                                                <strong class="small"><?= $course['year_level_name'] ?? 'N/A' ?></strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Schedule if available -->
                                                    <?php if (!empty($course['schedule_display']) && $course['schedule_display'] !== 'TBA'): ?>
                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-clock"></i> <?= esc($course['schedule_display']) ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Course Dates -->
                                                    <?php if (!empty($course['start_date'])): ?>
                                                        <div class="mb-3">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar"></i> 
                                                                <?= $course['start_date_formatted'] ?? date('M j, Y', strtotime($course['start_date'])) ?> - <?= $course['end_date_formatted'] ?? 'TBA' ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                      <!-- Enrollment Button -->
                                                    <button class="btn btn-primary btn-sm w-100 enroll-btn" 
                                                            data-course-id="<?= $course['id'] ?>"
                                                            data-course-title="<?= esc($course['course_code'] ?? '') ?> - <?= esc($course['course_title'] ?? $course['title'] ?? '') ?>"
                                                            data-available-slots="<?= $course['available_slots'] ?? 0 ?>"
                                                            data-max-students="<?= $course['max_students'] ?? 0 ?>"
                                                            data-credits="<?= $course['credits'] ?? 0 ?>">
                                                        <i class="fas fa-plus-circle me-1"></i>
                                                        Enroll in Course
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>                            <?php else: ?>
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <span style="font-size: 3rem; opacity: 0.3;">🎯</span>
                                    </div>
                                    <p class="mb-2 fw-bold">No available courses at the moment</p>
                                    <small class="text-muted d-block mb-2">
                                        <?php if (!empty($studentInfo['program_id'])): ?>
                                            No course offerings from your program curriculum are currently available for enrollment.
                                        <?php else: ?>
                                            Please make sure your program is assigned in your student profile.
                                        <?php endif; ?>
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Course offerings are based on your program: <strong><?= esc($studentInfo['program_name'] ?? 'Not Assigned') ?></strong>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Learning Statistics -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">⏰ Upcoming Deadlines</h5>
                            <small class="text-muted">Don't miss these important dates</small>
                        </div>
                        <div class="card-body pt-3">
                            <p class="text-muted">Assignment deadlines and important dates will appear here.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">🏆 Recent Grades & Feedback</h5>
                            <small class="text-muted">Your latest academic performance</small>
                        </div>
                        <div class="card-body pt-3">
                            <p class="text-muted">Your grades and teacher feedback will appear here.</p>
                        </div>
                    </div>
                </div>

                <!-- Enrollment Success/Error Modal -->
                <div class="modal fade" id="enrollmentModal" tabindex="-1" aria-labelledby="enrollmentModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="enrollmentModalLabel">Course Enrollment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="enrollmentModalBody">
                                <!-- Content will be filled by JavaScript -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Teacher Dashboard Pending Enrollments Script -->
<?php if ($user['role'] === 'teacher'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadTeacherPendingEnrollments();
});

function loadTeacherPendingEnrollments() {
    fetch('<?= base_url("/enrollment/pending") ?>', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Teacher pending enrollments:', data);
        if (data.success && data.pending_enrollments && data.pending_enrollments.length > 0) {
            displayTeacherPendingEnrollments(data.pending_enrollments);
            document.getElementById('teacherPendingApprovalsSection').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error loading pending enrollments:', error);
    });
}

function displayTeacherPendingEnrollments(enrollments) {
    const container = document.getElementById('teacherPendingApprovalsList');
    container.innerHTML = '';
    
    enrollments.forEach(enrollment => {
        const card = document.createElement('div');
        card.className = 'card mb-3 border-warning';
        card.innerHTML = `
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="card-title mb-1">
                            <i class="fas fa-user-graduate me-2"></i>
                            ${enrollment.student_name} (${enrollment.student_id_number})
                        </h6>
                        <p class="text-muted mb-1">
                            <small>
                                <i class="fas fa-book me-1"></i>${enrollment.course_code} - ${enrollment.course_title} |
                                Section ${enrollment.section} | ${enrollment.term_name} ${enrollment.academic_year}
                            </small>
                        </p>
                        <p class="text-muted mb-0">
                            <small>
                                <i class="fas fa-calendar me-1"></i>Requested: ${new Date(enrollment.enrollment_date).toLocaleDateString()}
                            </small>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-success btn-sm me-2" onclick="respondToTeacherEnrollment(${enrollment.enrollment_id}, 'accept')">
                            <i class="fas fa-check me-1"></i>Accept
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="respondToTeacherEnrollment(${enrollment.enrollment_id}, 'reject')">
                            <i class="fas fa-times me-1"></i>Reject
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function respondToTeacherEnrollment(enrollmentId, action) {
    if (!confirm(`Are you sure you want to ${action} this enrollment?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('enrollment_id', enrollmentId);
    formData.append('action', action);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    fetch('<?= base_url("/enrollment/respond") ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showTeacherAlert(data.message, 'success');
            loadTeacherPendingEnrollments();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showTeacherAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error responding to enrollment:', error);
        showTeacherAlert('An error occurred. Please try again.', 'danger');
    });
}

function showTeacherAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.getElementById('teacherPendingApprovalsSection');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => alertDiv.remove(), 5000);
}
</script>
<?php endif; ?>

<!-- Student Dashboard AJAX Enrollment Script -->
<?php if ($user['role'] === 'student'): ?>
    <script>
        // Set base URL for external JS file
        window.APP_BASE_URL = '<?= base_url() ?>';
        window.ENROLL_URL = '<?= base_url('/course/enroll') ?>';
        window.PENDING_URL = '<?= base_url('/enrollment/pending') ?>';
        window.RESPOND_URL = '<?= base_url('/enrollment/respond') ?>';

        document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Dashboard enrollment script loaded - v4.3');
        
        // Initialize after a short delay to ensure everything is loaded
        setTimeout(initializeEnrollment, 500);
        });

        function initializeEnrollment() {
        // Get all enrollment buttons
        const enrollButtons = document.querySelectorAll('.enroll-btn');
        console.log('Found enrollment buttons:', enrollButtons.length);
        
        // Check if modal exists before initializing
        let enrollmentModal = null;
        const modalElement = document.getElementById('enrollmentModal');
        if (modalElement) {
            try {
                enrollmentModal = new bootstrap.Modal(modalElement);
                console.log('Modal initialized successfully');
            } catch (e) {
                console.error('Error initializing modal:', e);
            }
        } else {
            console.error('Modal element not found!');
        }
        
        const modalBody = document.getElementById('enrollmentModalBody');
        const modalTitle = document.getElementById('enrollmentModalLabel');
        
        // Track courses being enrolled to prevent duplicate requests
        const enrollmentInProgress = new Set();

        if (enrollButtons.length === 0) {
            console.error('No enrollment buttons found on the page!');
            return;
        }

        enrollButtons.forEach((button, index) => {
            console.log(`Attaching handler to button ${index}:`, button.dataset);
            button.addEventListener('click', function(e) {
                console.log('Button clicked!', e);
                e.preventDefault();
                const courseId = this.dataset.courseId;
                const courseTitle = this.dataset.courseTitle;
                const originalButton = this;
                
                // Prevent duplicate enrollment attempts
                if (enrollmentInProgress.has(courseId)) {
                    console.log('⚠️ Enrollment already in progress for course:', courseId);
                    return;
                }
                
                // Mark enrollment as in progress
                enrollmentInProgress.add(courseId);
                console.log('🔄 Starting enrollment for course:', courseId);

                // Disable button and show loading state
                originalButton.disabled = true;
                originalButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Enrolling...';

                // Get CSRF tokens from meta tags
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfHash = document.querySelector('meta[name="csrf-hash"]').getAttribute('content');

                // Prepare the enrollment request with CSRF protection
                const formData = new FormData();
                formData.append('course_id', courseId);
                formData.append(csrfToken, csrfHash);

                // Make AJAX request with CSRF headers
                fetch(window.ENROLL_URL || '/course/enroll', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfHash
                    }
                })
                .then(response => {
                    console.log('📦 Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('📦 Response data:', data);
                    
                    // Update CSRF token if provided in response
                    if (data.csrf_hash) {
                        document.querySelector('meta[name="csrf-hash"]').setAttribute('content', data.csrf_hash);
                        console.log('🔐 CSRF token updated');
                    }
                    
                    if (data.success) {
                        console.log('✅ Enrollment successful!');
                        
                        // Success: Show success modal and update UI
                        modalTitle.textContent = 'Enrollment Successful!';
                        modalBody.innerHTML = `
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-success">Welcome to ${data.data.course_code}!</h5>
                                <p class="mb-3"><strong>${data.data.course_title}</strong></p>
                                <p class="text-muted">You have been successfully enrolled in this course. You can now access course materials and start learning.</p>
                                
                                <div class="card border-0 bg-light mt-3 mb-3">
                                    <div class="card-body py-2">
                                        <div class="row text-start">
                                            <div class="col-6 mb-2">
                                                <small class="text-muted">📅 Enrollment Date</small>
                                                <div class="fw-bold">${data.data.enrollment_date_formatted || 'N/A'}</div>
                                            </div>
                                            <div class="col-6 mb-2">
                                                <small class="text-muted">📚 Section</small>
                                                <div class="fw-bold">${data.data.section || 'N/A'}</div>
                                            </div>
                                            <div class="col-6 mb-2">
                                                <small class="text-muted">📖 Credits</small>
                                                <div class="fw-bold">${data.data.credits || 'N/A'}</div>
                                            </div>
                                            <div class="col-6 mb-2">
                                                <small class="text-muted">🎓 Term</small>
                                                <div class="fw-bold">${data.data.term || 'N/A'}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="window.location.reload()">
                                        <i class="fas fa-sync-alt me-1"></i>Refresh Page
                                    </button>
                                    <small class="text-muted d-block mt-2">Click refresh to see your enrolled courses</small>
                                </div>
                            </div>
                        `;
                        
                        // Update the course card to show enrolled status
                        const courseCard = originalButton.closest('.course-card');
                        if (courseCard) {
                            // Update the badge to show enrolled status
                            const badge = courseCard.querySelector('.badge');
                            if (badge) {
                                badge.className = 'badge bg-success rounded-pill small';
                                badge.textContent = 'Enrolled';
                            }
                            
                            // Replace enrollment button with enrolled status
                            originalButton.outerHTML = `
                                <div class="btn btn-success btn-sm w-100" style="pointer-events: none;">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Successfully Enrolled!
                                </div>
                            `;
                            
                            // Add a subtle visual indicator
                            courseCard.style.border = '2px solid #198754';
                            courseCard.style.borderRadius = '0.375rem';
                        }
                        
                        // Show success modal
                        enrollmentModal.show();
                        
                    } else {
                        // Error: Show error modal
                        console.log('❌ Enrollment failed:', data.error_code);
                        console.log('Error message:', data.message);
                        
                        try {
                            modalTitle.textContent = 'Enrollment Failed';
                            let errorMessage = data.message || 'An unexpected error occurred.';
                            
                            // Handle specific error types
                            if (data.error_code === 'ALREADY_ENROLLED') {
                                console.log('⚠️ Handling ALREADY_ENROLLED error');
                                
                                modalBody.innerHTML = `
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-info-circle text-warning" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="text-warning">Already Enrolled</h5>
                                        <p class="text-muted">${errorMessage}</p>
                                    </div>
                                `;
                                
                                enrollmentModal.show();
                                
                                // Update button to show already enrolled (don't allow retry)
                                originalButton.disabled = true;
                                originalButton.classList.remove('btn-primary');
                                originalButton.classList.add('btn-secondary');
                                originalButton.innerHTML = '<i class="fas fa-check-circle me-1"></i>Already Enrolled';
                                
                            } else if (data.error_code === 'PREREQUISITES_NOT_MET') {
                                console.log('🔒 Handling PREREQUISITES_NOT_MET error');
                                console.log('Missing prerequisites:', data.missing_prerequisites);
                                
                                // Build prerequisite list with detailed information
                                let prereqList = '';
                                if (data.missing_prerequisites && Array.isArray(data.missing_prerequisites) && data.missing_prerequisites.length > 0) {
                                    prereqList = '<div class="bg-light rounded p-3 mt-3 mb-3">';
                                    prereqList += '<p class="mb-2 text-start"><strong>Required Course(s):</strong></p>';
                                    prereqList += '<ul class="list-unstyled text-start mb-0">';
                                    
                                    data.missing_prerequisites.forEach(course => {
                                        let reason = '';
                                        if (course.reason === 'not_completed') {
                                            reason = '<span class="badge bg-danger ms-2">Not Completed</span>';
                                        } else if (course.reason === 'insufficient_grade') {
                                            reason = `<span class="badge bg-warning text-dark ms-2">Grade: ${course.student_grade}% (Need: ${course.minimum_grade}%)</span>`;
                                        }
                                        
                                        prereqList += `
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="fas fa-book text-danger me-2"></i>
                                                <div>
                                                    <strong>${course.course_code || 'N/A'}</strong> - ${course.title || 'Course'}
                                                    ${reason}
                                                    ${course.minimum_grade ? `<br><small class="text-muted">Minimum passing grade: ${course.minimum_grade}%</small>` : ''}
                                                </div>
                                            </li>
                                        `;
                                    });
                                    
                                    prereqList += '</ul></div>';
                                } else {
                                    prereqList = '<p class="text-muted">No prerequisite details available.</p>';
                                }
                                
                                modalBody.innerHTML = `
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-lock text-danger" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="text-danger">⚠️ Prerequisites Not Met</h5>
                                        <p class="text-muted mb-2">You cannot enroll in <strong>${courseTitle}</strong> yet.</p>
                                        ${prereqList}
                                        <div class="alert alert-info mt-3 mb-0 text-start">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>What you need to do:</strong>
                                            <ul class="mb-0 mt-2">
                                                <li>Complete the required prerequisite course(s)</li>
                                                <li>Pass with the minimum required grade</li>
                                                <li>Wait for your instructor to finalize your grades</li>
                                            </ul>
                                        </div>
                                    </div>
                                `;
                                
                                console.log('🎭 Showing prerequisite modal');
                                enrollmentModal.show();
                                
                                // Update button to locked state (don't allow retry for prerequisites)
                                originalButton.disabled = true;
                                originalButton.classList.remove('btn-primary');
                                originalButton.classList.add('btn-secondary');
                                originalButton.innerHTML = '<i class="fas fa-lock me-1"></i>Prerequisites Required';
                                
                                // Update course card styling
                                const courseCard = originalButton.closest('.course-card');
                                if (courseCard) {
                                    courseCard.style.opacity = '0.7';
                                    const badge = courseCard.querySelector('.badge');
                                    if (badge) {
                                        badge.className = 'badge bg-warning text-dark rounded-pill small';
                                        badge.textContent = 'Prerequisites Required';
                                    }
                                }
                                
                            } else if (data.error_code === 'OFFERING_FULL') {
                                console.log('⚠️ Handling OFFERING_FULL error');
                                
                                modalBody.innerHTML = `
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-users text-danger" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="text-danger">Course Full</h5>
                                        <p class="text-muted">${errorMessage}</p>
                                        <small class="text-muted">Please try another section or check back later.</small>
                                    </div>
                                `;
                                
                                enrollmentModal.show();
                                
                                // Allow retry for course full (might refresh and try again)
                                originalButton.disabled = false;
                                originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
                                
                            } else {
                                // Generic error handler with more details if available
                                console.log('⚠️ Handling generic error:', data.error_code);
                                
                                let errorDetails = '';
                                if (data.error_code) {
                                    errorDetails = `<div class="alert alert-secondary mt-3 mb-0"><small><strong>Error Code:</strong> ${data.error_code}</small></div>`;
                                }
                                
                                modalBody.innerHTML = `
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="text-danger">Enrollment Error</h5>
                                        <p class="text-muted">${errorMessage}</p>
                                        ${errorDetails}
                                        <small class="text-muted d-block mt-3">Please try again later or contact support if the problem persists.</small>
                                    </div>
                                `;
                                
                                enrollmentModal.show();
                                
                                // Allow retry for generic errors
                                originalButton.disabled = false;
                                originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
                            }
                            
                        } catch (error) {
                            console.error('💥 Error in error handling:', error);
                            console.error('Stack trace:', error.stack);
                            
                            // Fallback error display
                            modalTitle.textContent = 'Enrollment Failed';
                            modalBody.innerHTML = `
                                <div class="text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="text-danger">Error</h5>
                                    <p class="text-muted">${data.message || 'An error occurred during enrollment.'}</p>
                                </div>
                            `;
                            
                            enrollmentModal.show();
                            originalButton.disabled = false;
                            originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
                        }
                    }
                })
                .catch(error => {
                    console.error('💥 Network/Fetch error:', error);
                    
                    // Network or other error
                    modalTitle.textContent = 'Connection Error';
                    modalBody.innerHTML = `
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-wifi text-danger" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-danger">Connection Failed</h5>
                            <p class="text-muted">Unable to process your enrollment request. Please check your internet connection and try again.</p>
                        </div>
                    `;
                    
                    enrollmentModal.show();
                    
                    // Reset button state for network errors (allow retry)
                    originalButton.disabled = false;
                    originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
                })
                .finally(() => {
                    // Always remove from in-progress set
                    enrollmentInProgress.delete(courseId);
                    console.log('🏁 Enrollment process completed for course:', courseId);
                    
                    // Safety net: ensure button is never stuck in loading state
                    if (originalButton && originalButton.innerHTML && originalButton.innerHTML.includes('Enrolling')) {
                        console.log('⚠️ Safety net triggered - button still in loading state!');
                        originalButton.disabled = false;
                        originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
                    }
                });
            });
        });
        console.log('✅ Enrollment handlers attached to', enrollButtons.length, 'buttons');
        
        // Load pending enrollments
        loadPendingEnrollments();
    }

    function loadPendingEnrollments() {
        fetch(window.PENDING_URL || '/enrollment/pending', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.pending_enrollments.length > 0) {
                displayPendingEnrollments(data.pending_enrollments);
                document.getElementById('pendingApprovalsSection').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading pending enrollments:', error);
        });
    }

    function displayPendingEnrollments(enrollments) {
        const container = document.getElementById('pendingApprovalsList');
        container.innerHTML = '';
        
        enrollments.forEach(enrollment => {
            const card = document.createElement('div');
            card.className = 'card mb-3 border-warning';
            card.innerHTML = `
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="card-title mb-1">
                                <i class="fas fa-graduation-cap me-2"></i>
                                ${enrollment.course_code} - ${enrollment.course_title}
                            </h6>
                            <p class="text-muted mb-1">
                                <small>
                                    Section ${enrollment.section} • ${enrollment.term_name} • ${enrollment.academic_year}
                                    <br>
                                    <i class="fas fa-user me-1"></i>Enrolled by: ${enrollment.enrolled_by_name}
                                </small>
                            </p>
                            ${enrollment.notes ? `<p class="mb-0"><small><em>Notes: ${enrollment.notes}</em></small></p>` : ''}
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-success btn-sm me-2" onclick="respondToEnrollment(${enrollment.enrollment_id}, 'accept')">
                                <i class="fas fa-check me-1"></i>Accept
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="respondToEnrollment(${enrollment.enrollment_id}, 'reject')">
                                <i class="fas fa-times me-1"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    }

    function respondToEnrollment(enrollmentId, action) {
        if (!confirm('Are you sure you want to ' + action + ' this enrollment?')) {
            return;
        }
        
        // Get CSRF tokens
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfHash = document.querySelector('meta[name="csrf-hash"]').getAttribute('content');
        
        // Use FormData for proper POST handling
        const formData = new FormData();
        formData.append('enrollment_id', enrollmentId);
        formData.append('action', action);
        formData.append(csrfToken, csrfHash);
        
        fetch(window.RESPOND_URL || '/enrollment/respond', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfHash
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                loadPendingEnrollments(); // Reload the list
                location.reload(); // Refresh to update enrolled courses
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error responding to enrollment:', error);
            showAlert('An error occurred. Please try again.', 'danger');
        });
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.getElementById('pendingApprovalsSection');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    </script>
<?php endif; ?>
</body>
</html>