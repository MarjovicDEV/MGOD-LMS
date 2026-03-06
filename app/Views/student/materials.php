<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .material-card {
            transition: all 0.3s ease;
            border-left: 4px solid #0d6efd;
        }
        .material-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15) !important;
            transform: translateY(-2px);
        }
        .file-type-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .course-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .stats-card {
            border-radius: 15px;
            padding: 1.5rem;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.2;
            position: absolute;
            right: 1rem;
            bottom: 1rem;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .view-toggle-btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            border: 2px solid #dee2e6;
            background: white;
            transition: all 0.2s;
        }
        .view-toggle-btn.active {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
        .material-grid-card {
            transition: all 0.3s ease;
            height: 100%;
        }
        .material-grid-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15) !important;
            transform: translateY(-5px);
        }
        .file-icon-large {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?= view('templates/header') ?>

    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Course Materials</li>
                    </ol>
                </nav>
                <h2 class="fw-bold">
                    <i class="fas fa-folder-open text-primary me-2"></i>Course Materials
                </h2>
                <p class="text-muted">Browse and download materials from all your enrolled courses</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card stats-card shadow-sm position-relative">
                    <div style="position: relative; z-index: 1;">
                        <h6 class="text-white-50 mb-2">Total Materials</h6>
                        <h2 class="fw-bold mb-0"><?= $totalMaterials ?></h2>
                        <p class="mb-0 mt-2">
                            <i class="fas fa-check-circle me-1"></i>From <?= $totalCourses ?> course<?= $totalCourses !== 1 ? 's' : '' ?>
                        </p>
                    </div>
                    <i class="fas fa-file-alt stats-icon"></i>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card stats-card shadow-sm position-relative" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div style="position: relative; z-index: 1;">
                        <h6 class="text-white-50 mb-2">Enrolled Courses</h6>
                        <h2 class="fw-bold mb-0"><?= $totalCourses ?></h2>
                        <p class="mb-0 mt-2">
                            <a href="<?= base_url('student/courses') ?>" class="text-white text-decoration-none">
                                <i class="fas fa-eye me-1"></i>View All Courses
                            </a>
                        </p>
                    </div>
                    <i class="fas fa-graduation-cap stats-icon"></i>
                </div>
            </div>
        </div>

        <?php if (empty($materials)): ?>
            <!-- Empty State -->
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Materials Available</h4>
                    <p class="text-muted">
                        Your instructors haven't uploaded any materials yet.<br>
                        Check back later or contact your instructors for updates.
                    </p>
                    <a href="<?= base_url('student/courses') ?>" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-left me-2"></i>Back to Courses
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- View Toggle -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="text-muted">Showing <?= count($materials) ?> material<?= count($materials) !== 1 ? 's' : '' ?></span>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="view-toggle-btn active" id="listViewBtn" onclick="switchView('list')">
                        <i class="fas fa-list me-1"></i>List
                    </button>
                    <button type="button" class="view-toggle-btn" id="gridViewBtn" onclick="switchView('grid')">
                        <i class="fas fa-th-large me-1"></i>Grid
                    </button>
                </div>
            </div>

            <!-- List View (Default) -->
            <div id="listView">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;"></th>
                                        <th>Material Name</th>
                                        <th>Course</th>
                                        <th style="width: 100px;">Type</th>
                                        <th style="width: 180px;">Uploaded</th>
                                        <th style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materials as $material): ?>
                                        <?php
                                        $extension = strtoupper(pathinfo($material['file_name'], PATHINFO_EXTENSION));
                                        $iconClass = 'fa-file text-secondary';
                                        if (in_array($extension, ['PDF'])) {
                                            $iconClass = 'fa-file-pdf text-danger';
                                        } elseif (in_array($extension, ['PPT', 'PPTX'])) {
                                            $iconClass = 'fa-file-powerpoint text-warning';
                                        } elseif (in_array($extension, ['DOC', 'DOCX'])) {
                                            $iconClass = 'fa-file-word text-primary';
                                        } elseif (in_array($extension, ['XLS', 'XLSX'])) {
                                            $iconClass = 'fa-file-excel text-success';
                                        } elseif (in_array($extension, ['ZIP', 'RAR'])) {
                                            $iconClass = 'fa-file-archive text-info';
                                        } elseif (in_array($extension, ['JPG', 'JPEG', 'PNG', 'GIF'])) {
                                            $iconClass = 'fa-file-image text-primary';
                                        }
                                        ?>
                                        <tr>
                                            <td class="text-center">
                                                <i class="fas <?= $iconClass ?> fa-2x"></i>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= esc($material['file_name']) ?></div>
                                                <?php if (!empty($material['description'])): ?>
                                                    <small class="text-muted"><?= esc($material['description']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('student/course/' . $material['course_offering_id'] . '/materials') ?>" 
                                                   class="text-decoration-none">
                                                    <span class="course-badge">
                                                        <?= esc($material['course_info']['course_code']) ?>
                                                    </span>
                                                </a>
                                                <div class="small text-muted mt-1"><?= esc($material['course_info']['course_title']) ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary file-type-badge">
                                                    <?= $extension ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?= date('M d, Y', strtotime($material['uploaded_at'])) ?>
                                                    <br>
                                                    <?= date('g:i A', strtotime($material['uploaded_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('material/view/' . $material['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   target="_blank">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                                <a href="<?= base_url('material/download/' . $material['id']) ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download me-1"></i>Download
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid View (Hidden by default) -->
            <div id="gridView" style="display: none;">
                <div class="row">
                    <?php foreach ($materials as $material): ?>
                        <?php
                        $extension = strtoupper(pathinfo($material['file_name'], PATHINFO_EXTENSION));
                        $iconClass = 'fa-file text-secondary';
                        if (in_array($extension, ['PDF'])) {
                            $iconClass = 'fa-file-pdf text-danger';
                        } elseif (in_array($extension, ['PPT', 'PPTX'])) {
                            $iconClass = 'fa-file-powerpoint text-warning';
                        } elseif (in_array($extension, ['DOC', 'DOCX'])) {
                            $iconClass = 'fa-file-word text-primary';
                        } elseif (in_array($extension, ['XLS', 'XLSX'])) {
                            $iconClass = 'fa-file-excel text-success';
                        } elseif (in_array($extension, ['ZIP', 'RAR'])) {
                            $iconClass = 'fa-file-archive text-info';
                        } elseif (in_array($extension, ['JPG', 'JPEG', 'PNG', 'GIF'])) {
                            $iconClass = 'fa-file-image text-primary';
                        }
                        ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card material-grid-card shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas <?= $iconClass ?> file-icon-large"></i>
                                    <h6 class="fw-semibold mb-2"><?= esc($material['file_name']) ?></h6>
                                    <a href="<?= base_url('student/course/' . $material['course_offering_id'] . '/materials') ?>" 
                                       class="text-decoration-none">
                                        <span class="course-badge d-inline-block mb-2">
                                            <?= esc($material['course_info']['course_code']) ?>
                                        </span>
                                    </a>
                                    <?php if (!empty($material['description'])): ?>
                                        <p class="text-muted small mb-3"><?= esc($material['description']) ?></p>
                                    <?php endif; ?>
                                    <div class="small text-muted mb-3">
                                        <i class="fas fa-clock me-1"></i>
                                        <?= date('M d, Y', strtotime($material['uploaded_at'])) ?>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="<?= base_url('material/view/' . $material['id']) ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           target="_blank">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <a href="<?= base_url('material/download/' . $material['id']) ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?= view('templates/footer') ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function switchView(view) {
            const listView = document.getElementById('listView');
            const gridView = document.getElementById('gridView');
            const listBtn = document.getElementById('listViewBtn');
            const gridBtn = document.getElementById('gridViewBtn');

            if (view === 'list') {
                listView.style.display = 'block';
                gridView.style.display = 'none';
                listBtn.classList.add('active');
                gridBtn.classList.remove('active');
                localStorage.setItem('materialsView', 'list');
            } else {
                listView.style.display = 'none';
                gridView.style.display = 'block';
                listBtn.classList.remove('active');
                gridBtn.classList.add('active');
                localStorage.setItem('materialsView', 'grid');
            }
        }

        // Restore view preference on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedView = localStorage.getItem('materialsView');
            if (savedView === 'grid') {
                switchView('grid');
            }
        });
    </script>
</body>
</html>
