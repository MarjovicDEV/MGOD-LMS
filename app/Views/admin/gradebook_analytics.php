<?= $this->include('templates/header') ?>

<?php
$stats = $stats ?? [];
$distribution = $stats['distribution'] ?? [];
$totalStudents = (int) ($stats['total_students'] ?? 0);
$averageGrade = $stats['average_grade'] ?? null;
$distributionTotal = array_sum(array_map('intval', $distribution));
$selectedTerm = (string) ($selected_term ?? '');
$selectedCourse = (string) ($selected_course ?? '');
?>

<style>
    .lms-admin-view {
        --page-bg: #f8fafc;
        --surface: #ffffff;
        --surface-soft: #f8fbff;
        --text-main: #0f172a;
        --text-soft: #475569;
        --border-soft: #dbe4ef;
        --brand-primary: #2563eb;
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
                                <h2 class="fw-bold mb-1"><?= esc($title ?? 'Grade Analytics') ?></h2>
                                <p class="text-muted mb-0">Analyze final-grade performance across terms and courses.</p>
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
                        <form method="get" action="<?= base_url('admin/gradebook/analytics') ?>" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="term_id" class="form-label fw-semibold">Term</label>
                                <select name="term_id" id="term_id" class="form-select">
                                    <option value="">All Terms</option>
                                    <?php foreach (($terms ?? []) as $term): ?>
                                        <option value="<?= esc((string) ($term['id'] ?? '')) ?>" <?= $selectedTerm === (string) ($term['id'] ?? '') ? 'selected' : '' ?>>
                                            <?= esc(($term['term_name'] ?? 'Term') . ' - ' . ($term['semester_name'] ?? 'Semester') . ' (' . ($term['year_name'] ?? 'Year') . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="course_id" class="form-label fw-semibold">Course</label>
                                <select name="course_id" id="course_id" class="form-select">
                                    <option value="">All Courses</option>
                                    <?php foreach (($courses ?? []) as $course): ?>
                                        <option value="<?= esc((string) ($course['id'] ?? '')) ?>" <?= $selectedCourse === (string) ($course['id'] ?? '') ? 'selected' : '' ?>>
                                            <?= esc(($course['course_code'] ?? 'N/A') . ' - ' . ($course['title'] ?? 'Untitled')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <small class="text-muted d-block">Total Students</small>
                        <h4 class="fw-bold mb-0"><?= esc((string) $totalStudents) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <small class="text-muted d-block">Average Grade</small>
                        <h4 class="fw-bold mb-0">
                            <?= $totalStudents > 0 ? esc(number_format((float) $averageGrade, 2)) : 'N/A' ?>
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <small class="text-muted d-block">Passing</small>
                        <h4 class="fw-bold mb-0 text-success"><?= esc((string) ((int) ($stats['passing_count'] ?? 0))) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <small class="text-muted d-block">Failing</small>
                        <h4 class="fw-bold mb-0 text-danger"><?= esc((string) ((int) ($stats['failing_count'] ?? 0))) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <small class="text-muted d-block">Incomplete</small>
                        <h4 class="fw-bold mb-0 text-warning"><?= esc((string) ((int) ($stats['incomplete_count'] ?? 0))) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <small class="text-muted d-block">Calculated Entries</small>
                        <h4 class="fw-bold mb-0"><?= esc((string) $distributionTotal) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">Grade Distribution</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($distributionTotal === 0): ?>
                            <div class="alert alert-light border mb-0">
                                No calculated grades available for distribution.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Range</th>
                                            <th class="text-end">Count</th>
                                            <th>Share</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (['90-100', '80-89', '75-79', 'Below 75'] as $range): ?>
                                            <?php
                                            $count = (int) ($distribution[$range] ?? 0);
                                            $percent = $distributionTotal > 0 ? ($count / $distributionTotal) * 100 : 0;
                                            ?>
                                            <tr>
                                                <td class="fw-semibold"><?= esc($range) ?></td>
                                                <td class="text-end"><?= esc((string) $count) ?></td>
                                                <td style="min-width: 220px;">
                                                    <div class="progress" role="progressbar" aria-label="<?= esc($range) ?>">
                                                        <div class="progress-bar" style="width: <?= esc(number_format($percent, 2)) ?>%"></div>
                                                    </div>
                                                    <small class="text-muted"><?= esc(number_format($percent, 2)) ?>%</small>
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
</div>
