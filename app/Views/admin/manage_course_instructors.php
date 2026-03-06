<?= $this->include('templates/header') ?>

<!-- Manage Course Instructors View - Admin only functionality -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">👨‍🏫 Manage Course Instructors</h2>
                                <p class="mb-0 opacity-75">Assign and manage instructors for course offerings</p>
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

        <!-- Course Offering Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-3 fw-bold text-dark">🎯 Select Course Offering</h5>
                                <select class="form-select" id="offeringFilter" onchange="filterByOffering(this.value)">
                                    <option value="">-- Select Course Offering --</option>
                                    <?php foreach ($offerings as $offering): ?>
                                        <option value="<?= $offering['id'] ?>" <?= $selectedOfferingId == $offering['id'] ? 'selected' : '' ?>>
                                            <?= esc($offering['course_code']) ?> - <?= esc($offering['title']) ?> 
                                            (<?= esc($offering['section'] ?: 'No Section') ?>) - <?= esc($offering['term_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <?php if ($selectedOfferingId): ?>
                                    <a href="<?= base_url('admin/manage_course_instructors?action=assign&offering_id=' . $selectedOfferingId) ?>" class="btn btn-success">
                                        ➕ Assign Instructor
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-success" disabled title="Please select a course offering first">
                                        ➕ Assign Instructor
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Offering Info -->
        <?php if ($selectedOffering): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-info">
                    <div class="card-body bg-light">
                        <h5 class="mb-2 fw-bold text-info">📚 Selected Course:</h5>
                        <h4 class="mb-1"><?= esc($selectedOffering['course_code']) ?> - <?= esc($selectedOffering['title']) ?></h4>
                        <p class="text-muted mb-0">
                            <strong>Section:</strong> <?= esc($selectedOffering['section'] ?: 'No Section') ?> | 
                            <strong>Term:</strong> <?= esc($selectedOffering['term_name']) ?> | 
                            <strong>Credits:</strong> <?= esc($selectedOffering['credits']) ?> units | 
                            <strong>Instructors:</strong> <?= count($assignments) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Assign Instructor Form -->
        <?php if ($showAssignForm && $selectedOfferingId): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Assign Instructor to Course</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($availableInstructors)): ?>
                        <form method="post" action="<?= base_url('admin/manage_course_instructors?action=assign') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="course_offering_id" value="<?= $selectedOfferingId ?>">
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="instructor_id" class="form-label fw-semibold">Select Instructor <span class="text-danger">*</span></label>
                                        <select class="form-select" id="instructor_id" name="instructor_id" required>
                                            <option value="">-- Select Instructor --</option>
                                            <?php foreach ($availableInstructors as $instructor): ?>
                                                <option value="<?= $instructor['id'] ?>" <?= old('instructor_id') == $instructor['id'] ? 'selected' : '' ?>>
                                                    <?= esc($instructor['first_name'] . ' ' . $instructor['last_name']) ?> 
                                                    (<?= esc($instructor['employee_id']) ?>)
                                                    <?php if ($instructor['department_name']): ?>
                                                        - <?= esc($instructor['department_name']) ?>
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Choose from available instructors</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="is_primary" class="form-label fw-semibold">Primary Instructor</label>
                                        <select class="form-select" id="is_primary" name="is_primary">
                                            <option value="0" <?= old('is_primary') == '0' ? 'selected' : '' ?>>No</option>
                                            <option value="1" <?= old('is_primary') == '1' ? 'selected' : '' ?>>Yes</option>
                                        </select>
                                        <small class="text-muted">Set as primary instructor</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('admin/manage_course_instructors?offering_id=' . $selectedOfferingId) ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    ➕ Assign Instructor
                                </button>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            All available instructors have been assigned to this course offering.
                            <a href="<?= base_url('admin/manage_course_instructors?offering_id=' . $selectedOfferingId) ?>" class="alert-link">Go back</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Instructors Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">👨‍🏫 Assigned Instructors</h5>
                            <div class="text-muted small">
                                <?php if ($selectedOffering): ?>
                                    Total: <?= count($assignments) ?> instructor(s)
                                <?php else: ?>
                                    Select a course offering to view instructors
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold border-0 text-center">ID</th>
                                        <th class="fw-semibold border-0">Instructor Name</th>
                                        <th class="fw-semibold border-0">Employee ID</th>
                                        <th class="fw-semibold border-0">Department</th>
                                        <th class="fw-semibold border-0">Email</th>
                                        <th class="fw-semibold border-0 text-center">Primary</th>
                                        <th class="fw-semibold border-0 text-center">Assigned Date</th>
                                        <th class="fw-semibold border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($assignments)): ?>
                                        <?php foreach ($assignments as $assignment): ?>
                                        <tr class="border-bottom">
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">#<?= $assignment['id'] ?></span>
                                            </td>
                                            <td>
                                                <strong>
                                                    <?= esc($assignment['first_name'] . ' ' . $assignment['last_name']) ?>
                                                </strong>
                                                <?php if ($assignment['is_primary']): ?>
                                                    <span class="badge bg-warning text-dark ms-2">⭐ Primary</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= esc($assignment['employee_id']) ?></span>
                                            </td>
                                            <td>
                                                <?= $assignment['department_name'] ? esc($assignment['department_name']) : '<span class="text-muted">Not set</span>' ?>
                                            </td>
                                            <td>
                                                <small><?= esc($assignment['email']) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($assignment['is_primary']): ?>
                                                    <span class="badge bg-success">Yes</span>
                                                <?php else: ?>
                                                    <a href="<?= base_url('admin/manage_course_instructors?action=set_primary&id=' . $assignment['id']) ?>" 
                                                       class="btn btn-sm btn-outline-warning"
                                                       onclick="return confirm('Set this instructor as primary?')">
                                                        Set as Primary
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <?= $assignment['assigned_date'] ? date('M d, Y', strtotime($assignment['assigned_date'])) : 'N/A' ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= base_url('admin/manage_course_instructors?action=remove&id=' . $assignment['id']) ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   title="Remove Instructor"
                                                   onclick="return confirm('Are you sure you want to remove this instructor? This action cannot be undone.')">
                                                    🗑️ Remove
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <div class="display-1 mb-3">👨‍🏫</div>
                                                <p class="mb-0">
                                                    <?php if ($selectedOffering): ?>
                                                        No instructors assigned yet. Click "Assign Instructor" to add one.
                                                    <?php else: ?>
                                                        Please select a course offering to view assigned instructors.
                                                    <?php endif; ?>
                                                </p>
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
function filterByOffering(offeringId) {
    if (offeringId) {
        window.location.href = '<?= base_url('admin/manage_course_instructors') ?>?offering_id=' + offeringId;
    } else {
        window.location.href = '<?= base_url('admin/manage_course_instructors') ?>';
    }
}
</script>
