<?= $this->include('templates/header') ?>

<!-- Teacher Manage Assignments View -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold"><i class="fas fa-tasks me-2"></i>Manage Assignments</h3>
                                <p class="mb-0 opacity-75">Create, edit, and manage course assignments</p>
                            </div>
                            <div>
                                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                                    <i class="fas fa-plus me-2"></i>Create New Assignment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

        <!-- Statistics Cards -->
        <div class="row mb-4 g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📝</div>
                    <div class="display-5 fw-bold"><?= count($assignments) ?></div>
                    <div class="fw-semibold">Total Assignments</div>
                    <small class="opacity-75">All courses</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($assignments, fn($a) => $a['is_published'])) ?></div>
                    <div class="fw-semibold">Published</div>
                    <small class="opacity-75">Visible to students</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📋</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($assignments, fn($a) => !$a['is_published'])) ?></div>
                    <div class="fw-semibold">Drafts</div>
                    <small class="opacity-75">Not yet published</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📥</div>
                    <div class="display-5 fw-bold"><?= array_sum(array_column($assignments, 'submission_count')) ?></div>
                    <div class="fw-semibold">Total Submissions</div>
                    <small class="opacity-75">All assignments</small>
                </div>
            </div>
        </div>

        <!-- Assignments Table Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">📋 Assignment List</h5>
            </div>
            <div class="card-body">
                    <?php if (empty($assignments)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3">No assignments created yet. Click "Create New Assignment" to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Course</th>
                                        <th>Type</th>
                                        <th>Due Date</th>
                                        <th>Max Score</th>
                                        <th>Submissions</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($assignment['title']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= esc($assignment['course_code']) ?></span>
                                                <small class="text-muted d-block"><?= esc($assignment['section']) ?></small>
                                            </td>
                                            <td><?= esc($assignment['type_name'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php 
                                                $dueDate = strtotime($assignment['due_date']);
                                                $now = time();
                                                $isOverdue = $dueDate < $now;
                                                ?>
                                                <span class="<?= $isOverdue ? 'text-danger' : '' ?>">
                                                    <?= date('M j, Y g:i A', $dueDate) ?>
                                                </span>
                                                <?php if ($isOverdue): ?>
                                                    <br><small class="badge bg-danger">Overdue</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($assignment['max_score']) ?></td>
                                            <td>
                                                <a href="<?= base_url('teacher/submissions?assignment_id=' . $assignment['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-inbox me-1"></i><?= $assignment['submission_count'] ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($assignment['is_published']): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-outline-info" 
                                                            onclick="editAssignment(<?= htmlspecialchars(json_encode($assignment), ENT_QUOTES, 'UTF-8') ?>)"
                                                            title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if (!$assignment['is_published']): ?>
                                                        <button class="btn btn-outline-success" 
                                                                onclick="publishAssignment(<?= $assignment['id'] ?>)"
                                                                title="Publish">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                                                                    </div>
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
</div>

<!-- Create Assignment Modal -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= base_url('teacher/assignments') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create New Assignment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Course <span class="text-danger">*</span></label>
                        <select name="course_offering_id" id="create_course_offering_id" class="form-select" required>
                            <option value="" data-term="">Select Course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['course_offering_id'] ?>" data-term="<?= $course['term_id'] ?? '' ?>">
                                    <?= esc($course['course_code']) ?> - <?= esc($course['course_title']) ?> (<?= esc($course['section']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Assignment Type <span class="text-danger">*</span></label>
                            <select name="assignment_type_id" class="form-select" required>
                                <option value="">Select Type</option>
                                <?php foreach ($assignmentTypes as $type): ?>
                                    <option value="<?= $type['id'] ?>"><?= esc($type['type_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Grading Period</label>
                            <select name="grading_period_id" id="create_grading_period_id" class="form-select">
                                <option value="">Select Course First</option>
                            </select>
                            <small class="text-muted">Grading periods will load based on selected course</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="create_title" class="form-control" 
                               pattern="^[a-zA-Z0-9\s\-,]+$" 
                               title="Only letters, numbers, spaces, hyphens, and commas are allowed" required>
                        <small class="text-muted">Only letters, numbers, spaces, hyphens, and commas allowed</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Instructions</label>
                        <textarea name="instructions" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Assignment Attachment</label>
                        <input type="file" name="attachment_file" class="form-control" accept=".pdf, .ppt,.pptx">
                        <small class="text-muted">Optional: Upload a PDF or PPT document as reference material (Max 10MB)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Submission Type <span class="text-danger">*</span></label>
                        <select name="submission_type" class="form-select" required>
                            <option value="both">Both Text & File Upload</option>
                            <option value="text">Text Only</option>
                            <option value="file">File Upload Only (PDF/PPT)</option>
                        </select>
                        <small class="text-muted">Choose what type of submission students can make</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Max Score <span class="text-danger">*</span></label>
                            <input type="number" name="max_score" class="form-control" value="100" min="1" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Due Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="due_date" id="create_due_date" class="form-control" required>
                            <small class="text-muted">Cannot be a past date</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Available From</label>
                            <input type="datetime-local" name="available_from" id="create_available_from" class="form-control">
                            <small class="text-muted">Must be before due date</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Available Until</label>
                            <input type="datetime-local" name="available_until" id="create_available_until" class="form-control">
                            <small class="text-muted">Must be after available from</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="allow_late_submission" class="form-check-input" id="allowLate">
                            <label class="form-check-label" for="allowLate">Allow Late Submission</label>
                        </div>
                    </div>

                    <div class="mb-3" id="latePenaltyDiv" style="display: none;">
                        <label class="form-label fw-bold">Late Penalty (%)</label>
                        <input type="number" name="late_penalty_percentage" class="form-control" min="0" max="100" value="0">
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_published" class="form-check-input" id="publishNow">
                            <label class="form-check-label" for="publishNow">Publish Immediately</label>
                        </div>
                        <small class="text-muted">Check to publish now (students will see it). Uncheck to save as draft.</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div class="modal fade" id="editAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= base_url('teacher/assignments') ?>" id="editAssignmentForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="assignment_id" id="edit_assignment_id">
                
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Assignment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Assignment Type <span class="text-danger">*</span></label>
                            <select name="assignment_type_id" id="edit_assignment_type_id" class="form-select" required>
                                <?php foreach ($assignmentTypes as $type): ?>
                                    <option value="<?= $type['id'] ?>"><?= esc($type['type_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Grading Period</label>
                            <select name="grading_period_id" id="edit_grading_period_id" class="form-select">
                                <option value="">Select Period</option>
                                <?php foreach ($gradingPeriods as $period): ?>
                                    <option value="<?= $period['id'] ?>"><?= esc($period['period_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Instructions</label>
                        <textarea name="instructions" id="edit_instructions" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Assignment Attachment</label>
                        <div id="edit_attachment_current"></div>
                        <input type="file" name="attachment_file" id="edit_attachment_file" class="form-control" accept=".pdf,.ppt,.pptx">
                        <small class="text-muted">Optional: Upload PDF, or PowerPoint (Max 10MB). Uploading a new file will replace the current attachment.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Submission Type <span class="text-danger">*</span></label>
                        <select name="submission_type" id="edit_submission_type" class="form-select" required>
                            <option value="both">Both Text & File Upload</option>
                            <option value="text">Text Only</option>
                            <option value="file">File Upload Only (PDF/PPT)</option>
                        </select>
                        <small class="text-muted">Choose what type of submission students can make</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Max Score <span class="text-danger">*</span></label>
                            <input type="number" name="max_score" id="edit_max_score" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Due Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="due_date" id="edit_due_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Available From</label>
                            <input type="datetime-local" name="available_from" id="edit_available_from" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Available Until</label>
                            <input type="datetime-local" name="available_until" id="edit_available_until" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="allow_late_submission" class="form-check-input" id="edit_allow_late">
                            <label class="form-check-label" for="edit_allow_late">Allow Late Submission</label>
                        </div>
                    </div>

                    <div class="mb-3" id="editLatePenaltyDiv">
                        <label class="form-label fw-bold">Late Penalty (%)</label>
                        <input type="number" name="late_penalty_percentage" id="edit_late_penalty" class="form-control" min="0" max="100">
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save me-2"></i>Update Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Form -->
<form method="POST" action="<?= base_url('teacher/assignments') ?>" id="deleteForm" style="display: none;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="assignment_id" id="delete_assignment_id">
</form>

<!-- Publish Confirmation Form -->
<form method="POST" action="<?= base_url('teacher/assignments') ?>" id="publishForm" style="display: none;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="publish">
    <input type="hidden" name="assignment_id" id="publish_assignment_id">
</form>

<script>
// Grading periods by term data
const gradingPeriodsByTerm = <?= json_encode($gradingPeriodsByTerm) ?>;

// Set minimum date for due date (cannot be past)
const now = new Date();
const minDateTime = now.toISOString().slice(0, 16);
document.getElementById('create_due_date').min = minDateTime;

// Course selection - update grading periods based on term
document.getElementById('create_course_offering_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const termId = selectedOption.getAttribute('data-term');
    const gradingPeriodSelect = document.getElementById('create_grading_period_id');
    
    // Clear existing options
    gradingPeriodSelect.innerHTML = '<option value="">Select Period</option>';
    
    if (termId && gradingPeriodsByTerm[termId]) {
        gradingPeriodsByTerm[termId].forEach(period => {
            const option = document.createElement('option');
            option.value = period.id;
            option.textContent = period.period_name;
            gradingPeriodSelect.appendChild(option);
        });
    } else {
        gradingPeriodSelect.innerHTML = '<option value="">No periods available</option>';
    }
});

// Date validation for create form
document.getElementById('create_due_date').addEventListener('change', function() {
    const dueDate = new Date(this.value);
    const availableFrom = document.getElementById('create_available_from');
    const availableUntil = document.getElementById('create_available_until');
    
    // Set max for available_from to be before due date
    availableFrom.max = this.value;
    
    // Validate available_from is before due_date
    if (availableFrom.value && new Date(availableFrom.value) >= dueDate) {
        alert('Available From date must be before the Due Date');
        availableFrom.value = '';
    }
});

document.getElementById('create_available_from').addEventListener('change', function() {
    const availableFrom = new Date(this.value);
    const dueDate = new Date(document.getElementById('create_due_date').value);
    const availableUntil = document.getElementById('create_available_until');
    
    if (dueDate && availableFrom >= dueDate) {
        alert('Available From date must be before the Due Date');
        this.value = '';
        return;
    }
    
    // Set min for available_until to be after available_from
    availableUntil.min = this.value;
});

document.getElementById('create_available_until').addEventListener('change', function() {
    const availableUntil = new Date(this.value);
    const availableFrom = new Date(document.getElementById('create_available_from').value);
    
    if (availableFrom && availableUntil <= availableFrom) {
        alert('Available Until date must be after Available From date');
        this.value = '';
    }
});

// Title validation
document.getElementById('create_title').addEventListener('input', function() {
    const pattern = /^[a-zA-Z0-9\s\-,]*$/;
    if (!pattern.test(this.value)) {
        this.setCustomValidity('Only letters, numbers, spaces, hyphens, and commas are allowed');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('allowLate').addEventListener('change', function() {
    document.getElementById('latePenaltyDiv').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('edit_allow_late').addEventListener('change', function() {
    document.getElementById('editLatePenaltyDiv').style.display = this.checked ? 'block' : 'none';
});

function editAssignment(assignment) {
    document.getElementById('edit_assignment_id').value = assignment.id;
    document.getElementById('edit_assignment_type_id').value = assignment.assignment_type_id;
    document.getElementById('edit_grading_period_id').value = assignment.grading_period_id || '';
    document.getElementById('edit_title').value = assignment.title;
    document.getElementById('edit_description').value = assignment.description || '';
    document.getElementById('edit_instructions').value = assignment.instructions || '';
    document.getElementById('edit_submission_type').value = assignment.submission_type || 'both';
    document.getElementById('edit_max_score').value = assignment.max_score;
    
    if (assignment.due_date) {
        const dueDate = new Date(assignment.due_date);
        document.getElementById('edit_due_date').value = dueDate.toISOString().slice(0, 16);
    }
    
    if (assignment.available_from) {
        const availFrom = new Date(assignment.available_from);
        document.getElementById('edit_available_from').value = availFrom.toISOString().slice(0, 16);
    }
    
    if (assignment.available_until) {
        const availUntil = new Date(assignment.available_until);
        document.getElementById('edit_available_until').value = availUntil.toISOString().slice(0, 16);
    }
    
    document.getElementById('edit_allow_late').checked = assignment.allow_late_submission == 1;
    document.getElementById('edit_late_penalty').value = assignment.late_penalty_percentage || 0;
    document.getElementById('editLatePenaltyDiv').style.display = assignment.allow_late_submission == 1 ? 'block' : 'none';
    
    // Show current attachment if exists
    const attachmentDiv = document.getElementById('edit_attachment_current');
    if (assignment.attachment_path) {
        const fileName = assignment.attachment_path.split('/').pop();
        attachmentDiv.innerHTML = `<a href='${assignment.attachment_path}' target='_blank' class='btn btn-sm btn-outline-primary'><i class='fas fa-paperclip me-1'></i> ${fileName}</a>`;
    } else {
        attachmentDiv.innerHTML = '<span class="text-muted">No attachment uploaded.</span>';
    }
    
    new bootstrap.Modal(document.getElementById('editAssignmentModal')).show();
}

function publishAssignment(id) {
    if (confirm('Are you sure you want to publish this assignment? Students will be notified.')) {
        document.getElementById('publish_assignment_id').value = id;
        document.getElementById('publishForm').submit();
    }
}
</script>
