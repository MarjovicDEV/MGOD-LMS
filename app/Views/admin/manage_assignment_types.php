<?= $this->include('templates/header') ?>

<!-- Manage Assignment Types View - Admin only functionality for assignment type management -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">📝 Manage Assignment Types</h2>
                                <p class="mb-0 opacity-75">Create, edit, and manage assignment types in the system</p>
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

        <!-- Assignment Type Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📝</div>
                    <div class="display-5 fw-bold"><?= count($assignmentTypes) ?></div>
                    <div class="fw-semibold">Total Types</div>
                    <small class="opacity-75">In the system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($assignmentTypes, fn($t) => $t['is_active'] == 1)) ?></div>
                    <div class="fw-semibold">Active</div>
                    <small class="opacity-75">Currently active</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">❌</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($assignmentTypes, fn($t) => $t['is_active'] == 0)) ?></div>
                    <div class="fw-semibold">Inactive</div>
                    <small class="opacity-75">Deactivated</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">⚖️</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($assignmentTypes, fn($t) => !empty($t['default_weight']))) ?></div>
                    <div class="fw-semibold">With Weights</div>
                    <small class="opacity-75">Have default weights</small>
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
                            <h5 class="mb-0 fw-bold text-dark">⚡ Assignment Type Management</h5>
                            <a href="<?= base_url('admin/manage_assignment_types?action=create') ?>" class="btn btn-success">
                                ➕ Create New Type
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Assignment Type Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Create New Assignment Type</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_assignment_types?action=create') ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="type_code" class="form-label fw-semibold">Type Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase" id="type_code" name="type_code" 
                                               value="<?= old('type_code') ?>" required 
                                               pattern="[A-Z0-9_]+" 
                                               title="Type code must contain only uppercase letters, numbers, and underscores"
                                               minlength="2" maxlength="20"
                                               placeholder="e.g., AT-101, QUIZ, EXAM_TYPE">
                                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Format: XX-000 (e.g., AT-101) or UPPERCASE_CODE (e.g., QUIZ, EXAM_TYPE)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="type_name" class="form-label fw-semibold">Type Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="type_name" name="type_name" 
                                               value="<?= old('type_name') ?>" required 
                                               minlength="2" maxlength="50"
                                               placeholder="e.g., Quiz, Midterm Exam">
                                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Letters (including Ñ/ñ) and spaces only. No numbers or special characters.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="default_weight" class="form-label fw-semibold">Default Weight (%) <small class="text-muted">(optional)</small></label>
                                        <input type="number" class="form-control" id="default_weight" name="default_weight" 
                                               value="<?= old('default_weight') ?>" 
                                               min="0" max="100" step="0.01"
                                               placeholder="e.g., 20">
                                        <small class="text-muted">Default weight percentage (0-100)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label fw-semibold">Description <small class="text-muted">(optional)</small></label>
                                        <textarea class="form-control" id="description" name="description" rows="3" 
                                                  maxlength="255" placeholder="Brief description of this assignment type"><?= old('description') ?></textarea>
                                        <small class="text-muted">Brief description (max 255 characters)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    ✅ Create Assignment Type
                                </button>
                                <a href="<?= base_url('admin/manage_assignment_types') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Assignment Type Form (shown when editing) -->
        <?php if ($showEditForm && isset($editAssignmentType)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-white border-0">
                        <h5 class="mb-0">✏️ Edit Assignment Type: <?= esc($editAssignmentType['type_name']) ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_assignment_types?action=edit&id=' . $editAssignmentType['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_type_code" class="form-label fw-semibold">Type Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase" id="edit_type_code" name="type_code" 
                                               value="<?= old('type_code', $editAssignmentType['type_code']) ?>" required 
                                               pattern="[A-Z0-9_]+" 
                                               title="Type code must contain only uppercase letters, numbers, and underscores"
                                               minlength="2" maxlength="20">
                                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Format: XX-000 (e.g., AT-101) or UPPERCASE_CODE (e.g., QUIZ, EXAM_TYPE)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_type_name" class="form-label fw-semibold">Type Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_type_name" name="type_name" 
                                               value="<?= old('type_name', $editAssignmentType['type_name']) ?>" required 
                                               minlength="2" maxlength="50">
                                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Letters (including Ñ/ñ) and spaces only. No numbers or special characters.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_default_weight" class="form-label fw-semibold">Default Weight (%) <small class="text-muted">(optional)</small></label>
                                        <input type="number" class="form-control" id="edit_default_weight" name="default_weight" 
                                               value="<?= old('default_weight', $editAssignmentType['default_weight']) ?>" 
                                               min="0" max="100" step="0.01">
                                        <small class="text-muted">Default weight percentage (0-100)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="edit_description" class="form-label fw-semibold">Description <small class="text-muted">(optional)</small></label>
                                        <textarea class="form-control" id="edit_description" name="description" rows="3" 
                                                  maxlength="255"><?= old('description', $editAssignmentType['description']) ?></textarea>
                                        <small class="text-muted">Brief description (max 255 characters)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    💾 Update Assignment Type
                                </button>
                                <a href="<?= base_url('admin/manage_assignment_types') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Assignment Types List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">📋 All Assignment Types</h5>
                                <small class="text-muted">Manage all assignment types in the system</small>
                            </div>
                            <div class="text-muted small">
                                Total: <?= count($assignmentTypes) ?> types
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold border-0 text-center">ID</th>
                                        <th class="fw-semibold border-0">Type Code</th>
                                        <th class="fw-semibold border-0">Type Name</th>
                                        <th class="fw-semibold border-0">Description</th>
                                        <th class="fw-semibold border-0 text-center">Default Weight</th>
                                        <th class="fw-semibold border-0 text-center">Status</th>
                                        <th class="fw-semibold border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($assignmentTypes)): ?>
                                        <?php foreach ($assignmentTypes as $type): ?>
                                        <?php 
                                        $isInactive = ($type['is_active'] == 0);
                                        $rowClass = $isInactive ? 'border-bottom table-secondary opacity-75' : 'border-bottom';
                                        ?>
                                        <tr class="<?= $rowClass ?>">
                                            <td class="text-center">
                                                <span class="badge bg-secondary rounded-pill px-2 py-1"><?= $type['id'] ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary px-3 py-2">
                                                    <?= esc($type['type_code']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-dark"><?= esc($type['type_name']) ?></strong>
                                                <?php if ($isInactive): ?>
                                                    <span class="badge bg-danger ms-2 small">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted">
                                                <?php if (!empty($type['description'])): ?>
                                                    <?= esc(substr($type['description'], 0, 50)) . (strlen($type['description']) > 50 ? '...' : '') ?>
                                                <?php else: ?>
                                                    <em class="text-muted">No description</em>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($type['default_weight'])): ?>
                                                    <span class="badge bg-info px-3 py-2">
                                                        <?= number_format($type['default_weight'], 2) ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($isInactive): ?>
                                                    <span class="badge bg-danger rounded-pill px-3 py-2">
                                                        ❌ Inactive
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                                        ✅ Active
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <!-- Edit Button -->
                                                    <a href="<?= base_url('admin/manage_assignment_types?action=edit&id=' . $type['id']) ?>" 
                                                       class="btn btn-outline-warning" 
                                                       title="Edit Assignment Type">
                                                        ✏️
                                                    </a>
                                                    
                                                    <!-- Toggle Status Button -->
                                                    <?php 
                                                    $toggleText = $isInactive ? 'Activate' : 'Deactivate';
                                                    $toggleIcon = $isInactive ? '✅' : '❌';
                                                    $toggleClass = $isInactive ? 'btn-outline-success' : 'btn-outline-warning';
                                                    ?>
                                                    <a href="<?= base_url('admin/manage_assignment_types?action=toggle_status&id=' . $type['id']) ?>" 
                                                       class="btn <?= $toggleClass ?>" 
                                                       onclick="return confirm('Are you sure you want to <?= strtolower($toggleText) ?> this assignment type?')"
                                                       title="<?= $toggleText ?> Assignment Type">
                                                        <?= $toggleIcon ?>
                                                    </a>
                                                    
                                                    <!-- Delete Button -->
                                                    <a href="<?= base_url('admin/manage_assignment_types?action=delete&id=' . $type['id']) ?>" 
                                                       class="btn btn-outline-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this assignment type?\n\nType: <?= esc($type['type_name']) ?>\n\nThis action cannot be undone!')"
                                                       title="Delete Assignment Type">
                                                        🗑️
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <div class="mb-3">
                                                    <span style="font-size: 3rem; opacity: 0.3;">📝</span>
                                                </div>
                                                <p class="mb-0">No assignment types found in the system.</p>
                                                <p class="small">Click "Create New Type" to add your first assignment type.</p>
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
    // Type code validation - force uppercase and valid characters
    const typeCodeFields = document.querySelectorAll('input[name="type_code"]');
    
    typeCodeFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            // Convert to uppercase
            e.target.value = e.target.value.toUpperCase();
            
            // Remove invalid characters (only allow A-Z, 0-9, and underscore)
            e.target.value = e.target.value.replace(/[^A-Z0-9_]/g, '');
            
            // Visual feedback
            const validPattern = /^[A-Z0-9_]{2,}$/;
            if (validPattern.test(e.target.value)) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else if (e.target.value.length > 0) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
            }
        });
    });
    
    // Type name validation
    const typeNameFields = document.querySelectorAll('input[name="type_name"]');
    
    typeNameFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = e.target.value;
            
            // Visual feedback
            if (value.length >= 2) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else if (value.length > 0) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
            }
        });
    });
      // Default weight validation
    const weightFields = document.querySelectorAll('input[name="default_weight"]');
    
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
            if (e.target.value === '' || (finalValue >= 0 && finalValue <= 100)) {
                e.target.setCustomValidity('');
                e.target.classList.remove('is-invalid');
                if (e.target.value !== '') {
                    e.target.classList.add('is-valid');
                } else {
                    e.target.classList.remove('is-valid');
                }
            } else {
                e.target.setCustomValidity('Default weight must be between 0 and 100');
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            }
        });
        
        // Validate on blur (when user leaves the field)
        field.addEventListener('blur', function(e) {
            const value = parseFloat(e.target.value);
            
            if (e.target.value !== '' && (isNaN(value) || value < 0 || value > 100)) {
                e.target.setCustomValidity('Default weight must be between 0 and 100');
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            }
        });
    });
    
    // Form submission validation
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const weightField = form.querySelector('input[name="default_weight"]');
            if (weightField && weightField.value !== '') {
                const value = parseFloat(weightField.value);
                
                if (isNaN(value) || value < 0 || value > 100) {
                    e.preventDefault();
                    weightField.setCustomValidity('Default weight must be between 0 and 100');
                    weightField.classList.add('is-invalid');
                    weightField.focus();
                    
                    // Show alert to user
                    alert('Please enter a valid default weight between 0 and 100.');
                    return false;
                }
            }
        });
    });
});
</script>
