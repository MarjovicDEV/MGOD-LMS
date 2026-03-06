<?= $this->include('templates/header') ?>

<!-- Manage Course Offerings View - Admin only functionality for course offering management -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">📅 Manage Course Offerings</h2>
                                <p class="mb-0 opacity-75">Create and manage course offerings for each term</p>
                            </div>
                            <div>
                                <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-light btn-sm">
                                    ← Back to Dashboard
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
                <strong>✅ Success!</strong> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>❌ Error!</strong> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>❌ Validation Errors:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Offering Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📚</div>
                    <div class="display-5 fw-bold"><?= $statistics['total'] ?></div>
                    <div class="fw-semibold">Total Offerings</div>
                    <small class="opacity-75">In the system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-secondary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📝</div>
                    <div class="display-5 fw-bold"><?= $statistics['draft'] ?></div>
                    <div class="fw-semibold">Draft</div>
                    <small class="opacity-75">Being prepared</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= $statistics['open'] ?></div>
                    <div class="fw-semibold">Open</div>
                    <small class="opacity-75">Accepting students</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-danger text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🔒</div>
                    <div class="display-5 fw-bold"><?= $statistics['closed'] ?></div>
                    <div class="fw-semibold">Closed</div>
                    <small class="opacity-75">Not accepting</small>
                </div>
            </div>
        </div>

        <!-- Term Filter and Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">                        <div class="row align-items-center">                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="termFilter" class="form-label fw-semibold mb-2">Filter by Term:</label>
                                <select class="form-select" id="termFilter" onchange="filterByTerm(this.value)">
                                    <option value="">All Terms</option>
                                    <?php foreach ($terms as $termOption): ?>
                                        <option value="<?= $termOption['id'] ?>" <?= $selectedTermId == $termOption['id'] ? 'selected' : '' ?>>
                                            <?= esc($termOption['year_code'] ?? '') ?> | <?= esc($termOption['semester_name'] ?? '') ?> - <?= esc($termOption['term_name']) ?>
                                            <?php if (!empty($termOption['start_date']) && !empty($termOption['end_date'])): ?>
                                                (<?= date('M d', strtotime($termOption['start_date'])) ?> - <?= date('M d, Y', strtotime($termOption['end_date'])) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <a href="<?= base_url('admin/manage_offerings?action=create' . ($selectedTermId ? '&term_id=' . $selectedTermId : '')) ?>" class="btn btn-success">
                                    ➕ Add Course Offering
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        <!-- Selected Term Info -->
        <?php if ($selectedTerm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-primary">                    <div class="card-body bg-light">
                        <h5 class="mb-2 fw-bold text-primary">📆 Selected Term:</h5>
                        <h4 class="mb-1">
                            <?php if (!empty($selectedTerm['year_name'])): ?>
                                <?= esc($selectedTerm['year_name']) ?> - <?= esc($selectedTerm['semester_name'] ?? '') ?>
                            <?php endif; ?>
                        </h4>
                        <h5 class="text-secondary mb-2"><?= esc($selectedTerm['term_name']) ?></h5>
                        <p class="text-muted mb-0">
                            <?php if (!empty($selectedTerm['start_date']) && !empty($selectedTerm['end_date'])): ?>
                                <strong>Period:</strong> <?= date('M d, Y', strtotime($selectedTerm['start_date'])) ?> - <?= date('M d, Y', strtotime($selectedTerm['end_date'])) ?> | 
                            <?php endif; ?>
                            <strong>Offerings:</strong> <?= count($offerings) ?> |
                            <strong>Status:</strong> <span class="badge bg-<?= $selectedTerm['is_active'] ? 'success' : 'secondary' ?>"><?= $selectedTerm['is_active'] ? 'Active' : 'Inactive' ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Create Offering Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Add Course Offering</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_offerings?action=create') ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="course_id" class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                                        <select class="form-select" id="course_id" name="course_id" required>
                                            <option value="">-- Select Course --</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?= $course['id'] ?>" <?= old('course_id') == $course['id'] ? 'selected' : '' ?>>
                                                    <?= esc($course['course_code']) ?> - <?= esc($course['title']) ?> (<?= $course['credits'] ?> credits)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Select the course to offer</small>
                                    </div>
                                </div>                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="term_id" class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                                        <select class="form-select" id="term_id" name="term_id" required>
                                            <option value="">-- Select Term --</option>
                                            <?php foreach ($terms as $termOption): ?>
                                                <option value="<?= $termOption['id'] ?>" 
                                                        data-start="<?= esc($termOption['start_date'] ?? '') ?>" 
                                                        data-end="<?= esc($termOption['end_date'] ?? '') ?>"
                                                        <?= (old('term_id') == $termOption['id'] || $selectedTermId == $termOption['id']) ? 'selected' : '' ?>>
                                                    <?= esc($termOption['year_code'] ?? '') ?> | <?= esc($termOption['semester_name'] ?? '') ?> - <?= esc($termOption['term_name']) ?>
                                                    <?php if (!empty($termOption['start_date']) && !empty($termOption['end_date'])): ?>
                                                        (<?= date('M d', strtotime($termOption['start_date'])) ?> - <?= date('M d, Y', strtotime($termOption['end_date'])) ?>)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Select the academic term (Academic Year | Semester - Term)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="section" class="form-label fw-semibold">Section</label>
                                        <input type="text" class="form-control" id="section" name="section" 
                                               value="<?= old('section') ?>" maxlength="50" placeholder="e.g., A, B, 1-A">
                                        <small class="text-muted">Optional section identifier</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="max_students" class="form-label fw-semibold">Max Students <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="max_students" name="max_students" 
                                               value="<?= old('max_students', 30) ?>" min="1" required>
                                        <small class="text-muted">Maximum enrollment capacity</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="room" class="form-label fw-semibold">Room</label>
                                        <input type="text" class="form-control" id="room" name="room" 
                                               value="<?= old('room') ?>" maxlength="100" placeholder="e.g., Room 101, Lab A">
                                        <small class="text-muted">Classroom or location</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="draft" <?= old('status') == 'draft' ? 'selected' : '' ?>>📝 Draft</option>
                                            <option value="open" <?= old('status') == 'open' ? 'selected' : '' ?>>✅ Open</option>
                                            <option value="closed" <?= old('status') == 'closed' ? 'selected' : '' ?>>🔒 Closed</option>
                                            <option value="cancelled" <?= old('status') == 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                            <option value="completed" <?= old('status') == 'completed' ? 'selected' : '' ?>>✔️ Completed</option>
                                        </select>
                                        <small class="text-muted">Current offering status</small>
                                    </div>                                </div>                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label fw-semibold">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="<?= old('start_date') ?>"
                                               min="<?= date('Y-m-d') ?>">
                                        <small class="text-muted">Year must match Academic Year</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label fw-semibold">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                               value="<?= old('end_date') ?>"
                                               min="<?= date('Y-m-d') ?>">
                                        <small class="text-muted">Year must match Academic Year</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('admin/manage_offerings' . ($selectedTermId ? '?term_id=' . $selectedTermId : '')) ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    ➕ Create Offering
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Offering Form (shown when action=edit) -->
        <?php if ($showEditForm && isset($editOffering)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-dark border-0">
                        <h5 class="mb-0">✏️ Edit Course Offering</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_offerings?action=edit&id=' . $editOffering['id']) ?>">
                            <?= csrf_field() ?>                            <!-- Display course and term info (non-editable) -->
                            <div class="alert alert-info mb-3">
                                <strong>Course:</strong> <?= esc($course['course_code']) ?> - <?= esc($course['title']) ?><br>
                                <strong>Term:</strong> 
                                <?php if (!empty($term)): ?>
                                    <?php if (!empty($term['year_name'])): ?>
                                        <?= esc($term['year_name']) ?> - <?= esc($term['semester_name'] ?? '') ?> | 
                                    <?php endif; ?>
                                    <?= esc($term['term_name'] ?? 'Unknown Term') ?>
                                    <?php if (!empty($term['start_date']) && !empty($term['end_date'])): ?>
                                        <br><strong>Term Period:</strong> <?= date('M d, Y', strtotime($term['start_date'])) ?> - <?= date('M d, Y', strtotime($term['end_date'])) ?>
                                    <?php else: ?>
                                        <br><small class="text-warning">⚠️ Term dates not set - please set dates in Term management</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-danger">Term data not found</span>
                                <?php endif; ?>
                                <small class="d-block mt-1 text-muted">Course and term cannot be changed. Create a new offering if needed.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="section" class="form-label fw-semibold">Section</label>
                                        <input type="text" class="form-control" id="section" name="section" 
                                               value="<?= old('section', $editOffering['section']) ?>" maxlength="50">
                                        <small class="text-muted">Optional section identifier</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="max_students" class="form-label fw-semibold">Max Students <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="max_students" name="max_students" 
                                               value="<?= old('max_students', $editOffering['max_students']) ?>" min="1" required>
                                        <small class="text-muted">Current enrollment: <?= $editOffering['current_enrollment'] ?></small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="room" class="form-label fw-semibold">Room</label>
                                        <input type="text" class="form-control" id="room" name="room" 
                                               value="<?= old('room', $editOffering['room']) ?>" maxlength="100">
                                        <small class="text-muted">Classroom or location</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="draft" <?= old('status', $editOffering['status']) == 'draft' ? 'selected' : '' ?>>📝 Draft</option>
                                            <option value="open" <?= old('status', $editOffering['status']) == 'open' ? 'selected' : '' ?>>✅ Open</option>
                                            <option value="closed" <?= old('status', $editOffering['status']) == 'closed' ? 'selected' : '' ?>>🔒 Closed</option>
                                            <option value="cancelled" <?= old('status', $editOffering['status']) == 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                            <option value="completed" <?= old('status', $editOffering['status']) == 'completed' ? 'selected' : '' ?>>✔️ Completed</option>
                                        </select>
                                    </div>
                                </div>                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_start_date" class="form-label fw-semibold">Start Date</label>
                                        <input type="date" class="form-control" id="edit_start_date" name="start_date" 
                                               value="<?= old('start_date', $editOffering['start_date']) ?>"
                                               <?php if (!empty($term['start_date'])): ?>min="<?= $term['start_date'] ?>"<?php endif; ?>
                                               <?php if (!empty($term['end_date'])): ?>max="<?= $term['end_date'] ?>"<?php endif; ?>>
                                        <small class="text-muted">
                                            <?php if (!empty($term['year_code'])): ?>
                                                Year must match Academic Year <?= esc($term['year_code']) ?>
                                            <?php else: ?>
                                                Set the offering start date
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_end_date" class="form-label fw-semibold">End Date</label>
                                        <input type="date" class="form-control" id="edit_end_date" name="end_date" 
                                               value="<?= old('end_date', $editOffering['end_date']) ?>"
                                               <?php if (!empty($term['start_date'])): ?>min="<?= $term['start_date'] ?>"<?php endif; ?>
                                               <?php if (!empty($term['end_date'])): ?>max="<?= $term['end_date'] ?>"<?php endif; ?>>
                                        <small class="text-muted">
                                            <?php if (!empty($term['year_code'])): ?>
                                                Year must match Academic Year <?= esc($term['year_code']) ?>
                                            <?php else: ?>
                                                Set the offering end date
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('admin/manage_offerings?term_id=' . $editOffering['term_id']) ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    💾 Update Offering
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Offerings Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">📋 Course Offerings List</h5>
                        <small class="text-muted">Total: <?= count($offerings) ?> offering(s)</small>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($offerings)): ?>
                            <div class="text-center py-5">
                                <div class="display-1 mb-3">📭</div>
                                <h4 class="text-muted">No Course Offerings Found</h4>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3">Course</th>
                                            <th class="py-3">Term</th>
                                            <th class="py-3">Section</th>
                                            <th class="py-3">Enrollment</th>
                                            <th class="py-3">Room</th>
                                            <th class="py-3">Status</th>
                                            <th class="py-3">Dates</th>
                                            <th class="py-3 text-end px-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($offerings as $offering): ?>
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <strong><?= esc($offering['course_code']) ?></strong><br>
                                                    <small class="text-muted"><?= esc($offering['course_title']) ?></small><br>
                                                    <small class="badge bg-secondary"><?= $offering['credits'] ?> credits</small>
                                                </td>
                                                <td class="py-3">
                                                    <strong><?= esc($offering['term_name']) ?></strong>
                                                </td>
                                                <td class="py-3">
                                                    <?= $offering['section'] ? esc($offering['section']) : '<span class="text-muted">-</span>' ?>
                                                </td>
                                                <td class="py-3">
                                                    <?php
                                                    $enrolled = isset($offering['enrolled_count']) ? $offering['enrolled_count'] : $offering['current_enrollment'];
                                                    $maxStudents = $offering['max_students'];
                                                    $percentage = $maxStudents > 0 ? ($enrolled / $maxStudents) * 100 : 0;
                                                    $progressColor = $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');
                                                    ?>
                                                    <div class="d-flex align-items-center">
                                                        <small class="me-2"><?= $enrolled ?>/<?= $maxStudents ?></small>
                                                        <div class="progress flex-grow-1" style="height: 8px; width: 60px;">
                                                            <div class="progress-bar bg-<?= $progressColor ?>" style="width: <?= min($percentage, 100) ?>%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <?= $offering['room'] ? esc($offering['room']) : '<span class="text-muted">-</span>' ?>
                                                </td>
                                                <td class="py-3">
                                                    <?php
                                                    $statusBadges = [
                                                        'draft' => 'secondary',
                                                        'open' => 'success',
                                                        'closed' => 'danger',
                                                        'cancelled' => 'dark',
                                                        'completed' => 'info'
                                                    ];
                                                    $badgeClass = $statusBadges[$offering['status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($offering['status']) ?></span>
                                                </td>
                                                <td class="py-3">
                                                    <?php if ($offering['start_date'] && $offering['end_date']): ?>
                                                        <small>
                                                            <?= date('M d', strtotime($offering['start_date'])) ?> -<br>
                                                            <?= date('M d, Y', strtotime($offering['end_date'])) ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>                                                <td class="py-3 text-end px-4">
                                                    <div class="btn-group btn-group-sm">
                                                        <!-- Course Materials Button -->
                                                        <a href="<?= base_url('admin/course/' . $offering['id'] . '/upload') ?>" 
                                                           class="btn btn-outline-success" 
                                                           title="Manage Course Materials">
                                                            <i class="fas fa-folder-open"></i>
                                                        </a>
                                                        
                                                        <a href="<?= base_url('admin/manage_offerings?action=toggle_status&id=' . $offering['id']) ?>" 
                                                           class="btn btn-outline-primary" 
                                                           title="Toggle Status"
                                                           onclick="return confirm('Change status to next state?')">
                                                            🔄
                                                        </a>
                                                        <a href="<?= base_url('admin/manage_offerings?action=edit&id=' . $offering['id']) ?>" 
                                                           class="btn btn-outline-warning" 
                                                           title="Edit">
                                                            ✏️
                                                        </a>
                                                        <a href="<?= base_url('admin/manage_offerings?action=delete&id=' . $offering['id']) ?>" 
                                                           class="btn btn-outline-danger" 
                                                           title="Delete"
                                                           onclick="return confirm('Are you sure you want to delete this offering?\n\nCourse: <?= esc($offering['course_code']) ?>\nTerm: <?= esc($offering['term_name']) ?>\nSection: <?= esc($offering['section']) ?>')">
                                                            🗑️
                                                        </a>
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
</div>

<script>
function filterByTerm(termId) {
    if (termId) {
        window.location.href = '<?= base_url('admin/manage_offerings') ?>?term_id=' + termId;
    } else {
        window.location.href = '<?= base_url('admin/manage_offerings') ?>';
    }
}

// Term data for auto-populating dates and validation
const termsData = {
    <?php foreach ($terms as $termOption): ?>
    '<?= $termOption['id'] ?>': {
        start_date: '<?= $termOption['start_date'] ?? '' ?>',
        end_date: '<?= $termOption['end_date'] ?? '' ?>',
        term_name: '<?= esc($termOption['term_name']) ?>',
        year_code: '<?= esc($termOption['year_code'] ?? '') ?>',
        academic_year: '<?= esc($termOption['academic_year'] ?? '') ?>'
    },
    <?php endforeach; ?>
};

// Function to extract years from academic year code (e.g., "AY2025-2026" or "2025-2026" returns [2025, 2026])
function getAcademicYears(yearCode) {
    if (!yearCode) return [];
    
    // Remove "AY" prefix if present (case-insensitive)
    let cleanedCode = yearCode.replace(/^AY/i, '').trim();
    
    // Extract all 4-digit numbers from the string (handles dashes, HTML entities, etc.)
    const matches = cleanedCode.match(/\d{4}/g);
    
    if (!matches) return [];
    
    const years = matches.map(y => parseInt(y));
    
    return years.filter(y => !isNaN(y));
}

// Function to validate if a date falls within the academic year
function validateAcademicYearDate(dateString, yearCode) {
    if (!dateString || !yearCode) return true; // Allow empty dates
    
    const selectedDate = new Date(dateString);
    const selectedYear = selectedDate.getFullYear();
    const academicYears = getAcademicYears(yearCode);
    
    if (academicYears.length === 0) return true; // No validation if year code not available
    
    // Check if the selected year matches any of the academic years
    return academicYears.includes(selectedYear);
}

// Function to show validation error message
function showDateValidationError(inputElement, message) {
    inputElement.classList.add('is-invalid');
    inputElement.setCustomValidity(message); // HTML5 validation API
    
    // Remove existing error message
    const existingError = inputElement.parentElement.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback d-block';
    errorDiv.innerHTML = '<strong>⚠️ ' + message + '</strong>';
    inputElement.parentElement.appendChild(errorDiv);
}

// Function to clear validation error
function clearDateValidationError(inputElement) {
    inputElement.classList.remove('is-invalid');
    inputElement.setCustomValidity(''); // Clear HTML5 validation
    const errorDiv = inputElement.parentElement.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Auto-populate dates when term is selected
document.addEventListener('DOMContentLoaded', function() {
    const termSelect = document.getElementById('term_id');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format
    
    // Edit form elements
    const editStartDateInput = document.getElementById('edit_start_date');
    const editEndDateInput = document.getElementById('edit_end_date');
    
    // Function to setup date validation for a form
    function setupDateValidation(termSelectEl, startDateEl, endDateEl) {
        if (!termSelectEl || !startDateEl || !endDateEl) return;
        
        let currentYearCode = '';
        
        // Update year code when term changes
        termSelectEl.addEventListener('change', function() {
            const termId = this.value;
            if (termId && termsData[termId]) {
                currentYearCode = termsData[termId].year_code;
                
                // Validate existing dates
                if (startDateEl.value) {
                    validateDateInput(startDateEl, currentYearCode, 'Start Date');
                }
                if (endDateEl.value) {
                    validateDateInput(endDateEl, currentYearCode, 'End Date');
                }
            } else {
                currentYearCode = '';
                clearDateValidationError(startDateEl);
                clearDateValidationError(endDateEl);
            }
        });
        
        // Set initial year code if term is pre-selected
        if (termSelectEl.value && termsData[termSelectEl.value]) {
            currentYearCode = termsData[termSelectEl.value].year_code;
        }
        
        // Validate dates on input
        function validateDateInput(inputEl, yearCode, fieldName) {
            if (!inputEl.value) {
                clearDateValidationError(inputEl);
                return true;
            }
            
            if (!yearCode) {
                clearDateValidationError(inputEl);
                return true;
            }
            
            const isValid = validateAcademicYearDate(inputEl.value, yearCode);
            
            if (!isValid) {
                const academicYears = getAcademicYears(yearCode);
                const yearsList = academicYears.join(' or ');
                showDateValidationError(inputEl, `${fieldName} year must be ${yearsList} to match Academic Year ${yearCode}`);
                return false;
            } else {
                clearDateValidationError(inputEl);
                return true;
            }
        }
        
        startDateEl.addEventListener('change', function() {
            validateDateInput(this, currentYearCode, 'Start Date');
        });
        
        endDateEl.addEventListener('change', function() {
            validateDateInput(this, currentYearCode, 'End Date');
        });
          // Prevent form submission if validation fails
        const form = startDateEl.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const errors = [];
                
                if (startDateEl.value && !validateAcademicYearDate(startDateEl.value, currentYearCode)) {
                    validateDateInput(startDateEl, currentYearCode, 'Start Date');
                    isValid = false;
                    errors.push('Start Date year does not match Academic Year');
                }
                
                if (endDateEl.value && !validateAcademicYearDate(endDateEl.value, currentYearCode)) {
                    validateDateInput(endDateEl, currentYearCode, 'End Date');
                    isValid = false;
                    errors.push('End Date year does not match Academic Year');
                }
                  if (!isValid) {
                    e.preventDefault();
                    e.stopPropagation();
                    const academicYears = getAcademicYears(currentYearCode);
                    const yearsList = academicYears.join(' or ');
                    alert(`⚠️ Validation Error!\n\nThe dates you entered do not match the Academic Year ${currentYearCode}.\n\nPlease use dates from year ${yearsList} only.\n\nErrors:\n- ${errors.join('\n- ')}`);
                    return false;
                }
                
                // Check if term has dates set when offering dates are provided
                const termId = termSelectEl.value;
                if (termId && termsData[termId]) {
                    const termData = termsData[termId];
                    const hasOfferingDates = startDateEl.value || endDateEl.value;
                    const termHasDates = termData.start_date && termData.end_date;
                    
                    if (hasOfferingDates && !termHasDates) {
                        e.preventDefault();
                        e.stopPropagation();
                        alert(`⚠️ Term Dates Required!\n\nThe selected term does not have start/end dates set.\n\nPlease either:\n1. Clear the offering dates (leave them empty), OR\n2. Set term dates first in Term Management\n\nTerm: ${termData.term_name}\nAcademic Year: ${termData.academic_year || 'N/A'}`);
                        return false;
                    }
                }
            });
        }
    }
    
    // Setup validation for create form
    if (termSelect && startDateInput && endDateInput) {
        setupDateValidation(termSelect, startDateInput, endDateInput);
          termSelect.addEventListener('change', function() {
            const termId = this.value;
            
            if (termId && termsData[termId]) {
                const termData = termsData[termId];
                
                // Check if term has dates set
                const termHasDates = termData.start_date && termData.end_date;
                
                // Show warning if term doesn't have dates
                let warningDiv = document.getElementById('term-date-warning');
                if (!termHasDates) {
                    if (!warningDiv) {
                        warningDiv = document.createElement('div');
                        warningDiv.id = 'term-date-warning';
                        warningDiv.className = 'alert alert-warning mt-3';
                        warningDiv.innerHTML = `<strong>⚠️ Warning:</strong> The selected term does not have start/end dates set. You should leave the offering dates empty, or set term dates first in <a href="${'<?= base_url("admin/manage_terms") ?>'}" target="_blank">Term Management</a>.`;
                        termSelect.parentElement.parentElement.appendChild(warningDiv);
                    }
                    // Disable date inputs if term has no dates
                    startDateInput.disabled = true;
                    endDateInput.disabled = true;
                    startDateInput.value = '';
                    endDateInput.value = '';
                    startDateInput.parentElement.querySelector('.text-muted').innerHTML = '<span class="text-warning">⚠️ Term dates must be set first</span>';
                    endDateInput.parentElement.querySelector('.text-muted').innerHTML = '<span class="text-warning">⚠️ Term dates must be set first</span>';
                } else {
                    // Remove warning if it exists
                    if (warningDiv) {
                        warningDiv.remove();
                    }
                    // Enable date inputs
                    startDateInput.disabled = false;
                    endDateInput.disabled = false;
                    startDateInput.parentElement.querySelector('.text-muted').innerHTML = 'Year must match Academic Year';
                    endDateInput.parentElement.querySelector('.text-muted').innerHTML = 'Year must match Academic Year';
                }
                
                // Only auto-fill if term has dates and fields are empty
                if (termHasDates) {
                    if (!startDateInput.value && termData.start_date) {
                        // Use term date only if it's today or in the future, otherwise use today
                        startDateInput.value = termData.start_date >= today ? termData.start_date : today;
                    }
                    if (!endDateInput.value && termData.end_date) {
                        // Use term date only if it's today or in the future, otherwise use today
                        endDateInput.value = termData.end_date >= today ? termData.end_date : today;
                    }
                }
                
                // Update min/max attributes based on term dates (but min can't be before today)
                if (termData.start_date) {
                    const minDate = termData.start_date >= today ? termData.start_date : today;
                    startDateInput.min = minDate;
                    endDateInput.min = minDate;
                }
                if (termData.end_date) {
                    startDateInput.max = termData.end_date;
                    endDateInput.max = termData.end_date;
                }
            }
        });
        
        // Trigger on page load if term is pre-selected
        if (termSelect.value && termsData[termSelect.value]) {
            const termData = termsData[termSelect.value];
            if (!startDateInput.value && termData.start_date) {
                startDateInput.value = termData.start_date >= today ? termData.start_date : today;
            }
            if (!endDateInput.value && termData.end_date) {
                endDateInput.value = termData.end_date >= today ? termData.end_date : today;
            }
            
            // Update min/max attributes based on term dates
            if (termData.start_date) {
                const minDate = termData.start_date >= today ? termData.start_date : today;
                startDateInput.min = minDate;
                endDateInput.min = minDate;
            }
            if (termData.end_date) {
                startDateInput.max = termData.end_date;
                endDateInput.max = termData.end_date;
            }
        }
    }
    
    // Setup validation for edit form
    if (editStartDateInput && editEndDateInput) {
        // Get term ID from the edit form (it's fixed, not changeable)
        const editForm = editStartDateInput.closest('form');
        if (editForm) {
            const editTermInfo = editForm.querySelector('.alert-info');
            if (editTermInfo) {
                // Extract term info from the page context
                <?php if ($showEditForm && isset($editOffering) && isset($term)): ?>
                const editTermData = {
                    year_code: '<?= esc($term['year_code'] ?? '') ?>',
                    academic_year: '<?= esc($term['year_name'] ?? '') ?>'
                };
                
                let currentYearCode = editTermData.year_code;
                
                function validateEditDateInput(inputEl, fieldName) {
                    if (!inputEl.value || !currentYearCode) {
                        clearDateValidationError(inputEl);
                        return true;
                    }
                    
                    const isValid = validateAcademicYearDate(inputEl.value, currentYearCode);
                    
                    if (!isValid) {
                        const academicYears = getAcademicYears(currentYearCode);
                        const yearsList = academicYears.join(' or ');
                        showDateValidationError(inputEl, `${fieldName} year must be ${yearsList} to match Academic Year ${currentYearCode}`);
                        return false;
                    } else {
                        clearDateValidationError(inputEl);
                        return true;
                    }
                }
                
                editStartDateInput.addEventListener('change', function() {
                    validateEditDateInput(this, 'Start Date');
                });
                
                editEndDateInput.addEventListener('change', function() {
                    validateEditDateInput(this, 'End Date');
                });
                  // Validate on form submit
                editForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    const errors = [];
                    
                    if (editStartDateInput.value && !validateAcademicYearDate(editStartDateInput.value, currentYearCode)) {
                        validateEditDateInput(editStartDateInput, 'Start Date');
                        isValid = false;
                        errors.push('Start Date year does not match Academic Year');
                    }
                    
                    if (editEndDateInput.value && !validateAcademicYearDate(editEndDateInput.value, currentYearCode)) {
                        validateEditDateInput(editEndDateInput, 'End Date');
                        isValid = false;
                        errors.push('End Date year does not match Academic Year');
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                        e.stopPropagation();
                        const academicYears = getAcademicYears(currentYearCode);
                        const yearsList = academicYears.join(' or ');
                        alert(`⚠️ Validation Error!\n\nThe dates you entered do not match the Academic Year ${currentYearCode}.\n\nPlease use dates from year ${yearsList} only.\n\nErrors:\n- ${errors.join('\n- ')}`);
                        return false;
                    }
                });
                <?php endif; ?>
            }
        }
    }
});
</script>