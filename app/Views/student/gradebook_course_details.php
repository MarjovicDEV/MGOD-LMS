<?= $this->include('templates/header') ?>

<!-- Student Gradebook Course Details View -->
<div class="lms-dashboard lms-role-view min-vh-100 student-gradebook-details-page">
    <div class="container py-4">
        <!-- Breadcrumb & Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('student/gradebook') ?>">Gradebook</a>
                        </li>
                        <li class="breadcrumb-item active"><?= esc($enrollment['course_code']) ?></li>
                    </ol>
                </nav>
                <h2 class="fw-bold"><?= esc($enrollment['course_code']) ?> - <?= esc($enrollment['course_title']) ?></h2>
                <p class="text-muted">
                    Section: <?= esc($enrollment['section']) ?> | 
                    <?= esc($enrollment['semester_name']) ?> <?= esc($enrollment['academic_year']) ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group">
                    <a href="<?= base_url('student/gradebook/export/pdf/' . ($enrollment['enrollment_id'] ?? $enrollment['id'])) ?>" 
                       class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </a>
                    <a href="<?= base_url('student/gradebook/export/excel/' . ($enrollment['enrollment_id'] ?? $enrollment['id'])) ?>" 
                       class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#overview">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#assignments">Assignments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#breakdown">Grade Breakdown</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Overview Tab -->
            <div id="overview" class="tab-pane fade show active">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body text-center py-4">
                                <h6 class="text-muted">Current Grade</h6>
                                <?php 
                                    $finalGrade = $breakdown['final']['final_grade'] ?? 0;
                                    $gradeClass = $finalGrade >= 90 ? 'success' : 
                                                 ($finalGrade >= 80 ? 'primary' : 
                                                 ($finalGrade >= 75 ? 'warning' : 'danger'));
                                ?>
                                <h1 class="text-<?= $gradeClass ?> display-4 fw-bold"><?= number_format($finalGrade, 2) ?></h1>
                                <?php if ($breakdown['final']['is_overridden'] ?? false): ?>
                                    <span class="badge bg-info">Adjusted</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="mb-0 fw-bold">Grading Period Breakdown</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($breakdown['periods'])): ?>
                                    <?php foreach ($breakdown['periods'] as $period): ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span><strong><?= esc($period['period_name'] ?? 'Period') ?></strong></span>
                                                <span><?= number_format($period['final_grade'] ?? 0, 2) ?>%</span>
                                            </div>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-primary" 
                                                     style="width: <?= min($period['final_grade'] ?? 0, 100) ?>%">
                                                    Weight: <?= $period['period_weight'] ?? 0 ?>%
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No grading periods configured yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignments Tab -->
            <div id="assignments" class="tab-pane fade">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3">Assignment</th>
                                        <th class="px-3 py-3">Type</th>
                                        <th class="px-3 py-3">Period</th>
                                        <th class="px-3 py-3">Due Date</th>
                                        <th class="px-3 py-3">Score</th>
                                        <th class="px-3 py-3">Status</th>
                                        <th class="px-3 py-3">Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($submissions)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No graded assignments yet</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($submissions as $sub): ?>
                                            <tr>
                                                <td class="px-4 py-3"><?= esc($sub['title']) ?></td>
                                                <td class="px-3 py-3"><?= esc($sub['type_name'] ?? 'N/A') ?></td>
                                                <td class="px-3 py-3"><?= esc($sub['period_name'] ?? 'N/A') ?></td>
                                                <td class="px-3 py-3"><?= date('M d, Y', strtotime($sub['due_date'])) ?></td>
                                                <td class="px-3 py-3">
                                                    <?php if ($sub['status'] === 'graded'): ?>
                                                        <strong><?= $sub['score'] ?> / <?= $sub['max_score'] ?></strong>
                                                        <small class="text-muted">(<?= number_format(($sub['score'] / $sub['max_score']) * 100, 1) ?>%)</small>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not graded</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <?php if ($sub['status'] === 'graded'): ?>
                                                        <span class="badge bg-success">Graded</span>
                                                    <?php elseif ($sub['status'] === 'submitted'): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= ucfirst($sub['status']) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($sub['is_late']) && $sub['is_late']): ?>
                                                        <span class="badge bg-danger">Late</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <?php if (!empty($sub['feedback'])): ?>
                                                        <button type="button" class="btn btn-sm btn-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#feedbackModal<?= $sub['id'] ?>">
                                                            View
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grade Breakdown Tab -->
            <div id="breakdown" class="tab-pane fade">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">How Your Grade is Calculated</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Your final grade is calculated using a weighted average of grading periods.
                        </p>

                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Component</th>
                                    <th>Weight</th>
                                    <th>Your Grade</th>
                                    <th>Contribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $totalContribution = 0;
                                    if (!empty($breakdown['periods'])):
                                        foreach ($breakdown['periods'] as $period): 
                                            $periodGrade = $period['final_grade'] ?? 0;
                                            $periodWeight = $period['period_weight'] ?? 0;
                                            $contribution = ($periodGrade * $periodWeight) / 100;
                                            $totalContribution += $contribution;
                                ?>
                                    <tr>
                                        <td><?= esc($period['period_name'] ?? 'Period') ?></td>
                                        <td><?= $periodWeight ?>%</td>
                                        <td><?= number_format($periodGrade, 2) ?></td>
                                        <td><?= number_format($contribution, 2) ?></td>
                                    </tr>
                                <?php 
                                        endforeach;
                                    endif;
                                ?>
                                <tr class="table-primary">
                                    <td colspan="3"><strong>Final Grade</strong></td>
                                    <td><strong><?= number_format($totalContribution, 2) ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Modals -->
<?php if (!empty($submissions)): ?>
    <?php foreach ($submissions as $sub): ?>
        <?php if (!empty($sub['feedback'])): ?>
            <div class="modal fade" id="feedbackModal<?= $sub['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Feedback: <?= esc($sub['title']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><?= nl2br(esc($sub['feedback'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
        crossorigin="anonymous"></script>
</body>
</html>
