<?= $this->include('templates/header') ?>

<!-- Teacher Enrolled Students View -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-success text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold">📋 Enrolled Students</h3>
                                <p class="mb-0 opacity-75">View students enrolled in your courses</p>
                            </div>
                            <div>
                                <a href="<?= base_url('teacher/enroll_student') ?>" class="btn btn-light">
                                    <i class="fas fa-user-plus"></i> Enroll New Student
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
                <i class="fas fa-check-circle me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Pending Approvals Section -->
        <div class="row mb-4" id="pendingApprovalsSection" style="display: none;">
            <div class="col-12">
                <div class="card border-0 shadow-sm border-warning">
                    <div class="card-body p-4">
                        <h3 class="mb-3 fw-bold text-warning">
                            <i class="fas fa-clock me-2"></i>Pending Enrollment Approvals
                        </h3>
                        <p class="text-muted mb-3">The following student enrollments require your approval:</p>
                        <div id="pendingApprovalsList">
                            <!-- Pending approvals will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">👥</div>
                    <div class="display-5 fw-bold"><?= count($enrolledStudents) ?></div>
                    <div class="fw-semibold">Total Enrollments</div>
                    <small class="opacity-75">In your courses</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold">
                        <?= count(array_filter($enrolledStudents, fn($e) => $e['enrollment_status'] == 'enrolled')) ?>
                    </div>
                    <div class="fw-semibold">Enrolled</div>
                    <small class="opacity-75">Active students</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">⏳</div>
                    <div class="display-5 fw-bold">
                        <?= count(array_filter($enrolledStudents, fn($e) => $e['enrollment_status'] == 'pending')) ?>
                    </div>
                    <div class="fw-semibold">Pending</div>
                    <small class="opacity-75">Awaiting confirmation</small>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">🔍 Filter Students</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= base_url('teacher/enrolled_students') ?>" id="filterForm">
                            <div class="row align-items-end">
                                <div class="col-md-5 mb-2">
                                    <label for="course_offering_id" class="form-label fw-semibold">Course Offering</label>
                                    <select name="course_offering_id" id="course_offering_id" class="form-select">
                                        <option value="">-- All Courses --</option>
                                        <?php foreach ($assignedCourses as $course): ?>
                                            <option value="<?= $course['id'] ?>" 
                                                    <?= $selectedCourseId == $course['id'] ? 'selected' : '' ?>>
                                                <?= esc($course['course_code']) ?> - <?= esc($course['course_title']) ?>
                                                (<?= esc($course['term_name']) ?> <?= esc($course['academic_year']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5 mb-2">
                                    <label for="enrollment_status" class="form-label fw-semibold">Enrollment Status</label>
                                    <select name="enrollment_status" id="enrollment_status" class="form-select">
                                        <option value="">-- All Statuses --</option>
                                        <?php foreach ($enrollmentStatuses as $status): ?>
                                            <option value="<?= $status ?>" 
                                                    <?= $selectedStatus == $status ? 'selected' : '' ?>>
                                                <?= ucfirst($status) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter"></i> Apply Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrolled Students Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">📝 Enrollment Records</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($enrolledStudents)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No Enrollment Records Found</h5>
                                <p class="text-muted mb-3">
                                    <?php if ($selectedCourseId || $selectedStatus): ?>
                                        No enrollments match your filter criteria.
                                    <?php else: ?>
                                        You don't have any enrolled students yet.
                                    <?php endif; ?>
                                </p>
                                <?php if ($selectedCourseId || $selectedStatus): ?>
                                    <a href="<?= base_url('teacher/enrolled_students') ?>" class="btn btn-primary">
                                        <i class="fas fa-redo"></i> Clear Filters
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Course</th>                                            <th>Term</th>
                                            <th>Program</th>
                                            <th>Year</th>
                                            <th>Enrollment Date</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($enrolledStudents as $enrollment): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?= esc($enrollment['student_id_number']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?= esc($enrollment['full_name']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-envelope"></i> 
                                                            <?= esc($enrollment['email']) ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?= esc($enrollment['course_code']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= esc($enrollment['course_title']) ?></small>
                                                </td>
                                                <td>
                                                    <?= esc($enrollment['term_name']) ?>
                                                    <br>
                                                    <small class="text-muted"><?= esc($enrollment['academic_year']) ?></small>
                                                </td>
                                                <td>
                                                    <?= esc($enrollment['program_code']) ?>
                                                </td>
                                                <td>
                                                    <?= esc($enrollment['year_level']) ?>
                                                    <?php if ($enrollment['section']): ?>
                                                        <br>
                                                        <small class="text-muted">Sec: <?= esc($enrollment['section']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= date('M d, Y', strtotime($enrollment['enrollment_date'])) ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?= ucwords(str_replace('_', ' ', $enrollment['enrollment_type'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusColors = [
                                                        'enrolled' => 'success',
                                                        'pending' => 'warning',
                                                        'dropped' => 'danger',
                                                        'completed' => 'primary'
                                                    ];
                                                    $badgeColor = $statusColors[$enrollment['enrollment_status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?= $badgeColor ?>">
                                                        <?= ucfirst($enrollment['enrollment_status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $paymentColors = [
                                                        'paid' => 'success',
                                                        'partial' => 'warning',
                                                        'unpaid' => 'danger'
                                                    ];
                                                    $paymentColor = $paymentColors[$enrollment['payment_status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?= $paymentColor ?>">
                                                        <?= ucfirst($enrollment['payment_status']) ?>                                                </span>
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewModal<?= $enrollment['enrollment_id'] ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
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

<!-- View Details Modals -->
<?php foreach ($enrolledStudents as $enrollment): ?>
<div class="modal fade" id="viewModal<?= $enrollment['enrollment_id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Enrollment Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Student Information -->
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-user"></i> Student Information
                        </h6>
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted">Student ID:</td>
                                <td><strong><?= esc($enrollment['student_id_number']) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Name:</td>
                                <td><strong><?= esc($enrollment['full_name']) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td><?= esc($enrollment['email']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Program:</td>
                                <td><?= esc($enrollment['program_code']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Year Level:</td>
                                <td><?= esc($enrollment['year_level']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Section:</td>
                                <td><?= esc($enrollment['section']) ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Course & Enrollment Information -->
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-book"></i> Course & Enrollment Details
                        </h6>
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted">Course Code:</td>
                                <td><strong><?= esc($enrollment['course_code']) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Course Title:</td>
                                <td><?= esc($enrollment['course_title']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Term:</td>
                                <td><?= esc($enrollment['term_name']) ?> <?= esc($enrollment['academic_year']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Enrollment Date:</td>
                                <td><?= date('F d, Y', strtotime($enrollment['enrollment_date'])) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Type:</td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= ucwords(str_replace('_', ' ', $enrollment['enrollment_type'])) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status:</td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'enrolled' => 'success',
                                        'pending' => 'warning',
                                        'dropped' => 'danger',
                                        'completed' => 'primary'
                                    ];
                                    $badgeColor = $statusColors[$enrollment['enrollment_status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badgeColor ?>">
                                        <?= ucfirst($enrollment['enrollment_status']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Payment & Grade Information -->
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-file-alt"></i> Additional Information
                        </h6>
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted" style="width: 150px;">Payment Status:</td>
                                <td>
                                    <?php
                                    $paymentColors = [
                                        'paid' => 'success',
                                        'partial' => 'warning',
                                        'unpaid' => 'danger'
                                    ];
                                    $paymentColor = $paymentColors[$enrollment['payment_status']] ?? 'secondary';
                                    ?>                                    <span class="badge bg-<?= $paymentColor ?>">
                                        <?= ucfirst($enrollment['payment_status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php if ($enrollment['notes']): ?>
                                <tr>
                                    <td class="text-muted">Notes:</td>
                                    <td><?= nl2br(esc($enrollment['notes'])) ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load pending enrollments
    loadPendingEnrollments();
    
    // Auto-submit filter form when selections change
    const courseSelect = document.getElementById('course_offering_id');
    const statusSelect = document.getElementById('enrollment_status');
    
    if (courseSelect) {
        courseSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }
});

function loadPendingEnrollments() {
    fetch('<?= base_url("/enrollment/pending") ?>', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.pending_enrollments && data.pending_enrollments.length > 0) {
            displayPendingEnrollments(data.pending_enrollments);
            document.getElementById('pendingApprovalsSection').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error loading pending enrollments:', error);
    });
}

function displayPendingEnrollments(enrollments) {
    const container = document.getElementById('pendingApprovalsList');
    container.innerHTML = '';
    
    enrollments.forEach(enrollment => {
        const enrollmentCard = `
            <div class="card mb-3 border-warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="card-title mb-1">
                                ${enrollment.student_name} (${enrollment.student_id_number})
                            </h6>
                            <p class="text-muted mb-1">
                                <small>
                                    <i class="fas fa-book me-1"></i>${enrollment.course_code} - ${enrollment.course_title} | 
                                    <i class="fas fa-graduation-cap me-1"></i>${enrollment.credits} credits | 
                                    <i class="fas fa-calendar me-1"></i>${enrollment.term_name} ${enrollment.academic_year}
                                </small>
                            </p>
                            <p class="text-muted mb-0">
                                <small>
                                    <i class="fas fa-clock me-1"></i>Enrollment Date: ${new Date(enrollment.enrollment_date).toLocaleDateString()}
                                    ${enrollment.notes ? '<br><i class="fas fa-sticky-note me-1"></i>Notes: ' + enrollment.notes : ''}
                                </small>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-success btn-sm me-2" onclick="respondToEnrollment(${enrollment.enrollment_id}, 'accept')">
                                <i class="fas fa-check me-1"></i>Accept
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="respondToEnrollment(${enrollment.enrollment_id}, 'reject')">
                                <i class="fas fa-times me-1"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.innerHTML += enrollmentCard;
    });
}

function respondToEnrollment(enrollmentId, action) {
    if (!confirm(`Are you sure you want to ${action} this enrollment?`)) {
        return;
    }
    
    // Use FormData for proper POST handling
    const formData = new FormData();
    formData.append('enrollment_id', enrollmentId);
    formData.append('action', action);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    fetch('<?= base_url("/enrollment/respond") ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload pending enrollments
            loadPendingEnrollments();
            // Show success message
            showAlert(data.message, 'success');
            // Reload page after a short delay to update enrolled students list
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error responding to enrollment:', error);
        showAlert('An error occurred. Please try again.', 'danger');
    });
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert alert at the top of the container
    const container = document.querySelector('.container');
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>