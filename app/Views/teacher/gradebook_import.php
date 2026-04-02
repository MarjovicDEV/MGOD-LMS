<?= $this->include('templates/header') ?>

<div class="lms-dashboard lms-role-view min-vh-100 teacher-gradebook-import-page">
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3 role-hero">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold"><i class="fas fa-file-import me-2"></i>Import Grades CSV</h3>
                                <p class="mb-0 opacity-75">Upload assignment grades from a CSV file.</p>
                            </div>
                            <div>
                                <a href="<?= base_url('teacher/assignments') ?>" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Assignments
                                </a>
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

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3 role-section-card h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">Assignment Context</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">Assignment</div>
                            <div class="fw-bold"><?= esc($assignment['title'] ?? '') ?></div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="small text-muted">Course</div>
                                    <div class="fw-semibold">
                                        <?= esc($assignment['course_code'] ?? '') ?> - <?= esc($assignment['course_title'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="small text-muted">Section</div>
                                    <div class="fw-semibold"><?= esc($assignment['section'] ?? '-') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Max score reminder: this assignment is set to <strong><?= esc((string) ($assignment['max_score'] ?? '')) ?></strong>.
                            Ensure CSV scores do not exceed this value.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 role-section-card">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">Upload CSV</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= base_url('teacher/gradebook/import/' . (int) $assignment['id']) ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>

                            <?php if (isset($assignment['course_offering_id']) && $assignment['course_offering_id']): ?>
                                <input type="hidden" name="course_offering_id" value="<?= (int) $assignment['course_offering_id'] ?>">
                            <?php else: ?>
                                <div class="alert alert-warning small">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    course_offering_id is not available in assignment data. Import may fail if the backend requires it.
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="csvFile" class="form-label fw-semibold">CSV File</label>
                                <input
                                    class="form-control"
                                    type="file"
                                    id="csvFile"
                                    name="csv_file"
                                    accept=".csv,text/csv"
                                    required
                                >
                                <small class="text-muted">Accepted format: .csv</small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-upload me-1"></i>Import Grades
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 role-section-card mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Expected CSV Format</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">Use exactly these columns in the first row:</p>
                <code>student_code,score</code>

                <div class="mt-3">
                    <label class="form-label fw-semibold">Sample CSV</label>
                    <pre class="bg-light border rounded-3 p-3 mb-0"><code>student_code,score
2024-001,89.5
2024-002,95
2024-003,78</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
