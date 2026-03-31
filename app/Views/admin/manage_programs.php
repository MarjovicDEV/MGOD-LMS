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
    .lms-admin-view .btn-primary {
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

<!-- Manage Programs View - Admin only functionality for program management -->
<div class="lms-admin-view min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3 admin-hero">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">Manage Programs</h2>
                                <p class="mb-0 opacity-75">Create, edit, and manage academic programs in the learning management system</p>
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

        <!-- Program Statistics Cards -->
        <div class="row mb-4 admin-stats">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-5 fw-bold"><?= count($programs) ?></div>
                    <div class="fw-semibold">Total Programs</div>
                    <small class="opacity-75">In the system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-5 fw-bold"><?= count(array_filter($programs, fn($p) => $p['is_active'] == 1)) ?></div>
                    <div class="fw-semibold">Active</div>
                    <small class="opacity-75">Currently available</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-5 fw-bold"><?= array_sum(array_column($programs, 'student_count')) ?></div>
                    <div class="fw-semibold">Total Students</div>
                    <small class="opacity-75">Across all programs</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-5 fw-bold"><?= array_sum(array_column($programs, 'course_count')) ?></div>
                    <div class="fw-semibold">Total Courses</div>
                    <small class="opacity-75">In all programs</small>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">Program Management</h5>
                            <a href="<?= base_url('admin/manage_programs?action=create') ?>" class="btn btn-success">
                                Create New Program
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
                                           id="programSearchInput" 
                                           class="form-control border-start-0" 
                                           placeholder="Search programs by code, name, description, or department...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2 mt-md-0">
                                <div class="text-muted">
                                    <small>
                                        <strong id="searchResultCount"><?= count($programs) ?></strong> program(s) found
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Program Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">Create New Program</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_programs?action=create') ?>">
                            <?= csrf_field() ?>
                            
                            <!-- Basic Information Section -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="program_code" class="form-label fw-semibold">Program Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control text-uppercase" id="program_code" name="program_code" 
                                                   value="<?= old('program_code') ?>" required 
                                                   minlength="2" maxlength="20" 
                                                   placeholder="e.g., BSIT, BSCS">
                                            <div class="form-text text-info"><i class="fas fa-info-circle me-1"></i>Uppercase letters only (e.g., BSIT, BSCS, MIT). No numbers or special characters.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="program_name" class="form-label fw-semibold">Program Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="program_name" name="program_name" 
                                                   value="<?= old('program_name') ?>" required 
                                                   minlength="3" maxlength="200" 
                                                   placeholder="e.g., Bachelor of Science in Information Technology">
                                            <div class="form-text text-info"><i class="fas fa-info-circle me-1"></i>Letters and spaces only. No numbers or special characters.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Program Details Section -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-cogs me-2"></i>Program Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="degree_type" class="form-label fw-semibold">Degree Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="degree_type" name="degree_type" required>
                                                <option value="">-- Select Degree Type --</option>
                                                <option value="bachelor" <?= old('degree_type') == 'bachelor' ? 'selected' : '' ?>> Bachelor's Degree</option>
                                                <option value="master" <?= old('degree_type') == 'master' ? 'selected' : '' ?>> Master's Degree</option>
                                                <option value="doctorate" <?= old('degree_type') == 'doctorate' ? 'selected' : '' ?>> Doctorate Degree</option>
                                                <option value="certificate" <?= old('degree_type') == 'certificate' ? 'selected' : '' ?>> Certificate Program</option>
                                                <option value="diploma" <?= old('degree_type') == 'diploma' ? 'selected' : '' ?>> Diploma Program</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="department_id" class="form-label fw-semibold">Department</label>
                                            <select class="form-select" id="department_id" name="department_id">
                                                <option value="">-- Select Department --</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>" <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>>
                                                        <?= esc($dept['department_code']) ?> - <?= esc($dept['department_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="total_units" class="form-label fw-semibold">Total Units <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="total_units" name="total_units" 
                                                   value="<?= old('total_units') ?>" required
                                                   min="1" placeholder="e.g., 120">
                                            <div class="form-text">Total credit units required</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="total_years" class="form-label fw-semibold">Total Years <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="total_years" name="total_years" 
                                                   value="<?= old('total_years') ?>" required
                                                   min="1" max="10" placeholder="e.g., 4">
                                            <div class="form-text">Program duration in years</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-align-left me-2"></i>Description
                                </h6>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label fw-semibold">Description</label>
                                            <textarea class="form-control" 
                                                      id="description" 
                                                      name="description" 
                                                      rows="4"><?= old('description') ?></textarea>
                                            <div class="form-text">Provide detailed program information</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Section -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-toggle-on me-2"></i>Status
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active', 1) ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-semibold" for="is_active">
                                                Active (Available for enrollment)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    Create Program
                                </button>
                                <a href="<?= base_url('admin/manage_programs') ?>" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Program Form (shown when editing) -->
        <?php if ($showEditForm && $editProgram): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-dark border-0">
                        <h5 class="mb-0">Edit Program: <?= esc($editProgram['program_name']) ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_programs?action=edit&id=' . $editProgram['id']) ?>">
                            <?= csrf_field() ?>
                            
                            <!-- Basic Information Section -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_program_code" class="form-label fw-semibold">Program Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control text-uppercase" id="edit_program_code" name="program_code" 
                                                   value="<?= old('program_code', $editProgram['program_code']) ?>" required 
                                                   minlength="2" maxlength="20">
                                            <div class="form-text text-info"><i class="fas fa-info-circle me-1"></i>Uppercase letters only (e.g., BSIT, BSCS, MIT). No numbers or special characters.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_program_name" class="form-label fw-semibold">Program Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="edit_program_name" name="program_name" 
                                                   value="<?= old('program_name', $editProgram['program_name']) ?>" required 
                                                   minlength="3" maxlength="200">
                                            <div class="form-text text-info"><i class="fas fa-info-circle me-1"></i>Letters and spaces only. No numbers or special characters.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Program Details Section -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-cogs me-2"></i>Program Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_degree_type" class="form-label fw-semibold">Degree Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="edit_degree_type" name="degree_type" required>
                                                <option value="">-- Select Degree Type --</option>
                                                <option value="bachelor" <?= old('degree_type', $editProgram['degree_type']) == 'bachelor' ? 'selected' : '' ?>> Bachelor's Degree</option>
                                                <option value="master" <?= old('degree_type', $editProgram['degree_type']) == 'master' ? 'selected' : '' ?>> Master's Degree</option>
                                                <option value="doctorate" <?= old('degree_type', $editProgram['degree_type']) == 'doctorate' ? 'selected' : '' ?>> Doctorate Degree</option>
                                                <option value="certificate" <?= old('degree_type', $editProgram['degree_type']) == 'certificate' ? 'selected' : '' ?>> Certificate Program</option>
                                                <option value="diploma" <?= old('degree_type', $editProgram['degree_type']) == 'diploma' ? 'selected' : '' ?>> Diploma Program</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_department_id" class="form-label fw-semibold">Department</label>
                                            <select class="form-select" id="edit_department_id" name="department_id">
                                                <option value="">-- Select Department --</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>" <?= old('department_id', $editProgram['department_id']) == $dept['id'] ? 'selected' : '' ?>>
                                                        <?= esc($dept['department_code']) ?> - <?= esc($dept['department_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_total_units" class="form-label fw-semibold">Total Units <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="edit_total_units" name="total_units" 
                                                   value="<?= old('total_units', $editProgram['total_units']) ?>" required
                                                   min="1">
                                            <div class="form-text">Total credit units required</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_total_years" class="form-label fw-semibold">Total Years <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="edit_total_years" name="total_years" 
                                                   value="<?= old('total_years', $editProgram['total_years']) ?>" required
                                                   min="1" max="10">
                                            <div class="form-text">Program duration in years</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-align-left me-2"></i>Description
                                </h6>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="edit_description" class="form-label fw-semibold">Description</label>
                                            <textarea class="form-control" 
                                                      id="edit_description" 
                                                      name="description" 
                                                      rows="4"><?= old('description', $editProgram['description']) ?></textarea>
                                            <div class="form-text">Provide detailed program information</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Section -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-toggle-on me-2"></i>Status
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1" <?= old('is_active', $editProgram['is_active']) ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-semibold" for="edit_is_active">
                                                Active (Available for enrollment)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning text-dark">
                                    Update Program
                                </button>
                                <a href="<?= base_url('admin/manage_programs') ?>" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Programs List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 fw-bold">Program List</h5>
                    </div>                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="programsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Program Name</th>
                                        <th>Department</th>
                                        <th>Degree Type</th>
                                        <th class="text-center">Units</th>
                                        <th class="text-center">Years</th>
                                        <th class="text-center">Courses</th>
                                        <th class="text-center">Students</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="programTableBody">
                                    <?php if (!empty($programs)): ?>
                                        <?php foreach ($programs as $program): ?>
                                            <tr class="program-row"
                                                data-program-code="<?= esc(strtolower($program['program_code'])) ?>"
                                                data-program-name="<?= esc(strtolower($program['program_name'])) ?>"
                                                data-description="<?= esc(strtolower($program['description'] ?? '')) ?>"
                                                data-department="<?= esc(strtolower($program['department_name'] ?? '')) ?>">
                                                <td>
                                                    <span class="badge bg-secondary"><?= esc($program['program_code']) ?></span>
                                                </td>
                                                <td>
                                                    <strong><?= esc($program['program_name']) ?></strong>
                                                    <?php if (!empty($program['description'])): ?>
                                                        <br><small class="text-muted"><?= esc(substr($program['description'], 0, 50)) ?><?= strlen($program['description']) > 50 ? '...' : '' ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($program['department_name'])): ?>
                                                        <span class="badge bg-info"><?= esc($program['department_code']) ?></span>
                                                        <br><small><?= esc($program['department_name']) ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $degreeLabels = [
                                                        'bachelor' => ['Bachelor', 'primary'],
                                                        'master' => ['Master', 'success'],
                                                        'doctorate' => ['Doctorate', 'danger'],
                                                        'certificate' => ['Certificate', 'warning'],
                                                        'diploma' => ['Diploma', 'info']
                                                    ];
                                                    $degree = $degreeLabels[$program['degree_type']] ?? ['Unknown', 'secondary'];
                                                    ?>
                                                    <span class="badge bg-<?= $degree[1] ?>"><?= $degree[0] ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-dark"><?= $program['total_units'] ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-dark"><?= $program['total_years'] ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary"><?= $program['course_count'] ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success"><?= $program['student_count'] ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <form method="post" action="<?= base_url('admin/manage_programs') ?>" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                                        <button type="submit" class="btn btn-sm <?= $program['is_active'] ? 'btn-success' : 'btn-secondary' ?>" 
                                                                onclick="return confirm('Toggle program status?')">
                                                            <?= $program['is_active'] ? 'Active' : 'Inactive' ?>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="<?= base_url('admin/manage_programs?action=edit&id=' . $program['id']) ?>" class="btn btn-sm btn-primary">
                                                             Edit
                                                        </a>
                                                        <form method="post" action="<?= base_url('admin/manage_programs') ?>" class="d-inline">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this program? This action may fail if there are dependencies.')">
                                                                 Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>                                        </tr>
                                        <?php endforeach; ?>
                                        <tr id="noResultsRow" style="display: none;">
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <i class="fas fa-search mb-2" style="font-size: 2rem;"></i>
                                                <p class="mb-0">No programs match your search criteria.</p>
                                                <small>Try adjusting your search terms.</small>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr id="noProgramsRow">
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <p class="mb-0">No programs found. Create one to get started!</p>
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
$(document).ready(function() {
    // Add real-time validation for program code (uppercase letters only)
    $('#program_code, #edit_program_code').on('input', function() {
        let value = $(this).val().toUpperCase();
        // Remove any non-letter characters
        value = value.replace(/[^A-Z]/g, '');
        $(this).val(value);
        
        // Visual feedback
        if (value.length >= 2 && value.length <= 20) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Add real-time validation for program name (letters and spaces only)
    $('#program_name, #edit_program_name').on('input', function() {
        let value = $(this).val();
        // Remove any numbers or special characters
        value = value.replace(/[^A-Za-z\s]/g, '');
        $(this).val(value);
        
        // Visual feedback
        if (value.trim().length >= 3 && value.trim().length <= 200) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Validate numbers only for units and years
    $('#total_units, #edit_total_units, #total_years, #edit_total_years').on('input', function() {
        let value = $(this).val();
        // Only allow numbers
        value = value.replace(/[^0-9]/g, '');
        $(this).val(value);
    });

    // Program search functionality
    function filterPrograms() {
        const searchTerm = $('#programSearchInput').val().toLowerCase().trim();
        let visibleCount = 0;
        
        $('.program-row').each(function() {
            const programCode = $(this).data('program-code') || '';
            const programName = $(this).data('program-name') || '';
            const description = $(this).data('description') || '';
            const department = $(this).data('department') || '';
            
            const searchableText = programCode + ' ' + programName + ' ' + description + ' ' + department;
            
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
        if (visibleCount === 0 && $('.program-row').length > 0) {
            $('#noResultsRow').show();
        } else {
            $('#noResultsRow').hide();
        }
    }
    
    // Search on keyup
    $('#programSearchInput').on('keyup', function() {
        filterPrograms();
    });
    
    // Clear search button
    $('#clearSearch').on('click', function() {
        $('#programSearchInput').val('');
        filterPrograms();
        $('#programSearchInput').focus();
    });
});
</script>



