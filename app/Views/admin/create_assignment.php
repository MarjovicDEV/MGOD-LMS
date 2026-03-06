<?= $this->include('templates/header') ?>

<!-- Admin Create Assignment View -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center">
                    <a href="<?= base_url('admin/manage_assignments') ?>" class="btn btn-outline-secondary me-3">
                        ← Back to Assignments
                    </a>
                    <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Assignment</h3>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <p class="mb-1"><?= $error ?></p>
                <?php endforeach; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Create Assignment Form -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form method="POST" action="<?= base_url('admin/store_assignment') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Course <span class="text-danger">*</span></label>
                            <select name="course_offering_id" class="form-select" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['course_offering_id'] ?>">
                                        <?= esc($course['course_code']) ?> - <?= esc($course['course_title']) ?> (<?= esc($course['section']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Assignment Type <span class="text-danger">*</span></label>
                            <select name="assignment_type_id" class="form-select" required>
                                <option value="">Select Type</option>
                                <?php foreach ($assignmentTypes as $type): ?>
                                    <option value="<?= $type['id'] ?>"><?= esc($type['type_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required 
                                   placeholder="Enter assignment title"
                                   pattern="^[a-zA-Z0-9\s\-,]+$"
                                   title="Only letters, numbers, spaces, hyphens, and commas allowed">
                            <small class="text-muted">Only letters, numbers, spaces, hyphens, and commas allowed</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Grading Period <span class="text-danger">*</span></label>
                            <select name="grading_period_id" class="form-select" required>
                                <option value="">Select Period</option>
                                <?php foreach ($gradingPeriods as $period): ?>
                                    <option value="<?= $period['id'] ?>"><?= esc($period['period_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Brief description of the assignment"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Instructions</label>
                        <textarea name="instructions" class="form-control" rows="4" 
                                  placeholder="Detailed instructions for students"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Assignment Attachment</label>
                        <input type="file" name="attachment_file" class="form-control" accept=".pdf,.ppt,.pptx">
                        <small class="text-muted">Optional: Upload a PDF or PPT document as reference material (Max 10MB)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Submission Type <span class="text-danger">*</span></label>
                        <select name="submission_type" class="form-select" required>
                            <option value="both">Both Text & File Upload</option>
                            <option value="text">Text Only</option>
                            <option value="file">File Upload Only (PDF/PPT)</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Max Score <span class="text-danger">*</span></label>
                            <input type="number" name="max_score" class="form-control" value="100" min="1" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Due Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="due_date" class="form-control" required>
                            <small class="text-muted">Cannot be a past date</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Available From</label>
                            <input type="datetime-local" name="available_from" class="form-control">
                            <small class="text-muted">Must be before due date</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Available Until</label>
                            <input type="datetime-local" name="available_until" class="form-control">
                            <small class="text-muted">Must be after available from</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="allow_late_submission" class="form-check-input" id="allowLate">
                                <label class="form-check-label" for="allowLate">Allow Late Submission</label>
                            </div>
                            <input type="number" name="late_penalty_percentage" class="form-control mt-2" 
                                   placeholder="Late penalty %" min="0" max="100" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_published" class="form-check-input" id="publishNow">
                                <label class="form-check-label" for="publishNow">Publish Immediately</label>
                            </div>
                            <small class="text-muted">Check to publish now (students will see it). Uncheck to save as draft.</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('admin/manage_assignments') ?>" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Set minimum datetime to current datetime
const now = new Date();
const datetimeLocal = now.toISOString().slice(0, 16);
document.querySelector('input[name="due_date"]').min = datetimeLocal;

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const dueDate = new Date(document.querySelector('input[name="due_date"]').value);
    const availableFrom = document.querySelector('input[name="available_from"]').value;
    const availableUntil = document.querySelector('input[name="available_until"]').value;
    
    if (availableFrom && new Date(availableFrom) >= dueDate) {
        e.preventDefault();
        alert('Available From date must be before the Due Date');
        return;
    }
    
    if (availableFrom && availableUntil && new Date(availableUntil) <= new Date(availableFrom)) {
        e.preventDefault();
        alert('Available Until date must be after Available From');
        return;
    }
});
</script>