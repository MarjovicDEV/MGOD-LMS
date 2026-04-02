<?= $this->extend('templates/student_template') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>My Grades</h2>
            <p class="text-muted">View your academic performance across all courses</p>
        </div>
    </div>

    <?php if (empty($courses)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> You are not enrolled in any courses yet.
        </div>
    <?php else: ?>
        <div class="row">
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
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= esc($enrollment['course_code']) ?></h5>
                            <p class="card-text text-muted"><?= esc($enrollment['course_title']) ?></p>
                            <p class="small text-muted mb-3">
                                Section: <?= esc($enrollment['section']) ?><br>
                                <?= esc($enrollment['semester_name']) ?> <?= esc($enrollment['academic_year']) ?>
                            </p>

                            <div class="text-center mb-3">
                                <h2 class="text-<?= $gradeClass ?>">
                                    <?= number_format($gradeValue, 2) ?>
                                </h2>
                                <small class="text-muted">Current Grade</small>
                            </div>

                            <div class="progress mb-3" style="height: 20px;">
                                <div class="progress-bar bg-<?= $gradeClass ?>" 
                                     role="progressbar" 
                                     style="width: <?= min($gradeValue, 100) ?>%"
                                     aria-valuenow="<?= $gradeValue ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?= number_format($gradeValue, 1) ?>%
                                </div>
                            </div>

                            <div class="small mb-3">
                                <?php foreach ($periodGrades as $period): ?>
                                    <?php if ($period['grading_period_id'] !== null): ?>
                                        <div class="d-flex justify-content-between">
                                            <span><?= esc($period['period_name'] ?? 'Period') ?>:</span>
                                            <strong><?= number_format($period['final_grade'], 2) ?></strong>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>

                            <a href="<?= base_url('student/gradebook/course/' . $enrollment['id']) ?>" 
                               class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-chart-line"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
