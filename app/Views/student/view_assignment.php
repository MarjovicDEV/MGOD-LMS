<?= $this->include('templates/header') ?>

<?php 
$dueDate = strtotime($assignment['due_date']);
$now = time();
$isOverdue = $dueDate < $now;
?>

<!-- Student View Assignment -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('student/assignments') ?>">Assignments</a></li>
                <li class="breadcrumb-item active"><?= esc($assignment['title']) ?></li>
            </ol>
        </nav>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold"><i class="fas fa-clipboard-list me-2"></i><?= esc($assignment['title']) ?></h3>
                                <p class="mb-0 opacity-75">
                                    <?= esc($assignment['course_code']) ?> - <?= esc($assignment['course_title']) ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <?php if ($isOverdue): ?>
                                    <span class="badge bg-danger fs-6 mb-2">⚠️ Overdue</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-primary fs-6 mb-2">📝 Open</span>
                                <?php endif; ?>
                                <p class="mb-0 opacity-75 small">
                                    <i class="fas fa-star me-1"></i>Max Score: <?= esc($assignment['max_score']) ?> points
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Details Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">📋 Assignment Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-tag me-2 text-primary"></i>Type:</strong> <?= esc($assignment['type_name'] ?? 'Assignment') ?></p>
                                <p><strong><i class="fas fa-star me-2 text-warning"></i>Max Score:</strong> <?= esc($assignment['max_score']) ?> points</p>
                            </div>
                            <div class="col-md-6">
                                <p>
                                    <strong><i class="fas fa-calendar-alt me-2 text-info"></i>Due Date:</strong> 
                                    <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                        <?= date('M j, Y g:i A', $dueDate) ?>
                                    </span>
                                </p>
                                <?php if ($isOverdue): ?>
                                    <div class="alert alert-danger py-2 mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>This assignment is overdue!
                                        <?php if ($assignment['allow_late_submission']): ?>
                                            <br><small>Late submissions allowed with <?= esc($assignment['late_penalty_percentage']) ?>% penalty.</small>
                                        <?php else: ?>
                                            <br><small>Late submissions are not allowed.</small>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($assignment['description']): ?>
                            <hr>
                            <div class="mb-3">
                                <h6 class="fw-bold"><i class="fas fa-info-circle me-2 text-secondary"></i>Description</h6>
                                <div class="border rounded p-3 bg-light">
                                    <?= nl2br(esc($assignment['description'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($assignment['instructions']): ?>
                            <div class="mb-0">
                                <h6 class="fw-bold"><i class="fas fa-list-ol me-2 text-secondary"></i>Instructions</h6>
                                <div class="border rounded p-3 bg-light">
                                    <?= nl2br(esc($assignment['instructions'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($submission && $submission['status'] === 'graded'): ?>
            <!-- Graded Submission View -->
            <div class="card border-0 shadow-sm rounded-3 border-start border-success border-4 mb-4">
                <div class="card-header bg-success text-white py-3 rounded-top">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-check-circle me-2"></i>Graded Submission</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-clock me-2 text-muted"></i>Submitted At:</strong> <?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?></p>
                            <?php if ($submission['is_late']): ?>
                                <span class="badge bg-danger">Late Submission</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php 
                            $score = $submission['score'] ?? 0;
                            $percentage = $assignment['max_score'] > 0 ? ($score / $assignment['max_score']) * 100 : 0;
                            $scoreClass = $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                            ?>
                            <div class="text-center p-3 bg-<?= $scoreClass ?> bg-opacity-10 rounded-3 border border-<?= $scoreClass ?>">
                                <h2 class="text-<?= $scoreClass ?> mb-0 fw-bold">
                                    <?= esc($score) ?> / <?= esc($assignment['max_score']) ?>
                                </h2>
                                <p class="text-muted mb-0"><?= number_format($percentage, 1) ?>%</p>
                            </div>
                        </div>
                    </div>

                    <?php if ($submission['submission_text']): ?>
                        <div class="mb-3">
                            <h6 class="fw-bold"><i class="fas fa-file-alt me-2 text-secondary"></i>Your Text Submission:</h6>
                            <div class="border rounded p-3 bg-light">
                                <?= nl2br(esc($submission['submission_text'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($submission['file_path']): ?>
                        <div class="mb-3">
                            <h6 class="fw-bold"><i class="fas fa-file-download me-2 text-secondary"></i>Your File Submission:</h6>
                            <a href="<?= base_url('submission/download/' . $submission['id']) ?>" class="btn btn-outline-primary">
                                <i class="fas fa-download me-2"></i>Download Your Submission
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($submission['feedback']): ?>
                        <div class="mb-0">
                            <h6 class="fw-bold"><i class="fas fa-comment-dots me-2 text-primary"></i>Teacher's Feedback:</h6>
                            <div class="border border-primary rounded p-3 bg-primary bg-opacity-10">
                                <?= nl2br(esc($submission['feedback'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($submission && $submission['status'] === 'submitted'): ?>
            <!-- Pending Grading View -->
            <div class="card border-0 shadow-sm rounded-3 border-start border-warning border-4 mb-4">
                <div class="card-header bg-warning text-dark py-3 rounded-top">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-clock me-2"></i>Submission Pending Grading</h5>
                </div>
                <div class="card-body">
                    <p><strong><i class="fas fa-calendar-check me-2 text-muted"></i>Submitted At:</strong> <?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?></p>
                    <?php if ($submission['is_late']): ?>
                        <span class="badge bg-danger mb-3">Late Submission</span>
                    <?php endif; ?>

                    <?php if ($submission['submission_text']): ?>
                        <div class="mb-3">
                            <h6 class="fw-bold"><i class="fas fa-file-alt me-2 text-secondary"></i>Your Text Submission:</h6>
                            <div class="border rounded p-3 bg-light">
                                <?= nl2br(esc($submission['submission_text'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($submission['file_path']): ?>
                        <div class="mb-3">
                            <h6 class="fw-bold"><i class="fas fa-file-download me-2 text-secondary"></i>Your File Submission:</h6>
                            <a href="<?= base_url('submission/download/' . $submission['id']) ?>" class="btn btn-outline-primary">
                                <i class="fas fa-download me-2"></i>Download Your Submission
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>Your submission is being reviewed by your teacher. You will be notified once it's graded.
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Submission Form -->
            <?php 
            $submissionType = $assignment['submission_type'] ?? 'both';
            $showText = in_array($submissionType, ['text', 'both']);
            $showFile = in_array($submissionType, ['file', 'both']);
            ?>
            <?php if (!$isOverdue || $assignment['allow_late_submission']): ?>
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-info text-white py-3 rounded-top">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-upload me-2"></i>Submit Assignment</h5>
                    </div>
                    <div class="card-body">
                        <!-- Submission Type Info -->
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Submission Type:</strong> 
                            <?php if ($submissionType === 'text'): ?>
                                <span class="badge bg-primary">Text Only</span> - Submit your answer as text
                            <?php elseif ($submissionType === 'file'): ?>
                                <span class="badge bg-success">File Upload Only</span> - Upload PDF or PPT presentation
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Text & File</span> - You can submit text, file, or both
                            <?php endif; ?>
                        </div>

                        <form id="submissionForm" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                            <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">

                            <?php if ($showText): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-align-left me-2"></i>Text Submission <?= $submissionType === 'text' ? '<span class="text-danger">*</span>' : '(Optional)' ?>
                                </label>
                                <textarea name="submission_text" class="form-control" rows="6" 
                                          placeholder="Enter your answer or response here..." <?= $submissionType === 'text' ? 'required' : '' ?>></textarea>
                                <small class="text-muted">Type your answer directly here.</small>
                            </div>
                            <?php endif; ?>

                            <?php if ($showFile): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-file-upload me-2"></i>File Upload <?= $submissionType === 'file' ? '<span class="text-danger">*</span>' : '(Optional)' ?>
                                </label>
                                <input type="file" name="submission_file" class="form-control" 
                                       accept=".pdf,.ppt,.pptx" <?= $submissionType === 'file' ? 'required' : '' ?>>
                                <small class="text-muted">Accepted formats: PDF, PPT. Max size: 10MB</small>
                            </div>
                            <?php endif; ?>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php if ($submissionType === 'text'): ?>
                                    <strong>Note:</strong> Text submission is required for this assignment.
                                <?php elseif ($submissionType === 'file'): ?>
                                    <strong>Note:</strong> File upload is required for this assignment.
                                <?php else: ?>
                                    <strong>Note:</strong> You must provide either a text submission or upload a file (or both).
                                <?php endif; ?>
                            </div>

                            <div id="submitMessage"></div>

                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Submit Assignment
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-lock me-2"></i>
                    <strong>Submission Closed:</strong> This assignment is overdue and late submissions are not allowed.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('submissionForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const messageDiv = document.getElementById('submitMessage');
    const formData = new FormData(this);
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    messageDiv.innerHTML = '';
    
    fetch('<?= base_url('student/submit_assignment') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            messageDiv.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Assignment';
        }
    })
    .catch(error => {
        messageDiv.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>An error occurred while submitting. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Assignment';
        console.error('Error:', error);
    });
});
</script>
