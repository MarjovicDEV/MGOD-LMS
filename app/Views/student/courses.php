<?= $this->include('templates/header') ?>

<!-- Student My Courses View - Shows enrolled courses and available courses -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">📚 My Courses</h2>
                                <p class="mb-0 opacity-75">View your enrolled courses, track progress, and discover new learning opportunities</p>
                            </div>
                            <div>
                                <a href="<?= base_url('student/dashboard') ?>" class="btn btn-light btn-sm">
                                    ← Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        <!-- Course Statistics Cards -->
        <div class="row mb-4 g-4">
            <!-- Enrolled Courses -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3">📚</div>
                    <div class="display-5 fw-bold"><?= $totalEnrolled ?></div>
                    <div class="fw-semibold">Enrolled Courses</div>
                    <small class="opacity-75">Courses to Learn</small>
                </div>
            </div>
            
            <!-- Materials Count -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3">📁</div>
                    <div class="display-5 fw-bold">
                        <?php 
                        $totalMaterials = 0;
                        if (!empty($enrolledCourses)) {
                            foreach ($enrolledCourses as $course) {
                                if (!empty($course['materials'])) {
                                    $totalMaterials += count($course['materials']);
                                }
                            }
                        }
                        echo $totalMaterials;
                        ?>
                    </div>
                    <div class="fw-semibold">Materials</div>
                    <small class="opacity-75">Available resources</small>
                </div>
            </div>
            
            <!-- Pending Assignments -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3">⏰</div>
                    <div class="display-5 fw-bold">0</div>
                    <div class="fw-semibold">Pending</div>
                    <small class="opacity-75">Awaiting completion</small>
                </div>
            </div>
              <!-- Available Courses -->            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3">🎓</div>
                    <div class="display-5 fw-bold"><?= $totalAvailable ?></div>
                    <div class="fw-semibold">Available Courses</div>
                    <small class="opacity-75">Ready to enroll</small>
                </div>
            </div>        </div>

        <!-- Search Box -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0 ps-0" 
                                   id="courseSearchInput" 
                                   placeholder="🔍 Search courses by code, title, instructor, or term...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            Found: <strong id="searchResultCount"><?= count($enrolledCourses ?? []) ?></strong> course(s)
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals Section -->
        <div class="row mb-4" id="pendingApprovalsSection" style="display: none;">
            <div class="col-12">
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
        </div>

        <!-- Tab Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="mb-0 fw-bold">
                            <i class="fas fa-book-reader me-2 text-primary"></i>My Enrolled Courses
                        </h3>
                        <p class="text-muted mb-0">View and manage your current course enrollments</p>
                    </div>
                </div>
            </div>
        </div><!-- Course Content -->
        <div class="row">
            <!-- Enrolled Courses Section -->            <?php if (!empty($enrolledCourses)): ?>
                <?php foreach ($enrolledCourses as $course): ?>
                            <div class="col-lg-6 col-xl-4 mb-4 course-card-wrapper"
                                 data-course-code="<?= esc(strtolower($course['course_code'])) ?>"
                                 data-course-title="<?= esc(strtolower($course['course_title'])) ?>"
                                 data-course-description="<?= esc(strtolower($course['course_description'])) ?>"
                                 data-instructor-name="<?= esc(strtolower($course['instructor_name'])) ?>"
                                 data-term="<?= esc(strtolower($course['term_name'] ?? '')) ?>"
                                 data-academic-year="<?= esc(strtolower($course['academic_year'] ?? '')) ?>"
                                 data-semester="<?= esc(strtolower($course['semester'] ?? '')) ?>">
                                <div class="card h-100 border-0 shadow-sm rounded-3 course-card enrolled-course">
                                    <div class="card-body p-4">
                                        <!-- Course Header -->
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title fw-bold mb-2 text-truncate"><?= esc($course['course_title']) ?></h5>
                                                <p class="text-muted small mb-2">
                                                    <i class="fas fa-code me-1"></i><?= esc($course['course_code']) ?>
                                                </p>
                                            </div>
                                            <?= $course['status_badge'] ?>
                                        </div>

                                        <!-- Course Description -->
                                        <p class="card-text text-muted mb-3" style="font-size: 0.9rem; line-height: 1.4;">
                                            <?= strlen($course['course_description']) > 100 ? esc(substr($course['course_description'], 0, 100)) . '...' : esc($course['course_description']) ?>
                                        </p>

                                        <!-- Course Details -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="bg-light p-2 rounded-2 text-center">
                                                    <div class="fw-bold text-primary"><?= esc($course['credits']) ?></div>
                                                    <small class="text-muted">Credits</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="bg-light p-2 rounded-2 text-center">
                                                    <div class="fw-bold text-info"><?= esc($course['duration_weeks']) ?>w</div>
                                                    <small class="text-muted">Duration</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Progress Bar -->
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted fw-medium">Course Progress</small>
                                                <small class="fw-bold text-success"><?= $course['progress'] ?>%</small>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $course['progress'] ?>%"></div>
                                            </div>
                                        </div>

                                        <!-- Instructor Information -->
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Instructor</small>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    <?= strtoupper(substr(esc($course['instructor_name']), 0, 2)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-medium"><?= esc($course['instructor_name']) ?></div>
                                                    <small class="text-muted"><?= esc($course['instructor_email']) ?></small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Course Dates -->
                                        <div class="mb-3">
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <strong>Start:</strong> <?= $course['start_date_formatted'] ?>
                                                    </small>
                                                </div>
                                                <div class="col-12">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar-check me-1"></i>
                                                        <strong>End:</strong> <?= $course['end_date_formatted'] ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>                                        <!-- Enrollment Information -->
                                        <div class="card border-success mb-3">
                                            <div class="card-header bg-success text-white py-2">
                                                <small class="fw-semibold mb-0">
                                                    <i class="fas fa-check-circle me-1"></i>Enrollment Details
                                                </small>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row g-2 small">
                                                    <!-- Enrollment Date -->
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center text-muted mb-1">
                                                            <i class="fas fa-calendar me-2" style="width: 20px;"></i>
                                                            <span>Enrolled: <strong class="text-dark"><?= $course['enrollment_date_formatted'] ?></strong></span>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Year Level -->
                                                    <?php if (!empty($course['year_level_at_enrollment'])): ?>
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center text-muted mb-1">
                                                            <i class="fas fa-graduation-cap me-2" style="width: 20px;"></i>
                                                            <span>Year Level: <strong class="text-dark"><?= esc($course['year_level_at_enrollment']) ?></strong></span>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Semester -->
                                                    <?php if (!empty($course['semester'])): ?>
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center text-muted mb-1">
                                                            <i class="fas fa-calendar-check me-2" style="width: 20px;"></i>
                                                            <span>Semester: <strong class="text-dark"><?= esc($course['semester']) ?></strong></span>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Semester Duration -->
                                                    <?php if (!empty($course['semester_duration_weeks'])): ?>
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center text-muted mb-1">
                                                            <i class="fas fa-hourglass-half me-2" style="width: 20px;"></i>
                                                            <span>Duration: <strong class="text-dark"><?= esc($course['semester_duration_weeks']) ?> weeks</strong></span>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Semester End Date -->
                                                    <?php if (!empty($course['semester_end_date_formatted'])): ?>
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center text-muted mb-1">
                                                            <i class="fas fa-calendar-times me-2" style="width: 20px;"></i>
                                                            <span>Semester Ends: <strong class="text-dark"><?= $course['semester_end_date_formatted'] ?></strong></span>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Academic Year -->
                                                    <?php if (!empty($course['academic_year'])): ?>
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center text-muted mb-1">
                                                            <i class="fas fa-book me-2" style="width: 20px;"></i>
                                                            <span>Academic Year: <strong class="text-dark"><?= esc($course['academic_year']) ?></strong></span>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Enrollment Type -->
                                                    <?php if (!empty($course['enrollment_type'])): ?>
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center text-muted mb-1">
                                                            <i class="fas fa-tag me-2" style="width: 20px;"></i>
                                                            <span>Enrollment Type: <strong class="text-dark"><?= ucfirst($course['enrollment_type']) ?></strong></span>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Payment Status -->
                                                    <?php if (!empty($course['payment_status'])): ?>
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center text-muted mb-1">
                                                            <i class="fas fa-credit-card me-2" style="width: 20px;"></i>
                                                            <span>Payment Status: 
                                                                <strong class="<?= $course['payment_status'] === 'paid' ? 'text-success' : ($course['payment_status'] === 'partial' ? 'text-warning' : 'text-danger') ?>">
                                                                    <?= ucfirst($course['payment_status']) ?>
                                                                </strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div><!-- Course Materials Section - REDESIGNED WIDER -->
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-semibold text-primary mb-0">
                                                    <i class="fas fa-folder-open me-1"></i>Course Materials
                                                </h6>
                                                <?php if (!empty($course['materials'])): ?>
                                                <a href="<?= base_url('student/course/' . $course['course_offering_id'] . '/materials') ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>View All (<?= count($course['materials']) ?>)
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                              <?php if (!empty($course['materials'])): ?>
                                            <div class="card border-0 bg-light">
                                                <div class="card-body p-3">
                                                    <?php 
                                                    // Show max 3 materials in card
                                                    $displayMaterials = array_slice($course['materials'], 0, 3);
                                                    foreach ($displayMaterials as $idx => $material): 
                                                    ?>
                                                    <div class="py-2 <?= ($idx < count($displayMaterials) - 1) ? 'border-bottom' : '' ?>">
                                                        <div class="fw-medium mb-1">
                                                            <?php
                                                            $extension = pathinfo($material['file_name'], PATHINFO_EXTENSION);
                                                            $icon = match(strtolower($extension)) {
                                                                'pdf' => 'fa-file-pdf text-danger',
                                                                'ppt', 'pptx' => 'fa-file-powerpoint text-warning',
                                                                'doc', 'docx' => 'fa-file-word text-primary',
                                                                default => 'fa-file text-secondary'
                                                            };
                                                            ?>
                                                            <i class="fas <?= $icon ?> me-2"></i>
                                                            <?= esc($material['file_name']) ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar-alt me-1"></i>
                                                            Uploaded: <?= date('M j, Y', strtotime($material['created_at'])) ?>
                                                        </small>
                                                    </div>
                                                    <?php endforeach; ?>
                                                    
                                                    <?php if (count($course['materials']) > 3): ?>
                                                    <div class="text-center mt-2">
                                                        <a href="<?= base_url('student/course/' . $course['course_offering_id'] . '/materials') ?>" 
                                                           class="btn btn-sm btn-link">
                                                            <i class="fas fa-plus-circle me-1"></i>
                                                            View <?= count($course['materials']) - 3 ?> more material<?= (count($course['materials']) - 3) > 1 ? 's' : '' ?>
                                                        </a>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <div class="card border-0 bg-light">
                                                <div class="card-body p-3 text-center">
                                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                                    <p class="text-muted mb-0 small">
                                                        No materials available yet
                                                    </p>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div><!-- Course Actions -->
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-primary btn-sm">
                                                <i class="fas fa-play me-1"></i>Continue Learning
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-info btn-sm materials-toggle" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#materials-<?= $course['course_id'] ?>" 
                                                        aria-expanded="false">
                                                    <i class="fas fa-book me-1"></i>Materials 
                                                    <span class="badge bg-info ms-1"><?= count($course['materials']) ?></span>
                                                </button>
                                                <button class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-tasks me-1"></i>Assignments
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Expanded Materials Section -->
                                        <div class="collapse mt-3" id="materials-<?= $course['course_id'] ?>">
                                            <div class="card border-info">
                                                <div class="card-header bg-info text-white">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-folder-open me-2"></i>Course Materials
                                                    </h6>
                                                </div>
                                                <div class="card-body p-0">                                                    <?php if (!empty($course['materials'])): ?>
                                                        <div class="list-group list-group-flush">
                                                            <?php foreach ($course['materials'] as $index => $material): ?>
                                                            <div class="list-group-item">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-3">
                                                                        <?php
                                                                        $extension = strtolower(pathinfo($material['file_name'], PATHINFO_EXTENSION));
                                                                        $fileIcons = [
                                                                            'pdf' => ['icon' => '📄', 'color' => 'danger'],
                                                                            'doc' => ['icon' => '📝', 'color' => 'primary'],
                                                                            'docx' => ['icon' => '📝', 'color' => 'primary'],
                                                                            'xls' => ['icon' => '📊', 'color' => 'success'],
                                                                            'xlsx' => ['icon' => '📊', 'color' => 'success'],
                                                                            'ppt' => ['icon' => '📋', 'color' => 'warning'],
                                                                            'pptx' => ['icon' => '📋', 'color' => 'warning'],
                                                                            'txt' => ['icon' => '📄', 'color' => 'secondary'],
                                                                            'jpg' => ['icon' => '🖼️', 'color' => 'info'],
                                                                            'jpeg' => ['icon' => '🖼️', 'color' => 'info'],
                                                                            'png' => ['icon' => '🖼️', 'color' => 'info'],
                                                                            'mp4' => ['icon' => '🎥', 'color' => 'dark'],
                                                                            'mp3' => ['icon' => '🎵', 'color' => 'purple']
                                                                        ];
                                                                        $icon = $fileIcons[$extension] ?? ['icon' => '📎', 'color' => 'secondary'];
                                                                        ?>
                                                                        <span class="badge bg-<?= $icon['color'] ?> rounded-circle p-2" style="font-size: 1.2rem;">
                                                                            <?= $icon['icon'] ?>
                                                                        </span>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-medium"><?= esc($material['file_name']) ?></div>
                                                                        <small class="text-muted">
                                                                            <i class="fas fa-calendar me-1"></i>
                                                                            Uploaded: <?= date('M j, Y g:i A', strtotime($material['created_at'])) ?>
                                                                        </small>
                                                                        <div class="mt-1">
                                                                            <span class="badge bg-light text-dark">
                                                                                <?= strtoupper($extension) ?>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <div class="card-footer bg-light text-center">
                                                            <small class="text-muted">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                Total: <?= count($course['materials']) ?> file<?= count($course['materials']) !== 1 ? 's' : '' ?> available
                                                            </small>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="p-4 text-center">
                                                            <i class="fas fa-folder-open text-muted mb-3" style="font-size: 2rem;"></i>
                                                            <p class="text-muted mb-0">No materials have been uploaded for this course yet.</p>
                                                            <small class="text-muted">Check back later or contact your instructor.</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>                        <?php endforeach; ?>
            <?php else: ?>
                <!-- No Enrolled Courses -->
                <div class="text-center py-5">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-5">
                            <div class="mb-4">
                                <i class="fas fa-book-open text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="text-muted mb-3">No Enrolled Courses Yet</h4>
                            <p class="text-muted mb-4">
                                You haven't enrolled in any courses yet. Contact your administrator to get enrolled in available courses.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- No Search Results Message -->
            <div class="col-12 text-center py-5" id="noResultsMessage" style="display: none;">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-muted mb-3">No Courses Found</h4>
                        <p class="text-muted mb-4">
                            No courses match your search criteria. Try adjusting your search terms.
                        </p>
                        <button class="btn btn-primary" id="clearSearchBtn">
                            <i class="fas fa-times me-2"></i>Clear Search
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Success/Error Modal (for future functionality) -->
        <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="messageModalLabel">Message</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="messageModalBody">
                        <!-- Content will be filled by JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Course Card Styling */
.course-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: default;
    border-radius: 12px !important;
}

.course-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15) !important;
}

/* Course Type Indicators */
.enrolled-course {
    border-left: 4px solid #198754;
}

/* Progress Bar Styling */
.progress {
    border-radius: 10px;
    height: 8px;
}

.progress-bar {
    border-radius: 10px;
}

/* Button Groups */
.btn-group .btn {
    flex: 1;
}

/* Alert Styling */
.alert-success {
    border: none;
    background-color: rgba(25, 135, 84, 0.1);
    border-radius: 8px;
}

/* Card Enhancements */
.card {
    border-radius: 12px;
    overflow: hidden;
}

.card-body {
    padding: 1.25rem;
}

/* Button Hover Effects */
.btn:hover {
    transition: all 0.2s ease;
}

/* Statistics Cards */
.bg-primary, .bg-success, .bg-info, .bg-warning {
    border-radius: 12px !important;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .course-card {
        margin-bottom: 1rem;
    }
    
    .display-4 {
        font-size: 2.5rem;
    }
    
    .display-5 {
        font-size: 2rem;
    }
}

/* Loading State for Buttons */
.btn[disabled] {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Enhanced Shadows and Depth */
.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
}

.rounded-3 {
    border-radius: 12px !important;
}

/* Custom Badge Styling */
.badge {
    font-size: 0.75em;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
}

/* Materials Toggle Button */
.materials-toggle {
    position: relative;
    transition: all 0.3s ease;
}

.materials-toggle:hover {
    transform: translateY(-1px);
}

.materials-toggle .badge {
    font-size: 0.7em;
    padding: 0.2rem 0.4rem;
}

/* Materials Collapse Animation */
.collapse {
    transition: all 0.35s ease;
}

/* File List Styling */
.list-group-item {
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
}

.list-group-item:hover {
    border-left-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

/* File Icons */
.badge.rounded-circle {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

/* Purple color for audio files */
.bg-purple {
    background-color: #6f42c1 !important;
}

/* Download Button Animation */
.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: scale(1.05);
}
</style>

<!-- Student Courses Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Course Search Functionality with AJAX
    let searchTimeout;
    
    function filterCourses() {
        const searchTerm = $('#courseSearchInput').val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // If search is empty, show all courses
        if (searchTerm === '') {
            $('.course-card-wrapper').show();
            $('#searchResultCount').text($('.course-card-wrapper').length);
            $('#noResultsMessage').hide();
            return;
        }

        searchTimeout = setTimeout(function() {
            // Show loading state
            $('#courseSearchInput').prop('disabled', true);
            
            // Make AJAX request
            $.ajax({
                url: '<?= base_url('student/search_courses') ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    search: searchTerm,
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.success) {
                        // Hide all courses first
                        $('.course-card-wrapper').hide();
                        
                        // Show matching courses
                        let visibleCount = 0;
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(course) {
                                // Find matching course card by course_offering_id
                                $('.course-card-wrapper').each(function() {
                                    const courseCode = $(this).data('course-code');
                                    const searchableCourseCode = course.course_code.toLowerCase();
                                    
                                    if (courseCode === searchableCourseCode) {
                                        $(this).show();
                                        visibleCount++;
                                    }
                                });
                            });
                        }
                        
                        // Update result count
                        $('#searchResultCount').text(visibleCount);
                        
                        // Show/hide no results message
                        if (visibleCount === 0) {
                            $('#noResultsMessage').show();
                        } else {
                            $('#noResultsMessage').hide();
                        }
                    } else {
                        console.error('Search error:', response.message);
                        // Fall back to client-side search
                        filterCoursesClientSide(searchTerm);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    // Fall back to client-side search
                    filterCoursesClientSide(searchTerm);
                },
                complete: function() {
                    $('#courseSearchInput').prop('disabled', false);
                }
            });
        }, 600); 
    }
    
    // Fallback client-side search function
    function filterCoursesClientSide(searchTerm) {
        const searchLower = searchTerm.toLowerCase();
        let visibleCount = 0;
        
        $('.course-card-wrapper').each(function() {
            const courseCode = $(this).data('course-code') || '';
            const courseTitle = $(this).data('course-title') || '';
            const courseDescription = $(this).data('course-description') || '';
            const instructorName = $(this).data('instructor-name') || '';
            const term = $(this).data('term') || '';
            const academicYear = $(this).data('academic-year') || '';
            const semester = $(this).data('semester') || '';
            
            // Combine all searchable fields
            const searchableText = `${courseCode} ${courseTitle} ${courseDescription} ${instructorName} ${term} ${academicYear} ${semester}`;
            
            if (searchableText.includes(searchLower)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        // Update result count
        $('#searchResultCount').text(visibleCount);
        
        // Show/hide no results message
        if (visibleCount === 0 && searchTerm !== '') {
            $('#noResultsMessage').show();
        } else {
            $('#noResultsMessage').hide();
        }
    }
    
    // Search input event
    $('#courseSearchInput').on('keyup', filterCourses);
    
    // Clear search button
    $('#clearSearch, #clearSearchBtn').on('click', function() {
        $('#courseSearchInput').val('');
        $('.course-card-wrapper').show();
        $('#searchResultCount').text($('.course-card-wrapper').length);
        $('#noResultsMessage').hide();
        $('#courseSearchInput').focus();
    });

    // Materials toggle functionality
    const materialsButtons = document.querySelectorAll('.materials-toggle');
    materialsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Toggle icon
            if (isExpanded) {
                icon.className = 'fas fa-book me-1';
            } else {
                icon.className = 'fas fa-book-open me-1';
            }
        });
    });
});

// File preview function
function previewFile(fileName, extension) {
    const modal = new bootstrap.Modal(document.createElement('div'));
    const modalElement = document.createElement('div');
    modalElement.className = 'modal fade';
    modalElement.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file me-2"></i>File Preview: ${fileName}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="mb-3">
                            ${getFileIcon(extension, '4rem')}
                        </div>
                        <h6>${fileName}</h6>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <strong>File Type</strong><br>
                                        <span class="badge bg-primary">${extension.toUpperCase()}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <strong>Category</strong><br>
                                        <span class="text-muted">${getFileCategory(extension)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Click download to access this file on your device
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modalElement);
    const bsModal = new bootstrap.Modal(modalElement);
    bsModal.show();
    
    // Clean up modal after hiding
    modalElement.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modalElement);
    });
}

function getFileIcon(extension, size = '1rem') {
    const icons = {
        'pdf': '📄',
        'doc': '📝', 'docx': '📝',
        'xls': '📊', 'xlsx': '📊',
        'ppt': '📋', 'pptx': '📋',
        'txt': '📄',
        'jpg': '🖼️', 'jpeg': '🖼️', 'png': '🖼️', 'gif': '🖼️',
        'mp4': '🎥', 'avi': '🎥', 'mov': '🎥',
        'mp3': '🎵', 'wav': '🎵'
    };
    
    return `<span style="font-size: ${size}">${icons[extension] || '📎'}</span>`;
}

function getFileCategory(extension) {
    const categories = {
        'pdf': 'Document',
        'doc': 'Document', 'docx': 'Document', 'txt': 'Document', 'rtf': 'Document',
        'xls': 'Spreadsheet', 'xlsx': 'Spreadsheet',
        'ppt': 'Presentation', 'pptx': 'Presentation',
        'jpg': 'Image', 'jpeg': 'Image', 'png': 'Image', 'gif': 'Image',
        'mp4': 'Video', 'avi': 'Video', 'mov': 'Video',
        'mp3': 'Audio', 'wav': 'Audio'
    };
    
    return categories[extension] || 'File';
}

// Load pending enrollments on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPendingEnrollments();
});

function loadPendingEnrollments() {
    fetch('<?= base_url("/enrollment/pending") ?>', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.pending_enrollments && data.pending_enrollments.length > 0) {
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
        const enrollmentCard = `
            <div class="card mb-3 border-warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="card-title mb-1">
                                ${enrollment.course_code} - ${enrollment.course_title}
                            </h6>
                            <p class="text-muted mb-1">
                                <small>
                                    <i class="fas fa-graduation-cap me-1"></i>${enrollment.credits} credits | 
                                    <i class="fas fa-calendar me-1"></i>${enrollment.term_name} ${enrollment.academic_year} | 
                                    <i class="fas fa-user me-1"></i>Enrolled by: ${enrollment.enrolled_by_name}
                                </small>
                            </p>
                            <p class="text-muted mb-0">
                                <small>
                                    <i class="fas fa-clock me-1"></i>Enrollment Date: ${new Date(enrollment.enrollment_date).toLocaleDateString()}
                                </small>
                            </p>
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
            </div>
        `;
        container.innerHTML += enrollmentCard;
    });
}

function respondToEnrollment(enrollmentId, action) {
    if (!confirm(`Are you sure you want to ${action} this enrollment?`)) {
        return;
    }
    
    // Use FormData for proper POST handling
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
            // Reload pending enrollments
            loadPendingEnrollments();
            // Show success message
            showAlert(data.message, 'success');
            // Reload page after a short delay to update enrolled courses
            setTimeout(() => {
                window.location.reload();
            }, 1500);
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
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert alert at the top of the container
    const container = document.querySelector('.container');
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>

