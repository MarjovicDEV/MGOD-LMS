<?= $this->include('templates/header') ?>

<style>
    .lms-admin-view {
        --brand-primary: #2563eb;
        --brand-soft: #eef4ff;
        --page-bg: #f8fafc;
        --surface: #ffffff;
        --surface-soft: #f8fbff;
        --text-main: #0f172a;
        --text-soft: #475569;
        --border-soft: #dbe4ef;
        --hover-soft: #f4f7fb;
        background-color: var(--page-bg);
        color: var(--text-main);
    }

    .lms-admin-view .card {
        border: 1px solid var(--border-soft) !important;
        border-radius: 12px;
        background-color: var(--surface) !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    }

    .lms-admin-view .admin-hero {
        background-color: var(--surface-soft) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-soft);
    }

    .lms-admin-view .admin-hero .opacity-75 {
        opacity: 1 !important;
        color: var(--text-soft) !important;
    }

    .lms-admin-view .btn-primary,
    .lms-admin-view .btn-success,
    .lms-admin-view .btn-warning {
        background-color: var(--brand-primary) !important;
        border-color: var(--brand-primary) !important;
        color: #ffffff !important;
    }

    .lms-admin-view .btn-light,
    .lms-admin-view .btn-secondary,
    .lms-admin-view .btn-outline-secondary {
        background-color: #ffffff !important;
        border-color: var(--border-soft) !important;
        color: var(--text-main) !important;
    }

    .lms-admin-view .table thead th {
        background-color: var(--surface-soft) !important;
        color: var(--text-main) !important;
        border-bottom: 1px solid var(--border-soft) !important;
        font-size: 0.82rem;
    }

    .lms-admin-view .table tbody td {
        font-size: 0.84rem;
        color: var(--text-main);
    }

    .lms-admin-view .table-hover > tbody > tr:hover > * {
        background-color: var(--hover-soft) !important;
    }

    .lms-admin-view .badge.bg-warning {
        color: var(--text-main) !important;
        background-color: #fef3c7 !important;
    }

    .lms-admin-view .text-muted,
    .lms-admin-view small,
    .lms-admin-view .form-text {
        color: var(--text-soft) !important;
    }
</style>

<!-- Admin Manage Assignments View -->
<div class="lms-admin-view min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3 admin-hero">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">Manage Assignments</h2>
                                <p class="mb-0 opacity-75">View and manage all assignments in the system</p>
                            </div>
                            <div>
                                <a href="<?= base_url('admin/create_assignment') ?>" class="btn btn-primary btn-sm">
                                    Create Assignment
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
