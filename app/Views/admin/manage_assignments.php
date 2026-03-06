<?= $this->include('templates/header') ?>

<!-- Admin Manage Assignments View -->
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
                                <p class="mb-0 opacity-75">View and manage all assignments in the system</p>
                            </div>
                            <div>
                                <a href="<?= base_url('admin/create_assignment') ?>" class="btn btn-light">
                                    <i class="fas fa-plus-circle me-2"></i>Create Assignment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Assignments Table -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <?php if (empty($assignments)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-tasks text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3">No assignments found in the system.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Course</th>
                                    <th>Instructor</th>
                                    <th>Type</th>
                                    <th>Max Score</th>
                                    <th>Due Date</th>
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
                                            <?php if ($assignment['attachment_path']): ?>
                                                <i class="fas fa-paperclip text-muted ms-1" title="Has attachment"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= esc($assignment['course_code']) ?>
                                            <br><small class="text-muted"><?= esc($assignment['section']) ?></small>
                                        </td>
                                        <td><?= esc($assignment['instructor_name']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= esc($assignment['type_name'] ?? 'N/A') ?></span>
                                        </td>
                                        <td><?= esc($assignment['max_score']) ?></td>
                                        <td>
                                            <?= date('M j, Y', strtotime($assignment['due_date'])) ?>
                                            <br><small class="text-muted"><?= date('g:i A', strtotime($assignment['due_date'])) ?></small>
                                        </td>
                                        <td>
                                            <?= $assignment['submission_count'] ?>
                                        </td>
                                        <td>
                                            <?php if ($assignment['is_published']): ?>
                                                <span class="badge bg-success">Published</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= base_url('admin/view_assignment/' . $assignment['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="View Assignment">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('admin/edit_assignment/' . $assignment['id']) ?>" 
                                                   class="btn btn-sm btn-outline-secondary" title="Edit Assignment">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($assignment['submission_count'] == 0 && empty($assignment['attachment_path'])): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete(<?= $assignment['id'] ?>, '<?= esc($assignment['title']) ?>')"
                                                            title="Delete Assignment">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            disabled 
                                                            title="Cannot delete - has submissions or attachment">
                                                        <i class="fas fa-trash"></i>
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

<script>
function confirmDelete(assignmentId, title) {
    if (confirm('Are you sure you want to delete the assignment "' + title + '"?\n\nThis action cannot be undone.')) {
        window.location.href = '<?= base_url('admin/delete_assignment/') ?>' + assignmentId;
    }
}
</script>
