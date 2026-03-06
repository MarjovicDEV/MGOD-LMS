<?= $this->include('templates/header') ?>

<!-- Assignment Detail View -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="<?= base_url('student/assignments') ?>" class="btn btn-outline-secondary">
                ← Back to Assignments
            </a>
        </div>

        <!-- Assignment Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="mb-1"><?= esc($assignment['title']) ?></h4>
                        <p class="text-muted mb-0">
                            <i class="fas fa-book me-1"></i><?= esc($assignment['course_code']) ?> - <?= esc($assignment['section']) ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary"><?= esc($assignment['type_name'] ?? 'Assignment') ?></span>
                        <p class="mb-0 mt-2">
                            <i class="fas fa-star me-1"></i>
                            <strong>Max Score:</strong> <?= esc($assignment['max_score']) ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Assignment Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <i class="fas fa-calendar-alt me-1"></i>
                            <strong>Due Date:</strong>
                            <span class="<?= $isOverdue ? 'text-danger' : '' ?>">
                                <?= date('M j, Y g:i A', strtotime($assignment['due_date'])) ?>
                            </span>
                        </p>
                        <?php if ($assignment['available_from']): ?>
                            <p class="mb-2">
                                <i class="fas fa-clock me-1"></i>
                                <strong>Available From:</strong>
                                <?= date('M j, Y g:i A', strtotime($assignment['available_from'])) ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($assignment['available_until']): ?>
                            <p class="mb-2">
                                <i class="fas fa-clock me-1"></i>
                                <strong>Available Until:</strong>
                                <?= date('M j, Y g:i A', strtotime($assignment['available_until'])) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <?php if ($isOverdue && $assignment['allow_late_submission']): ?>
                            <div class="alert alert-warning mb-2">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Late submission allowed
                                <?php if ($assignment['late_penalty_percentage'] > 0): ?>
                                    <br>Penalty: <?= esc($assignment['late_penalty_percentage']) ?>%
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($submission): ?>
                            <div class="alert alert-<?= $submission['status'] === 'graded' ? 'success' : 'info' ?> mb-2">
                                <i class="fas fa-<?= $submission['status'] === 'graded' ? 'check-circle' : 'clock' ?> me-1"></i>
                                Status: <?= ucfirst($submission['status']) ?>
                                <?php if ($submission['submitted_at']): ?>
                                    <br>Submitted: <?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Description -->
                <?php if ($assignment['description']): ?>
                    <div class="mb-4">
                        <h5 class="fw-bold">Description</h5>
                        <p class="text-muted"><?= nl2br(esc($assignment['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Instructions -->
                <?php if ($assignment['instructions']): ?>
                    <div class="mb-4">
                        <h5 class="fw-bold">Instructions</h5>
                        <div class="bg-light p-3 rounded">
                            <?= nl2br(esc($assignment['instructions'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Assignment Attachment -->
                <?php if ($assignment['attachment_path']): ?>
                    <div class="mb-4">
                        <h5 class="fw-bold">Assignment Attachment</h5>
                        <div class="card border-secondary">
                            <div class="card-body">
                                <?php 
                                $fileExtension = pathinfo($assignment['attachment_path'], PATHINFO_EXTENSION);
                                $fileName = basename($assignment['attachment_path']);
                                ?>
                                
                                <?php if (strtolower($fileExtension) === 'pdf'): ?>
                                    <!-- PDF Document -->
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-file-pdf fa-3x text-danger me-3"></i>
                                        <div>
                                            <p class="mb-1 fw-bold"><?= $fileName ?></p>
                                            <p class="mb-0 text-muted">PDF document - Download to view</p>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Preview not available in browser. Please download the file to view the assignment attachment.
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-file-word fa-3x text-primary me-3"></i>
                                        <div>
                                            <p class="mb-1 fw-bold"><?= $fileName ?></p>
                                            <p class="mb-0 text-muted">PPT presentation - Download to view</p>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Preview not available in browser. Please download the file to view the assignment attachment.
                                    </div>
                                <?php endif; ?>
                                
                                <a href="<?= base_url('student/download_attachment/' . $assignment['id']) ?>" 
                                   class="btn btn-primary" target="_blank">
                                    <i class="fas fa-download me-1"></i>Download Attachment
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Submission Type Info -->
                <div class="mb-4">
                    <h5 class="fw-bold">Submission Type</h5>
                    <p class="mb-0">
                        <?php
                        $submissionType = $assignment['submission_type'] ?? 'both';
                        switch($submissionType) {
                            case 'text':
                                echo '<i class="fas fa-keyboard me-1"></i> Text submission only';
                                break;
                            case 'file':
                                echo '<i class="fas fa-file me-1"></i> File upload only (PDF, PPT)';
                                break;
                            case 'both':
                            default:
                                echo '<i class="fas fa-keyboard me-1"></i> Text submission OR <i class="fas fa-file me-1"></i> File upload (PDF, PPT)';
                                break;
                        }
                        ?>
                    </p>
                </div>

                <!-- Existing Submission -->
                <?php if ($submission): ?>
                    <div class="card border-secondary mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Your Submission</h6>
                        </div>
                        <div class="card-body">
                            <?php if ($submission['submission_text']): ?>
                                <div class="mb-3">
                                    <strong>Text Submission:</strong>
                                    <p class="mt-2"><?= nl2br(esc($submission['submission_text'])) ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if ($submission['file_path']): ?>
                                <div class="mb-3">
                                    <strong>Submitted File:</strong>
                                    <a href="<?= base_url('student/download_submission/' . $submission['id']) ?>" 
                                       class="btn btn-sm btn-outline-primary ms-2" target="_blank">
                                        <i class="fas fa-download me-1"></i>Download
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if ($submission['status'] === 'graded'): ?>
                                <div class="alert alert-success">
                                    <strong>Grade:</strong> <?= $submission['score'] ?> / <?= $assignment['max_score'] ?>
                                    <?php if ($submission['feedback']): ?>
                                        <br><strong>Feedback:</strong>
                                        <p class="mt-2 mb-0"><?= nl2br(esc($submission['feedback'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Submission Form -->
                <?php if ($isAvailable && (!$submission || $submission['status'] !== 'graded')): ?>
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <?= $submission ? 'Update Submission' : 'Submit Assignment' ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="submissionForm" method="POST" action="<?= base_url('student/submit_assignment') ?>" 
                                  enctype="multipart/form-data">
                                <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                
                                <?php if ($submissionType === 'text' || $submissionType === 'both'): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Text Submission</label>
                                        <textarea name="submission_text" class="form-control" rows="6" 
                                                  placeholder="Type your submission here..."
                                                  <?= $submissionType === 'text' ? 'required' : '' ?>><?= $submission ? esc($submission['submission_text']) : '' ?></textarea>
                                    </div>
                                <?php endif; ?>

                                <?php if ($submissionType === 'file' || $submissionType === 'both'): ?>
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Upload File
                                            <?php if ($submissionType === 'file'): ?>
                                                <span class="text-danger">*</span>
                                            <?php endif; ?>
                                        </label>
                                        <input type="file" name="submission_file" class="form-control" 
                                               accept=".pdf,.ppt,.pptx"
                                               <?= $submissionType === 'file' ? 'required' : '' ?>>
                                        <small class="form-text text-muted">
                                            Accepted formats: PDF, PPT (Max 10MB)
                                        </small>
                                        <?php if ($submission && $submission['file_path']): ?>
                                            <p class="mt-2">
                                                <small>Current file: 
                                                    <a href="<?= base_url('student/download_submission/' . $submission['id']) ?>" target="_blank">
                                                        View uploaded file
                                                    </a>
                                                </small>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between">
                                    <a href="<?= base_url('student/assignments') ?>" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-paper-plane me-1"></i>
                                        <?= $submission ? 'Update Submission' : 'Submit Assignment' ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php elseif (!$isAvailable): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i>
                        <?= $availabilityMessage ?>
                    </div>
                <?php elseif ($submission && $submission['status'] === 'graded'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-1"></i>
                        This assignment has been graded and cannot be resubmitted.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('submissionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
    
    const formData = new FormData(this);
    
    fetch('<?= base_url('student/submit_assignment') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>

