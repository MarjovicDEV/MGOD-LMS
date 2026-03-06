<?= $this->include('templates/header') ?>

<!-- Manage Grade Components View - Admin only functionality for grade component management -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold">🎯 Manage Grade Components</h3>
                                <p class="mb-0 opacity-75">Configure grade components for course offerings</p>
                            </div>
                            <div class="text-end">
                                <i class="bi bi-mortarboard-fill" style="font-size: 3rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grade Component Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🎯</div>
                    <div class="display-5 fw-bold"><?= count($gradeComponents) ?></div>
                    <div class="fw-semibold">Total Components</div>
                    <small class="opacity-75">In the system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($gradeComponents, fn($c) => $c['is_active'] == 1)) ?></div>
                    <div class="fw-semibold">Active</div>
                    <small class="opacity-75">Currently active</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">❌</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($gradeComponents, fn($c) => $c['is_active'] == 0)) ?></div>
                    <div class="fw-semibold">Inactive</div>
                    <small class="opacity-75">Deactivated</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📚</div>
                    <div class="display-5 fw-bold"><?= count(array_unique(array_column($gradeComponents, 'course_offering_id'))) ?></div>
                    <div class="fw-semibold">Offerings</div>
                    <small class="opacity-75">With components</small>
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

        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Warning!</strong> <?= session()->getFlashdata('warning') ?>
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
                            <h5 class="mb-0 fw-bold text-dark">📝 Quick Actions</h5>
                            <a href="<?= base_url('admin/manage_grade_components?action=create') ?>" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Create New Grade Component
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Grade Component Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Create New Grade Component</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_grade_components?action=create') ?>">
                            <div class="row">
                                <!-- Course Offering -->
                                <div class="col-md-6 mb-3">
                                    <label for="course_offering_id" class="form-label fw-semibold">Course Offering <span class="text-danger">*</span></label>
                                    <select class="form-select" id="course_offering_id" name="course_offering_id" required>
                                        <option value="">-- Select Course Offering --</option>
                                        <?php foreach ($courseOfferings as $offering): ?>
                                            <?php 
                                                $termInfo = $offering['year_name'] . ' - ' . $offering['semester_name'];
                                                if (!empty($offering['term_name'])) {
                                                    $termInfo .= ' (' . $offering['term_name'] . ')';
                                                }
                                            ?>
                                            <option value="<?= $offering['id'] ?>" <?= old('course_offering_id') == $offering['id'] ? 'selected' : '' ?>>
                                                <?= esc($offering['course_code']) ?> - <?= esc($offering['course_name']) ?> 
                                                [<?= esc($termInfo) ?>]
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Select the course offering for this grade component</small>
                                </div>

                                <!-- Assignment Type -->
                                <div class="col-md-6 mb-3">
                                    <label for="assignment_type_id" class="form-label fw-semibold">Assignment Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="assignment_type_id" name="assignment_type_id" required>
                                        <option value="">-- Select Assignment Type --</option>
                                        <?php foreach ($assignmentTypes as $type): ?>
                                            <option value="<?= $type['id'] ?>" <?= old('assignment_type_id') == $type['id'] ? 'selected' : '' ?>>
                                                <?= esc($type['type_name']) ?> (<?= esc($type['type_code']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Type of assignment (Quiz, Exam, etc.)</small>
                                </div>
                            </div>

                            <div class="row">                                <!-- Grading Period -->
                                <div class="col-md-6 mb-3">
                                    <label for="grading_period_id" class="form-label fw-semibold">Grading Period</label>
                                    <select class="form-select" id="grading_period_id" name="grading_period_id">
                                        <option value="">-- All Periods / Not Specific --</option>
                                        <?php 
                                        // Group periods by term for better display
                                        $periodsByTerm = [];
                                        foreach ($gradingPeriods as $period) {
                                            $termKey = $period['term_id'] ?? 'unknown';
                                            if (!isset($periodsByTerm[$termKey])) {
                                                $periodsByTerm[$termKey] = [];
                                            }
                                            $periodsByTerm[$termKey][] = $period;
                                        }
                                        
                                        foreach ($periodsByTerm as $periods):
                                            $firstPeriod = $periods[0];
                                            $termLabel = '';
                                            if (isset($firstPeriod['year_name']) && isset($firstPeriod['semester_name'])) {
                                                $termLabel = $firstPeriod['year_name'] . ' - ' . $firstPeriod['semester_name'];
                                                if (!empty($firstPeriod['term_name'])) {
                                                    $termLabel .= ' (' . $firstPeriod['term_name'] . ')';
                                                }
                                            } else {
                                                $termLabel = 'Unknown Term';
                                            }
                                        ?>
                                            <optgroup label="<?= esc($termLabel) ?>">
                                                <?php foreach ($periods as $period): ?>
                                                    <option value="<?= $period['id'] ?>" <?= old('grading_period_id') == $period['id'] ? 'selected' : '' ?>>
                                                        <?= esc($period['period_name']) ?> (<?= number_format($period['weight_percentage'], 2) ?>%)
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Optional: Select grading period by term, or leave blank for all periods</small>
                                </div>

                                <!-- Weight Percentage -->
                                <div class="col-md-6 mb-3">
                                    <label for="weight_percentage" class="form-label fw-semibold">Weight Percentage (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="weight_percentage" name="weight_percentage" 
                                           min="0.01" max="100" step="0.01" 
                                           value="<?= old('weight_percentage') ?>" 
                                           placeholder="e.g., 25.00" required>
                                    <small class="text-muted">Weight in final grade (0.01 - 100%)</small>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Create Grade Component
                                </button>
                                <a href="<?= base_url('admin/manage_grade_components') ?>" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Grade Component Form (shown when editing) -->
        <?php if ($showEditForm && isset($editGradeComponent)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-white border-0">
                        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Grade Component #<?= $editGradeComponent['id'] ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_grade_components?action=edit&id=' . $editGradeComponent['id']) ?>">
                            <div class="row">
                                <!-- Course Offering -->
                                <div class="col-md-6 mb-3">
                                    <label for="edit_course_offering_id" class="form-label fw-semibold">Course Offering <span class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_course_offering_id" name="course_offering_id" required>
                                        <option value="">-- Select Course Offering --</option>
                                        <?php foreach ($courseOfferings as $offering): ?>
                                            <?php 
                                                $termInfo = $offering['year_name'] . ' - ' . $offering['semester_name'];
                                                if (!empty($offering['term_name'])) {
                                                    $termInfo .= ' (' . $offering['term_name'] . ')';
                                                }
                                            ?>
                                            <option value="<?= $offering['id'] ?>" <?= $editGradeComponent['course_offering_id'] == $offering['id'] ? 'selected' : '' ?>>
                                                <?= esc($offering['course_code']) ?> - <?= esc($offering['course_name']) ?> 
                                                [<?= esc($termInfo) ?>]
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Select the course offering for this grade component</small>
                                </div>

                                <!-- Assignment Type -->
                                <div class="col-md-6 mb-3">
                                    <label for="edit_assignment_type_id" class="form-label fw-semibold">Assignment Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_assignment_type_id" name="assignment_type_id" required>
                                        <option value="">-- Select Assignment Type --</option>
                                        <?php foreach ($assignmentTypes as $type): ?>
                                            <option value="<?= $type['id'] ?>" <?= $editGradeComponent['assignment_type_id'] == $type['id'] ? 'selected' : '' ?>>
                                                <?= esc($type['type_name']) ?> (<?= esc($type['type_code']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Type of assignment (Quiz, Exam, etc.)</small>
                                </div>
                            </div>

                            <div class="row">                                <!-- Grading Period -->
                                <div class="col-md-6 mb-3">
                                    <label for="edit_grading_period_id" class="form-label fw-semibold">Grading Period</label>
                                    <select class="form-select" id="edit_grading_period_id" name="grading_period_id">
                                        <option value="">-- All Periods / Not Specific --</option>
                                        <?php 
                                        // Group periods by term for better display
                                        $periodsByTerm = [];
                                        foreach ($gradingPeriods as $period) {
                                            $termKey = $period['term_id'] ?? 'unknown';
                                            if (!isset($periodsByTerm[$termKey])) {
                                                $periodsByTerm[$termKey] = [];
                                            }
                                            $periodsByTerm[$termKey][] = $period;
                                        }
                                        
                                        foreach ($periodsByTerm as $periods):
                                            $firstPeriod = $periods[0];
                                            $termLabel = '';
                                            if (isset($firstPeriod['year_name']) && isset($firstPeriod['semester_name'])) {
                                                $termLabel = $firstPeriod['year_name'] . ' - ' . $firstPeriod['semester_name'];
                                                if (!empty($firstPeriod['term_name'])) {
                                                    $termLabel .= ' (' . $firstPeriod['term_name'] . ')';
                                                }
                                            } else {
                                                $termLabel = 'Unknown Term';
                                            }
                                        ?>
                                            <optgroup label="<?= esc($termLabel) ?>">
                                                <?php foreach ($periods as $period): ?>
                                                    <option value="<?= $period['id'] ?>" <?= $editGradeComponent['grading_period_id'] == $period['id'] ? 'selected' : '' ?>>
                                                        <?= esc($period['period_name']) ?> (<?= number_format($period['weight_percentage'], 2) ?>%)
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Optional: Select grading period by term, or leave blank for all periods</small>
                                </div>

                                <!-- Weight Percentage -->
                                <div class="col-md-6 mb-3">
                                    <label for="edit_weight_percentage" class="form-label fw-semibold">Weight Percentage (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_weight_percentage" name="weight_percentage" 
                                           min="0.01" max="100" step="0.01" 
                                           value="<?= esc($editGradeComponent['weight_percentage']) ?>" 
                                           placeholder="e.g., 25.00" required>
                                    <small class="text-muted">Weight in final grade (0.01 - 100%)</small>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning text-white">
                                    <i class="bi bi-save"></i> Update Grade Component
                                </button>
                                <a href="<?= base_url('admin/manage_grade_components') ?>" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Grade Components List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">📋 All Grade Components</h5>
                                <small class="text-muted">Manage all grade components in the system</small>
                            </div>
                            <div class="text-muted small">
                                Total: <?= count($gradeComponents) ?> components
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">ID</th>
                                        <th>Course</th>
                                        <th>Term</th>
                                        <th>Assignment Type</th>
                                        <th>Grading Period</th>
                                        <th class="text-center">Weight %</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($gradeComponents)): ?>
                                        <?php foreach ($gradeComponents as $component): ?>
                                            <?php 
                                                $rowClass = $component['is_active'] == 0 ? 'table-secondary opacity-50' : '';
                                            ?>                                            <tr class="<?= $rowClass ?>">
                                                <td class="text-center fw-bold"><?= $component['id'] ?></td>
                                                <td>
                                                    <div class="fw-semibold text-primary"><?= esc($component['course_code']) ?></div>
                                                    <small class="text-muted"><?= esc($component['course_name']) ?></small>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?= esc($component['year_name']) ?><br>
                                                        <?= esc($component['semester_name']) ?>
                                                        <?php if (!empty($component['term_name'])): ?>
                                                            <br><span class="badge bg-light text-dark"><?= esc($component['term_name']) ?></span>
                                                        <?php endif; ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?= esc($component['type_name']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($component['period_name']): ?>
                                                        <span class="badge bg-secondary"><?= esc($component['period_name']) ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark">All Periods</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary"><?= number_format($component['weight_percentage'], 2) ?>%</span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($component['is_active'] == 1): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?= base_url('admin/manage_grade_components?action=edit&id=' . $component['id']) ?>" 
                                                           class="btn btn-outline-warning" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="<?= base_url('admin/manage_grade_components?action=toggle_status&id=' . $component['id']) ?>" 
                                                           class="btn btn-outline-<?= $component['is_active'] == 1 ? 'secondary' : 'success' ?>" 
                                                           title="<?= $component['is_active'] == 1 ? 'Deactivate' : 'Activate' ?>"
                                                           onclick="return confirm('Are you sure you want to <?= $component['is_active'] == 1 ? 'deactivate' : 'activate' ?> this grade component?')">
                                                            <i class="bi bi-<?= $component['is_active'] == 1 ? 'toggle-off' : 'toggle-on' ?>"></i>
                                                        </a>
                                                        <a href="<?= base_url('admin/manage_grade_components?action=delete&id=' . $component['id']) ?>" 
                                                           class="btn btn-outline-danger" title="Delete"
                                                           onclick="return confirm('Are you sure you want to delete this grade component? This action cannot be undone.')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-5">
                                                <div class="mb-3">
                                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                                </div>
                                                <h5>No Grade Components Found</h5>
                                                <p class="mb-3">Create your first grade component to get started.</p>
                                                <a href="<?= base_url('admin/manage_grade_components?action=create') ?>" class="btn btn-success">
                                                    <i class="bi bi-plus-circle"></i> Create Grade Component
                                                </a>
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

<!-- JavaScript for Enhanced Validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Weight percentage validation
    const weightFields = document.querySelectorAll('input[name="weight_percentage"]');
    
    weightFields.forEach(function(field) {
        // Validate on input
        field.addEventListener('input', function(e) {
            const value = parseFloat(e.target.value);
            
            // Enforce maximum value by capping at 100
            if (value > 100) {
                e.target.value = 100;
            }
            
            // Visual feedback
            const finalValue = parseFloat(e.target.value);
            if (e.target.value === '' || (finalValue > 0 && finalValue <= 100)) {
                e.target.setCustomValidity('');
                e.target.classList.remove('is-invalid');
                if (e.target.value !== '') {
                    e.target.classList.add('is-valid');
                } else {
                    e.target.classList.remove('is-valid');
                }
            } else {
                e.target.setCustomValidity('Weight percentage must be between 0.01 and 100');
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            }
        });
        
        // Validate on blur (when user leaves the field)
        field.addEventListener('blur', function(e) {
            const value = parseFloat(e.target.value);
            
            if (e.target.value !== '' && (isNaN(value) || value <= 0 || value > 100)) {
                e.target.setCustomValidity('Weight percentage must be between 0.01 and 100');
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            }
        });
    });
    
    // Form submission validation
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const weightField = form.querySelector('input[name="weight_percentage"]');
            if (weightField) {
                const value = parseFloat(weightField.value);
                
                if (isNaN(value) || value <= 0 || value > 100) {
                    e.preventDefault();
                    weightField.setCustomValidity('Weight percentage must be between 0.01 and 100');
                    weightField.classList.add('is-invalid');
                    weightField.focus();
                    
                    // Show alert to user
                    alert('Please enter a valid weight percentage between 0.01 and 100.');
                    return false;
                }
            }
        });
    });
});
</script>
