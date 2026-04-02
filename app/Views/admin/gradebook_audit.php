<?= $this->include('templates/header') ?>

<?php
$filters = $filters ?? [];
$selectedCourseOffering = (string) ($filters['course_offering_id'] ?? '');
$selectedStudentId = (string) ($filters['student_id'] ?? '');
$selectedChangedBy = (string) ($filters['changed_by'] ?? '');
$selectedChangeType = (string) ($filters['change_type'] ?? '');
$dateFrom = (string) ($filters['date_from'] ?? '');
$dateTo = (string) ($filters['date_to'] ?? '');
?>

<style>
    .lms-admin-view {
        --page-bg: #f8fafc;
        --surface: #ffffff;
        --surface-soft: #f8fbff;
        --text-main: #0f172a;
        --border-soft: #dbe4ef;
        background-color: var(--page-bg);
        color: var(--text-main);
    }

    .lms-admin-view .card {
        border: 1px solid var(--border-soft) !important;
        border-radius: 12px;
        background-color: var(--surface) !important;
    }

    .lms-admin-view .admin-hero {
        background-color: var(--surface-soft) !important;
        border: 1px solid var(--border-soft);
    }

    .lms-admin-view .table thead th {
        background-color: var(--surface-soft) !important;
        font-size: 0.82rem;
    }

    .lms-admin-view .table tbody td {
        font-size: 0.85rem;
        vertical-align: middle;
    }
</style>

<div class="lms-admin-view min-vh-100">
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body admin-hero p-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h2 class="fw-bold mb-1"><?= esc($title ?? 'Grade Change Audit Trail') ?></h2>
                                <p class="text-muted mb-0">Track grade updates, overrides, and status changes.</p>
                            </div>
                            <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="get" action="<?= base_url('admin/gradebook/audit') ?>" class="row g-3">
                            <div class="col-lg-4">
                                <label for="course_offering_id" class="form-label fw-semibold">Course Offering</label>
                                <select id="course_offering_id" name="course_offering_id" class="form-select">
                                    <option value="">All Offerings</option>
                                    <?php foreach (($courses ?? []) as $course): ?>
                                        <option value="<?= esc((string) ($course['id'] ?? '')) ?>" <?= $selectedCourseOffering === (string) ($course['id'] ?? '') ? 'selected' : '' ?>>
                                            <?= esc(($course['course_code'] ?? 'N/A') . ' - Sec ' . ($course['section'] ?? 'N/A') . ' (' . ($course['term_name'] ?? 'No Term') . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label for="student_id" class="form-label fw-semibold">Student ID</label>
                                <input type="text" id="student_id" name="student_id" class="form-control" value="<?= esc($selectedStudentId) ?>" placeholder="e.g. 15">
                            </div>
                            <div class="col-lg-3">
                                <label for="changed_by" class="form-label fw-semibold">Changed By</label>
                                <select id="changed_by" name="changed_by" class="form-select">
                                    <option value="">All Instructors</option>
                                    <?php foreach (($instructors ?? []) as $instructor): ?>
                                        <?php $instructorId = (string) ($instructor['id'] ?? ''); ?>
                                        <option value="<?= esc($instructorId) ?>" <?= $selectedChangedBy === $instructorId ? 'selected' : '' ?>>
                                            <?= esc(trim(($instructor['last_name'] ?? '') . ', ' . ($instructor['first_name'] ?? '')) ?: ('User #' . $instructorId)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label for="change_type" class="form-label fw-semibold">Change Type</label>
                                <select id="change_type" name="change_type" class="form-select">
                                    <option value="">All Types</option>
                                    <?php foreach (['calculated', 'override', 'status_change'] as $type): ?>
                                        <option value="<?= esc($type) ?>" <?= $selectedChangeType === $type ? 'selected' : '' ?>>
                                            <?= esc(ucwords(str_replace('_', ' ', $type))) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label for="date_from" class="form-label fw-semibold">Date From</label>
                                <input type="date" id="date_from" name="date_from" class="form-control" value="<?= esc($dateFrom) ?>">
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label for="date_to" class="form-label fw-semibold">Date To</label>
                                <input type="date" id="date_to" name="date_to" class="form-control" value="<?= esc($dateTo) ?>">
                            </div>
                            <div class="col-md-6 col-lg-8 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="<?= base_url('admin/gradebook/audit') ?>" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">Audit Log</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Changed At</th>
                                        <th>Course / Student</th>
                                        <th class="text-end">Old Grade</th>
                                        <th class="text-end">New Grade</th>
                                        <th>Change Type</th>
                                        <th>Changer</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($audit_trail ?? [])): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No audit records found for the selected filters.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($audit_trail as $entry): ?>
                                            <?php
                                            $changedAt = !empty($entry['changed_at']) ? strtotime((string) $entry['changed_at']) : false;
                                            $oldGrade = $entry['old_grade'] ?? null;
                                            $newGrade = $entry['new_grade'] ?? null;
                                            $badgeClass = match ($entry['change_type'] ?? '') {
                                                'override' => 'bg-warning text-dark',
                                                'status_change' => 'bg-info text-dark',
                                                default => 'bg-secondary',
                                            };
                                            ?>
                                            <tr>
                                                <td>
                                                    <?= $changedAt ? esc(date('M d, Y g:i A', $changedAt)) : '<span class="text-muted">N/A</span>' ?>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">
                                                        <?= esc(($entry['course_code'] ?? 'N/A') . ' - ' . ($entry['course_title'] ?? 'Untitled')) ?>
                                                    </div>
                                                    <small class="text-muted d-block">
                                                        Section: <?= esc($entry['section'] ?? 'N/A') ?>
                                                        <?php if (!empty($entry['period_name'])): ?>
                                                            | Period: <?= esc($entry['period_name']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        Student:
                                                        <?= esc(trim(($entry['student_last_name'] ?? '') . ', ' . ($entry['student_first_name'] ?? '')) ?: 'Unknown Student') ?>
                                                        <?php if (!empty($entry['student_code'])): ?>
                                                            (<?= esc($entry['student_code']) ?>)
                                                        <?php endif; ?>
                                                    </small>
                                                </td>
                                                <td class="text-end">
                                                    <?= ($oldGrade !== null && $oldGrade !== '') ? esc((string) $oldGrade) : '<span class="text-muted">N/A</span>' ?>
                                                </td>
                                                <td class="text-end">
                                                    <?= ($newGrade !== null && $newGrade !== '') ? esc((string) $newGrade) : '<span class="text-muted">N/A</span>' ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= esc($badgeClass) ?>">
                                                        <?= esc(ucwords(str_replace('_', ' ', (string) ($entry['change_type'] ?? 'unknown')))) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= esc(trim(($entry['changer_last_name'] ?? '') . ', ' . ($entry['changer_first_name'] ?? '')) ?: 'System') ?>
                                                    <?php if (!empty($entry['changer_code'])): ?>
                                                        <small class="text-muted d-block"><?= esc($entry['changer_code']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= !empty($entry['change_reason']) ? esc($entry['change_reason']) : '<span class="text-muted">No reason provided</span>' ?>
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
        </div>
    </div>
</div>
