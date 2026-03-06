<?= $this->include('templates/header') ?>

<!-- Manage Program Curriculum View - Admin only functionality for curriculum management -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">📚 Manage Program Curriculum</h2>
                                <p class="mb-0 opacity-75">Add, edit, and manage courses in program curricula</p>
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

        <!-- Curriculum Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📋</div>
                    <div class="display-5 fw-bold"><?= count($curriculum) ?></div>
                    <div class="fw-semibold">Total Entries</div>
                    <small class="opacity-75">In curriculum</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($curriculum, fn($c) => $c['is_active'] == 1)) ?></div>
                    <div class="fw-semibold">Active</div>
                    <small class="opacity-75">Currently available</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📝</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($curriculum, fn($c) => $c['is_active'] == 0)) ?></div>
                    <div class="fw-semibold">Inactive</div>
                    <small class="opacity-75">Not available</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-secondary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🎓</div>
                    <div class="display-5 fw-bold"><?= count($programs) ?></div>
                    <div class="fw-semibold">Programs</div>
                    <small class="opacity-75">Available</small>
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

        <!-- Filter by Program -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0 fw-bold text-dark">🔍 Filter & Actions</h5>
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <form method="get" action="<?= base_url('admin/manage_curriculum') ?>" class="d-flex gap-2">
                                    <select name="program_id" class="form-select form-select-sm" style="min-width: 250px;" onchange="this.form.submit()">
                                        <option value="">-- All Programs --</option>
                                        <?php foreach ($programs as $program): ?>
                                            <option value="<?= $program['id'] ?>" <?= $selectedProgramId == $program['id'] ? 'selected' : '' ?>>
                                                <?= esc($program['program_code']) ?> - <?= esc($program['program_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <a href="<?= base_url('admin/manage_curriculum?action=create' . ($selectedProgramId ? '&program_id=' . $selectedProgramId : '')) ?>" class="btn btn-success btn-sm">
                                    ➕ Add Course to Curriculum
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Curriculum Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Add Course to Program Curriculum</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_curriculum?action=create') ?>">
                            <?= csrf_field() ?>
                            
                            <!-- Program & Course Selection -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-graduation-cap me-2"></i>Program & Course Selection
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="program_id" class="form-label fw-semibold">Program <span class="text-danger">*</span></label>
                                            <select class="form-select" id="program_id" name="program_id" required>
                                                <option value="">-- Select Program --</option>
                                                <?php foreach ($programs as $program): ?>
                                                    <option value="<?= $program['id'] ?>" <?= old('program_id', $selectedProgramId) == $program['id'] ? 'selected' : '' ?>>
                                                        <?= esc($program['program_code']) ?> - <?= esc($program['program_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Select the academic program</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="course_id" class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                                            <select class="form-select" id="course_id" name="course_id" required>
                                                <option value="">-- Select Course --</option>
                                                <?php foreach ($courses as $course): ?>
                                                    <option value="<?= $course['id'] ?>" <?= old('course_id') == $course['id'] ? 'selected' : '' ?>>
                                                        <?= esc($course['course_code']) ?> - <?= esc($course['title']) ?> (<?= esc($course['credits']) ?> units)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Select the course to add</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Schedule Details -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-calendar-alt me-2"></i>Schedule Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="year_level_id" class="form-label fw-semibold">Year Level <span class="text-danger">*</span></label>                                            <select class="form-select" id="year_level_id" name="year_level_id" required>
                                                <option value="">-- Select Year Level --</option>
                                                <?php foreach ($yearLevels as $yearLevel): ?>
                                                    <option value="<?= $yearLevel['id'] ?>" <?= old('year_level_id') == $yearLevel['id'] ? 'selected' : '' ?>>
                                                        <?= esc($yearLevel['year_level_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">When this course is offered</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="semester_id" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                            <select class="form-select" id="semester_id" name="semester_id" required>
                                                <option value="">-- Select Semester --</option>
                                                <?php foreach ($semesters as $semester): ?>
                                                    <option value="<?= $semester['id'] ?>" <?= old('semester_id') == $semester['id'] ? 'selected' : '' ?>>
                                                        <?= esc($semester['semester_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Semester when course is offered</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Details -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-cogs me-2"></i>Course Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="course_type" class="form-label fw-semibold">Course Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="course_type" name="course_type" required>
                                                <option value="">-- Select Course Type --</option>
                                                <?php foreach ($courseTypes as $key => $label): ?>
                                                    <option value="<?= $key ?>" <?= old('course_type') == $key ? 'selected' : '' ?>>
                                                        <?= esc($label) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Classification of the course</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="units" class="form-label fw-semibold">Units <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="units" name="units" 
                                                   value="<?= old('units', 3) ?>" required min="1" max="12">
                                            <div class="form-text">Credit units for this course (1-12)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    💾 Add to Curriculum
                                </button>
                                <a href="<?= base_url('admin/manage_curriculum' . ($selectedProgramId ? '?program_id=' . $selectedProgramId : '')) ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Curriculum Form (shown when editing) -->
        <?php if ($showEditForm && $editCurriculum): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-dark border-0">
                        <h5 class="mb-0">✏️ Edit Curriculum Entry</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_curriculum?action=edit&id=' . $editCurriculum['id']) ?>">
                            <?= csrf_field() ?>
                            
                            <!-- Program & Course Selection -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-graduation-cap me-2"></i>Program & Course Selection
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_program_id" class="form-label fw-semibold">Program <span class="text-danger">*</span></label>
                                            <select class="form-select" id="edit_program_id" name="program_id" required>
                                                <option value="">-- Select Program --</option>
                                                <?php foreach ($programs as $program): ?>
                                                    <option value="<?= $program['id'] ?>" <?= old('program_id', $editCurriculum['program_id']) == $program['id'] ? 'selected' : '' ?>>
                                                        <?= esc($program['program_code']) ?> - <?= esc($program['program_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_course_id" class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                                            <select class="form-select" id="edit_course_id" name="course_id" required>
                                                <option value="">-- Select Course --</option>
                                                <?php foreach ($courses as $course): ?>
                                                    <option value="<?= $course['id'] ?>" <?= old('course_id', $editCurriculum['course_id']) == $course['id'] ? 'selected' : '' ?>>
                                                        <?= esc($course['course_code']) ?> - <?= esc($course['title']) ?> (<?= esc($course['credits']) ?> units)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-calendar-alt me-2"></i>Schedule Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_year_level_id" class="form-label fw-semibold">Year Level <span class="text-danger">*</span></label>                                            <select class="form-select" id="edit_year_level_id" name="year_level_id" required>
                                                <option value="">-- Select Year Level --</option>
                                                <?php foreach ($yearLevels as $yearLevel): ?>
                                                    <option value="<?= $yearLevel['id'] ?>" <?= old('year_level_id', $editCurriculum['year_level_id']) == $yearLevel['id'] ? 'selected' : '' ?>>
                                                        <?= esc($yearLevel['year_level_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_semester_id" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                            <select class="form-select" id="edit_semester_id" name="semester_id" required>
                                                <option value="">-- Select Semester --</option>
                                                <?php foreach ($semesters as $semester): ?>
                                                    <option value="<?= $semester['id'] ?>" <?= old('semester_id', $editCurriculum['semester_id']) == $semester['id'] ? 'selected' : '' ?>>
                                                        <?= esc($semester['semester_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Details -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-cogs me-2"></i>Course Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_course_type" class="form-label fw-semibold">Course Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="edit_course_type" name="course_type" required>
                                                <option value="">-- Select Course Type --</option>
                                                <?php foreach ($courseTypes as $key => $label): ?>
                                                    <option value="<?= $key ?>" <?= old('course_type', $editCurriculum['course_type']) == $key ? 'selected' : '' ?>>
                                                        <?= esc($label) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_units" class="form-label fw-semibold">Units <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="edit_units" name="units" 
                                                   value="<?= old('units', $editCurriculum['units']) ?>" required min="1" max="12">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning text-dark">
                                    💾 Update Curriculum Entry
                                </button>
                                <a href="<?= base_url('admin/manage_curriculum' . ($selectedProgramId ? '?program_id=' . $selectedProgramId : '')) ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Curriculum List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 fw-bold">📋 Curriculum List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Program</th>
                                        <th>Course</th>
                                        <th>Year Level</th>
                                        <th>Semester</th>
                                        <th>Type</th>
                                        <th class="text-center">Units</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($curriculum)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <div class="display-1">📭</div>
                                                <p class="mb-0">No curriculum entries found. Add courses to get started!</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($curriculum as $entry): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary"><?= esc($entry['program_code']) ?></span>
                                                    <br><small class="text-muted"><?= esc($entry['program_name']) ?></small>
                                                </td>
                                                <td>
                                                    <strong><?= esc($entry['course_code']) ?></strong>
                                                    <br><small class="text-muted"><?= esc($entry['course_title']) ?></small>
                                                </td>
                                                <td><?= esc($entry['year_level_name']) ?></td>
                                                <td><?= esc($entry['semester_name']) ?></td>
                                                <td>
                                                    <?php
                                                    $typeColors = [
                                                        'major' => 'danger',
                                                        'minor' => 'warning',
                                                        'general_education' => 'info'
                                                    ];
                                                    $typeLabels = [
                                                        'major' => 'Major',
                                                        'minor' => 'Minor',
                                                        'general_education' => 'GE'
                                                    ];
                                                    $color = $typeColors[$entry['course_type']] ?? 'secondary';
                                                    $label = $typeLabels[$entry['course_type']] ?? $entry['course_type'];
                                                    ?>
                                                    <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-dark"><?= $entry['units'] ?></span>
                                                </td>
                                                <td>
                                                    <form method="post" action="<?= base_url('admin/manage_curriculum') ?>" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="curriculum_id" value="<?= $entry['id'] ?>">
                                                        <input type="hidden" name="program_id" value="<?= $selectedProgramId ?>">
                                                        <button type="submit" class="btn btn-sm <?= $entry['is_active'] ? 'btn-success' : 'btn-secondary' ?>" 
                                                                onclick="return confirm('Toggle status?')">
                                                            <?= $entry['is_active'] ? '✅ Active' : '❌ Inactive' ?>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?= base_url('admin/manage_curriculum?action=edit&id=' . $entry['id']) ?>" 
                                                           class="btn btn-outline-warning" title="Edit">
                                                            ✏️
                                                        </a>
                                                        <form method="post" action="<?= base_url('admin/manage_curriculum') ?>" class="d-inline">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="curriculum_id" value="<?= $entry['id'] ?>">
                                                            <input type="hidden" name="program_id" value="<?= $selectedProgramId ?>">
                                                            <button type="submit" class="btn btn-outline-danger" title="Delete"
                                                                    onclick="return confirm('Are you sure you want to remove this course from the curriculum?')">
                                                                🗑️
                                                            </button>
                                                        </form>
                                                    </div>
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

        <!-- Course Type Reference -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">📖 Course Type Reference:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-danger">Major Course</span>
                            <span class="badge bg-warning text-dark">Minor Course</span>
                            <span class="badge bg-info">General Education (GE)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-populate units when course is selected
    $('#course_id, #edit_course_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const text = selectedOption.text();
        const match = text.match(/\((\d+)\s*units\)/);
        if (match) {
            const targetId = $(this).attr('id') === 'course_id' ? '#units' : '#edit_units';
            $(targetId).val(match[1]);
        }
    });
});
</script>
