<?= $this->include('templates/header') ?>

<!-- Student Gradebook View -->
<div class="lms-dashboard lms-role-view min-vh-100 student-gradebook-page">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3 role-hero">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold"><i class="fas fa-book-open me-2"></i>Detailed Gradebook</h2>
                                <p class="mb-0 opacity-75">View your academic performance across all courses</p>
                            </div>
                            <div>
                                <a href="<?= base_url('student/grades') ?>" class="btn btn-light btn-sm me-2">
                                    <i class="fas fa-chart-bar me-1"></i> Grade Summary
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

        <?php if (empty($courses)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> You are not enrolled in any courses yet.
                <a href="<?= base_url('student/courses') ?>" class="alert-link">Browse available courses</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($courses as $courseData): ?>
                    <?php 
                        $enrollment = $courseData['enrollment'];
                        $finalGrade = $courseData['final_grade'];
                        $periodGrades = $courseData['period_grades'];
                        
                        $gradeValue = $finalGrade['final_grade'] ?? 0;
                        $gradeClass = '';
                        if ($gradeValue >= 90) {
                            $gradeClass = 'success';
                        } elseif ($gradeValue >= 80) {
                            $gradeClass = 'primary';
                        } elseif ($gradeValue >= 75) {
                            $gradeClass = 'warning';
                        } else {
                            $gradeClass = 'danger';
                        }
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-3">
                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?= esc($enrollment['course_code']) ?></h5>
                                <p class="card-text text-muted"><?= esc($enrollment['course_title']) ?></p>
                                <p class="small text-muted mb-3">
                                    Section: <?= esc($enrollment['section']) ?><br>
                                    <?= esc($enrollment['semester_name']) ?> <?= esc($enrollment['academic_year']) ?>
                                </p>

                                <div class="text-center mb-3 py-3 bg-light rounded-3">
                                    <h2 class="text-<?= $gradeClass ?> mb-0">
                                        <?= number_format($gradeValue, 2) ?>
                                    </h2>
                                    <small class="text-muted">Current Grade</small>
                                </div>

                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar bg-<?= $gradeClass ?>" 
                                         role="progressbar" 
                                         style="width: <?= min($gradeValue, 100) ?>%"
                                         aria-valuenow="<?= $gradeValue ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>

                                <div class="small mb-3">
                                    <?php foreach ($periodGrades as $period): ?>
                                        <?php if ($period['grading_period_id'] !== null): ?>
                                            <div class="d-flex justify-content-between border-bottom py-1">
                                                <span class="text-muted"><?= esc($period['period_name'] ?? 'Period') ?>:</span>
                                                <strong><?= number_format($period['final_grade'] ?? 0, 2) ?></strong>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>

                                <a href="<?= base_url('student/gradebook/course/' . $enrollment['id']) ?>" 
                                   class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-chart-line me-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
