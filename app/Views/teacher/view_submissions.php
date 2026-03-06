<?= $this->include('templates/header') ?>

<!-- Teacher View Submissions -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('teacher/assignments') ?>">Assignments</a></li>
                <li class="breadcrumb-item active">View Submissions</li>
            </ol>
        </nav>

        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold"><i class="fas fa-clipboard-check me-2"></i><?= esc($assignment['title']) ?></h3>
                                <p class="mb-0 opacity-75">
                                    <?= esc($assignment['course_code']) ?> - <?= esc($assignment['course_title']) ?> | Section: <?= esc($assignment['section']) ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-primary fs-6 mb-2">
                                    <?php if ($assignment['is_published']): ?>
                                        ✅ Published
                                    <?php else: ?>
                                        📝 Draft
                                    <?php endif; ?>
                                </span>
                                <p class="mb-0 opacity-75 small">
                                    <i class="fas fa-calendar me-1"></i>Due: <?= date('M j, Y g:i A', strtotime($assignment['due_date'])) ?>
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
                                <p><strong><i class="fas fa-tag me-2 text-primary"></i>Type:</strong> <?= esc($assignment['type_name'] ?? 'N/A') ?></p>
                                <p><strong><i class="fas fa-star me-2 text-warning"></i>Max Score:</strong> <?= esc($assignment['max_score']) ?> points</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-calendar-alt me-2 text-info"></i>Due Date:</strong> <?= date('M j, Y g:i A', strtotime($assignment['due_date'])) ?></p>
                            </div>
                        </div>
                        <?php if ($assignment['description']): ?>
                            <hr>
                            <div>
                                <strong><i class="fas fa-info-circle me-2 text-secondary"></i>Description:</strong>
                                <p class="mt-2 mb-0"><?= nl2br(esc($assignment['description'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4 g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">👥</div>
                    <div class="display-5 fw-bold"><?= $stats['total_students'] ?></div>
                    <div class="fw-semibold">Total Students</div>
                    <small class="opacity-75">Enrolled in course</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📥</div>
                    <div class="display-5 fw-bold"><?= $stats['total_submissions'] ?></div>
                    <div class="fw-semibold">Submissions</div>
                    <small class="opacity-75">Total received</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">⏳</div>
                    <div class="display-5 fw-bold"><?= $stats['pending_count'] ?></div>
                    <div class="fw-semibold">Pending Grading</div>
                    <small class="opacity-75">Awaiting review</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-danger text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">❌</div>
                    <div class="display-5 fw-bold"><?= $stats['missing_submissions'] ?></div>
                    <div class="fw-semibold">Not Submitted</div>
                    <small class="opacity-75">Missing work</small>
                </div>
            </div>
        </div>

        <!-- Submissions Tabs -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <ul class="nav nav-pills" id="submissionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="submitted-tab" data-bs-toggle="tab" data-bs-target="#submitted" type="button">
                            <i class="fas fa-check-circle me-1"></i>Submitted (<?= count($submissions) ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="not-submitted-tab" data-bs-toggle="tab" data-bs-target="#not-submitted" type="button">
                            <i class="fas fa-times-circle me-1"></i>Not Submitted (<?= count($notSubmitted) ?>)
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="submissionTabsContent">
                    <div class="tab-pane fade show active" id="submitted" role="tabpanel">
                            <?php if (empty($submissions)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                                    <p class="text-muted mt-3">No submissions yet.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Student</th>
                                                <th>Student Code</th>
                                                <th>Submitted At</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($submissions as $submission): ?>
                                                <tr>
                                                    <td><?= esc($submission['student_name']) ?></td>
                                                    <td><?= esc($submission['student_code']) ?></td>
                                                    <td>
                                                        <?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?>
                                                        <?php if ($submission['is_late']): ?>
                                                            <br><span class="badge bg-danger">Late</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $statusColors = [
                                                            'submitted' => 'warning',
                                                            'graded' => 'success',
                                                            'returned' => 'info'
                                                        ];
                                                        $color = $statusColors[$submission['status']] ?? 'secondary';
                                                        ?>
                                                        <span class="badge bg-<?= $color ?>"><?= ucfirst($submission['status']) ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($submission['status'] === 'graded'): ?>
                                                            <strong><?= esc($submission['score']) ?></strong> / <?= esc($assignment['max_score']) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">Not graded</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" 
                                                                onclick="viewSubmission(<?= htmlspecialchars(json_encode($submission), ENT_QUOTES, 'UTF-8') ?>)">
                                                            <i class="fas fa-eye me-1"></i>View
                                                        </button>
                                                        <?php if ($submission['status'] !== 'graded'): ?>
                                                            <button class="btn btn-sm btn-success" 
                                                                    onclick="gradeSubmission(<?= $submission['id'] ?>, '<?= esc($submission['student_name']) ?>')">
                                                                <i class="fas fa-star me-1"></i>Grade
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="not-submitted" role="tabpanel">
                        <?php if (empty($notSubmitted)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-check-double text-success" style="font-size: 4rem;"></i>
                                <p class="text-muted mt-3">All students have submitted!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student</th>
                                            <th>Student Code</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($notSubmitted as $student): ?>
                                            <tr>
                                                <td><?= esc($student['student_name']) ?></td>
                                                <td><?= esc($student['student_code']) ?></td>
                                                <td><span class="badge bg-danger">Not Submitted</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Submission Modal -->
<div class="modal fade" id="viewSubmissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Submission Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Student:</strong> <span id="view_student_name"></span>
                </div>
                <div class="mb-3">
                    <strong>Submitted At:</strong> <span id="view_submitted_at"></span>
                </div>
                <div class="mb-3">
                    <strong>Status:</strong> <span id="view_status"></span>
                </div>
                <div class="mb-3" id="view_text_div">
                    <strong>Text Submission:</strong>
                    <div class="border rounded p-3 bg-light" id="view_submission_text"></div>
                </div>
                <div class="mb-3" id="view_file_div">
                    <strong>File Submission:</strong><br>
                    <a id="view_file_link" class="btn btn-outline-primary" target="_blank">
                        <i class="fas fa-download me-2"></i>Download File
                    </a>
                </div>
                <div class="mb-3" id="view_score_div">
                    <strong>Score:</strong> <span id="view_score"></span>
                </div>
                <div class="mb-3" id="view_feedback_div">
                    <strong>Feedback:</strong>
                    <div class="border rounded p-3 bg-light" id="view_feedback"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Grade Submission Modal -->
<div class="modal fade" id="gradeSubmissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-star me-2"></i>Grade Submission</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="grade_submission_id">
                <div class="mb-3">
                    <strong>Student:</strong> <span id="grade_student_name"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Score <span class="text-danger">*</span></label>
                    <input type="number" id="grade_score" class="form-control" min="0" max="<?= $assignment['max_score'] ?>" step="0.01" required>
                    <small class="text-muted">Max Score: <?= $assignment['max_score'] ?></small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Feedback</label>
                    <textarea id="grade_feedback" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitGrade()">
                    <i class="fas fa-save me-2"></i>Submit Grade
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewSubmission(submission) {
    document.getElementById('view_student_name').textContent = submission.student_name;
    document.getElementById('view_submitted_at').textContent = new Date(submission.submitted_at).toLocaleString();
    
    const statusBadge = `<span class="badge bg-${submission.status === 'graded' ? 'success' : 'warning'}">${submission.status.charAt(0).toUpperCase() + submission.status.slice(1)}</span>`;
    document.getElementById('view_status').innerHTML = statusBadge;
    
    if (submission.submission_text) {
        document.getElementById('view_text_div').style.display = 'block';
        document.getElementById('view_submission_text').textContent = submission.submission_text;
    } else {
        document.getElementById('view_text_div').style.display = 'none';
    }
    
    if (submission.file_path) {
        document.getElementById('view_file_div').style.display = 'block';
        document.getElementById('view_file_link').href = '<?= base_url('submission/download/') ?>' + submission.id;
    } else {
        document.getElementById('view_file_div').style.display = 'none';
    }
    
    if (submission.status === 'graded') {
        document.getElementById('view_score_div').style.display = 'block';
        document.getElementById('view_score').textContent = submission.score + ' / <?= $assignment['max_score'] ?>';
        
        if (submission.feedback) {
            document.getElementById('view_feedback_div').style.display = 'block';
            document.getElementById('view_feedback').textContent = submission.feedback;
        } else {
            document.getElementById('view_feedback_div').style.display = 'none';
        }
    } else {
        document.getElementById('view_score_div').style.display = 'none';
        document.getElementById('view_feedback_div').style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('viewSubmissionModal')).show();
}

function gradeSubmission(submissionId, studentName) {
    document.getElementById('grade_submission_id').value = submissionId;
    document.getElementById('grade_student_name').textContent = studentName;
    document.getElementById('grade_score').value = '';
    document.getElementById('grade_feedback').value = '';
    
    new bootstrap.Modal(document.getElementById('gradeSubmissionModal')).show();
}

function submitGrade() {
    const submissionId = document.getElementById('grade_submission_id').value;
    const score = document.getElementById('grade_score').value;
    const feedback = document.getElementById('grade_feedback').value;
    
    if (!score) {
        alert('Please enter a score');
        return;
    }
    
    const formData = new FormData();
    formData.append('submission_id', submissionId);
    formData.append('score', score);
    formData.append('feedback', feedback);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    fetch('<?= base_url('teacher/grade_submission') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Submission graded successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('An error occurred while grading the submission');
        console.error('Error:', error);
    });
}
</script>
