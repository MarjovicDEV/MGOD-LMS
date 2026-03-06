<?= $this->include('templates/header') ?>

<!-- Manage Courses View - Admin only functionality for course management -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">📚 Manage Courses</h2>
                                <p class="mb-0 opacity-75">Create, edit, and manage courses in the learning management system</p>
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

        <!-- Course Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📚</div>
                    <div class="display-5 fw-bold"><?= count($courses) ?></div>
                    <div class="fw-semibold">Total Courses</div>
                    <small class="opacity-75">In the system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($courses, fn($c) => $c['is_active'] == 1)) ?></div>
                    <div class="fw-semibold">Active</div>
                    <small class="opacity-75">Currently available</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📝</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($courses, fn($c) => $c['is_active'] == 0)) ?></div>
                    <div class="fw-semibold">Inactive</div>
                    <small class="opacity-75">Not available</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-secondary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🏫</div>
                    <div class="display-5 fw-bold"><?= count(array_unique(array_filter(array_column($courses, 'department_name')))) ?></div>
                    <div class="fw-semibold">Departments</div>
                    <small class="opacity-75">With courses</small>
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
                            <h5 class="mb-0 fw-bold text-dark">⚡ Course Management</h5>
                            <a href="<?= base_url('admin/manage_courses?action=create') ?>" class="btn btn-success">
                                ➕ Create New Course
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Box -->
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
                                           placeholder="🔍 Search courses by code, title, description, department, or category...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2 mt-md-0">
                                <div class="text-muted">
                                    <small>
                                        <strong id="searchResultCount"><?= count($courses) ?></strong> course(s) found
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Course Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Create New Course</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_courses?action=create') ?>">
                            <?= csrf_field() ?>
                            
                            <!-- Basic Information Section -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="course_code" class="form-label fw-semibold">Course Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control text-uppercase" id="course_code" name="course_code" 
                                                   value="<?= old('course_code') ?>" required 
                                                   minlength="2" maxlength="20"
                                                   pattern="[A-Z]{2,10}-[0-9]{1,5}"
                                                   placeholder="e.g., CC-101, IT-201, BSIT-301">
                                            <div class="form-text text-info"><i class="fas fa-info-circle me-1"></i>Format: Uppercase letters, hyphen, then numbers (e.g., CC-101, IT-201)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="title" class="form-label fw-semibold">Course Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?= old('title') ?>" required 
                                                   minlength="3" maxlength="255" 
                                                   placeholder="e.g., Introduction to Programming">
                                            <div class="form-text text-info"><i class="fas fa-info-circle me-1"></i>Letters (including Ñ/ñ), numbers, and spaces only. No special characters.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Details Section -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-cogs me-2"></i>Course Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="credits" class="form-label fw-semibold">Credits <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="credits" name="credits" 
                                                   value="<?= old('credits', 3) ?>" required
                                                   min="1" max="12" step="1">
                                            <div class="form-text">Credit units (typically 1-6)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="lecture_hours" class="form-label fw-semibold">Lecture Hours</label>
                                            <input type="number" class="form-control" id="lecture_hours" name="lecture_hours" 
                                                   value="<?= old('lecture_hours') ?>" 
                                                   min="0" max="10" step="0.5">
                                            <div class="form-text">Hours per week (optional)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="lab_hours" class="form-label fw-semibold">Lab Hours</label>
                                            <input type="number" class="form-control" id="lab_hours" name="lab_hours" 
                                                   value="<?= old('lab_hours') ?>" 
                                                   min="0" max="10" step="0.5">
                                            <div class="form-text">Hours per week (optional)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Classification Section -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-layer-group me-2"></i>Classification
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="department_id" class="form-label fw-semibold">Department</label>
                                            <select class="form-select" id="department_id" name="department_id">
                                                <option value="">-- Select Department --</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>" <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>>
                                                        <?= esc($dept['department_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Optional: Select department</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label fw-semibold">Category</label>
                                            <select class="form-select" id="category_id" name="category_id">
                                                <option value="">-- Select Category --</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?= $cat['id'] ?>" <?= old('category_id') == $cat['id'] ? 'selected' : '' ?>>
                                                        <?= esc($cat['category_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Optional: Select category</div>
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
                                            <div class="form-text">Optional: Recommended year level</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-align-left me-2"></i>Description
                                </h6>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label fw-semibold">Description</label>
                                            <textarea class="form-control" 
                                                      id="description" 
                                                      name="description" 
                                                      rows="4" 
                                                      placeholder="Enter course description, learning objectives, and prerequisites..."><?= old('description') ?></textarea>
                                            <div class="form-text">Optional: Provide detailed course information</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Section -->
                            <div class="mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-toggle-on me-2"></i>Status
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active', 1) ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-semibold" for="is_active">
                                                Active (Available for enrollment)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    💾 Create Course
                                </button>
                                <a href="<?= base_url('admin/manage_courses') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Course Form (shown when editing) -->
        <?php if ($showEditForm && $editCourse): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-dark border-0">
                        <h5 class="mb-0">✏️ Edit Course: <?= esc($editCourse['title']) ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_courses?action=edit&id=' . $editCourse['id']) ?>">
                            <?= csrf_field() ?>
                            
                            <!-- Basic Information Section -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_course_code" class="form-label fw-semibold">Course Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control text-uppercase" id="edit_course_code" name="course_code" 
                                                   value="<?= old('course_code', $editCourse['course_code']) ?>" required 
                                                   minlength="2" maxlength="20"
                                                   pattern="[A-Z]{2,10}-[0-9]{1,5}">
                                            <div class="form-text text-info"><i class="fas fa-info-circle me-1"></i>Format: Uppercase letters, hyphen, then numbers (e.g., CC-101, IT-201)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_title" class="form-label fw-semibold">Course Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="edit_title" name="title" 
                                                   value="<?= old('title', $editCourse['title']) ?>" required 
                                                   minlength="3" maxlength="255">
                                            <div class="form-text text-info"><i class="fas fa-info-circle me-1"></i>Letters (including Ñ/ñ), numbers, and spaces only. No special characters.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Details Section -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-cogs me-2"></i>Course Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_credits" class="form-label fw-semibold">Credits <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="edit_credits" name="credits" 
                                                   value="<?= old('credits', $editCourse['credits']) ?>" required
                                                   min="1" max="12">
                                            <div class="form-text">Credit units</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_lecture_hours" class="form-label fw-semibold">Lecture Hours</label>
                                            <input type="number" class="form-control" id="edit_lecture_hours" name="lecture_hours" 
                                                   value="<?= old('lecture_hours', $editCourse['lecture_hours']) ?>" 
                                                   min="0" max="10" step="0.5">
                                            <div class="form-text">Hours per week</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_lab_hours" class="form-label fw-semibold">Lab Hours</label>
                                            <input type="number" class="form-control" id="edit_lab_hours" name="lab_hours" 
                                                   value="<?= old('lab_hours', $editCourse['lab_hours']) ?>" 
                                                   min="0" max="10" step="0.5">
                                            <div class="form-text">Hours per week</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Classification Section -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-layer-group me-2"></i>Classification
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_department_id" class="form-label fw-semibold">Department</label>
                                            <select class="form-select" id="edit_department_id" name="department_id">
                                                <option value="">-- Select Department --</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>" <?= old('department_id', $editCourse['department_id']) == $dept['id'] ? 'selected' : '' ?>>
                                                        <?= esc($dept['department_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_category_id" class="form-label fw-semibold">Category</label>
                                            <select class="form-select" id="edit_category_id" name="category_id">
                                                <option value="">-- Select Category --</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?= $cat['id'] ?>" <?= old('category_id', $editCourse['category_id']) == $cat['id'] ? 'selected' : '' ?>>
                                                        <?= esc($cat['category_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_year_level_id" class="form-label fw-semibold">Year Level</label>
                                            <select class="form-select" id="edit_year_level_id" name="year_level_id">
                                                <option value="">-- Select Year Level --</option>
                                                <?php foreach ($yearLevels as $year): ?>
                                                    <option value="<?= $year['id'] ?>" <?= old('year_level_id', $editCourse['year_level_id']) == $year['id'] ? 'selected' : '' ?>>
                                                        <?= esc($year['year_level_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-align-left me-2"></i>Description
                                </h6>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="edit_description" class="form-label fw-semibold">Description</label>
                                            <textarea class="form-control" 
                                                      id="edit_description" 
                                                      name="description" 
                                                      rows="4"><?= old('description', $editCourse['description']) ?></textarea>
                                            <div class="form-text">Provide detailed course information</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Section -->
                            <div class="mb-4">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="fas fa-toggle-on me-2"></i>Status
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1" <?= old('is_active', $editCourse['is_active']) ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-semibold" for="edit_is_active">
                                                Active (Available for enrollment)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning text-dark">
                                    💾 Update Course
                                </button>
                                <a href="<?= base_url('admin/manage_courses') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Courses List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 fw-bold">📋 Course List</h5>
                    </div>
                    <div class="card-body p-0">                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="coursesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Title</th>
                                        <th>Credits</th>
                                        <th>Department</th>
                                        <th>Category</th>
                                        <th>Year Level</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="courseTableBody">
                                    <?php if (empty($courses)): ?>
                                        <tr id="noCoursesRow">
                                            <td colspan="8" class="text-center text-muted py-4">
                                                No courses found. Create your first course to get started!
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($courses as $course): ?>
                                            <tr class="course-row" 
                                                data-course-code="<?= esc(strtolower($course['course_code'])) ?>"
                                                data-title="<?= esc(strtolower($course['title'])) ?>"
                                                data-description="<?= esc(strtolower($course['description'] ?? '')) ?>"
                                                data-department="<?= esc(strtolower($course['department_name'] ?? '')) ?>"
                                                data-category="<?= esc(strtolower($course['category_name'] ?? '')) ?>">
                                                <td><strong><?= esc($course['course_code']) ?></strong></td>
                                                <td><?= esc($course['title']) ?></td>
                                                <td><?= esc($course['credits']) ?></td>
                                                <td><?= esc($course['department_name'] ?? 'N/A') ?></td>
                                                <td><?= esc($course['category_name'] ?? 'N/A') ?></td>
                                                <td><?= esc($course['year_level_name'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php if ($course['is_active']): ?>
                                                        <span class="badge bg-success">✅ Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">⭕ Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?= base_url('admin/manage_courses?action=edit&id=' . $course['id']) ?>" 
                                                           class="btn btn-outline-warning" title="Edit">
                                                            ✏️
                                                        </a>
                                                        <a href="<?= base_url('admin/manage_courses?action=delete&id=' . $course['id']) ?>" 
                                                           class="btn btn-outline-danger" title="Delete"
                                                           onclick="return confirm('Are you sure you want to delete this course?')">
                                                            🗑️
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr id="noResultsRow" style="display: none;">
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-search mb-2" style="font-size: 2rem;"></i>
                                                <p class="mb-0">No courses match your search criteria.</p>
                                                <small>Try adjusting your search terms.</small>
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

<!-- Course Search JavaScript -->
<script>
$(document).ready(function() {
    // Course search functionality
    function filterCourses() {
        const searchTerm = $('#courseSearchInput').val().toLowerCase().trim();
        let visibleCount = 0;
        
        $('.course-row').each(function() {
            const courseCode = $(this).data('course-code') || '';
            const title = $(this).data('title') || '';
            const description = $(this).data('description') || '';
            const department = $(this).data('department') || '';
            const category = $(this).data('category') || '';
            
            const searchableText = courseCode + ' ' + title + ' ' + description + ' ' + department + ' ' + category;
            
            if (searchableText.includes(searchTerm)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        // Update count
        $('#searchResultCount').text(visibleCount);
        
        // Show/hide no results message
        if (visibleCount === 0 && $('.course-row').length > 0) {
            $('#noResultsRow').show();
        } else {
            $('#noResultsRow').hide();
        }
    }
    
    // Search on keyup
    $('#courseSearchInput').on('keyup', function() {
        filterCourses();
    });
    
    // Clear search button
    $('#clearSearch').on('click', function() {
        $('#courseSearchInput').val('');
        filterCourses();
        $('#courseSearchInput').focus();
    });
});
</script>
