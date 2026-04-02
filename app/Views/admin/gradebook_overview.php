<?= $this->include('templates/header') ?>

<?php
$selectedTerm = (string) ($selected_term ?? '');
$departmentData = $department_data ?? [];
$hasOfferings = false;
foreach ($departmentData as $offerings) {
    if (!empty($offerings)) {
        $hasOfferings = true;
        break;
    }
}
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
        font-size: 0.86rem;
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
                                <h2 class="fw-bold mb-1"><?= esc($title ?? 'System-Wide Gradebook') ?></h2>
                                <p class="text-muted mb-0">Course offerings grouped by department for the selected term.</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?= base_url('admin/gradebook/analytics') ?>" class="btn btn-outline-primary btn-sm">Analytics</a>
                                <a href="<?= base_url('admin/gradebook/audit') ?>" class="btn btn-outline-primary btn-sm">Audit Trail</a>
                                <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="get" action="<?= base_url('admin/gradebook/overview') ?>" class="row g-3 align-items-end">
                            <div class="col-md-8 col-lg-6">
                                <label for="term_id" class="form-label fw-semibold">Term</label>
                                <select name="term_id" id="term_id" class="form-select">
                                    <?php foreach (($terms ?? []) as $term): ?>
                                        <option value="<?= esc((string) ($term['id'] ?? '')) ?>" <?= $selectedTerm === (string) ($term['id'] ?? '') ? 'selected' : '' ?>>
                                            <?= esc(($term['term_name'] ?? 'Term') . ' - ' . ($term['semester_name'] ?? 'Semester') . ' (' . ($term['year_name'] ?? 'Year') . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 col-lg-2 d-grid">
                                <button type="submit" class="btn btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$hasOfferings): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="alert alert-light border mb-0">
                                No course offerings found for this term.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($departmentData as $departmentName => $offerings): ?>
                <?php if (empty($offerings)): ?>
                    <?php continue; ?>
                <?php endif; ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 fw-bold"><?= esc((string) $departmentName) ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Course Code</th>
                                                <th>Title</th>
                                                <th>Section</th>
                                                <th class="text-end">Student Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($offerings as $offering): ?>
                                                <tr>
                                                    <td class="fw-semibold"><?= esc($offering['course_code'] ?? 'N/A') ?></td>
                                                    <td><?= esc($offering['title'] ?? 'Untitled') ?></td>
                                                    <td><?= esc($offering['section'] ?? 'N/A') ?></td>
                                                    <td class="text-end"><?= esc((string) ((int) ($offering['student_count'] ?? 0))) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
