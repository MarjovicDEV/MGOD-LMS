<?= $this->include('templates/header') ?>

<!-- Student Assignments View -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold"><i class="fas fa-tasks me-2"></i>My Assignments</h3>
                                <p class="mb-0 opacity-75">View and submit your course assignments</p>
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
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4 g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📝</div>
                    <div class="display-5 fw-bold"><?= count($upcoming) ?></div>
                    <div class="fw-semibold">Upcoming</div>
                    <small class="opacity-75">Due soon</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-danger text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">⚠️</div>
                    <div class="display-5 fw-bold"><?= count($overdue) ?></div>
                    <div class="fw-semibold">Overdue</div>
                    <small class="opacity-75">Past due date</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">⏳</div>
                    <div class="display-5 fw-bold"><?= count($submitted) ?></div>
                    <div class="fw-semibold">Submitted</div>
                    <small class="opacity-75">Awaiting grade</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= count($graded) ?></div>
                    <div class="fw-semibold">Graded</div>
                    <small class="opacity-75">Completed</small>
                </div>
            </div>
        </div>

        <!-- Assignments Tabs Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 py-3">
                <ul class="nav nav-pills" id="assignmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button">
                        <i class="fas fa-clock me-1"></i>Upcoming (<?= count($upcoming) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue" type="button">
                        <i class="fas fa-exclamation-triangle me-1"></i>Overdue (<?= count($overdue) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="submitted-tab" data-bs-toggle="tab" data-bs-target="#submitted" type="button">
                        <i class="fas fa-check-circle me-1"></i>Submitted (<?= count($submitted) ?>)
                    </button>
                </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="graded-tab" data-bs-toggle="tab" data-bs-target="#graded" type="button">
                            <i class="fas fa-star me-1"></i>Graded (<?= count($graded) ?>)
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="assignmentTabsContent">
                    <!-- Upcoming Assignments -->
                    <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
                        <?php if (empty($upcoming)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-check-double text-success" style="font-size: 4rem;"></i>
                                <p class="text-muted mt-3">No upcoming assignments. You're all caught up!</p>
                            </div>
                        <?php else: ?>
                        <div class="row">
                            <?php foreach ($upcoming as $assignment): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0"><?= esc($assignment['title']) ?></h5>
                                                <span class="badge bg-primary"><?= esc($assignment['type_name'] ?? 'Assignment') ?></span>
                                            </div>
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-book me-1"></i><?= esc($assignment['course_code']) ?> - <?= esc($assignment['section']) ?>
                                            </p>
                                            <p class="mb-2">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <strong>Due:</strong> <?= date('M j, Y g:i A', strtotime($assignment['due_date'])) ?>
                                            </p>
                                            <p class="mb-3">
                                                <i class="fas fa-star me-1"></i>
                                                <strong>Max Score:</strong> <?= esc($assignment['max_score']) ?>
                                            </p>
                                            <a href="<?= base_url('student/assignment/' . $assignment['id']) ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>View & Submit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Overdue Assignments -->
                <div class="tab-pane fade" id="overdue" role="tabpanel">
                    <?php if (empty($overdue)): ?>
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-smile text-success" style="font-size: 4rem;"></i>
                                <p class="text-muted mt-3">No overdue assignments!</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($overdue as $assignment): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card shadow-sm border-danger h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0"><?= esc($assignment['title']) ?></h5>
                                                <span class="badge bg-danger">Overdue</span>
                                            </div>
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-book me-1"></i><?= esc($assignment['course_code']) ?> - <?= esc($assignment['section']) ?>
                                            </p>
                                            <p class="mb-2 text-danger">
                                                <i class="fas fa-calendar-times me-1"></i>
                                                <strong>Was Due:</strong> <?= date('M j, Y g:i A', strtotime($assignment['due_date'])) ?>
                                            </p>
                                            <p class="mb-3">
                                                <i class="fas fa-star me-1"></i>
                                                <strong>Max Score:</strong> <?= esc($assignment['max_score']) ?>
                                            </p>
                                            <?php if ($assignment['allow_late_submission']): ?>
                                                <a href="<?= base_url('student/assignment/' . $assignment['id']) ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Submit Late
                                                </a>
                                                <?php if ($assignment['late_penalty_percentage'] > 0): ?>
                                                    <small class="text-muted d-block mt-2">
                                                        Late penalty: <?= esc($assignment['late_penalty_percentage']) ?>%
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="fas fa-lock me-1"></i>Late Submission Not Allowed
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Submitted Assignments -->
                <div class="tab-pane fade" id="submitted" role="tabpanel">
                    <?php if (empty($submitted)): ?>
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                                <p class="text-muted mt-3">No submitted assignments pending grading.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($submitted as $assignment): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0"><?= esc($assignment['title']) ?></h5>
                                                <span class="badge bg-warning">Pending Grading</span>
                                            </div>
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-book me-1"></i><?= esc($assignment['course_code']) ?> - <?= esc($assignment['section']) ?>
                                            </p>
                                            <p class="mb-2">
                                                <i class="fas fa-check me-1"></i>
                                                <strong>Submitted:</strong> <?= date('M j, Y g:i A', strtotime($assignment['submitted_at'])) ?>
                                            </p>
                                            <?php if ($assignment['is_late']): ?>
                                                <p class="mb-2 text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    <strong>Late Submission</strong>
                                                </p>
                                            <?php endif; ?>
                                            <a href="<?= base_url('student/assignment/' . $assignment['id']) ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Graded Assignments -->
                <div class="tab-pane fade" id="graded" role="tabpanel">
                    <?php if (empty($graded)): ?>
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-clipboard-list text-muted" style="font-size: 4rem;"></i>
                                <p class="text-muted mt-3">No graded assignments yet.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($graded as $assignment): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0"><?= esc($assignment['title']) ?></h5>
                                                <span class="badge bg-success">Graded</span>
                                            </div>
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-book me-1"></i><?= esc($assignment['course_code']) ?> - <?= esc($assignment['section']) ?>
                                            </p>
                                            <p class="mb-2">
                                                <i class="fas fa-check me-1"></i>
                                                <strong>Submitted:</strong> <?= date('M j, Y', strtotime($assignment['submitted_at'])) ?>
                                            </p>
                                            <div class="mb-3">
                                                <?php 
                                                $score = $assignment['score'] ?? 0;
                                                $percentage = $assignment['max_score'] > 0 ? ($score / $assignment['max_score']) * 100 : 0;
                                                $scoreClass = $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                                                ?>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-star me-2 text-<?= $scoreClass ?>"></i>
                                                    <strong class="text-<?= $scoreClass ?>" style="font-size: 1.2rem;">
                                                        <?= esc($score) ?> / <?= esc($assignment['max_score']) ?>
                                                    </strong>
                                                    <span class="ms-2 text-muted">(<?= number_format($percentage, 1) ?>%)</span>
                                                </div>
                                            </div>
                                            <a href="<?= base_url('student/assignment/' . $assignment['id']) ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-eye me-1"></i>View Feedback
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
