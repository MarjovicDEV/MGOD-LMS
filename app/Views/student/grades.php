<?= $this->include('templates/header') ?>

<!-- Student Grades View - Shows grades for all enrolled courses -->
<div class="lms-dashboard lms-role-view min-vh-100 student-grades-page">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3 role-hero">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold"><i class="fas fa-graduation-cap me-2"></i>My Grades</h2>
                                <p class="mb-0 opacity-75">View your academic performance across all enrolled courses</p>
                            </div>
                            <div>
                                <a href="<?= base_url('student/grades/download') ?>" class="btn btn-light btn-sm me-2">
                                    <i class="fas fa-download me-1"></i> Download All
                                </a>
                                <a href="<?= base_url('student/dashboard') ?>" class="btn btn-outline-light btn-sm">
                                    ← Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grade Statistics Cards -->
        <div class="row mb-4 g-4 role-stats">
            <!-- Total Courses -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3"><i class="fas fa-book"></i></div>
                    <div class="display-5 fw-bold"><?= $totalCourses ?></div>
                    <div class="fw-semibold">Total Courses</div>
                    <small class="opacity-75">Enrolled this term</small>
                </div>
            </div>
            
            <!-- Completed Courses -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3"><i class="fas fa-check-circle"></i></div>
                    <div class="display-5 fw-bold"><?= $completedCourses ?></div>
                    <div class="fw-semibold">Courses Passed</div>
                    <small class="opacity-75">Grade 75 or above</small>
                </div>
            </div>
            
            <!-- Total Units -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3"><i class="fas fa-calculator"></i></div>
                    <div class="display-5 fw-bold"><?= $totalUnits ?></div>
                    <div class="fw-semibold">Total Units</div>
                    <small class="opacity-75">Credit hours</small>
                </div>
            </div>
            
            <!-- Weighted Average -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3"><i class="fas fa-chart-line"></i></div>
                    <div class="display-5 fw-bold"><?= number_format($gpa, 2) ?></div>
                    <div class="fw-semibold">Weighted Average</div>
                    <small class="opacity-75">Overall GPA</small>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Grades Content -->
        <?php if (empty($courses)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> You are not enrolled in any courses yet.
                <a href="<?= base_url('student/courses') ?>" class="alert-link">Browse available courses</a>
            </div>
        <?php else: ?>
            <!-- Grades Table -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-table me-2"></i>Grade Summary</h5>
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control border-start-0" id="gradeSearch" placeholder="Search courses...">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="gradesTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Course</th>
                                    <th class="px-3 py-3">Section</th>
                                    <th class="px-3 py-3">Credits</th>
                                    <th class="px-3 py-3">Term</th>
                                    <th class="px-3 py-3">Instructor</th>
                                    <th class="px-3 py-3 text-center">Grade</th>
                                    <th class="px-3 py-3 text-center">Status</th>
                                    <th class="px-3 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                    <?php 
                                        $enrollment = $course['enrollment'];
                                        $gradeValue = $course['grade_value'];
                                        $gradeClass = $course['grade_class'];
                                    ?>
                                    <tr class="grade-row">
                                        <td class="px-4 py-3">
                                            <div class="fw-semibold"><?= esc($enrollment['course_code']) ?></div>
                                            <small class="text-muted"><?= esc($enrollment['course_title']) ?></small>
                                        </td>
                                        <td class="px-3 py-3"><?= esc($enrollment['section']) ?></td>
                                        <td class="px-3 py-3"><?= esc($course['credits']) ?></td>
                                        <td class="px-3 py-3">
                                            <small><?= esc($enrollment['semester_name']) ?><br><?= esc($enrollment['academic_year']) ?></small>
                                        </td>
                                        <td class="px-3 py-3"><?= esc($course['instructor_name']) ?></td>
                                        <td class="px-3 py-3 text-center">
                                            <?php if ($gradeValue > 0): ?>
                                                <span class="badge bg-<?= $gradeClass ?> fs-6 px-3 py-2">
                                                    <?= number_format($gradeValue, 2) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary px-3 py-2">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <?php if ($gradeValue >= 75): ?>
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fas fa-check me-1"></i>Passed
                                                </span>
                                            <?php elseif ($gradeValue > 0): ?>
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="fas fa-times me-1"></i>Failed
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="fas fa-clock me-1"></i>In Progress
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('student/gradebook/course/' . $enrollment['id']) ?>" 
                                                   class="btn btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('student/grades/download/' . $enrollment['id']) ?>" 
                                                   class="btn btn-outline-success" title="Download Grade">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Grade Cards (Alternative View) -->
            <div class="row g-4" id="gradeCards">
                <?php foreach ($courses as $course): ?>
                    <?php 
                        $enrollment = $course['enrollment'];
                        $gradeValue = $course['grade_value'];
                        $gradeClass = $course['grade_class'];
                        $periodGrades = $course['period_grades'];
                    ?>
                    <div class="col-md-6 col-lg-4 grade-card">
                        <div class="card h-100 border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold"><?= esc($enrollment['course_code']) ?></h5>
                                        <p class="card-text text-muted small mb-0"><?= esc($enrollment['course_title']) ?></p>
                                    </div>
                                    <span class="badge bg-<?= $gradeClass ?>-subtle text-<?= $gradeClass ?>">
                                        <?= $course['grade_label'] ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Section</small>
                                        <div class="fw-semibold"><?= esc($enrollment['section']) ?></div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Credits</small>
                                        <div class="fw-semibold"><?= esc($course['credits']) ?> units</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Instructor</small>
                                    <div class="fw-semibold"><?= esc($course['instructor_name']) ?></div>
                                </div>

                                <!-- Grade Display -->
                                <div class="text-center py-3 mb-3 bg-light rounded-3">
                                    <small class="text-muted d-block mb-1">Current Grade</small>
                                    <h2 class="mb-0 text-<?= $gradeClass ?>">
                                        <?= $gradeValue > 0 ? number_format($gradeValue, 2) : 'N/A' ?>
                                    </h2>
                                </div>

                                <!-- Progress Bar -->
                                <?php if ($gradeValue > 0): ?>
                                    <div class="progress mb-3" style="height: 8px;">
                                        <div class="progress-bar bg-<?= $gradeClass ?>" 
                                             role="progressbar" 
                                             style="width: <?= min($gradeValue, 100) ?>%"
                                             aria-valuenow="<?= $gradeValue ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Period Breakdown -->
                                <?php if (!empty($periodGrades)): ?>
                                    <div class="small">
                                        <div class="fw-semibold mb-2">Period Breakdown:</div>
                                        <?php foreach ($periodGrades as $period): ?>
                                            <?php if ($period['grading_period_id'] !== null && isset($period['final_grade'])): ?>
                                                <div class="d-flex justify-content-between border-bottom py-1">
                                                    <span class="text-muted"><?= esc($period['period_name'] ?? 'Period') ?></span>
                                                    <strong><?= number_format($period['final_grade'], 2) ?></strong>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white border-top-0 pb-4">
                                <div class="d-flex gap-2">
                                    <a href="<?= base_url('student/gradebook/course/' . $enrollment['id']) ?>" 
                                       class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-chart-line me-1"></i> View Details
                                    </a>
                                    <a href="<?= base_url('student/grades/download/' . $enrollment['id']) ?>" 
                                       class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Search functionality
document.getElementById('gradeSearch')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    
    // Filter table rows
    document.querySelectorAll('.grade-row').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
    
    // Filter cards
    document.querySelectorAll('.grade-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

</body>
</html>
