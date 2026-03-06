<?= $this->include('templates/header') ?>

<!-- Teacher Courses View - Shows courses assigned by admin -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold">📚 Courses I Teach</h3>
                                <p class="mb-0 opacity-75">Courses assigned to me by the administrator</p>
                            </div>
                            <div class="text-end">
                                <i class="fas fa-chalkboard-teacher" style="font-size: 3rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📖</div>
                    <div class="display-5 fw-bold"><?= count($assignedCourses) ?></div>
                    <div class="fw-semibold">Total Courses</div>
                    <small class="opacity-75">Assigned to me</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">👥</div>
                    <div class="display-5 fw-bold"><?= array_sum(array_column($assignedCourses, 'enrolled_students')) ?></div>
                    <div class="fw-semibold">Total Students</div>
                    <small class="opacity-75">Across all courses</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">⭐</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($assignedCourses, fn($c) => $c['is_primary'] == 1)) ?></div>
                    <div class="fw-semibold">Primary Instructor</div>
                    <small class="opacity-75">Lead courses</small>
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

        <!-- Search Box -->
        <?php if (!empty($assignedCourses)): ?>
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
                                           id="courseSearchInput" 
                                           class="form-control border-start-0" 
                                           placeholder="🔍 Search courses by code, title, category, term, or academic year...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2 mt-md-0">
                                <div class="text-muted">
                                    <small>
                                        <strong id="searchResultCount"><?= count($assignedCourses) ?></strong> course(s) found
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Courses List -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">📋 My Assigned Courses</h5>
                                <small class="text-muted">Courses I'm currently teaching</small>
                            </div>
                            <div class="text-muted small">
                                Total: <?= count($assignedCourses) ?> course<?= count($assignedCourses) !== 1 ? 's' : '' ?>
                            </div>
                        </div>
                    </div>                    <div class="card-body pt-0">
                        <?php if (!empty($assignedCourses)): ?>
                            <div class="row" id="coursesContainer">
                                <?php foreach ($assignedCourses as $course): ?>
                                <div class="col-lg-6 mb-4 course-item"
                                     data-course-code="<?= esc(strtolower($course['course_code'])) ?>"
                                     data-title="<?= esc(strtolower($course['title'])) ?>"
                                     data-description="<?= esc(strtolower($course['description'] ?? '')) ?>"
                                     data-category="<?= esc(strtolower($course['category'] ?? '')) ?>"
                                     data-term="<?= esc(strtolower($course['term_name'] ?? '')) ?>"
                                     data-academic-year="<?= esc(strtolower($course['academic_year'] ?? '')) ?>"
                                     data-semester="<?= esc(strtolower($course['semester_name'] ?? '')) ?>">
                                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                                        <!-- Card Header -->
                                        <div class="card-header bg-primary text-white border-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1 fw-bold"><?= esc($course['course_code']) ?></h5>
                                                    <p class="mb-0 small opacity-75"><?= esc($course['title']) ?></p>
                                                </div>
                                                <div>
                                                    <?php if ($course['is_primary'] == 1): ?>
                                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                                            <i class="fas fa-star me-1"></i>Primary
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                                                            <i class="fas fa-user-tie me-1"></i>Co-Instructor
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Card Body -->
                                        <div class="card-body">
                                            <!-- Course Details -->
                                            <div class="mb-3">
                                                <!-- Badges -->
                                                <div class="mb-3">
                                                    <?php if ($course['category']): ?>
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="fas fa-tag me-1"></i><?= esc($course['category']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($course['academic_year']): ?>
                                                        <span class="badge bg-primary ms-1">
                                                            <i class="fas fa-calendar me-1"></i><?= esc($course['academic_year']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($course['semester_name']): ?>
                                                        <span class="badge bg-info ms-1">
                                                            <i class="fas fa-calendar-alt me-1"></i><?= esc($course['semester_name']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($course['term_name']): ?>
                                                        <span class="badge bg-secondary ms-1">
                                                            <?= esc($course['term_name']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Course Info Grid -->
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <div class="p-2 bg-light rounded-3 text-center">
                                                            <div class="text-muted small mb-1"><i class="fas fa-book-open me-1"></i>Credits</div>
                                                            <div class="fw-bold text-dark"><?= $course['credits'] ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="p-2 bg-light rounded-3 text-center">
                                                            <div class="text-muted small mb-1"><i class="fas fa-users me-1"></i>Max Students</div>
                                                            <div class="fw-bold text-dark"><?= $course['max_students'] ?? 'N/A' ?></div>
                                                        </div>
                                                    </div>
                                                    <?php if ($course['start_date']): ?>
                                                    <div class="col-6">
                                                        <div class="p-2 bg-light rounded-3 text-center">
                                                            <div class="text-muted small mb-1"><i class="fas fa-calendar-check me-1"></i>Start Date</div>
                                                            <div class="fw-bold text-dark"><?= date('M j, Y', strtotime($course['start_date'])) ?></div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if ($course['end_date']): ?>
                                                    <div class="col-6">
                                                        <div class="p-2 bg-light rounded-3 text-center">
                                                            <div class="text-muted small mb-1"><i class="fas fa-calendar-times me-1"></i>End Date</div>
                                                            <div class="fw-bold text-dark"><?= date('M j, Y', strtotime($course['end_date'])) ?></div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Enrolled Students -->
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <h6 class="fw-bold text-primary mb-0">
                                                        <i class="fas fa-users me-2"></i>Enrolled Students
                                                    </h6>
                                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                                        <?= $course['enrolled_students'] ?> Student<?= $course['enrolled_students'] !== 1 ? 's' : '' ?>
                                                    </span>
                                                </div>
                                                
                                                <?php if ($course['enrolled_students'] > 0 && !empty($course['students'])): ?>
                                                    <div class="bg-light p-3 rounded-3">
                                                        <div class="student-list" style="max-height: 300px; overflow-y: auto;">
                                                            <?php foreach ($course['students'] as $student): ?>
                                                                <div class="student-item mb-2 p-2 bg-white rounded border d-flex align-items-center">
                                                                    <div class="student-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                                         style="width: 35px; height: 35px; min-width: 35px;">
                                                                        <i class="fas fa-user"></i>
                                                                    </div>                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-semibold text-dark small"><?= esc($student['full_name']) ?></div>
                                                                        <div class="text-muted" style="font-size: 0.75rem;">
                                                                            <i class="fas fa-envelope me-1"></i><?= esc($student['email']) ?>
                                                                        </div>
                                                                    </div>
                                                                    <span class="badge bg-success rounded-pill">
                                                                        <?= ucfirst($student['enrollment_status']) ?>
                                                                    </span>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center py-3 bg-light rounded-3">
                                                        <i class="fas fa-user-graduate text-muted mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                                        <p class="text-muted mb-0 small">No students enrolled yet</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Co-Instructors -->
                                            <?php if (!empty($course['co_instructors'])): ?>
                                            <div class="mb-3">
                                                <h6 class="fw-semibold text-info mb-2">
                                                    <i class="fas fa-user-tie me-2"></i>Co-Instructors
                                                </h6>
                                                <div class="d-flex flex-wrap gap-1">                                                    <?php foreach ($course['co_instructors'] as $coInstructor): ?>
                                                        <span class="badge bg-info text-white rounded-pill" title="<?= esc($coInstructor['email']) ?>">
                                                            <i class="fas fa-user-tie me-1"></i><?= esc($coInstructor['full_name']) ?>
                                                            <?php if ($coInstructor['is_primary'] == 1): ?>
                                                                <i class="fas fa-star ms-1"></i>
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Course Description -->
                                            <?php if ($course['description']): ?>
                                            <div class="mb-3">
                                                <h6 class="fw-semibold text-secondary mb-2">
                                                    <i class="fas fa-info-circle me-2"></i>Description
                                                </h6>
                                                <p class="small text-muted mb-0"><?= esc($course['description']) ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Card Footer -->
                                        <div class="card-footer bg-light border-0">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    Assigned: <?= date('M j, Y', strtotime($course['assigned_date'])) ?>
                                                </small>
                                                <small class="text-muted">
                                                    Status: 
                                                    <?php
                                                    $statusBadges = [
                                                        'open' => '<span class="badge bg-success">Open</span>',
                                                        'closed' => '<span class="badge bg-secondary">Closed</span>',
                                                        'draft' => '<span class="badge bg-warning text-dark">Draft</span>',
                                                        'archived' => '<span class="badge bg-dark">Archived</span>'
                                                    ];
                                                    echo $statusBadges[$course['offering_status']] ?? '<span class="badge bg-secondary">'.ucfirst($course['offering_status']).'</span>';
                                                    ?>
                                                </small>                                            </div>
                                              <!-- Action Buttons -->
                                            <div class="d-flex gap-2">
                                                <button type="button" 
                                                   class="btn btn-primary btn-sm flex-fill view-course-btn"
                                                   data-offering-id="<?= $course['offering_id'] ?>">
                                                    <i class="fas fa-eye me-1"></i>View Course
                                                </button>
                                                <a href="<?= base_url('teacher/course/' . $course['offering_id'] . '/upload') ?>" 
                                                   class="btn btn-outline-primary btn-sm flex-fill"
                                                   title="Manage Course Materials">
                                                    <i class="fas fa-folder-open me-1"></i>Course Materials
                                                </a>
                                            </div>
                                        </div>
                                    </div>                                
                                </div>
                                <?php endforeach; ?>
                                
                                <!-- No Results Message -->
                                <div class="col-12" id="noResultsMessage" style="display: none;">
                                    <div class="text-center py-5">
                                        <div class="mb-4">
                                            <i class="fas fa-search text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                        </div>
                                        <h5 class="text-muted mb-3">No Courses Found</h5>
                                        <p class="text-muted mb-4">
                                            No courses match your search criteria.<br>
                                            Try adjusting your search terms.
                                        </p>
                                        <button type="button" class="btn btn-primary" onclick="$('#courseSearchInput').val(''); $('#courseSearchInput').trigger('keyup');">
                                            <i class="fas fa-redo me-2"></i>Clear Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5" id="noCoursesMessage">
                                <div class="mb-4">
                                    <i class="fas fa-chalkboard-teacher text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
                                </div>
                                <h5 class="text-muted mb-3">No Courses Assigned Yet</h5>
                                <p class="text-muted mb-4">
                                    You haven't been assigned to any courses yet.<br>
                                    Please contact the administrator to get course assignments.
                                </p>
                                <a href="<?= base_url('teacher/dashboard') ?>" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>    </div>
</div>

<!-- Course Details Modal -->
<div class="modal fade" id="courseDetailsModal" tabindex="-1" aria-labelledby="courseDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="courseDetailsModalLabel">
                    <i class="fas fa-eye me-2"></i>Course Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="courseDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading course details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('.view-course-btn');
    const modal = new bootstrap.Modal(document.getElementById('courseDetailsModal'));
    const modalContent = document.getElementById('courseDetailsContent');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const offeringId = this.getAttribute('data-offering-id');
            
            // Show modal with loading state
            modalContent.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading course details...</p>
                </div>
            `;
            modal.show();
            
            // Fetch course details
            fetch('<?= base_url('teacher/course/') ?>' + offeringId + '/view', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayCourseDetails(data);
                } else {
                    modalContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Failed to load course details. Please try again.
                    </div>
                `;
            });
        });
    });
    
    function displayCourseDetails(data) {
        const course = data.course;
        const students = data.students;
        const coInstructors = data.coInstructors;
        const materialCount = data.materialCount;
        const isPrimary = data.isPrimary;
        
        // Status badge colors
        const statusColors = {
            'draft': 'warning',
            'open': 'success',
            'closed': 'secondary',
            'completed': 'info',
            'cancelled': 'danger'
        };
        
        let html = `
            <!-- Course Header -->
            <div class="card border-0 mb-4 bg-light">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2 fw-bold text-primary">
                                <i class="fas fa-book-open me-2"></i>${course.course_code}
                            </h3>
                            <h5 class="mb-3">${course.title}</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-primary px-3 py-2">
                                    <i class="fas fa-calendar me-1"></i>${course.academic_year}
                                </span>
                                <span class="badge bg-info px-3 py-2">
                                    <i class="fas fa-calendar-alt me-1"></i>${course.semester_name}
                                </span>
                                <span class="badge bg-secondary px-3 py-2">
                                    ${course.term_name}
                                </span>
                                <span class="badge bg-${statusColors[course.status] || 'secondary'} px-3 py-2">
                                    <i class="fas fa-circle me-1"></i>${course.status.toUpperCase()}
                                </span>
                                ${isPrimary ? '<span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-star me-1"></i>Primary Instructor</span>' : ''}
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex flex-column gap-2">
                                <a href="<?= base_url('teacher/course/') ?>${course.id}/upload" class="btn btn-primary">
                                    <i class="fas fa-folder-open me-2"></i>Course Materials (${materialCount})
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Course Information -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>Course Information
                            </h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 40%;"><i class="fas fa-code me-2"></i>Course Code:</td>
                                    <td class="fw-semibold">${course.course_code}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-graduation-cap me-2"></i>Credits:</td>
                                    <td class="fw-semibold">${course.credits}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-chalkboard-teacher me-2"></i>Lecture Hours:</td>
                                    <td class="fw-semibold">${course.lecture_hours || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-flask me-2"></i>Lab Hours:</td>
                                    <td class="fw-semibold">${course.lab_hours || 'N/A'}</td>
                                </tr>
                                ${course.category_name ? `
                                <tr>
                                    <td class="text-muted"><i class="fas fa-tag me-2"></i>Category:</td>
                                    <td class="fw-semibold">${course.category_name}</td>
                                </tr>
                                ` : ''}
                                ${course.department_name ? `
                                <tr>
                                    <td class="text-muted"><i class="fas fa-building me-2"></i>Department:</td>
                                    <td class="fw-semibold">${course.department_name}</td>
                                </tr>
                                ` : ''}
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="fas fa-calendar-check me-2"></i>Offering Details
                            </h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 40%;"><i class="fas fa-section me-2"></i>Section:</td>
                                    <td class="fw-semibold">${course.section || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-door-open me-2"></i>Room:</td>
                                    <td class="fw-semibold">${course.room || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-users me-2"></i>Max Students:</td>
                                    <td class="fw-semibold">${course.max_students}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-user-check me-2"></i>Enrolled:</td>
                                    <td class="fw-semibold">${course.enrolled_count} / ${course.max_students}</td>
                                </tr>
                                ${course.start_date ? `
                                <tr>
                                    <td class="text-muted"><i class="fas fa-calendar-day me-2"></i>Start Date:</td>
                                    <td class="fw-semibold">${new Date(course.start_date).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'})}</td>
                                </tr>
                                ` : ''}
                                ${course.end_date ? `
                                <tr>
                                    <td class="text-muted"><i class="fas fa-calendar-times me-2"></i>End Date:</td>
                                    <td class="fw-semibold">${new Date(course.end_date).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'})}</td>
                                </tr>
                                ` : ''}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Course Description -->
            ${course.description ? `
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold text-secondary mb-3">
                        <i class="fas fa-align-left me-2"></i>Course Description
                    </h6>
                    <p class="mb-0 text-muted">${course.description}</p>
                </div>
            </div>
            ` : ''}
            
            <!-- Co-Instructors -->
            ${coInstructors.length > 0 ? `
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold text-info mb-3">
                        <i class="fas fa-user-tie me-2"></i>Co-Instructors (${coInstructors.length})
                    </h6>
                    <div class="row">
                        ${coInstructors.map(instructor => `
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center p-2 bg-light rounded">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">${instructor.full_name}</div>
                                        <div class="small text-muted">
                                            <i class="fas fa-envelope me-1"></i>${instructor.email}
                                        </div>
                                    </div>
                                    ${instructor.is_primary ? '<span class="badge bg-warning text-dark"><i class="fas fa-star"></i></span>' : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
            ` : ''}
            
            <!-- Enrolled Students -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="fas fa-users me-2"></i>Enrolled Students (${students.length})
                    </h6>
                    ${students.length > 0 ? `
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 15%;">ID Number</th>
                                        <th style="width: 25%;">Name</th>
                                        <th style="width: 25%;">Email</th>
                                        <th style="width: 15%;">Program</th>
                                        <th style="width: 10%;">Year Level</th>
                                        <th style="width: 5%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${students.map((student, index) => `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td><span class="badge bg-light text-dark">${student.student_id_number}</span></td>
                                            <td>${student.full_name}</td>
                                            <td><small>${student.email}</small></td>
                                            <td><span class="badge bg-primary">${student.program_code || 'N/A'}</span></td>
                                            <td>${student.year_level_name || 'N/A'}</td>
                                            <td><span class="badge bg-success rounded-pill">✓</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : `
                        <div class="text-center py-4">
                            <i class="fas fa-user-graduate text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mb-0">No students enrolled yet</p>
                        </div>
                    `}
                </div>
            </div>
        `;
        
        modalContent.innerHTML = html;
    }
});
</script>

<!-- Course Search JavaScript -->
<script>
$(document).ready(function() {
    // Course search functionality with AJAX
    let searchTimeout;
    
    function filterCourses() {
        const searchTerm = $('#courseSearchInput').val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // If search is empty, show all courses
        if (searchTerm === '') {
            $('.course-item').show();
            $('#searchResultCount').text($('.course-item').length);
            $('#noResultsMessage').hide();
            return;
        }

        searchTimeout = setTimeout(function() {
            // Show loading state
            $('#courseSearchInput').prop('disabled', true);
            
            // Make AJAX request
            $.ajax({
                url: '<?= base_url('teacher/search_courses') ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    search: searchTerm,
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.success) {
                        // Hide all courses first
                        $('.course-item').hide();
                        
                        // Show matching courses
                        let visibleCount = 0;
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(course) {
                                // Find matching course card by offering_id
                                $('.course-item').each(function() {
                                    const courseCode = $(this).data('course-code');
                                    const searchableCourseCode = course.course_code.toLowerCase();
                                    
                                    if (courseCode === searchableCourseCode) {
                                        $(this).show();
                                        visibleCount++;
                                    }
                                });
                            });
                        }
                        
                        // Update count
                        $('#searchResultCount').text(visibleCount);
                        
                        // Show/hide no results message
                        if (visibleCount === 0) {
                            $('#noResultsMessage').show();
                        } else {
                            $('#noResultsMessage').hide();
                        }
                    } else {
                        console.error('Search error:', response.message);
                        // Fall back to client-side search
                        filterCoursesClientSide(searchTerm);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    // Fall back to client-side search
                    filterCoursesClientSide(searchTerm);
                },
                complete: function() {
                    $('#courseSearchInput').prop('disabled', false);
                }
            });
        }, 600); 
    }
    
    // Fallback client-side search function
    function filterCoursesClientSide(searchTerm) {
        const searchLower = searchTerm.toLowerCase();
        let visibleCount = 0;
        
        $('.course-item').each(function() {
            const courseCode = $(this).data('course-code') || '';
            const title = $(this).data('title') || '';
            const description = $(this).data('description') || '';
            const category = $(this).data('category') || '';
            const term = $(this).data('term') || '';
            const academicYear = $(this).data('academic-year') || '';
            const semester = $(this).data('semester') || '';
            
            const searchableText = courseCode + ' ' + title + ' ' + description + ' ' + 
                                   category + ' ' + term + ' ' + academicYear + ' ' + semester;
            
            if (searchableText.includes(searchLower)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        // Update count
        $('#searchResultCount').text(visibleCount);
        
        // Show/hide no results message
        if (visibleCount === 0 && $('.course-item').length > 0) {
            $('#noResultsMessage').show();
        } else {
            $('#noResultsMessage').hide();
        }
    }
    
    // Search on keyup
    $('#courseSearchInput').on('keyup', function() {
        filterCourses();
    });
    
    // Clear search button
    $('#clearSearch').on('click', function() {
        $('#courseSearchInput').val('');
        $('.course-item').show();
        $('#searchResultCount').text($('.course-item').length);
        $('#noResultsMessage').hide();
        $('#courseSearchInput').focus();
    });
});
</script>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.student-list::-webkit-scrollbar {
    width: 6px;
}

.student-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.student-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.student-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>



