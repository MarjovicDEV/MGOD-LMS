<?= $this->include('templates/header') ?>

<!-- Teacher Enroll Student View -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold">👥 Enroll a Student</h3>
                                <p class="mb-0 opacity-75">Add students to your assigned course offerings</p>
                            </div>
                            <div>
                                <a href="<?= base_url('teacher/enrolled_students') ?>" class="btn btn-light">
                                    <i class="fas fa-list"></i> View Enrolled Students
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

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Validation Errors:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Course Selection -->
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">📖 Select Course Offering</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assignedCourses)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                You don't have any assigned course offerings yet.
                            </div>
                        <?php else: ?>                            <form method="get" action="<?= base_url('teacher/enroll_student') ?>" id="courseFilterForm">
                                <div class="row align-items-end">
                                    <div class="col-12">
                                        <label for="course_offering_id" class="form-label fw-semibold">
                                            Course Offering
                                            <small class="text-muted">(Students will be filtered automatically)</small>
                                        </label>
                                        <select name="course_offering_id" id="course_offering_id" class="form-select" required>
                                            <option value="">-- Select a Course Offering --</option>                                            <?php foreach ($assignedCourses as $course): ?>
                                                <option value="<?= $course['id'] ?>" 
                                                        <?= $selectedCourseId == $course['id'] ? 'selected' : '' ?>
                                                        data-department="<?= esc($course['department_name'] ?? 'N/A') ?>"
                                                        data-enrolled="<?= $course['enrolled_count'] ?>"
                                                        data-max="<?= $course['max_students'] ?>">
                                                    <?= esc($course['course_code']) ?> - <?= esc($course['course_title']) ?> 
                                                    <?php if (!empty($course['section'])): ?>
                                                        (Section <?= esc($course['section']) ?>)
                                                    <?php endif; ?>
                                                    - <?= esc($course['term_name']) ?> <?= esc($course['academic_year']) ?>
                                                    - Enrolled: <?= $course['enrolled_count'] ?><?= $course['max_students'] ? '/' . $course['max_students'] : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </form><?php if ($selectedCourseId): ?>
                                <div class="mt-3">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="p-2 bg-light rounded">
                                                <small class="text-muted d-block">Department:</small>
                                                <strong id="selectedDepartment">-</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-2 bg-light rounded">
                                                <small class="text-muted d-block">Capacity:</small>
                                                <strong id="selectedCapacity">-</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>            <!-- Enrollment Form -->
            <?php if ($selectedCourseId && !empty($students)): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">👥 Enroll Students</h5>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                                        <i class="fas fa-check-double"></i> Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllBtn">
                                        <i class="fas fa-times"></i> Clear All
                                    </button>
                                </div>
                            </div>
                        </div>                        <div class="card-body">
                            <form method="post" action="<?= base_url('teacher/enroll_student') ?>" id="enrollmentForm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="course_offering_id" value="<?= esc($selectedCourseId) ?>">
                            
                                <!-- Student Selection Table -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label fw-semibold mb-0">
                                            <i class="fas fa-users text-primary"></i> Select Students <span class="text-danger">*</span>
                                        </label>
                                        <span class="badge bg-info" id="selectedCount">0 selected</span>
                                    </div>
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-hover table-sm">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th width="40">
                                                        <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                                    </th>
                                                    <th>Student ID</th>
                                                    <th>Name</th>
                                                    <th>Program</th>
                                                    <th>Year Level</th>
                                                    <th>Section</th>
                                                </tr>
                                            </thead>
                                            <tbody id="studentsTableBody">
                                                <?php foreach ($students as $student): ?>
                                                    <tr class="student-row">
                                                        <td>
                                                            <input type="checkbox" 
                                                                   class="form-check-input student-checkbox" 
                                                                   name="student_ids[]" 
                                                                   value="<?= $student['id'] ?>"
                                                                   data-student-id="<?= esc($student['student_id_number']) ?>"
                                                                   data-name="<?= esc($student['full_name']) ?>"
                                                                   data-email="<?= esc($student['email']) ?>"
                                                                   data-program="<?= esc($student['program_code']) ?>"
                                                                   data-year="<?= esc($student['year_level']) ?>"
                                                                   data-section="<?= esc($student['section']) ?>">
                                                        </td>
                                                        <td><span class="badge bg-secondary"><?= esc($student['student_id_number']) ?></span></td>
                                                        <td><?= esc($student['full_name']) ?></td>
                                                        <td><?= esc($student['program_code']) ?></td>
                                                        <td><?= esc($student['year_level']) ?></td>
                                                        <td><?= esc($student['section']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row">                                    <!-- Enrollment Date -->
                                    <div class="col-md-6 mb-3">
                                        <label for="enrollment_date" class="form-label fw-semibold">
                                            <i class="fas fa-calendar text-primary"></i> Enrollment Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="enrollment_date" id="enrollment_date" 
                                               class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>

                                    <!-- Enrollment Status -->
                                    <div class="col-md-6 mb-3">
                                        <label for="enrollment_status" class="form-label fw-semibold">
                                            <i class="fas fa-check-circle text-primary"></i> Enrollment Status <span class="text-danger">*</span>
                                        </label>
                                        <select name="enrollment_status" id="enrollment_status" class="form-select" required>
                                            <option value="">-- Select Status --</option>
                                            <?php foreach ($enrollmentStatuses as $status): ?>
                                                <option value="<?= $status ?>" <?= $status === 'enrolled' ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Enrollment Type -->
                                    <div class="col-md-6 mb-3">
                                        <label for="enrollment_type" class="form-label fw-semibold">
                                            <i class="fas fa-tag text-primary"></i> Enrollment Type <span class="text-danger">*</span>
                                        </label>
                                        <select name="enrollment_type" id="enrollment_type" class="form-select" required>
                                            <option value="">-- Select Type --</option>
                                            <?php foreach ($enrollmentTypes as $type): ?>
                                                <option value="<?= $type ?>" <?= $type === 'regular' ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $type)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Payment Status -->
                                    <div class="col-md-6 mb-3">
                                        <label for="payment_status" class="form-label fw-semibold">
                                            <i class="fas fa-money-bill text-primary"></i> Payment Status <span class="text-danger">*</span>
                                        </label>
                                        <select name="payment_status" id="payment_status" class="form-select" required>
                                            <option value="">-- Select Payment Status --</option>
                                            <?php foreach ($paymentStatuses as $status): ?>
                                                <option value="<?= $status ?>" <?= $status === 'unpaid' ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Notes -->
                                    <div class="col-12 mb-3">
                                        <label for="notes" class="form-label fw-semibold">
                                            <i class="fas fa-sticky-note text-primary"></i> Notes (Optional)
                                        </label>
                                        <textarea name="notes" id="notes" class="form-control" rows="2" 
                                                  placeholder="Add any additional notes about this enrollment..."></textarea>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted">
                                                    <i class="fas fa-info-circle"></i> 
                                                    Select students from the table above
                                                </span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="<?= base_url('teacher/enroll_student') ?>" class="btn btn-secondary">
                                                    <i class="fas fa-times"></i> Cancel
                                                </a>
                                                <button type="submit" class="btn btn-primary" id="enrollButton" disabled>
                                                    <i class="fas fa-user-plus"></i> Enroll Selected Students
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php elseif ($selectedCourseId && empty($students)): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-users-slash fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Eligible Students Found</h5>
                            <p class="text-muted mb-0">
                                All eligible students for this course are already enrolled, or there are no students 
                                matching the course's program and department requirements.
                            </p>
                        </div>
                    </div>
                </div>
            <?php elseif (!$selectedCourseId && !empty($assignedCourses)): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-hand-pointer fa-4x text-primary mb-3"></i>
                            <h5 class="text-muted">Select a Course Offering</h5>
                            <p class="text-muted mb-0">
                                Please select a course offering from the dropdown above to view eligible students.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Loaded - Initializing enrollment form');    // Update course info when selection changes and auto-submit form
    const courseSelect = document.getElementById('course_offering_id');
    const courseFilterForm = document.getElementById('courseFilterForm');
    
    if (courseSelect && courseFilterForm) {
        let isSubmitting = false; // Prevent duplicate submissions
        
        courseSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value && !isSubmitting) {
                // Update course info display (if elements exist)
                const deptElement = document.getElementById('selectedDepartment');
                const capacityElement = document.getElementById('selectedCapacity');
                
                if (deptElement) {
                    deptElement.textContent = selectedOption.dataset.department || 'Not specified';
                }
                if (capacityElement) {
                    capacityElement.textContent = selectedOption.dataset.enrolled + '/' + 
                        (selectedOption.dataset.max || '∞');
                }
                
                // Auto-submit the form to load students for this course
                console.log('Auto-submitting course filter form for course:', selectedOption.value);
                isSubmitting = true;
                courseFilterForm.submit();
            }
        });
    }

    // Bulk selection functionality
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const clearAllBtn = document.getElementById('clearAllBtn');
    const selectedCountBadge = document.getElementById('selectedCount');
    const enrollButton = document.getElementById('enrollButton');

    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
        selectedCountBadge.textContent = `${checkedCount} selected`;
        
        // Enable/disable enroll button
        if (enrollButton) {
            enrollButton.disabled = checkedCount === 0;
        }
        
        // Update select all checkbox state
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = checkedCount === studentCheckboxes.length && checkedCount > 0;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < studentCheckboxes.length;
        }
    }

    // Individual checkbox change
    studentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }

    // Select all button
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            updateSelectedCount();
        });
    }

    // Clear all button
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedCount();
        });
    }    // Form validation and AJAX submission
    const enrollmentForm = document.getElementById('enrollmentForm');
    if (enrollmentForm) {
        console.log('Form found:', enrollmentForm);
        console.log('Form action:', enrollmentForm.action);
        
        enrollmentForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            console.log('Form submit triggered - using AJAX');
            
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            const checkedCount = checkedBoxes.length;
            console.log('Checked students:', checkedCount);
            
            if (checkedCount === 0) {
                showAlert('danger', 'Please select at least one student to enroll.');
                return false;
            }

            const requiredFields = ['enrollment_date', 'enrollment_status', 
                                   'enrollment_type', 'payment_status'];
            let isValid = true;

            requiredFields.forEach(function(fieldName) {
                const field = document.getElementById(fieldName);
                if (!field || !field.value) {
                    isValid = false;
                    console.log('Missing field:', fieldName);
                    if (field) field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                showAlert('danger', 'Please fill in all required fields.');
                return false;
            }

            // Confirm bulk enrollment
            if (checkedCount > 1) {
                const confirmed = confirm(`You are about to enroll ${checkedCount} students. Continue?`);
                if (!confirmed) {
                    return false;
                }
            }

            // Collect form data
            const formData = new FormData();
            
            // Add CSRF token
            const csrfToken = document.querySelector('input[name="csrf_test_name"]');
            if (csrfToken) {
                formData.append('csrf_test_name', csrfToken.value);
            }
            
            // Add course offering ID
            formData.append('course_offering_id', document.querySelector('input[name="course_offering_id"]').value);
            
            // Add selected student IDs
            checkedBoxes.forEach(function(checkbox) {
                formData.append('student_ids[]', checkbox.value);
            });
            
            // Add other form fields
            formData.append('enrollment_date', document.getElementById('enrollment_date').value);
            formData.append('enrollment_status', document.getElementById('enrollment_status').value);
            formData.append('enrollment_type', document.getElementById('enrollment_type').value);
            formData.append('payment_status', document.getElementById('payment_status').value);
            formData.append('notes', document.getElementById('notes').value || '');

            console.log('Submitting enrollment via AJAX...');
            
            // Disable submit button and show loading
            const enrollButton = document.getElementById('enrollButton');
            const originalButtonText = enrollButton.innerHTML;
            enrollButton.disabled = true;
            enrollButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enrolling...';

            // Send AJAX request
            fetch('<?= base_url('teacher/ajax_enroll_students') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    showAlert('success', data.message);
                    
                    // Remove enrolled students from the table
                    if (data.data && data.data.enrolled_count > 0) {
                        checkedBoxes.forEach(function(checkbox) {
                            // Check if this student was enrolled (not skipped)
                            const row = checkbox.closest('tr');
                            if (row) {
                                row.style.transition = 'opacity 0.5s';
                                row.style.opacity = '0';
                                setTimeout(() => row.remove(), 500);
                            }
                        });
                        
                        // Update the count after removal
                        setTimeout(() => {
                            updateSelectedCount();
                            
                            // Check if no students left
                            const remainingStudents = document.querySelectorAll('.student-checkbox');
                            if (remainingStudents.length === 0) {
                                location.reload(); // Reload to show "no students" message
                            }
                        }, 600);
                    }
                } else {
                    showAlert('danger', data.message || 'Failed to enroll students.');
                }
                
                // Re-enable button
                enrollButton.disabled = false;
                enrollButton.innerHTML = originalButtonText;
                updateSelectedCount();
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                showAlert('danger', 'An error occurred while processing your request. Please try again.');
                
                // Re-enable button
                enrollButton.disabled = false;
                enrollButton.innerHTML = originalButtonText;
            });
        });
    } else {
        console.log('Form NOT found!');
    }
    
    // Helper function to show alerts
    function showAlert(type, message) {
        // Remove any existing alerts
        const existingAlerts = document.querySelectorAll('.ajax-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show ajax-alert`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at the top of the container
        const container = document.querySelector('.container.py-4');
        const firstRow = container.querySelector('.row.mb-4');
        if (firstRow && firstRow.nextElementSibling) {
            container.insertBefore(alertDiv, firstRow.nextElementSibling);
        } else {
            container.prepend(alertDiv);
        }
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 5000);
    }

    // Initialize count
    updateSelectedCount();
});
</script>

<style>
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

.student-row:hover {
    background-color: #f8f9fa;
}

.student-row {
    cursor: pointer;
}

.student-row td {
    vertical-align: middle;
}
</style>