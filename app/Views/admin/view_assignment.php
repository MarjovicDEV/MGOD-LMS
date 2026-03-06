<?= $this->include('templates/header') ?>

<!-- Admin View Assignment Page -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center">
                    <a href="<?= base_url('admin/manage_assignments') ?>" class="btn btn-outline-secondary me-3">
                        ← Back to Assignments
                    </a>
                    <h3 class="mb-0"><i class="fas fa-eye me-2"></i>View Assignment Details</h3>
                </div>
            </div>
        </div>

        <!-- Assignment Details Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="mb-3"><?= esc($assignment['title']) ?></h4>
                        <p class="text-muted mb-2">
                            <strong>Course:</strong> <?= esc($assignment['course_code']) ?> - <?= esc($assignment['course_title']) ?> (<?= esc($assignment['section']) ?>)
                        </p>
                        <p class="text-muted mb-2">
                            <strong>Type:</strong> <?= esc($assignment['type_name'] ?? 'N/A') ?>
                            | <strong>Max Score:</strong> <?= esc($assignment['max_score']) ?>
                            | <strong>Status:</strong> 
                            <?php if ($assignment['is_published']): ?>
                                <span class="badge bg-success">Published</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Draft</span>
                            <?php endif; ?>
                        </p>
                        <p class="text-muted mb-3">
                            <strong>Due Date:</strong> <?= date('M j, Y \a\t g:i A', strtotime($assignment['due_date'])) ?>
                            <?php if ($assignment['available_from']): ?>
                                | <strong>Available:</strong> <?= date('M j, Y', strtotime($assignment['available_from'])) ?>
                            <?php endif; ?>
                            <?php if ($assignment['available_until']): ?>
                                | <strong>Until:</strong> <?= date('M j, Y', strtotime($assignment['available_until'])) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="<?= base_url('admin/edit_assignment/' . $assignment['id']) ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Assignment
                        </a>
                    </div>
                </div>

                <?php if ($assignment['description']): ?>
                    <div class="mb-3">
                        <h5>Description</h5>
                        <p class="mb-0"><?= nl2br(esc($assignment['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($assignment['instructions']): ?>
                    <div class="mb-3">
                        <h5>Instructions</h5>
                        <div class="bg-light p-3 rounded">
                            <?= nl2br(esc($assignment['instructions'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($assignment['attachment_path']): ?>
                    <div class="mb-3">
                        <h5>Assignment Attachment</h5>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-paperclip fa-2x text-primary me-3"></i>
                            <div>
                                <p class="mb-0 fw-bold"><?= basename($assignment['attachment_path']) ?></p>
                                <a href="<?= base_url('student/download_attachment/' . $assignment['id']) ?>" 
                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submissions Section -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-inbox me-2"></i>Student Submissions 
                    <span class="badge bg-secondary ms-2"><?= count($submissions) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($submissions)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No submissions yet for this assignment.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Student Code</th>
                                    <th>Submission Type</th>
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
                                            <?php if ($submission['submission_text'] && $submission['file_path']): ?>
                                                <span class="badge bg-info">Text & File</span>
                                            <?php elseif ($submission['submission_text']): ?>
                                                <span class="badge bg-primary">Text Only</span>
                                            <?php elseif ($submission['file_path']): ?>
                                                <span class="badge bg-secondary">File Only</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'draft' => 'secondary',
                                                'submitted' => 'primary',
                                                'graded' => 'success',
                                                'returned' => 'warning'
                                            ];
                                            $status = $submission['status'] ?? 'draft';
                                            ?>
                                            <span class="badge bg-<?= $statusClass[$status] ?>"><?= ucfirst($status) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($submission['score'] !== null): ?>
                                                <?= $submission['score'] ?> / <?= $assignment['max_score'] ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($submission['file_path']): ?>
                                                <a href="<?= base_url('submission/download/' . $submission['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary" target="_blank" title="Download File">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($submission['submission_text']): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="viewSubmissionText('<?= esc($submission['student_name']) ?>', '<?= esc($submission['submission_text']) ?>')"
                                                        title="View Text">
                                                    <i class="fas fa-eye"></i>
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
        </div>
    </div>
</div>

<!-- Text Submission Modal -->
<div class="modal fade" id="textSubmissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submission Text</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="text-muted mb-3">Student: <span id="studentName"></span></h6>
                <div class="bg-light p-3 rounded">
                    <p id="submissionText" class="mb-0"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewSubmissionText(studentName, text) {
    document.getElementById('studentName').textContent = studentName;
    document.getElementById('submissionText').textContent = text;
    new bootstrap.Modal(document.getElementById('textSubmissionModal')).show();
}
</script>