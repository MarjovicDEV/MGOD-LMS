<?= $this->include('templates/header') ?>

<!-- Manage Enrollments View - Admin only functionality for enrollment management -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">📝 Manage Enrollments</h2>
                                <p class="mb-0 opacity-75">Create, edit, and manage student enrollments in the system</p>
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

        <!-- Enrollment Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📝</div>
                    <div class="display-5 fw-bold"><?= count($enrollments) ?></div>
                    <div class="fw-semibold">Total Enrollments</div>
                    <small class="opacity-75"><?= $selectedTermId ? 'In selected term' : 'All terms' ?></small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($enrollments, fn($e) => $e['enrollment_status'] === 'enrolled')) ?></div>
                    <div class="fw-semibold">Enrolled</div>
                    <small class="opacity-75">Active enrollments</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">⏳</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($enrollments, fn($e) => $e['enrollment_status'] === 'pending')) ?></div>
                    <div class="fw-semibold">Pending</div>
                    <small class="opacity-75">Awaiting approval</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-secondary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">💰</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($enrollments, fn($e) => $e['payment_status'] === 'paid')) ?></div>
                    <div class="fw-semibold">Fully Paid</div>
                    <small class="opacity-75">Payment complete</small>
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

        <!-- Action Buttons and Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <h5 class="mb-0 fw-bold text-dark">⚡ Enrollment Management</h5>
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <!-- Term Filter -->
                                <form method="get" action="<?= base_url('admin/manage_enrollments') ?>" class="d-flex gap-2">
                                    <select class="form-select form-select-sm" name="term_id" style="min-width: 200px;">
                                        <option value="">All Terms</option>
                                        <?php foreach ($terms as $term): ?>
                                            <option value="<?= $term['id'] ?>" <?= $selectedTermId == $term['id'] ? 'selected' : '' ?>>
                                                <?= esc($term['term_name']) ?> - <?= esc($term['semester_name']) ?> (<?= esc($term['academic_year']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-outline-primary btn-sm">Filter</button>
                                </form>
                                <a href="<?= base_url('admin/manage_enrollments?action=create' . ($selectedTermId ? '&term_id=' . $selectedTermId : '')) ?>" class="btn btn-success">
                                    ➕ Create New Enrollment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Enrollment Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Create New Enrollment</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_enrollments?action=create' . ($selectedTermId ? '&term_id=' . $selectedTermId : '')) ?>">
                            <?= csrf_field() ?>
                            
                            <!-- Student and Course Selection -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-user-graduate me-2"></i>Student & Course
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="student_id" class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                                            <select class="form-select" id="student_id" name="student_id" required>
                                                <option value="">-- Select Student --</option>
                                                <?php foreach ($students as $student): ?>
                                                    <option value="<?= $student['id'] ?>" 
                                                            data-year-level-id="<?= $student['year_level_id'] ?? '' ?>"
                                                            data-year-level-name="<?= esc($student['year_level_name'] ?? '') ?>"
                                                            <?= old('student_id') == $student['id'] ? 'selected' : '' ?>>
                                                        <?= esc($student['student_id_number']) ?> - <?= esc($student['last_name']) ?>, <?= esc($student['first_name']) ?>
                                                        <?php if (!empty($student['year_level_name'])): ?>
                                                            (<?= esc($student['year_level_name']) ?>)
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Select the student to enroll</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="course_offering_id" class="form-label fw-semibold">Course Offering <span class="text-danger">*</span></label>
                                            <select class="form-select" id="course_offering_id" name="course_offering_id" required>
                                                <option value="">-- Select Course Offering --</option>
                                                <?php foreach ($courseOfferings as $offering): ?>
                                                    <option value="<?= $offering['id'] ?>" <?= old('course_offering_id') == $offering['id'] ? 'selected' : '' ?>>
                                                        <?= esc($offering['course_code']) ?> - <?= esc($offering['course_title']) ?> 
                                                        (Section: <?= esc($offering['section']) ?>, <?= esc($offering['semester_name']) ?> - <?= esc($offering['academic_year']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Select the course offering (only open offerings are shown)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Enrollment Details -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-clipboard-list me-2"></i>Enrollment Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="enrollment_date" class="form-label fw-semibold">Enrollment Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="enrollment_date" name="enrollment_date" 
                                                   value="<?= old('enrollment_date', date('Y-m-d')) ?>" required>
                                            <div class="form-text">Date of enrollment</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="year_level_id" class="form-label fw-semibold">Year Level</label>
                                            <select class="form-select" id="year_level_id" name="year_level_id">
                                                <option value="">-- Select Year Level --</option>
                                                <?php foreach ($yearLevels as $year): ?>
                                                    <option value="<?= $year['id'] ?>" <?= old('year_level_id') == $year['id'] ? 'selected' : '' ?>>
                                                        <?= esc($year['year_level_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Auto-filled from student's current year level (can be adjusted for retakes/cross-enrollment)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="enrollment_type" class="form-label fw-semibold">Enrollment Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="enrollment_type" name="enrollment_type" required>
                                                <?php foreach ($enrollmentTypes as $key => $value): ?>
                                                    <option value="<?= $key ?>" <?= old('enrollment_type', 'regular') == $key ? 'selected' : '' ?>>
                                                        <?= esc($value) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Type of enrollment</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Section -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-toggle-on me-2"></i>Status & Payment
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="enrollment_status" class="form-label fw-semibold">Enrollment Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="enrollment_status" name="enrollment_status" required>
                                                <?php foreach ($enrollmentStatuses as $key => $value): ?>
                                                    <option value="<?= $key ?>" <?= old('enrollment_status', 'pending') == $key ? 'selected' : '' ?>>
                                                        <?= esc($value) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Current enrollment status</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="payment_status" class="form-label fw-semibold">Payment Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="payment_status" name="payment_status" required>
                                                <?php foreach ($paymentStatuses as $key => $value): ?>
                                                    <option value="<?= $key ?>" <?= old('payment_status', 'unpaid') == $key ? 'selected' : '' ?>>
                                                        <?= esc($value) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Payment status</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes Section -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-sticky-note me-2"></i>Additional Notes
                                </h6>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="notes" class="form-label fw-semibold">Notes</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                      placeholder="Enter any additional notes or remarks..."><?= old('notes') ?></textarea>
                                            <div class="form-text">Optional: Additional information about this enrollment</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    💾 Create Enrollment
                                </button>
                                <a href="<?= base_url('admin/manage_enrollments' . ($selectedTermId ? '?term_id=' . $selectedTermId : '')) ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Enrollment Form (shown when editing) -->
        <?php if ($showEditForm && $editEnrollment): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-dark border-0">
                        <h5 class="mb-0">✏️ Edit Enrollment #<?= esc($editEnrollment['id']) ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_enrollments?action=edit&id=' . $editEnrollment['id'] . ($selectedTermId ? '&term_id=' . $selectedTermId : '')) ?>">
                            <?= csrf_field() ?>
                            
                            <!-- Student and Course Selection -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-user-graduate me-2"></i>Student & Course
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_student_id" class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                                            <select class="form-select" id="edit_student_id" name="student_id" required>
                                                <option value="">-- Select Student --</option>
                                                <?php foreach ($students as $student): ?>
                                                    <option value="<?= $student['id'] ?>" <?= old('student_id', $editEnrollment['student_id']) == $student['id'] ? 'selected' : '' ?>>
                                                        <?= esc($student['student_id_number']) ?> - <?= esc($student['last_name']) ?>, <?= esc($student['first_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_course_offering_id" class="form-label fw-semibold">Course Offering <span class="text-danger">*</span></label>
                                            <select class="form-select" id="edit_course_offering_id" name="course_offering_id" required>
                                                <option value="">-- Select Course Offering --</option>
                                                <?php foreach ($courseOfferings as $offering): ?>
                                                    <option value="<?= $offering['id'] ?>" <?= old('course_offering_id', $editEnrollment['course_offering_id']) == $offering['id'] ? 'selected' : '' ?>>
                                                        <?= esc($offering['course_code']) ?> - <?= esc($offering['course_title']) ?> 
                                                        (Section: <?= esc($offering['section']) ?>, <?= esc($offering['semester_name']) ?> - <?= esc($offering['academic_year']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Enrollment Details -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-clipboard-list me-2"></i>Enrollment Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_enrollment_date" class="form-label fw-semibold">Enrollment Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="edit_enrollment_date" name="enrollment_date" 
                                                   value="<?= old('enrollment_date', $editEnrollment['enrollment_date']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_year_level_id" class="form-label fw-semibold">Year Level</label>
                                            <select class="form-select" id="edit_year_level_id" name="year_level_id">
                                                <option value="">-- Select Year Level --</option>
                                                <?php foreach ($yearLevels as $year): ?>
                                                    <option value="<?= $year['id'] ?>" <?= old('year_level_id', $editEnrollment['year_level_id']) == $year['id'] ? 'selected' : '' ?>>
                                                        <?= esc($year['year_level_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_enrollment_type" class="form-label fw-semibold">Enrollment Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="edit_enrollment_type" name="enrollment_type" required>
                                                <?php foreach ($enrollmentTypes as $key => $value): ?>
                                                    <option value="<?= $key ?>" <?= old('enrollment_type', $editEnrollment['enrollment_type']) == $key ? 'selected' : '' ?>>
                                                        <?= esc($value) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Section -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-toggle-on me-2"></i>Status & Payment
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_enrollment_status" class="form-label fw-semibold">Enrollment Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="edit_enrollment_status" name="enrollment_status" required>
                                                <?php foreach ($enrollmentStatuses as $key => $value): ?>
                                                    <option value="<?= $key ?>" <?= old('enrollment_status', $editEnrollment['enrollment_status']) == $key ? 'selected' : '' ?>>
                                                        <?= esc($value) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_payment_status" class="form-label fw-semibold">Payment Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="edit_payment_status" name="payment_status" required>
                                                <?php foreach ($paymentStatuses as $key => $value): ?>
                                                    <option value="<?= $key ?>" <?= old('payment_status', $editEnrollment['payment_status']) == $key ? 'selected' : '' ?>>
                                                        <?= esc($value) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes Section -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-sticky-note me-2"></i>Additional Notes
                                </h6>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="edit_notes" class="form-label fw-semibold">Notes</label>
                                            <textarea class="form-control" id="edit_notes" name="notes" rows="3"><?= old('notes', $editEnrollment['notes']) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning text-dark">
                                    💾 Update Enrollment
                                </button>
                                <a href="<?= base_url('admin/manage_enrollments' . ($selectedTermId ? '?term_id=' . $selectedTermId : '')) ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Enrollments List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 fw-bold">📋 Enrollment List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Section</th>
                                        <th>Term</th>
                                        <th>Year Level</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($enrollments)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                No enrollments found. <?= $selectedTermId ? 'Try selecting a different term or' : '' ?> Create your first enrollment to get started!
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($enrollments as $enrollment): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= esc($enrollment['student_id_number']) ?></strong><br>
                                                    <small class="text-muted"><?= esc($enrollment['last_name']) ?>, <?= esc($enrollment['first_name']) ?></small>
                                                </td>
                                                <td>
                                                    <strong><?= esc($enrollment['course_code']) ?></strong><br>
                                                    <small class="text-muted"><?= esc(substr($enrollment['course_title'], 0, 30)) ?><?= strlen($enrollment['course_title']) > 30 ? '...' : '' ?></small>
                                                </td>
                                                <td><?= esc($enrollment['section']) ?></td>
                                                <td>
                                                    <small><?= esc($enrollment['semester_name']) ?><br><?= esc($enrollment['academic_year']) ?></small>
                                                </td>
                                                <td><?= esc($enrollment['year_level_name'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php
                                                    $typeBadges = [
                                                        'regular' => 'bg-primary',
                                                        'irregular' => 'bg-info',
                                                        'retake' => 'bg-warning text-dark',
                                                        'cross_enroll' => 'bg-secondary',
                                                        'special' => 'bg-dark'
                                                    ];
                                                    $badgeClass = $typeBadges[$enrollment['enrollment_type']] ?? 'bg-secondary';
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= esc(ucfirst(str_replace('_', ' ', $enrollment['enrollment_type']))) ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusBadges = [
                                                        'pending' => 'bg-warning text-dark',
                                                        'enrolled' => 'bg-success',
                                                        'dropped' => 'bg-danger',
                                                        'withdrawn' => 'bg-secondary',
                                                        'completed' => 'bg-info'
                                                    ];
                                                    $statusBadge = $statusBadges[$enrollment['enrollment_status']] ?? 'bg-secondary';
                                                    ?>
                                                    <span class="badge <?= $statusBadge ?>"><?= esc(ucfirst($enrollment['enrollment_status'])) ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $paymentBadges = [
                                                        'unpaid' => 'bg-danger',
                                                        'partial' => 'bg-warning text-dark',
                                                        'paid' => 'bg-success',
                                                        'scholarship' => 'bg-info',
                                                        'waived' => 'bg-secondary'
                                                    ];
                                                    $paymentBadge = $paymentBadges[$enrollment['payment_status']] ?? 'bg-secondary';
                                                    ?>
                                                    <span class="badge <?= $paymentBadge ?>"><?= esc(ucfirst($enrollment['payment_status'])) ?></span>
                                                </td>
                                                <td><small><?= date('M d, Y', strtotime($enrollment['enrollment_date'])) ?></small></td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?= base_url('admin/manage_enrollments?action=edit&id=' . $enrollment['id'] . ($selectedTermId ? '&term_id=' . $selectedTermId : '')) ?>" 
                                                           class="btn btn-outline-warning" title="Edit">
                                                            ✏️
                                                        </a>
                                                        
                                                        <!-- Quick Status Update Dropdown -->
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" title="Quick Status Update">
                                                                ⚡
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><h6 class="dropdown-header">Change Status</h6></li>
                                                                <?php foreach ($enrollmentStatuses as $statusKey => $statusValue): ?>
                                                                    <?php if ($statusKey !== $enrollment['enrollment_status']): ?>
                                                                        <li>
                                                                            <form method="post" action="<?= base_url('admin/manage_enrollments') ?>" style="display: inline;">
                                                                                <?= csrf_field() ?>
                                                                                <input type="hidden" name="action" value="update_status">
                                                                                <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                                                                <input type="hidden" name="new_status" value="<?= $statusKey ?>">
                                                                                <input type="hidden" name="term_id" value="<?= $selectedTermId ?>">
                                                                                <button type="submit" class="dropdown-item" onclick="return confirm('Change status to <?= $statusValue ?>?')">
                                                                                    <?= esc($statusValue) ?>
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                        
                                                        <!-- Delete Button -->
                                                        <form method="post" action="<?= base_url('admin/manage_enrollments') ?>" style="display: inline;">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                                            <input type="hidden" name="term_id" value="<?= $selectedTermId ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete"
                                                                    onclick="return confirm('Are you sure you want to delete this enrollment?')">
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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill year level when a student is selected
    const studentSelect = document.getElementById('student_id');
    const yearLevelSelect = document.getElementById('year_level_id');
    
    if (studentSelect && yearLevelSelect) {
        studentSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const yearLevelId = selectedOption.getAttribute('data-year-level-id');
            
            if (yearLevelId) {
                yearLevelSelect.value = yearLevelId;
                // Add visual feedback
                yearLevelSelect.classList.add('is-valid');
                setTimeout(() => yearLevelSelect.classList.remove('is-valid'), 2000);
            } else {
                yearLevelSelect.value = '';
            }
        });
    }
    
    // Filter by term
    const termFilter = document.getElementById('termFilter');
    if (termFilter) {
        termFilter.addEventListener('change', function() {
            const termId = this.value;
            if (termId) {
                window.location.href = '<?= base_url('admin/manage_enrollments') ?>?term_id=' + termId;
            } else {
                window.location.href = '<?= base_url('admin/manage_enrollments') ?>';
            }
        });
    }
});
</script>
