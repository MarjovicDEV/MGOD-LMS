<?= $this->include('templates/header') ?>

<style>
    .lms-admin-view {
        --brand-primary: #2563eb;
        --brand-soft: #eef4ff;
        --page-bg: #f8fafc;
        --surface: #ffffff;
        --surface-soft: #f8fbff;
        --text-main: #0f172a;
        --text-soft: #475569;
        --border-soft: #dbe4ef;
        --hover-soft: #f4f7fb;
        background-color: var(--page-bg);
        color: var(--text-main);
    }

    .lms-admin-view .card {
        border: 1px solid var(--border-soft) !important;
        border-radius: 12px;
        background-color: var(--surface) !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    }

    .lms-admin-view .admin-hero {
        background-color: var(--surface-soft) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-soft);
    }

    .lms-admin-view .admin-hero .opacity-75 {
        opacity: 1 !important;
        color: var(--text-soft) !important;
    }

    .lms-admin-view .admin-stats .card,
    .lms-admin-view .card.text-white {
        background-color: var(--surface) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-soft) !important;
    }

    .lms-admin-view .admin-stats .display-4 {
        display: none;
    }

    .lms-admin-view .admin-stats .display-5 {
        font-size: 2rem;
        margin-bottom: 0.35rem;
    }

    .lms-admin-view .card-header.bg-success,
    .lms-admin-view .card-header.bg-warning,
    .lms-admin-view .card-header.bg-primary,
    .lms-admin-view .card-header.bg-info {
        background-color: var(--surface-soft) !important;
        color: var(--text-main) !important;
        border-bottom: 1px solid var(--border-soft) !important;
    }

    .lms-admin-view .btn-success,
    .lms-admin-view .btn-warning,
    .lms-admin-view .btn-primary {
        background-color: var(--brand-primary) !important;
        border-color: var(--brand-primary) !important;
        color: #ffffff !important;
    }

    .lms-admin-view .btn-light,
    .lms-admin-view .btn-secondary,
    .lms-admin-view .btn-outline-secondary {
        background-color: #ffffff !important;
        border-color: var(--border-soft) !important;
        color: var(--text-main) !important;
    }

    .lms-admin-view .table thead th {
        background-color: var(--surface-soft) !important;
        color: var(--text-main) !important;
        border-bottom: 1px solid var(--border-soft) !important;
        font-size: 0.82rem;
    }

    .lms-admin-view .table tbody td {
        font-size: 0.84rem;
        color: var(--text-main);
    }

    .lms-admin-view .table-hover > tbody > tr:hover > * {
        background-color: var(--hover-soft) !important;
    }

    .lms-admin-view .form-control,
    .lms-admin-view .form-select,
    .lms-admin-view .input-group-text {
        border-color: var(--border-soft);
        font-size: 0.86rem;
    }

    .lms-admin-view .form-control:focus,
    .lms-admin-view .form-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.12);
    }

    .lms-admin-view .text-muted,
    .lms-admin-view small,
    .lms-admin-view .form-text {
        color: var(--text-soft) !important;
    }
</style>

<!-- Manage Course Prerequisites View - Admin only functionality for prerequisite management -->
<div class="lms-admin-view min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3 admin-hero">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">Manage Course Prerequisites</h2>
                                <p class="mb-0 opacity-75">Define prerequisite relationships between courses</p>
                            </div>
                            <div>
                                <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-light btn-sm">
                                    Back to Dashboard
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
                <strong>Success!</strong> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Prerequisite Statistics Cards -->
        <div class="row mb-4 admin-stats">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-5 fw-bold"><?= $statistics['total'] ?></div>
                    <div class="fw-semibold">Total Prerequisites</div>
                    <small class="opacity-75">In the system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-danger text-center p-4 rounded-3 h-100">
                    <div class="display-5 fw-bold"><?= $statistics['required'] ?></div>
                    <div class="fw-semibold">Required</div>
                    <small class="opacity-75">Must be completed</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-5 fw-bold"><?= $statistics['recommended'] ?></div>
                    <div class="fw-semibold">Recommended</div>
                    <small class="opacity-75">Suggested courses</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-5 fw-bold"><?= $statistics['corequisite'] ?></div>
                    <div class="fw-semibold">Corequisites</div>
                    <small class="opacity-75">Take together</small>
                </div>
            </div>
        </div>

        <!-- Course Filter and Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="courseFilter" class="form-label fw-semibold mb-2">Filter by Course:</label>
                                <select class="form-select" id="courseFilter" onchange="filterByCourse(this.value)">
                                    <option value="">All Courses</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= $course['id'] ?>" <?= $selectedCourseId == $course['id'] ? 'selected' : '' ?>>
                                            <?= esc($course['course_code']) ?> - <?= esc($course['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <a href="<?= base_url('admin/manage_prerequisites?action=create' . ($selectedCourseId ? '&course_id=' . $selectedCourseId : '')) ?>" class="btn btn-success">
                                    Add Prerequisite
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Course Info -->
        <?php if ($selectedCourse): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-primary">
                    <div class="card-body bg-light">
                        <h5 class="mb-2 fw-bold text-primary">Current Course:</h5>
                        <h4 class="mb-1"><?= esc($selectedCourse['course_code']) ?> - <?= esc($selectedCourse['title']) ?></h4>
                        <p class="text-muted mb-0">
                            <strong>Credits:</strong> <?= $selectedCourse['credits'] ?> | 
                            <strong>Prerequisites:</strong> <?= count($prerequisites) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Create Prerequisite Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">Add Course Prerequisite</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_prerequisites?action=create') ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="course_id" class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                                        <select class="form-select" id="course_id" name="course_id" required>
                                            <option value="">Select Course</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?= $course['id'] ?>" <?= old('course_id', $selectedCourseId) == $course['id'] ? 'selected' : '' ?>>
                                                    <?= esc($course['course_code']) ?> - <?= esc($course['title']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">The course that has the prerequisite requirement</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="prerequisite_course_id" class="form-label fw-semibold">Prerequisite Course <span class="text-danger">*</span></label>
                                        <select class="form-select" id="prerequisite_course_id" name="prerequisite_course_id" required>
                                            <option value="">Select Prerequisite Course</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?= $course['id'] ?>" <?= old('prerequisite_course_id') == $course['id'] ? 'selected' : '' ?>>
                                                    <?= esc($course['course_code']) ?> - <?= esc($course['title']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">The course that must be completed first</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="prerequisite_type" class="form-label fw-semibold">Prerequisite Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="prerequisite_type" name="prerequisite_type" required>
                                            <option value="">Select Type</option>
                                            <option value="required" <?= old('prerequisite_type') == 'required' ? 'selected' : '' ?>>Required - Must be completed</option>
                                            <option value="recommended" <?= old('prerequisite_type') == 'recommended' ? 'selected' : '' ?>>Recommended - Suggested but not mandatory</option>
                                            <option value="corequisite" <?= old('prerequisite_type') == 'corequisite' ? 'selected' : '' ?>>Corequisite - Take at the same time</option>
                                        </select>
                                    </div>
                                </div>                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="minimum_grade" class="form-label fw-semibold">Minimum Grade <small class="text-muted">(optional)</small></label>
                                        <input type="number" class="form-control" id="minimum_grade" name="minimum_grade" 
                                               value="<?= old('minimum_grade', '75') ?>" 
                                               step="1" min="75" max="100"
                                               placeholder="e.g., 75">
                                        <small class="text-muted">Minimum grade required to satisfy prerequisite (75-100 scale, 75 = passing)</small>
                                    </div>
                                </div>                            </div>
                            <div class="alert alert-info mb-3">
                                <strong>Grading Scale:</strong> The system uses a 75-100 grading scale where 75 is the passing score. Set the minimum grade a student must achieve in the prerequisite course to enroll in the main course.
                            </div>
                            <div class="alert alert-warning mb-3">
                                <strong>Note:</strong> The system will automatically check for circular dependencies to prevent infinite prerequisite chains.
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-success">
                                    Add Prerequisite
                                </button>
                                <a href="<?= base_url('admin/manage_prerequisites' . ($selectedCourseId ? '?course_id=' . $selectedCourseId : '')) ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Prerequisite Form (shown when action=edit) -->
        <?php if ($showEditForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-white border-0">
                        <h5 class="mb-0">Edit Prerequisite</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <strong>Course:</strong> <?= esc($course['course_code']) ?> - <?= esc($course['title']) ?><br>
                            <strong>Prerequisite:</strong> <?= esc($prerequisiteCourse['course_code']) ?> - <?= esc($prerequisiteCourse['title']) ?>
                        </div>
                        <form method="post" action="<?= base_url('admin/manage_prerequisites?action=edit&id=' . $editPrerequisite['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="prerequisite_type" class="form-label fw-semibold">Prerequisite Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="prerequisite_type" name="prerequisite_type" required>
                                            <option value="">Select Type</option>
                                            <option value="required" <?= old('prerequisite_type', $editPrerequisite['prerequisite_type']) == 'required' ? 'selected' : '' ?>>Required - Must be completed</option>
                                            <option value="recommended" <?= old('prerequisite_type', $editPrerequisite['prerequisite_type']) == 'recommended' ? 'selected' : '' ?>>Recommended - Suggested but not mandatory</option>
                                            <option value="corequisite" <?= old('prerequisite_type', $editPrerequisite['prerequisite_type']) == 'corequisite' ? 'selected' : '' ?>>Corequisite - Take at the same time</option>
                                        </select>
                                    </div>
                                </div>                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="minimum_grade" class="form-label fw-semibold">Minimum Grade <small class="text-muted">(optional)</small></label>
                                        <input type="number" class="form-control" id="minimum_grade" name="minimum_grade" 
                                               value="<?= old('minimum_grade', $editPrerequisite['minimum_grade'] ?? '75') ?>" 
                                               step="1" min="75" max="100"
                                               placeholder="e.g., 75">
                                        <small class="text-muted">Minimum grade required to satisfy prerequisite (75-100 scale, 75 = passing)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-warning text-white">
                                    Update Prerequisite
                                </button>
                                <a href="<?= base_url('admin/manage_prerequisites?course_id=' . $editPrerequisite['course_id']) ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Prerequisites Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">Prerequisites List</h5>
                            <div class="text-muted small">
                                Total: <?= count($prerequisites) ?> prerequisites
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold border-0 text-center">ID</th>
                                        <th class="fw-semibold border-0">Course</th>
                                        <th class="fw-semibold border-0">Prerequisite Course</th>
                                        <th class="fw-semibold border-0 text-center">Type</th>
                                        <th class="fw-semibold border-0 text-center">Min. Grade</th>
                                        <th class="fw-semibold border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($prerequisites)): ?>
                                        <?php foreach ($prerequisites as $prereq): ?>
                                        <tr class="border-bottom">
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">#<?= $prereq['id'] ?></span>
                                            </td>
                                            <td>
                                                <strong><?= esc($prereq['course_code'] ?? $selectedCourse['course_code']) ?></strong><br>
                                                <small class="text-muted"><?= esc($prereq['course_title'] ?? $selectedCourse['title']) ?></small>
                                            </td>
                                            <td>
                                                <strong><?= esc($prereq['prereq_course_code'] ?? $prereq['course_code']) ?></strong><br>
                                                <small class="text-muted"><?= esc($prereq['prereq_course_title'] ?? $prereq['title']) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($prereq['prerequisite_type'] == 'required'): ?>
                                                    <span class="badge bg-danger">Required</span>
                                                <?php elseif ($prereq['prerequisite_type'] == 'recommended'): ?>
                                                    <span class="badge bg-warning">Recommended</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Corequisite</span>
                                                <?php endif; ?>
                                            </td>                                            <td class="text-center">
                                                <?= $prereq['minimum_grade'] ? number_format($prereq['minimum_grade'], 0) : '<span class="text-muted">N/A</span>' ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('admin/manage_prerequisites?action=edit&id=' . $prereq['id']) ?>" 
                                                       class="btn btn-sm btn-warning text-white" 
                                                       title="Edit Prerequisite Requirement"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="top">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                    <a href="<?= base_url('admin/manage_prerequisites?action=delete&id=' . $prereq['id']) ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       title="Delete Prerequisite"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       onclick="return confirm('Are you sure you want to delete this prerequisite relationship? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <h5>No prerequisites found</h5>
                                                <p class="mb-0">
                                                    <?php if ($selectedCourseId): ?>
                                                        This course has no prerequisites yet. Click "Add Prerequisite" to add one.
                                                    <?php else: ?>
                                                        No prerequisites defined in the system. Select a course above or click "Add Prerequisite" to get started.
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
function filterByCourse(courseId) {
    if (courseId) {
        window.location.href = '<?= base_url('admin/manage_prerequisites') ?>?course_id=' + courseId;
    } else {
        window.location.href = '<?= base_url('admin/manage_prerequisites') ?>';
    }
}
// Client-side validation to prevent self-prerequisite
document.addEventListener('DOMContentLoaded', function() {
    const createForm = document.querySelector('form[action*="manage_prerequisites"][method="post"]');
    
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            const courseId = document.querySelector('select[name="course_id"]');
            const prerequisiteCourseId = document.querySelector('select[name="prerequisite_course_id"]');
            
            if (courseId && prerequisiteCourseId) {
                if (courseId.value === prerequisiteCourseId.value && courseId.value !== '') {
                    e.preventDefault();
                    alert('Error: A course cannot be its own prerequisite.\n\nPlease select a different prerequisite course.');
                    prerequisiteCourseId.focus();
                    return false;
                }
            }
        });
        
        // Also add real-time validation feedback
        const courseSelect = document.querySelector('select[name="course_id"]');
        const prereqSelect = document.querySelector('select[name="prerequisite_course_id"]');
        
        if (courseSelect && prereqSelect) {
            function checkSelfPrerequisite() {
                if (courseSelect.value === prereqSelect.value && courseSelect.value !== '') {
                    prereqSelect.classList.add('is-invalid');
                    
                    // Add or update error message
                    let errorDiv = prereqSelect.parentElement.querySelector('.invalid-feedback');
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        prereqSelect.parentElement.appendChild(errorDiv);
                    }
                    errorDiv.textContent = 'A course cannot be its own prerequisite.';
                } else {
                    prereqSelect.classList.remove('is-invalid');
                    const errorDiv = prereqSelect.parentElement.querySelector('.invalid-feedback');
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                }
            }
            
            courseSelect.addEventListener('change', checkSelfPrerequisite);
            prereqSelect.addEventListener('change', checkSelfPrerequisite);
        }
    }
});
</script>



