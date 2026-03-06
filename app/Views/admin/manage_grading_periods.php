<?= $this->include('templates/header') ?>

<!-- Manage Grading Periods View - Admin only functionality for grading period management -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">📊 Manage Grading Periods</h2>
                                <p class="mb-0 opacity-75">Create, edit, and manage grading periods for academic terms</p>
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

        <!-- Grading Period Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📊</div>
                    <div class="display-5 fw-bold"><?= count($gradingPeriods) ?></div>
                    <div class="fw-semibold">Total Periods</div>
                    <small class="opacity-75">In the system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($gradingPeriods, fn($p) => $p['is_active'] == 1)) ?></div>
                    <div class="fw-semibold">Active</div>
                    <small class="opacity-75">Currently active</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">❌</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($gradingPeriods, fn($p) => $p['is_active'] == 0)) ?></div>
                    <div class="fw-semibold">Inactive</div>
                    <small class="opacity-75">Deactivated</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📅</div>
                    <div class="display-5 fw-bold"><?= count(array_unique(array_column($gradingPeriods, 'term_id'))) ?></div>
                    <div class="fw-semibold">Terms</div>
                    <small class="opacity-75">With periods</small>
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
                            <h5 class="mb-0 fw-bold text-dark">⚡ Grading Period Management</h5>
                            <a href="<?= base_url('admin/manage_grading_periods?action=create') ?>" class="btn btn-success">
                                ➕ Create New Period
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Grading Period Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Create New Grading Period</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_grading_periods?action=create') ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="term_id" class="form-label fw-semibold">Academic Term <span class="text-danger">*</span></label>
                                        <select class="form-select" id="term_id" name="term_id" required>
                                            <option value="">Select Term</option>
                                            <?php if (isset($terms) && !empty($terms)): ?>
                                                <?php foreach ($terms as $term): ?>
                                                    <option value="<?= $term['id'] ?>" <?= old('term_id') == $term['id'] ? 'selected' : '' ?>>
                                                        <?= esc($term['year_name'] ?? '') ?> - <?= esc($term['semester_name'] ?? '') ?>
                                                        <?php if (!empty($term['term_name'])): ?>
                                                            (<?= esc($term['term_name']) ?>)
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <small class="text-muted">Select the academic term for this grading period</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="period_name" class="form-label fw-semibold">Period Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="period_name" name="period_name" 
                                               value="<?= old('period_name') ?>" required 
                                               minlength="2" maxlength="50"
                                               placeholder="e.g., Prelim, Midterm, Finals">
                                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Letters (including Ñ/ñ), numbers, and spaces only. No special characters.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="period_order" class="form-label fw-semibold">Period Order <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="period_order" name="period_order" 
                                               value="<?= old('period_order', '1') ?>" required 
                                               min="1" max="10"
                                               placeholder="1">
                                        <small class="text-muted">Order of this period (1, 2, 3, etc.)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="weight_percentage" class="form-label fw-semibold">Weight (%) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="weight_percentage" name="weight_percentage" 
                                               value="<?= old('weight_percentage') ?>" required 
                                               min="0.01" max="100" step="0.01"
                                               placeholder="33.33">
                                        <small class="text-muted">Weight percentage (should total 100% per term)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Total Weight Check</label>
                                        <div class="alert alert-info mb-0 py-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <span id="weightWarning">Enter weight to check</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label fw-semibold">Start Date <small class="text-muted">(optional)</small></label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="<?= old('start_date') ?>">
                                        <small class="text-muted">When this grading period begins</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label fw-semibold">End Date <small class="text-muted">(optional)</small></label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                               value="<?= old('end_date') ?>">
                                        <small class="text-muted">When this grading period ends</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    ✅ Create Grading Period
                                </button>
                                <a href="<?= base_url('admin/manage_grading_periods') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Grading Period Form (shown when editing) -->
        <?php if ($showEditForm && isset($editGradingPeriod)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-white border-0">
                        <h5 class="mb-0">✏️ Edit Grading Period: <?= esc($editGradingPeriod['period_name']) ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_grading_periods?action=edit&id=' . $editGradingPeriod['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_term_id" class="form-label fw-semibold">Academic Term <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_term_id" name="term_id" required>
                                            <option value="">Select Term</option>
                                            <?php if (isset($terms) && !empty($terms)): ?>
                                                <?php foreach ($terms as $term): ?>
                                                    <option value="<?= $term['id'] ?>" <?= old('term_id', $editGradingPeriod['term_id']) == $term['id'] ? 'selected' : '' ?>>
                                                        <?= esc($term['year_name'] ?? '') ?> - <?= esc($term['semester_name'] ?? '') ?>
                                                        <?php if (!empty($term['term_name'])): ?>
                                                            (<?= esc($term['term_name']) ?>)
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <small class="text-muted">Select the academic term for this grading period</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_period_name" class="form-label fw-semibold">Period Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_period_name" name="period_name" 
                                               value="<?= old('period_name', $editGradingPeriod['period_name']) ?>" required 
                                               minlength="2" maxlength="50">
                                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Letters (including Ñ/ñ), numbers, and spaces only. No special characters.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_period_order" class="form-label fw-semibold">Period Order <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="edit_period_order" name="period_order" 
                                               value="<?= old('period_order', $editGradingPeriod['period_order']) ?>" required 
                                               min="1" max="10">
                                        <small class="text-muted">Order of this period (1, 2, 3, etc.)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_weight_percentage" class="form-label fw-semibold">Weight (%) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="edit_weight_percentage" name="weight_percentage" 
                                               value="<?= old('weight_percentage', $editGradingPeriod['weight_percentage']) ?>" required 
                                               min="0.01" max="100" step="0.01">
                                        <small class="text-muted">Weight percentage (should total 100% per term)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Total Weight Check</label>
                                        <div class="alert alert-info mb-0 py-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <span id="editWeightWarning">Checking...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_start_date" class="form-label fw-semibold">Start Date <small class="text-muted">(optional)</small></label>
                                        <input type="date" class="form-control" id="edit_start_date" name="start_date" 
                                               value="<?= old('start_date', $editGradingPeriod['start_date']) ?>">
                                        <small class="text-muted">When this grading period begins</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_end_date" class="form-label fw-semibold">End Date <small class="text-muted">(optional)</small></label>
                                        <input type="date" class="form-control" id="edit_end_date" name="end_date" 
                                               value="<?= old('end_date', $editGradingPeriod['end_date']) ?>">
                                        <small class="text-muted">When this grading period ends</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    💾 Update Grading Period
                                </button>
                                <a href="<?= base_url('admin/manage_grading_periods') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Grading Periods List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">📋 All Grading Periods</h5>
                                <small class="text-muted">Manage all grading periods in the system</small>
                            </div>
                            <div class="text-muted small">
                                Total: <?= count($gradingPeriods) ?> periods
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold border-0 text-center">Order</th>
                                        <th class="fw-semibold border-0">Period Name</th>
                                        <th class="fw-semibold border-0">Academic Term</th>
                                        <th class="fw-semibold border-0 text-center">Weight</th>
                                        <th class="fw-semibold border-0 text-center">Dates</th>
                                        <th class="fw-semibold border-0 text-center">Status</th>
                                        <th class="fw-semibold border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($gradingPeriods)): ?>
                                        <?php 
                                        $currentTermId = null;
                                        $termTotalWeight = 0;
                                        ?>
                                        <?php foreach ($gradingPeriods as $index => $period): ?>
                                        <?php 
                                        // Track weight per term
                                        if ($currentTermId !== $period['term_id']) {
                                            if ($currentTermId !== null && $termTotalWeight > 0) {
                                                // Show term total row
                                                $weightClass = abs($termTotalWeight - 100) < 0.01 ? 'success' : 'danger';
                                                ?>
                                                <tr class="table-<?= $weightClass ?> fw-bold">
                                                    <td colspan="3" class="text-end">Term Total:</td>
                                                    <td class="text-center"><?= number_format($termTotalWeight, 2) ?>%</td>
                                                    <td colspan="3"></td>
                                                </tr>
                                                <?php
                                            }
                                            $currentTermId = $period['term_id'];
                                            $termTotalWeight = 0;
                                        }
                                        $termTotalWeight += floatval($period['weight_percentage']);
                                        
                                        $isInactive = ($period['is_active'] == 0);
                                        $rowClass = $isInactive ? 'border-bottom table-secondary opacity-75' : 'border-bottom';
                                        ?>
                                        <tr class="<?= $rowClass ?>">
                                            <td class="text-center">
                                                <span class="badge bg-primary rounded-circle" style="width: 30px; height: 30px; line-height: 30px;">
                                                    <?= $period['period_order'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-dark"><?= esc($period['period_name']) ?></strong>
                                                <?php if ($isInactive): ?>
                                                    <span class="badge bg-danger ms-2 small">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= esc($period['year_name'] ?? 'N/A') ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= esc($period['semester_name'] ?? 'N/A') ?></small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info px-3 py-2 fs-6">
                                                    <?= number_format($period['weight_percentage'], 2) ?>%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($period['start_date']) && !empty($period['end_date'])): ?>
                                                    <small class="text-muted">
                                                        <?= date('M j, Y', strtotime($period['start_date'])) ?>
                                                        <br>to<br>
                                                        <?= date('M j, Y', strtotime($period['end_date'])) ?>
                                                    </small>
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
                                                    <a href="<?= base_url('admin/manage_grading_periods?action=edit&id=' . $period['id']) ?>" 
                                                       class="btn btn-outline-warning" 
                                                       title="Edit Grading Period">
                                                        ✏️
                                                    </a>
                                                    
                                                    <!-- Toggle Status Button -->
                                                    <?php 
                                                    $toggleText = $isInactive ? 'Activate' : 'Deactivate';
                                                    $toggleIcon = $isInactive ? '✅' : '❌';
                                                    $toggleClass = $isInactive ? 'btn-outline-success' : 'btn-outline-warning';
                                                    ?>
                                                    <a href="<?= base_url('admin/manage_grading_periods?action=toggle_status&id=' . $period['id']) ?>" 
                                                       class="btn <?= $toggleClass ?>" 
                                                       onclick="return confirm('Are you sure you want to <?= strtolower($toggleText) ?> this grading period?')"
                                                       title="<?= $toggleText ?> Grading Period">
                                                        <?= $toggleIcon ?>
                                                    </a>
                                                    
                                                    <!-- Delete Button -->
                                                    <a href="<?= base_url('admin/manage_grading_periods?action=delete&id=' . $period['id']) ?>" 
                                                       class="btn btn-outline-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this grading period?\n\nPeriod: <?= esc($period['period_name']) ?>\n\nThis action cannot be undone!')"
                                                       title="Delete Grading Period">
                                                        🗑️
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php 
                                        // Show last term total
                                        if ($termTotalWeight > 0) {
                                            $weightClass = abs($termTotalWeight - 100) < 0.01 ? 'success' : 'danger';
                                            ?>
                                            <tr class="table-<?= $weightClass ?> fw-bold">
                                                <td colspan="3" class="text-end">Term Total:</td>
                                                <td class="text-center"><?= number_format($termTotalWeight, 2) ?>%</td>
                                                <td colspan="3"></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <div class="mb-3">
                                                    <span style="font-size: 3rem; opacity: 0.3;">📊</span>
                                                </div>
                                                <p class="mb-0">No grading periods found in the system.</p>
                                                <p class="small">Click "Create New Period" to add your first grading period.</p>
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
    // Date validation
    const startDateFields = document.querySelectorAll('input[name="start_date"]');
    const endDateFields = document.querySelectorAll('input[name="end_date"]');
    
    function validateDates(startField, endField) {
        if (startField && endField && startField.value && endField.value) {
            const startDate = new Date(startField.value);
            const endDate = new Date(endField.value);
            
            if (startDate > endDate) {
                endField.setCustomValidity('End date must be after start date');
                endField.classList.add('is-invalid');
                endField.classList.remove('is-valid');
            } else {
                endField.setCustomValidity('');
                endField.classList.remove('is-invalid');
                if (endField.value) {
                    endField.classList.add('is-valid');
                }
            }
        }
    }
    
    // Add event listeners for date fields
    if (startDateFields.length > 0) {
        startDateFields.forEach(function(startField, index) {
            const endField = endDateFields[index];
            
            startField.addEventListener('change', function() {
                validateDates(startField, endField);
            });
            
            if (endField) {
                endField.addEventListener('change', function() {
                    validateDates(startField, endField);
                });
            }
        });
    }
    
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
    
    // Period order validation
    const orderFields = document.querySelectorAll('input[name="period_order"]');
    
    orderFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = parseInt(e.target.value);
            
            // Visual feedback
            if (value >= 1 && value <= 10) {
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
