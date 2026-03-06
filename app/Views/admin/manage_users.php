<?= $this->include('templates/header') ?>

<!-- Manage Users View - Admin only functionality for user management -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
          <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">👥 Manage Users</h2>
                                <p class="mb-0 opacity-75">Create, edit, and manage user accounts in the system</p>
                            </div>                            <div>
                                <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-light btn-sm">
                                    ← Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- User Statistics Cards - Moved to top with new order -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">👥</div>
                    <div class="display-5 fw-bold"><?= count($users) ?></div>
                    <div class="fw-semibold">Total Users</div>
                    <small class="opacity-75">All registered</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✓</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($users, fn($u) => ($u['is_active'] ?? 1) == 1)) ?></div>
                    <div class="fw-semibold">Active</div>
                    <small class="opacity-75">Can log in</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-danger text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🔒</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($users, fn($u) => ($u['is_active'] ?? 1) == 0)) ?></div>
                    <div class="fw-semibold">Inactive</div>
                    <small class="opacity-75">Deactivated</small>
                </div>
            </div>            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🎓</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($users, fn($u) => strtolower($u['role_name'] ?? '') === 'student' && ($u['is_active'] ?? 1) == 1)) ?></div>
                    <div class="fw-semibold">Students</div>
                    <small class="opacity-75">Active learners</small>
                </div>
            </div>        </div>

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
                        <div class="d-flex justify-content-between align-items-center">                            <h5 class="mb-0 fw-bold text-dark">⚡ User Management</h5>
                            <a href="<?= base_url('admin/manage_users?action=create') ?>" class="btn btn-success">
                                ➕ Create New User
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
                                           id="userSearchInput" 
                                           class="form-control border-start-0" 
                                           placeholder="🔍 Search users by name, email, user code, student ID, employee ID, role, program, or department...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2 mt-md-0">
                                <div class="text-muted">
                                    <small>
                                        <strong id="searchResultCount"><?= count($users) ?></strong> user(s) found
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create User Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Create New User</h5>
                    </div>
                    <div class="card-body">                        <form method="post" action="<?= base_url('admin/manage_users?action=create') ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label fw-semibold">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?= old('first_name') ?>" required 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="First name can only contain letters, spaces, hyphens, and periods"
                                               minlength="2" maxlength="100">
                                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Letters and spaces only.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="middle_name" class="form-label fw-semibold">Middle Name <small class="text-muted">(optional)</small></label>
                                        <input type="text" class="form-control" id="middle_name" name="middle_name" 
                                               value="<?= old('middle_name') ?>" 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="Middle name can only contain letters, spaces, hyphens, and periods"
                                               maxlength="100">
                                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Letters and spaces only.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label fw-semibold">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?= old('last_name') ?>" required 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="Last name can only contain letters, spaces, hyphens, and periods"
                                               minlength="2" maxlength="100">
                                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Letters and spaces only.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-semibold">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" 
                                        required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-semibold">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               required minlength="6">
                                        <div class="form-text">Password must be at least 6 characters long</div>
                                    </div>
                                </div>                                <div class="col-md-6">
                                    <div class="mb-3">                                        <label for="role" class="form-label fw-semibold">Role</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="teacher" <?= old('role') === 'teacher' ? 'selected' : '' ?>>Teacher/Instructor</option>
                                            <option value="student" <?= old('role') === 'student' ? 'selected' : '' ?>>Student</option>
                                        </select>
                                    </div>                                </div>
                                
                                <!-- Student-specific fields -->
                                <div class="col-md-6" id="year_level_container" style="display: none;">
                                    <div class="mb-3">
                                        <label for="year_level_id" class="form-label fw-semibold">Year Level <span class="text-danger">*</span></label>
                                        <select class="form-select" id="year_level_id" name="year_level_id" disabled>
                                            <option value="">Select Year Level</option>
                                            <?php foreach ($yearLevels as $level): ?>
                                                <option value="<?= $level['id'] ?>" <?= old('year_level_id') == $level['id'] ? 'selected' : '' ?>>
                                                    <?= esc($level['year_level_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Required for student accounts</div>
                                    </div>
                                </div>
                                <div class="col-md-6" id="department_container" style="display: none;">
                                    <div class="mb-3">
                                        <label for="department_id" class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                                        <select class="form-select" id="department_id" name="department_id" disabled>
                                            <option value="">Select Department</option>
                                            <?php if (isset($departments)): ?>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>" <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>>
                                                        <?= esc($dept['department_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <div class="form-text">Required for student accounts</div>
                                    </div>
                                </div>
                                <div class="col-md-6" id="program_container" style="display: none;">
                                    <div class="mb-3">
                                        <label for="program_id" class="form-label fw-semibold">Program <span class="text-danger">*</span></label>
                                        <select class="form-select" id="program_id" name="program_id" disabled>
                                            <option value="">Select Program</option>
                                            <?php if (isset($programs)): ?>
                                                <?php foreach ($programs as $prog): ?>
                                                    <option value="<?= $prog['id'] ?>" data-department="<?= $prog['department_id'] ?>" <?= old('program_id') == $prog['id'] ? 'selected' : '' ?>>
                                                        <?= esc($prog['program_name']) ?> (<?= esc($prog['program_code']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <div class="form-text">Select department first to see available programs</div>
                                    </div>
                                </div>
                                
                                <!-- Teacher-specific fields -->
                                <div class="col-md-6" id="teacher_department_container" style="display: none;">
                                    <div class="mb-3">
                                        <label for="teacher_department_id" class="form-label fw-semibold">Department <small class="text-muted">(optional)</small></label>
                                        <select class="form-select" id="teacher_department_id" name="department_id">
                                            <option value="">Select Department</option>
                                            <?php if (isset($departments)): ?>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>" <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>>
                                                        <?= esc($dept['department_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <div class="form-text">Optional for teacher accounts</div>
                                    </div>
                                </div>
                                <div class="col-md-6" id="specialization_container" style="display: none;">
                                    <div class="mb-3">
                                        <label for="specialization" class="form-label fw-semibold">Specialization <small class="text-muted">(optional)</small></label>
                                        <input type="text" class="form-control" id="specialization" name="specialization" 
                                               value="<?= old('specialization') ?>" 
                                               maxlength="255"
                                               placeholder="e.g., Computer Science, Mathematics">
                                        <div class="form-text">Teacher's area of expertise</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    💾 Create User
                                </button>
                                <a href="<?= base_url('admin/manage_users') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit User Form (shown when editing) -->
        <?php if ($showEditForm && $editUser): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">                    <div class="card-header bg-warning text-dark border-0">
                        <?php 
                        $editUserFullName = trim(($editUser['first_name'] ?? '') . ' ' . ($editUser['middle_name'] ?? '') . ' ' . ($editUser['last_name'] ?? ''));
                        ?>
                        <h5 class="mb-0">✏️ Edit User: <?= esc($editUserFullName) ?></h5>
                    </div>
                    <div class="card-body">                        <form method="post" action="<?= base_url('admin/manage_users?action=edit&id=' . $editUser['id']) ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_first_name" class="form-label fw-semibold">First Name</label>
                                        <input type="text" class="form-control" id="edit_first_name" name="first_name" 
                                               value="<?= old('first_name', $editUser['first_name'] ?? '') ?>" required 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="First name can only contain letters, spaces, hyphens, and periods"
                                               minlength="2" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_middle_name" class="form-label fw-semibold">Middle Name <small class="text-muted">(optional)</small></label>
                                        <input type="text" class="form-control" id="edit_middle_name" name="middle_name" 
                                               value="<?= old('middle_name', $editUser['middle_name'] ?? '') ?>" 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="Middle name can only contain letters, spaces, hyphens, and periods"
                                               maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_last_name" class="form-label fw-semibold">Last Name</label>
                                        <input type="text" class="form-control" id="edit_last_name" name="last_name" 
                                               value="<?= old('last_name', $editUser['last_name'] ?? '') ?>" required 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="Last name can only contain letters, spaces, hyphens, and periods"
                                               minlength="2" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_email" class="form-label fw-semibold">Email Address</label>
                                        <input type="email" class="form-control" id="edit_email" name="email" value="<?= old('email', $editUser['email']) ?>" 
                                        required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_password" class="form-label fw-semibold">Password <small class="text-muted">(leave blank to keep current)</small></label>
                                        <input type="password" class="form-control" id="edit_password" name="password" 
                                               minlength="6">
                                        <div class="form-text">Password must be at least 6 characters long (if changing)</div>
                                    </div>
                                </div>                                <div class="col-md-6">                                    <div class="mb-3">
                                        <label for="edit_role" class="form-label fw-semibold">Role</label>
                                        <select class="form-select" id="edit_role" name="role" required>
                                            <option value="admin" <?= old('role', strtolower($editUser['role_name'] ?? '')) === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="teacher" <?= in_array(old('role', strtolower($editUser['role_name'] ?? '')), ['instructor', 'teacher']) ? 'selected' : '' ?>>Teacher/Instructor</option>
                                            <option value="student" <?= old('role', strtolower($editUser['role_name'] ?? '')) === 'student' ? 'selected' : '' ?>>Student</option>
                                        </select>
                                    </div>                                </div>
                                
                                <!-- Student-specific fields -->
                                <div class="col-md-6" id="edit_year_level_container" style="display: <?= old('role', strtolower($editUser['role_name'] ?? '')) === 'student' ? 'block' : 'none' ?>;">
                                    <div class="mb-3">
                                        <label for="edit_year_level_id" class="form-label fw-semibold">Year Level <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_year_level_id" name="year_level_id">
                                            <option value="">Select Year Level</option>
                                            <?php foreach ($yearLevels as $level): ?>
                                                <option value="<?= $level['id'] ?>" <?= old('year_level_id', $roleSpecificData['year_level_id'] ?? '') == $level['id'] ? 'selected' : '' ?>>
                                                    <?= esc($level['year_level_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Required for student accounts</div>
                                    </div>
                                </div>
                                <div class="col-md-6" id="edit_department_container" style="display: <?= old('role', strtolower($editUser['role_name'] ?? '')) === 'student' ? 'block' : 'none' ?>;">
                                    <div class="mb-3">
                                        <label for="edit_department_id" class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_department_id" name="department_id">
                                            <option value="">Select Department</option>
                                            <?php if (isset($departments)): ?>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>" <?= old('department_id', $roleSpecificData['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                                        <?= esc($dept['department_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <div class="form-text">Required for student accounts</div>
                                    </div>
                                </div>
                                <div class="col-md-6" id="edit_program_container" style="display: <?= old('role', strtolower($editUser['role_name'] ?? '')) === 'student' ? 'block' : 'none' ?>;">
                                    <div class="mb-3">
                                        <label for="edit_program_id" class="form-label fw-semibold">Program <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_program_id" name="program_id">
                                            <option value="">Select Program</option>
                                            <?php if (isset($programs)): ?>
                                                <?php foreach ($programs as $prog): ?>
                                                    <option value="<?= $prog['id'] ?>" data-department="<?= $prog['department_id'] ?>" <?= old('program_id', $roleSpecificData['program_id'] ?? '') == $prog['id'] ? 'selected' : '' ?>>
                                                        <?= esc($prog['program_name']) ?> (<?= esc($prog['program_code']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <div class="form-text">Select department first to see available programs</div>
                                    </div>
                                </div>
                                
                                <!-- Teacher-specific fields -->
                                <div class="col-md-6" id="edit_teacher_department_container" style="display: <?= in_array(old('role', strtolower($editUser['role_name'] ?? '')), ['instructor', 'teacher']) ? 'block' : 'none' ?>;">
                                    <div class="mb-3">
                                        <label for="edit_teacher_department_id" class="form-label fw-semibold">Department <small class="text-muted">(optional)</small></label>
                                        <select class="form-select" id="edit_teacher_department_id" name="department_id">
                                            <option value="">Select Department</option>
                                            <?php if (isset($departments)): ?>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>" <?= old('department_id', $roleSpecificData['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                                        <?= esc($dept['department_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <div class="form-text">Optional for teacher accounts</div>
                                    </div>
                                </div>
                                <div class="col-md-6" id="edit_specialization_container" style="display: <?= in_array(old('role', strtolower($editUser['role_name'] ?? '')), ['instructor', 'teacher']) ? 'block' : 'none' ?>;">
                                    <div class="mb-3">
                                        <label for="edit_specialization" class="form-label fw-semibold">Specialization <small class="text-muted">(optional)</small></label>
                                        <input type="text" class="form-control" id="edit_specialization" name="specialization" 
                                               value="<?= old('specialization', $roleSpecificData['specialization'] ?? '') ?>" 
                                               maxlength="255"
                                               placeholder="e.g., Computer Science, Mathematics">
                                        <div class="form-text">Teacher's area of expertise</div>
                                    </div>
                                </div>
                                        <div class="form-text">Select department first to see available programs</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    💾 Update User
                                </button>
                                <a href="<?= base_url('admin/manage_users') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Users List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">                    
                    <div class="card-header bg-white border-0 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">👤 All Users</h5>
                                <small class="text-muted">Manage all system users</small>
                            </div>
                            <div class="text-muted small">
                                Total: <?= count($users) ?> users
                            </div>
                        </div>
                    </div>                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="usersTable">                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold border-0 text-center">ID</th>
                                        <th class="fw-semibold border-0">User</th>
                                        <th class="fw-semibold border-0">Email</th>
                                        <th class="fw-semibold border-0 text-center">Role</th>
                                        <th class="fw-semibold border-0 text-center">Status</th>
                                        <th class="fw-semibold border-0 text-center">Created</th>
                                        <th class="fw-semibold border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="userTableBody">
                                    <?php if (!empty($users)): ?>
                                        <?php 
                                        // Sort users by ID in ascending order (1, 2, 3, etc.)
                                        usort($users, function($a, $b) {
                                            return $a['id'] <=> $b['id'];
                                        });
                                        ?>                                          <?php foreach ($users as $user): ?>
                                        <?php 
                                        $isInactive = ($user['is_active'] == 0);
                                        $rowClass = $isInactive ? 'border-bottom table-secondary opacity-75' : 'border-bottom';
                                        $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                                        ?>
                                        <tr class="user-row <?= $rowClass ?>"
                                            data-first-name="<?= esc(strtolower($user['first_name'] ?? '')) ?>"
                                            data-last-name="<?= esc(strtolower($user['last_name'] ?? '')) ?>"
                                            data-email="<?= esc(strtolower($user['email'])) ?>"
                                            data-user-code="<?= esc(strtolower($user['user_code'] ?? '')) ?>"
                                            data-student-id="<?= esc(strtolower($user['student_id'] ?? '')) ?>"
                                            data-employee-id="<?= esc(strtolower($user['employee_id'] ?? '')) ?>"
                                            data-role="<?= esc(strtolower($user['role_name'] ?? '')) ?>"
                                            data-program="<?= esc(strtolower($user['program_name'] ?? '')) ?>"
                                            data-department="<?= esc(strtolower($user['department_name'] ?? '')) ?>">
                                            <td class="text-center">
                                                <span class="badge bg-secondary rounded-pill px-2 py-1"><?= $user['id'] ?></span>
                                            </td>                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php 
                                                    $initial = strtoupper(substr($user['first_name'] ?? 'U', 0, 1));
                                                    $avatarClass = $isInactive ? 'bg-secondary' : 'bg-primary';
                                                    ?>
                                                    <div class="<?= $avatarClass ?> text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                        <?= $initial ?>
                                                    </div>
                                                    <div>
                                                        <strong class="text-dark"><?= esc($fullName) ?></strong>
                                                        <?php if ($user['id'] == $currentAdminID): ?>
                                                            <span class="badge bg-info ms-2 small">You</span>
                                                        <?php endif; ?>
                                                        <?php if ($isInactive): ?>
                                                            <span class="badge bg-danger ms-2 small">Inactive</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-muted"><?= esc($user['email']) ?></td><td class="text-center">
                                                <?php
                                                $roleStyles = [
                                                    'admin' => ['color' => 'danger', 'icon' => '👑'],
                                                    'teacher' => ['color' => 'primary', 'icon' => '👨‍🏫'],
                                                    'instructor' => ['color' => 'primary', 'icon' => '👨‍🏫'],
                                                    'student' => ['color' => 'success', 'icon' => '🎓']
                                                ];
                                                $userRole = strtolower($user['role_name'] ?? 'student');
                                                $style = $roleStyles[$userRole] ?? ['color' => 'secondary', 'icon' => '👤'];
                                                ?>
                                                <span class="badge bg-<?= $style['color'] ?> rounded-pill px-3 py-2">
                                                    <?= $style['icon'] ?> <?= esc($user['role_name'] ?? 'User') ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($isInactive): ?>
                                                    <span class="badge bg-danger rounded-pill px-3 py-2">
                                                        🔒 Inactive
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                                        ✓ Active
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                                </small>
                                            </td>                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">                                                      <?php 
                                                    // Check if current admin can edit this user
                                                    $canEdit = ($user['id'] != $currentAdminID && !$isInactive);
                                                    $canDeactivate = (strtolower($user['role_name'] ?? '') !== 'admin' && $user['id'] != $currentAdminID && !$isInactive);
                                                    $canReactivate = ($user['id'] != $currentAdminID && $isInactive);
                                                    ?>
                                                    
                                                    <!-- Edit Button -->                                                    
                                                     <?php if ($canEdit): ?>
                                                        <a href="<?= base_url('admin/manage_users?action=edit&id=' . $user['id']) ?>" 
                                                           class="btn btn-outline-warning btn-sm me-1" 
                                                           title="Edit User">
                                                            ✏️
                                                        </a>
                                                    <?php elseif ($isInactive): ?>
                                                        <button class="btn btn-outline-secondary btn-sm me-1" 
                                                                disabled 
                                                                title="Cannot edit inactive accounts">
                                                            🔒
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-outline-secondary btn-sm me-1" 
                                                                disabled 
                                                                title="Cannot edit own account">
                                                            🔒
                                                        </button>
                                                    <?php endif; ?>
                                                      
                                                    <!-- Deactivate/Reactivate Button -->
                                                    <?php if ($canReactivate): ?>
                                                        <?php 
                                                        $reactivateUserName = trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                                                        ?>
                                                         <a href="<?= base_url('admin/manage_users?action=reactivate&id=' . $user['id']) ?>" 
                                                           class="btn btn-outline-success btn-sm" 
                                                           onclick="return confirm('Are you sure you want to reactivate this user?\n\nUser: <?= esc($reactivateUserName) ?>\nEmail: <?= esc($user['email']) ?>\n\nThis user will be able to log in again.')"
                                                           title="Reactivate User">
                                                            🔓
                                                        </a>
                                                    <?php elseif ($canDeactivate): ?>
                                                        <?php 
                                                        $deactivateUserName = trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                                                        ?>
                                                         <a href="<?= base_url('admin/manage_users?action=delete&id=' . $user['id']) ?>" 
                                                           class="btn btn-outline-danger btn-sm" 
                                                           onclick="return confirm('Are you sure you want to deactivate this user?\n\nUser: <?= esc($deactivateUserName) ?>\nEmail: <?= esc($user['email']) ?>\n\nThis user will no longer be able to log in, but their data will be preserved.')"
                                                           title="Deactivate User">
                                                            🔒
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-outline-secondary btn-sm" 
                                                                disabled 
                                                                title="Cannot deactivate admin accounts or own account">
                                                            🛡️
                                                        </button>
                                                    <?php endif; ?>                                                
                                                </div>
                                            </td>                                        </tr>
                                        <?php endforeach; ?>
                                        <tr id="noResultsRow" style="display: none;">
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-search mb-2" style="font-size: 2rem;"></i>
                                                <p class="mb-0">No users match your search criteria.</p>
                                                <small>Try adjusting your search terms.</small>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr id="noUsersRow">
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <div class="mb-3">
                                                    <span style="font-size: 3rem; opacity: 0.3;">👥</span>
                                                </div>
                                                <p class="mb-0">No users found in the system.</p>
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
    // ======= CREATE FORM =======
    const roleSelect = document.getElementById('role');
    const yearLevelContainer = document.getElementById('year_level_container');
    const yearLevelSelect = document.getElementById('year_level_id');
    const departmentContainer = document.getElementById('department_container');
    const departmentSelect = document.getElementById('department_id');
    const programContainer = document.getElementById('program_container');
    const programSelect = document.getElementById('program_id');
    const teacherDepartmentContainer = document.getElementById('teacher_department_container');
    const teacherDepartmentSelect = document.getElementById('teacher_department_id');
    const specializationContainer = document.getElementById('specialization_container');
    const specializationInput = document.getElementById('specialization');
    
    // Function to filter programs based on selected department
    function filterProgramsByDepartment(deptSelect, progSelect) {
        const selectedDept = deptSelect.value;
        const options = progSelect.querySelectorAll('option');
        
        // Reset program selection
        progSelect.value = '';
        
        options.forEach(function(option) {
            if (option.value === '') {
                option.style.display = 'block'; // Always show "Select Program" option
            } else {
                const optionDept = option.getAttribute('data-department');
                if (selectedDept === '' || optionDept === selectedDept) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            }
        });
    }
      // Function to show/hide student fields
    function toggleStudentFields(isStudent, containers, selects) {
        if (isStudent) {
            containers.forEach(c => { if(c) c.style.display = 'block'; });
            selects.forEach(s => { 
                if(s) {
                    s.setAttribute('required', 'required');
                    s.disabled = false;
                }
            });
        } else {
            containers.forEach(c => { if(c) c.style.display = 'none'; });
            selects.forEach(s => { 
                if(s) {
                    s.removeAttribute('required');
                    s.disabled = true;
                    s.value = '';
                }
            });
        }
    }
    
    // Function to show/hide teacher fields
    function toggleTeacherFields(isTeacher, containers, inputs) {
        if (isTeacher) {
            containers.forEach(c => { if(c) c.style.display = 'block'; });
            inputs.forEach(i => { 
                if(i) {
                    i.disabled = false;
                }
            });
        } else {
            containers.forEach(c => { if(c) c.style.display = 'none'; });
            inputs.forEach(i => { 
                if(i) {
                    i.disabled = true;
                    i.value = '';
                }
            });
        }
    }
    
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            const isStudent = this.value === 'student';
            const isTeacher = this.value === 'teacher';
            
            toggleStudentFields(
                isStudent,
                [yearLevelContainer, departmentContainer, programContainer],
                [yearLevelSelect, departmentSelect, programSelect]
            );
            
            toggleTeacherFields(
                isTeacher,
                [teacherDepartmentContainer, specializationContainer],
                [teacherDepartmentSelect, specializationInput]
            );
        });
          // Trigger on page load
        if (roleSelect.value === 'student') {
            toggleStudentFields(
                true,
                [yearLevelContainer, departmentContainer, programContainer],
                [yearLevelSelect, departmentSelect, programSelect]
            );
        } else if (roleSelect.value === 'teacher') {
            toggleTeacherFields(
                true,
                [teacherDepartmentContainer, specializationContainer],
                [teacherDepartmentSelect, specializationInput]
            );
        }
    }
    
    // Filter programs when department changes
    if (departmentSelect && programSelect) {
        departmentSelect.addEventListener('change', function() {
            filterProgramsByDepartment(departmentSelect, programSelect);
        });
        
        // Trigger on page load if department is already selected
        if (departmentSelect.value) {
            filterProgramsByDepartment(departmentSelect, programSelect);
        }
    }
    
    // ======= EDIT FORM =======
    const editRoleSelect = document.getElementById('edit_role');
    const editYearLevelContainer = document.getElementById('edit_year_level_container');    const editYearLevelSelect = document.getElementById('edit_year_level_id');
    const editDepartmentContainer = document.getElementById('edit_department_container');
    const editDepartmentSelect = document.getElementById('edit_department_id');
    const editProgramContainer = document.getElementById('edit_program_container');
    const editProgramSelect = document.getElementById('edit_program_id');
    const editTeacherDepartmentContainer = document.getElementById('edit_teacher_department_container');
    const editTeacherDepartmentSelect = document.getElementById('edit_teacher_department_id');
    const editSpecializationContainer = document.getElementById('edit_specialization_container');
    const editSpecializationInput = document.getElementById('edit_specialization');
    
    if (editRoleSelect) {
        editRoleSelect.addEventListener('change', function() {
            const isStudent = this.value === 'student';
            const isTeacher = this.value === 'teacher';
            
            toggleStudentFields(
                isStudent,
                [editYearLevelContainer, editDepartmentContainer, editProgramContainer],
                [editYearLevelSelect, editDepartmentSelect, editProgramSelect]
            );
            
            toggleTeacherFields(
                isTeacher,
                [editTeacherDepartmentContainer, editSpecializationContainer],
                [editTeacherDepartmentSelect, editSpecializationInput]
            );
        });
        
        // Trigger on page load if role is already student
        if (editRoleSelect.value === 'student') {
            toggleStudentFields(
                true,
                [editYearLevelContainer, editDepartmentContainer, editProgramContainer],
                [editYearLevelSelect, editDepartmentSelect, editProgramSelect]
            );
            // Also filter programs based on selected department
            if (editDepartmentSelect && editDepartmentSelect.value) {
                filterProgramsByDepartment(editDepartmentSelect, editProgramSelect);
                // Restore the selected program after filtering
                const selectedProgram = editProgramSelect.getAttribute('data-selected') || '<?= old('program_id', isset($roleSpecificData) ? ($roleSpecificData['program_id'] ?? '') : '') ?>';
                if (selectedProgram) {
                    editProgramSelect.value = selectedProgram;
                }
            }
        } else if (editRoleSelect.value === 'teacher') {
            toggleTeacherFields(
                true,
                [editTeacherDepartmentContainer, editSpecializationContainer],
                [editTeacherDepartmentSelect, editSpecializationInput]
            );
        }
    }
    
    // Filter programs when department changes in edit form
    if (editDepartmentSelect && editProgramSelect) {
        editDepartmentSelect.addEventListener('change', function() {
            filterProgramsByDepartment(editDepartmentSelect, editProgramSelect);
        });
        
        // Trigger on page load if department is already selected
        if (editDepartmentSelect.value) {
            filterProgramsByDepartment(editDepartmentSelect, editProgramSelect);
            // Restore the selected program value
            const currentProgramId = '<?= old('program_id', isset($roleSpecificData) ? ($roleSpecificData['program_id'] ?? '') : '') ?>';
            if (currentProgramId) {
                editProgramSelect.value = currentProgramId;
            }
        }
    }

    // Name field validation for both create and edit forms
    const nameFields = document.querySelectorAll('input[name="first_name"], input[name="middle_name"], input[name="last_name"]');
      nameFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = e.target.value;
            const validPattern = /^[A-Za-zñÑ\s]*$/;
            
            // Remove invalid characters as user types (letters, ñÑ, and spaces allowed)
            if (!validPattern.test(value)) {
                e.target.value = value.replace(/[^A-Za-zñÑ\s]/g, '');
            }
            
            // Visual feedback
            if (e.target.value.length >= 2 && validPattern.test(e.target.value)) {
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
    
    // Email field validation
    const emailFields = document.querySelectorAll('input[name="email"]');
    
    emailFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = e.target.value;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // Visual feedback for email
            if (emailPattern.test(value)) {
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
    
    // Password field validation
    const passwordFields = document.querySelectorAll('input[name="password"]');
    
    passwordFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = e.target.value;
            
            // Skip validation for edit password field if empty (optional)
            if (field.id === 'edit_password' && value.length === 0) {
                e.target.classList.remove('is-valid', 'is-invalid');
                return;
            }
            
            // Visual feedback for password
            if (value.length >= 6) {
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

    // User search functionality
    function filterUsers() {
        const searchTerm = $('#userSearchInput').val().toLowerCase().trim();
        let visibleCount = 0;
        
        $('.user-row').each(function() {
            const firstName = $(this).data('first-name') || '';
            const lastName = $(this).data('last-name') || '';
            const email = $(this).data('email') || '';
            const userCode = $(this).data('user-code') || '';
            const studentId = $(this).data('student-id') || '';
            const employeeId = $(this).data('employee-id') || '';
            const role = $(this).data('role') || '';
            const program = $(this).data('program') || '';
            const department = $(this).data('department') || '';
            
            const searchableText = firstName + ' ' + lastName + ' ' + email + ' ' + 
                                 userCode + ' ' + studentId + ' ' + employeeId + ' ' + 
                                 role + ' ' + program + ' ' + department;
            
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
        if (visibleCount === 0 && $('.user-row').length > 0) {
            $('#noResultsRow').show();
        } else {
            $('#noResultsRow').hide();
        }
    }
    
    // Search on keyup
    $('#userSearchInput').on('keyup', function() {
        filterUsers();
    });
    
    // Clear search button
    $('#clearSearch').on('click', function() {
        $('#userSearchInput').val('');
        filterUsers();
        $('#userSearchInput').focus();
    });
});
</script>

