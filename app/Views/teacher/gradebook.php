<?= $this->include('templates/header') ?>

<div class="lms-dashboard lms-role-view min-vh-100 teacher-gradebook-page">
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3 role-hero">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold"><i class="fas fa-book me-2"></i>Gradebook</h3>
                                <p class="mb-0 opacity-75">Manage grades for your assigned courses.</p>
                            </div>
                            <div>
                                <i class="fas fa-clipboard-check" style="font-size: 3rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($courses)): ?>
            <div class="card border-0 shadow-sm rounded-3 role-section-card">
                <div class="card-body text-center py-5 role-empty-state rounded-3">
                    <i class="fas fa-chalkboard-teacher text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                    <h5 class="text-muted mb-2">No Courses in Gradebook</h5>
                    <p class="text-muted mb-0">You do not have active course assignments yet.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row mb-4 role-stats">
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm bg-primary text-white text-center p-4 rounded-3 h-100">
                        <div class="display-5 fw-bold"><?= count($courses) ?></div>
                        <div class="fw-semibold">Courses</div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm bg-success text-white text-center p-4 rounded-3 h-100">
                        <div class="display-5 fw-bold"><?= (int) array_sum(array_map(static fn($course) => (int) ($course['student_count'] ?? 0), $courses)) ?></div>
                        <div class="fw-semibold">Total Students</div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 role-section-card">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Courses Taught</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Course Code</th>
                                    <th>Title</th>
                                    <th>Section</th>
                                    <th>Term / Year</th>
                                    <th>Students</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><span class="badge bg-primary"><?= esc($course['course_code']) ?></span></td>
                                        <td><?= esc($course['title']) ?></td>
                                        <td><?= esc($course['section'] ?? '-') ?></td>
                                        <td>
                                            <?= esc($course['term_name'] ?? '-') ?>
                                            <?php if (!empty($course['year_name'])): ?>
                                                <span class="text-muted">/ <?= esc($course['year_name']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= (int) ($course['student_count'] ?? 0) ?></span></td>
                                        <td class="text-end">
                                            <a href="<?= base_url('teacher/gradebook/entry/' . (int) $course['course_offering_id']) ?>" class="btn btn-sm btn-primary me-1">
                                                <i class="fas fa-pen me-1"></i>Grade Entry
                                            </a>
                                            <a href="<?= base_url('teacher/gradebook/export/' . (int) $course['course_offering_id']) ?>" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-file-csv me-1"></i>Export
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
