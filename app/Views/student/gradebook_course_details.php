<?= $this->extend('templates/student_template') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('student/gradebook') ?>">My Grades</a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($enrollment['course_code']) ?></li>
                </ol>
            </nav>
            <h2><?= esc($enrollment['course_code']) ?> - <?= esc($enrollment['course_title']) ?></h2>
            <p class="text-muted">
                Section: <?= esc($enrollment['section']) ?> | 
                <?= esc($enrollment['semester_name']) ?> <?= esc($enrollment['academic_year']) ?>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <a href="<?= base_url('student/gradebook/export/pdf/' . $enrollment['enrollment_id']) ?>" 
                   class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
                <a href="<?= base_url('student/gradebook/export/excel/' . $enrollment['enrollment_id']) ?>" 
                   class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Export Excel
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
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Current Grade</h6>
                            <?php 
                                $finalGrade = $breakdown['final']['final_grade'] ?? 0;
                                $gradeClass = $finalGrade >= 90 ? 'success' : 
                                             ($finalGrade >= 80 ? 'primary' : 
                                             ($finalGrade >= 75 ? 'warning' : 'danger'));
                            ?>
                            <h1 class="text-<?= $gradeClass ?>"><?= number_format($finalGrade, 2) ?></h1>
                            <?php if ($breakdown['final']['is_overridden'] ?? false): ?>
                                <span class="badge bg-info">Adjusted</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Grading Period Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($breakdown['periods'] as $period): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span><strong><?= esc($period['period_name'] ?? 'Period') ?></strong></span>
                                        <span><?= number_format($period['final_grade'], 2) ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-primary" 
                                             style="width: <?= min($period['final_grade'], 100) ?>%">
                                            Weight: <?= $period['period_weight'] ?>%
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments Tab -->
        <div id="assignments" class="tab-pane fade">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Type</th>
                                    <th>Period</th>
                                    <th>Due Date</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($submissions)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No graded assignments yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($submissions as $sub): ?>
                                        <tr>
                                            <td><?= esc($sub['title']) ?></td>
                                            <td><?= esc($sub['type_name'] ?? 'N/A') ?></td>
                                            <td><?= esc($sub['period_name'] ?? 'N/A') ?></td>
                                            <td><?= date('M d, Y', strtotime($sub['due_date'])) ?></td>
                                            <td>
                                                <?php if ($sub['status'] === 'graded'): ?>
                                                    <strong><?= $sub['score'] ?> / <?= $sub['max_score'] ?></strong>
                                                    (<?= number_format(($sub['score'] / $sub['max_score']) * 100, 1) ?>%)
                                                <?php else: ?>
                                                    <span class="text-muted">Not graded</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
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
                                            <td>
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
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">How Your Grade is Calculated</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Your final grade is calculated using a weighted average of grading periods.
                    </p>

                    <table class="table table-bordered">
                        <thead>
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
                                foreach ($breakdown['periods'] as $period): 
                                    $contribution = ($period['final_grade'] * $period['period_weight']) / 100;
                                    $totalContribution += $contribution;
                            ?>
                                <tr>
                                    <td><?= esc($period['period_name'] ?? 'Period') ?></td>
                                    <td><?= $period['period_weight'] ?>%</td>
                                    <td><?= number_format($period['final_grade'], 2) ?></td>
                                    <td><?= number_format($contribution, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-active">
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

<!-- Feedback Modals -->
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
<?= $this->endSection() ?>
