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

    .lms-admin-view .admin-stats .card,
    .lms-admin-view .card.text-white {
        background-color: var(--surface) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-soft) !important;
    }

    .lms-admin-view .admin-stats .display-4 {
        display: none;
    }

    .lms-admin-view .admin-stats .display-5 {
        font-size: 2rem;
        margin-bottom: 0.35rem;
    }

    .lms-admin-view .card-header.bg-success,
    .lms-admin-view .card-header.bg-warning,
    .lms-admin-view .card-header.bg-primary,
    .lms-admin-view .card-header.bg-info {
        background-color: var(--surface-soft) !important;
        color: var(--text-main) !important;
        border-bottom: 1px solid var(--border-soft) !important;
    }

    .lms-admin-view .btn-success,
    .lms-admin-view .btn-warning,
    .lms-admin-view .btn-primary,
    .lms-admin-view .btn-info {
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

    .lms-admin-view .form-control,
    .lms-admin-view .form-select,
    .lms-admin-view .input-group-text {
        border-color: var(--border-soft);
        font-size: 0.86rem;
    }

    .lms-admin-view .form-control:focus,
    .lms-admin-view .form-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.12);
    }

    .lms-admin-view .text-muted,
    .lms-admin-view small,
    .lms-admin-view .form-text {
        color: var(--text-soft) !important;
    }
</style>

<!-- Manage Terms View - Admin only functionality for term management -->
<div class="lms-admin-view min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3 admin-hero">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">Manage Terms</h2>
                                <p class="mb-0 opacity-75">Create, edit, and manage academic terms in the system</p>
                            </div>
                            <div>
                                <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-light btn-sm">
                                    Back to Dashboard
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
                <strong>Success!</strong> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Validation Errors:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Term Statistics Cards -->
        <div class="row mb-4 admin-stats">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">TRM</div>
                    <div class="display-5 fw-bold"><?= $statistics['total'] ?></div>
                    <div class="fw-semibold">Total Terms</div>
                    <small class="opacity-75">In the system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">ACT</div>
                    <div class="display-5 fw-bold"><?= $statistics['active'] ?></div>
                    <div class="fw-semibold">Active</div>
                    <small class="opacity-75">Currently active</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">INA</div>
                    <div class="display-5 fw-bold"><?= $statistics['inactive'] ?></div>
                    <div class="fw-semibold">Inactive</div>
                    <small class="opacity-75">Deactivated</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">CUR</div>
                    <div class="display-5 fw-bold"><?= $statistics['current'] ?></div>
                    <div class="fw-semibold">Current Term</div>
                    <small class="opacity-75">Active now</small>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">Term Management</h5>
                            <a href="<?= base_url('admin/manage_terms?action=create') ?>" class="btn btn-success">
                                Create New Term
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Box -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           id="termSearchInput" 
                                           class="form-control border-start-0" 
                                           placeholder="Search terms by name, academic year, or semester...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2 mt-md-0">
                                <div class="text-muted">
                                    <small>
                                        <strong id="searchResultCount"><?= count($terms) ?></strong> term(s) found
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Term Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">Create New Term</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_terms?action=create') ?>">
                            <?= csrf_field() ?>                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="academic_year_id" class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                                        <select class="form-select" id="academic_year_id" name="academic_year_id" required>
                                            <option value="">Select Academic Year</option>
                                            <?php foreach ($academicYears as $year): ?>
                                                <option value="<?= $year['id'] ?>" 
                                                        data-start="<?= esc($year['start_date'] ?? '') ?>" 
                                                        data-end="<?= esc($year['end_date'] ?? '') ?>"
                                                        <?= old('academic_year_id') == $year['id'] ? 'selected' : '' ?>>
                                                    <?= esc($year['year_name']) ?> (<?= esc($year['year_code']) ?>)
                                                    <?php if (!empty($year['start_date']) && !empty($year['end_date'])): ?>
                                                        [<?= date('M d, Y', strtotime($year['start_date'])) ?> - <?= date('M d, Y', strtotime($year['end_date'])) ?>]
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted" id="ay_date_hint">Select an Academic Year to see valid date range</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="semester_id" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                        <select class="form-select" id="semester_id" name="semester_id" required>
                                            <option value="">Select Semester</option>
                                            <?php foreach ($semesters as $semester): ?>
                                                <option value="<?= $semester['id'] ?>" <?= old('semester_id') == $semester['id'] ? 'selected' : '' ?>>
                                                    <?= esc($semester['semester_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="term_name" class="form-label fw-semibold">Term Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="term_name" name="term_name" 
                                               value="<?= old('term_name') ?>" required 
                                               minlength="3" maxlength="100"
                                               placeholder="e.g., Fall 2024, Spring 2025">
                                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Letters, numbers, and spaces only. No special characters.</small>
                                    </div>
                                </div>
                            </div>                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label fw-semibold">Start Date</label>
                                        <input type="date" class="form-control term-date" id="start_date" name="start_date" 
                                               value="<?= old('start_date') ?>">
                                        <small class="text-muted">Must be within the Academic Year date range</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label fw-semibold">End Date</label>
                                        <input type="date" class="form-control term-date" id="end_date" name="end_date" 
                                               value="<?= old('end_date') ?>">
                                        <small class="text-muted">Must be within the Academic Year date range</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="enrollment_start" class="form-label fw-semibold">Enrollment Start</label>
                                        <input type="date" class="form-control enrollment-date" id="enrollment_start" name="enrollment_start" 
                                               value="<?= old('enrollment_start') ?>" min="<?= date('Y-m-d') ?>">
                                        <small class="text-muted">When enrollment opens (cannot be in the past)</small>
                                    </div>
                                </div>                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="enrollment_end" class="form-label fw-semibold">Enrollment End</label>
                                        <input type="date" class="form-control enrollment-date" id="enrollment_end" name="enrollment_end" 
                                               value="<?= old('enrollment_end') ?>" min="<?= date('Y-m-d') ?>">
                                        <small class="text-muted">Enrollment deadline (cannot be in the past)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_current" name="is_current" value="1" <?= old('is_current') ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="is_current">
                                            Set as Current Term
                                        </label>
                                        <small class="text-muted d-block">Check this to make this the active term</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-success">
                                    Create Term
                                </button>
                                <a href="<?= base_url('admin/manage_terms') ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Term Form (shown when action=edit) -->
        <?php if ($showEditForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-white border-0">
                        <h5 class="mb-0">Edit Term</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_terms?action=edit&id=' . $editTerm['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="academic_year_id" class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                                        <select class="form-select" id="academic_year_id" name="academic_year_id" required>
                                            <option value="">Select Academic Year</option>
                                            <?php foreach ($academicYears as $year): ?>
                                                <option value="<?= $year['id'] ?>" <?= old('academic_year_id', $editTerm['academic_year_id']) == $year['id'] ? 'selected' : '' ?>>
                                                    <?= esc($year['year_name']) ?> (<?= esc($year['year_code']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="semester_id" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                        <select class="form-select" id="semester_id" name="semester_id" required>
                                            <option value="">Select Semester</option>
                                            <?php foreach ($semesters as $semester): ?>
                                                <option value="<?= $semester['id'] ?>" <?= old('semester_id', $editTerm['semester_id']) == $semester['id'] ? 'selected' : '' ?>>
                                                    <?= esc($semester['semester_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="term_name" class="form-label fw-semibold">Term Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="term_name" name="term_name" 
                                               value="<?= old('term_name', $editTerm['term_name']) ?>" required 
                                               minlength="3" maxlength="100"
                                               placeholder="e.g., Fall 2024, Spring 2025">
                                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Letters, numbers, and spaces only. No special characters.</small>
                                    </div>
                                </div>
                            </div>                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label fw-semibold">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="<?= old('start_date', $editTerm['start_date']) ?>" 
                                               min="<?= $editTerm['start_date'] && strtotime($editTerm['start_date']) < strtotime('today') ? $editTerm['start_date'] : date('Y-m-d') ?>">
                                        <small class="text-muted">Must be today or future date</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label fw-semibold">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                               value="<?= old('end_date', $editTerm['end_date']) ?>" 
                                               min="<?= $editTerm['end_date'] && strtotime($editTerm['end_date']) < strtotime('today') ? $editTerm['end_date'] : date('Y-m-d') ?>">
                                        <small class="text-muted">Must be today or future date</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="enrollment_start" class="form-label fw-semibold">Enrollment Start</label>
                                        <input type="date" class="form-control" id="enrollment_start" name="enrollment_start" 
                                               value="<?= old('enrollment_start', $editTerm['enrollment_start']) ?>" 
                                               min="<?= $editTerm['enrollment_start'] && strtotime($editTerm['enrollment_start']) < strtotime('today') ? $editTerm['enrollment_start'] : date('Y-m-d') ?>">
                                        <small class="text-muted">When enrollment opens (must be today or future)</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="enrollment_end" class="form-label fw-semibold">Enrollment End</label>
                                        <input type="date" class="form-control" id="enrollment_end" name="enrollment_end" 
                                               value="<?= old('enrollment_end', $editTerm['enrollment_end']) ?>" 
                                               min="<?= $editTerm['enrollment_end'] && strtotime($editTerm['enrollment_end']) < strtotime('today') ? $editTerm['enrollment_end'] : date('Y-m-d') ?>">
                                        <small class="text-muted">Enrollment deadline (must be today or future)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_current" name="is_current" value="1" <?= old('is_current', $editTerm['is_current']) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="is_current">
                                            Set as Current Term
                                        </label>
                                        <small class="text-muted d-block">Check this to make this the active term</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-warning text-white">
                                    Update Term
                                </button>
                                <a href="<?= base_url('admin/manage_terms') ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Terms Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">Terms List</h5>
                            <div class="text-muted small">
                                Total: <?= count($terms) ?> terms
                            </div>
                        </div>
                    </div>                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="termsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold border-0 text-center">ID</th>
                                        <th class="fw-semibold border-0">Term Name</th>
                                        <th class="fw-semibold border-0">Academic Year</th>
                                        <th class="fw-semibold border-0">Semester</th>
                                        <th class="fw-semibold border-0">Start Date</th>
                                        <th class="fw-semibold border-0">End Date</th>
                                        <th class="fw-semibold border-0 text-center">Current</th>
                                        <th class="fw-semibold border-0 text-center">Status</th>
                                        <th class="fw-semibold border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="termTableBody">
                                    <?php if (!empty($terms)): ?>
                                        <?php foreach ($terms as $term): ?>
                                        <tr class="term-row border-bottom"
                                            data-term-name="<?= esc(strtolower($term['term_name'])) ?>"
                                            data-academic-year="<?= esc(strtolower($term['year_name'])) ?>"
                                            data-semester="<?= esc(strtolower($term['semester_name'])) ?>">
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">#<?= $term['id'] ?></span>
                                            </td>
                                            <td>
                                                <strong><?= esc($term['term_name']) ?></strong>
                                            </td>
                                            <td>
                                                <?= esc($term['year_name']) ?>
                                            </td>
                                            <td>
                                                <?= esc($term['semester_name']) ?>
                                            </td>
                                            <td>
                                                <?= $term['start_date'] ? date('M d, Y', strtotime($term['start_date'])) : '<span class="text-muted">Not set</span>' ?>
                                            </td>
                                            <td>
                                                <?= $term['end_date'] ? date('M d, Y', strtotime($term['end_date'])) : '<span class="text-muted">Not set</span>' ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($term['is_current'] == 1): ?>
                                                    <span class="badge bg-info">Current</span>
                                                <?php else: ?>
                                                    <a href="<?= base_url('admin/manage_terms?action=set_current&id=' . $term['id']) ?>" 
                                                       class="btn btn-sm btn-outline-info"
                                                       onclick="return confirm('Set this as the current term?')">
                                                        Set Current
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($term['is_active'] == 1): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('admin/manage_terms?action=edit&id=' . $term['id']) ?>" 
                                                       class="btn btn-sm btn-warning text-white" 
                                                       title="Edit Term">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                    <a href="<?= base_url('admin/manage_terms?action=toggle_status&id=' . $term['id']) ?>" 
                                                       class="btn btn-sm btn-info text-white" 
                                                       title="Toggle Status"
                                                       onclick="return confirm('Are you sure you want to change the status of this term?')">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </a>
                                                    <?php if ($term['is_current'] != 1): ?>
                                                        <a href="<?= base_url('admin/manage_terms?action=delete&id=' . $term['id']) ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           title="Delete Term"
                                                           onclick="return confirm('Are you sure you want to delete this term? This action cannot be undone.')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-secondary" disabled title="Cannot delete current term">
                                                            <i class="fas fa-lock"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr id="noResultsRow" style="display: none;">
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-search mb-2" style="font-size: 2rem;"></i>
                                                <p class="mb-0">No terms match your search criteria.</p>
                                                <small>Try adjusting your search terms.</small>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr id="noTermsRow">
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="fas fa-calendar-alt mb-3" style="font-size: 2.5rem; opacity: 0.3;"></i>
                                                <h5>No terms found</h5>
                                                <p class="mb-0">Click the "Create New Term" button to add your first term.</p>
                                            </td>
                                        </tr>
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

<script>
// Academic Year date constraint handler
document.addEventListener('DOMContentLoaded', function() {
    const academicYearSelect = document.getElementById('academic_year_id');
    const ayDateHint = document.getElementById('ay_date_hint');
    const termDateInputs = document.querySelectorAll('.term-date');
    const enrollmentDateInputs = document.querySelectorAll('.enrollment-date');
    
    if (academicYearSelect) {
        academicYearSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const ayStartDate = selectedOption.getAttribute('data-start');
            const ayEndDate = selectedOption.getAttribute('data-end');
            
            if (ayStartDate && ayEndDate) {
                // Set min/max for term dates (start_date, end_date)
                termDateInputs.forEach(input => {
                    input.setAttribute('min', ayStartDate);
                    input.setAttribute('max', ayEndDate);
                });
                
                // Set min for enrollment dates (cannot be in the past, but within AY range)
                const today = new Date().toISOString().split('T')[0];
                const minEnrollmentDate = ayStartDate > today ? ayStartDate : today;
                
                enrollmentDateInputs.forEach(input => {
                    input.setAttribute('min', minEnrollmentDate);
                    input.setAttribute('max', ayEndDate);
                });
                
                // Update hint text
                if (ayDateHint) {
                    ayDateHint.innerHTML = '<strong class="text-success">Valid date range:</strong> ' + 
                                          formatDate(ayStartDate) + ' - ' + formatDate(ayEndDate);
                }
            } else {
                // Clear constraints if no dates available
                termDateInputs.forEach(input => {
                    input.removeAttribute('min');
                    input.removeAttribute('max');
                });
                
                const today = new Date().toISOString().split('T')[0];
                enrollmentDateInputs.forEach(input => {
                    input.setAttribute('min', today);
                    input.removeAttribute('max');
                });
                
                if (ayDateHint) {
                    ayDateHint.textContent = 'Select an Academic Year to see valid date range';
                }
            }
        });
        
        // Trigger on page load if an academic year is already selected
        if (academicYearSelect.value) {
            academicYearSelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Helper function to format date
    function formatDate(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
    }
});
</script>

<!-- Term Search JavaScript -->
<script>
$(document).ready(function() {
    // Term search functionality
    function filterTerms() {
        const searchTerm = $('#termSearchInput').val().toLowerCase().trim();
        let visibleCount = 0;
        
        $('.term-row').each(function() {
            const termName = $(this).data('term-name') || '';
            const academicYear = $(this).data('academic-year') || '';
            const semester = $(this).data('semester') || '';
            
            const searchableText = termName + ' ' + academicYear + ' ' + semester;
            
            if (searchableText.includes(searchTerm)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        // Update count
        $('#searchResultCount').text(visibleCount);
        
        // Show/hide no results message
        if (visibleCount === 0 && $('.term-row').length > 0) {
            $('#noResultsRow').show();
        } else {
            $('#noResultsRow').hide();
        }
    }
    
    // Search on keyup
    $('#termSearchInput').on('keyup', function() {
        filterTerms();
    });
    
    // Clear search button
    $('#clearSearch').on('click', function() {
        $('#termSearchInput').val('');
        filterTerms();
        $('#termSearchInput').focus();
    });
});
</script>
