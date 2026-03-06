<?= $this->include('templates/header') ?>

<!-- Student Course Materials View - Shows all materials for a specific course -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="mb-2 mb-md-0">
                                <div class="d-flex align-items-center mb-2">
                                    <a href="<?= base_url('student/courses') ?>" class="btn btn-light btn-sm me-3">
                                        <i class="fas fa-arrow-left me-1"></i>Back
                                    </a>
                                    <h2 class="mb-0 fw-bold">
                                        <i class="fas fa-folder-open me-2"></i><?= esc($course['course_title']) ?>
                                    </h2>
                                </div>
                                <p class="mb-0 opacity-75">
                                    <i class="fas fa-code me-1"></i><?= esc($course['course_code']) ?> - <?= esc($course['section']) ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-light text-primary fs-6 px-3 py-2">
                                    <i class="fas fa-file me-1"></i><?= $totalMaterials ?> Material<?= $totalMaterials != 1 ? 's' : '' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Info Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-info-circle text-primary me-2"></i>Course Information
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-chalkboard-teacher text-primary me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted d-block">Instructor</small>
                                        <strong><?= esc($course['instructor_name']) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-book text-primary me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted d-block">Credits</small>
                                        <strong><?= esc($course['credits']) ?> Units</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-calendar text-primary me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted d-block">Term</small>
                                        <strong><?= esc($course['term_name']) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-clock text-primary me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted d-block">Duration</small>
                                        <strong><?= esc($course['start_date_formatted']) ?> - <?= esc($course['end_date_formatted']) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                        <h6 class="text-muted mb-2">Course Progress</h6>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= $course['progress'] ?>%;" 
                                 aria-valuenow="<?= $course['progress'] ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?= $course['progress'] ?>%
                            </div>
                        </div>
                        <small class="text-muted">Keep learning!</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Materials List Section -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-download me-2 text-primary"></i>Course Materials
                            </h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary active" id="view-list">
                                    <i class="fas fa-list me-1"></i>List
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="view-grid">
                                    <i class="fas fa-th me-1"></i>Grid
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($materials)): ?>
                            <!-- List View -->
                            <div id="list-view" class="materials-view">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 50px;"><i class="fas fa-file"></i></th>
                                                <th>File Name</th>
                                                <th style="width: 150px;">Type</th>
                                                <th style="width: 150px;">Uploaded</th>
                                                <th style="width: 150px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($materials as $material): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <?php
                                                    $extension = pathinfo($material['file_name'], PATHINFO_EXTENSION);
                                                    $iconClass = match(strtolower($extension)) {
                                                        'pdf' => 'fa-file-pdf text-danger',
                                                        'ppt', 'pptx' => 'fa-file-powerpoint text-warning',
                                                        'doc', 'docx' => 'fa-file-word text-primary',
                                                        'xls', 'xlsx' => 'fa-file-excel text-success',
                                                        default => 'fa-file text-secondary'
                                                    };
                                                    ?>
                                                    <i class="fas <?= $iconClass ?> fa-2x"></i>
                                                </td>
                                                <td>
                                                    <strong><?= esc($material['file_name']) ?></strong>
                                                    <?php if (!empty($material['description'])): ?>
                                                    <br><small class="text-muted"><?= esc($material['description']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?= strtoupper($extension) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar-alt me-1"></i>
                                                        <?= date('M j, Y', strtotime($material['created_at'])) ?>
                                                        <br>
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?= date('g:i A', strtotime($material['created_at'])) ?>
                                                    </small>
                                                </td>                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button"
                                                           class="btn btn-sm btn-outline-info" 
                                                           onclick='viewMaterialInfo(<?= json_encode([
                                                               "id" => $material["id"],
                                                               "file_name" => $material["file_name"],
                                                               "title" => $material["title"] ?? $material["file_name"],
                                                               "description" => $material["description"] ?? "",
                                                               "file_type" => strtoupper($extension),
                                                               "file_size" => $material["file_size"] ?? 0,
                                                               "uploaded_at" => $material["created_at"],
                                                               "download_url" => base_url("material/download/" . $material["id"])
                                                           ]) ?>)'
                                                           title="View Info">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="<?= base_url('material/download/' . $material['id']) ?>" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Grid View (Hidden by default) -->
                            <div id="grid-view" class="materials-view" style="display: none;">
                                <div class="row g-3">
                                    <?php foreach ($materials as $material): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 border shadow-sm hover-card">
                                            <div class="card-body">
                                                <div class="text-center mb-3">
                                                    <?php
                                                    $extension = pathinfo($material['file_name'], PATHINFO_EXTENSION);
                                                    $iconClass = match(strtolower($extension)) {
                                                        'pdf' => 'fa-file-pdf text-danger',
                                                        'ppt', 'pptx' => 'fa-file-powerpoint text-warning',
                                                        'doc', 'docx' => 'fa-file-word text-primary',
                                                        'xls', 'xlsx' => 'fa-file-excel text-success',
                                                        default => 'fa-file text-secondary'
                                                    };
                                                    ?>
                                                    <i class="fas <?= $iconClass ?> fa-4x mb-3"></i>
                                                    <h6 class="card-title fw-bold text-truncate" title="<?= esc($material['file_name']) ?>">
                                                        <?= esc($material['file_name']) ?>
                                                    </h6>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="badge bg-light text-dark">
                                                        <?= strtoupper($extension) ?>
                                                    </span>
                                                    <small class="text-muted">
                                                        <?= date('M j, Y', strtotime($material['created_at'])) ?>
                                                    </small>
                                                </div>                                                <div class="d-grid gap-2">
                                                    <button type="button"
                                                       class="btn btn-sm btn-outline-info" 
                                                       onclick='viewMaterialInfo(<?= json_encode([
                                                           "id" => $material["id"],
                                                           "file_name" => $material["file_name"],
                                                           "title" => $material["title"] ?? $material["file_name"],
                                                           "description" => $material["description"] ?? "",
                                                           "file_type" => strtoupper($extension),
                                                           "file_size" => $material["file_size"] ?? 0,
                                                           "uploaded_at" => $material["created_at"],
                                                           "download_url" => base_url("material/download/" . $material["id"])
                                                       ]) ?>)'>
                                                        <i class="fas fa-eye me-1"></i>View Info
                                                    </button>
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

                        <?php else: ?>
                            <!-- No Materials -->
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No Materials Available</h5>
                                <p class="text-muted">Your instructor hasn't uploaded any materials yet. Check back later!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>    </div>
</div>

<!-- Material Preview Modal -->
<div class="modal fade" id="materialPreviewModal" tabindex="-1" aria-labelledby="materialPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="materialPreviewModalLabel">
                    <i class="fas fa-info-circle me-2"></i><span id="previewFileName">Material Information</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="min-height: 500px;">
                <div id="previewContent" class="w-100 h-100">
                    <!-- Content will be loaded here -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading preview...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a id="downloadLink" href="#" class="btn btn-primary" download>
                    <i class="fas fa-download me-2"></i>Download File
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
    cursor: pointer;
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

#previewContent iframe {
    width: 100%;
    height: 70vh;
    border: none;
}

#previewContent img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
}

.preview-error {
    text-align: center;
    padding: 3rem;
}

.preview-error i {
    font-size: 4rem;
    color: #6c757d;
    margin-bottom: 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewListBtn = document.getElementById('view-list');
    const viewGridBtn = document.getElementById('view-grid');
    const listView = document.getElementById('list-view');
    const gridView = document.getElementById('grid-view');

    if (viewListBtn && viewGridBtn && listView && gridView) {
        viewListBtn.addEventListener('click', function() {
            listView.style.display = 'block';
            gridView.style.display = 'none';
            viewListBtn.classList.add('active');
            viewGridBtn.classList.remove('active');
        });

        viewGridBtn.addEventListener('click', function() {
            listView.style.display = 'none';
            gridView.style.display = 'block';
            viewGridBtn.classList.add('active');
            viewListBtn.classList.remove('active');
        });
    }
});

/**
 * View material information in modal
 */
function viewMaterialInfo(material) {
    const modal = new bootstrap.Modal(document.getElementById('materialPreviewModal'));
    const previewContent = document.getElementById('previewContent');
    const previewFileNameEl = document.getElementById('previewFileName');
    const downloadLink = document.getElementById('downloadLink');
    
    // Set modal title
    previewFileNameEl.textContent = material.title || material.file_name;
    
    // Set download link
    downloadLink.href = material.download_url;
    downloadLink.download = material.file_name;    // Get file icon based on type
    let fileIcon = 'fa-file';
    let iconColor = 'text-secondary';
    
    switch(material.file_type.toLowerCase()) {
        case 'pdf':
            fileIcon = 'fa-file-pdf';
            iconColor = 'text-danger';
            break;
        case 'ppt':
        case 'pptx':
            fileIcon = 'fa-file-powerpoint';
            iconColor = 'text-warning';
            break;
        case 'doc':
        case 'docx':
            fileIcon = 'fa-file-word';
            iconColor = 'text-primary';
            break;
        case 'xls':
        case 'xlsx':
            fileIcon = 'fa-file-excel';
            iconColor = 'text-success';
            break;
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
        case 'webp':
            fileIcon = 'fa-file-image';
            iconColor = 'text-info';
            break;
        case 'txt':
            fileIcon = 'fa-file-alt';
            iconColor = 'text-secondary';
            break;
        case 'mp4':
        case 'webm':
        case 'ogg':
            fileIcon = 'fa-file-video';
            iconColor = 'text-danger';
            break;
        case 'mp3':
        case 'wav':
            fileIcon = 'fa-file-audio';
            iconColor = 'text-primary';
            break;
        default:
            fileIcon = 'fa-file';
            iconColor = 'text-secondary';
    }
    
    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }
    
    // Format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return date.toLocaleDateString('en-US', options);
    }
    
    // Build information HTML
    const infoHTML = `
        <div class="p-5">
            <div class="text-center mb-4">
                <i class="fas ${fileIcon} ${iconColor}" style="font-size: 5rem;"></i>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="card-title fw-bold mb-4 text-center">${material.title || material.file_name}</h4>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-file me-3 mt-1 ${iconColor}"></i>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">File Name</small>
                                        <strong class="d-block">${material.file_name}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-tag me-3 mt-1 text-primary"></i>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">File Type</small>
                                        <strong class="d-block">${material.file_type}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-hdd me-3 mt-1 text-success"></i>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">File Size</small>
                                        <strong class="d-block">${formatFileSize(material.file_size)}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-calendar-alt me-3 mt-1 text-info"></i>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">Uploaded On</small>
                                        <strong class="d-block">${formatDate(material.uploaded_at)}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${material.description ? `
                        <div class="col-12">
                            <div class="info-item">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-align-left me-3 mt-1 text-secondary"></i>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">Description</small>
                                        <p class="mb-0">${material.description}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Click the download button below to get this file
                </p>
            </div>
        </div>
    `;
    
    // Display the information
    previewContent.innerHTML = infoHTML;
    
    // Open modal
    modal.show();
}
</script>
