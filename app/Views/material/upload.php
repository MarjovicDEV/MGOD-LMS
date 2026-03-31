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

<!-- Material Upload View - File upload functionality for teachers and admins -->
<div class="lms-admin-view min-vh-100">
    <div class="container py-4">        
    <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-success text-white p-4 rounded-3 admin-hero">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="mb-3 mb-md-0">
                                <h2 class="mb-2 fw-bold">
                                    <i class="fas fa-folder-open me-2"></i>Course Materials
                                </h2>
                                <p class="mb-1 opacity-75">
                                    <i class="fas fa-book-open me-1"></i> 
                                    <strong><?= esc($course['title'] ?? $course['course_name'] ?? 'N/A') ?></strong>
                                </p>
                                <p class="mb-0 opacity-75">
                                    <small>
                                        <i class="fas fa-code me-1"></i><?= esc($course['course_code']) ?>
                                        <?php if (isset($course['credits'])): ?>
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-graduation-cap me-1"></i><?= $course['credits'] ?> Credits
                                        <?php endif; ?>
                                    </small>
                                </p>
                            </div>
                            <div class="text-md-end">
                                <div class="badge bg-light text-success fs-5 mb-2 px-4 py-2">
                                    <i class="fas fa-files me-2"></i> 
                                    <strong><?= count($materials ?? []) ?></strong> Material<?= count($materials ?? []) !== 1 ? 's' : '' ?>
                                </div>
                                <?php if (isset($course['status'])): ?>
                                    <div class="mt-2">
                                        <?php
                                        $statusStyles = [
                                            'draft' => ['color' => 'warning', 'icon' => 'fa-pencil-alt', 'text' => 'Draft'],
                                            'open' => ['color' => 'success', 'icon' => 'fa-check-circle', 'text' => 'Open'],
                                            'active' => ['color' => 'success', 'icon' => 'fa-check-circle', 'text' => 'Active'],
                                            'closed' => ['color' => 'danger', 'icon' => 'fa-lock', 'text' => 'Closed'],
                                            'completed' => ['color' => 'info', 'icon' => 'fa-flag-checkered', 'text' => 'Completed'],
                                            'cancelled' => ['color' => 'dark', 'icon' => 'fa-times-circle', 'text' => 'Cancelled']
                                        ];
                                        $style = $statusStyles[$course['status']] ?? ['color' => 'secondary', 'icon' => 'fa-question-circle', 'text' => 'Unknown'];
                                        ?>
                                        <span class="badge bg-<?= $style['color'] ?> rounded-pill px-3 py-2">
                                            <i class="fas <?= $style['icon'] ?> me-1"></i><?= $style['text'] ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Please correct the following errors:
                <ul class="mb-0 mt-2">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- File Upload Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload New Material</h5>
                    </div>
                    <div class="card-body p-4">                        <form method="post" action="<?= base_url(($user['role'] === 'admin' ? 'admin' : 'teacher') . '/course/' . $course_offering_id . '/upload') ?>" 
                              enctype="multipart/form-data" id="uploadForm">
                            <?= csrf_field() ?>
                              <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="material_file" class="form-label fw-semibold">
                                            <i class="fas fa-file-pdf text-danger me-1"></i>
                                            <i class="fas fa-file-powerpoint text-warning me-2"></i>
                                            Select File to Upload
                                        </label>
                                        <input type="file" class="form-control form-control-lg" id="material_file" 
                                               name="material_file" required 
                                               accept=".pdf,.ppt,.pptx,application/pdf,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation">
                                        <div class="form-text">
                                            <i class="fas fa-info-circle text-primary"></i>
                                            <strong>Allowed file types:</strong> PDF, PowerPoint (PPT/PPTX) only
                                            <br>
                                            <i class="fas fa-weight-hanging text-primary"></i>
                                            <strong>Maximum file size:</strong> 10 MB
                                        </div>
                                        <!-- File Preview -->
                                        <div id="filePreview" class="mt-3 d-none">
                                            <div class="alert alert-info d-flex align-items-center">
                                                <i class="fas fa-file fa-2x me-3"></i>
                                                <div>
                                                    <strong id="fileName"></strong>
                                                    <br>
                                                    <small id="fileSize" class="text-muted"></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-transparent">Click here to Upload</label>
                                        <button type="submit" class="btn btn-success btn-lg w-100" id="uploadBtn">
                                            <i class="fas fa-cloud-upload-alt me-2"></i>Upload Material
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Guidelines -->
                            <div class="alert alert-light border mt-3">
                                <h6 class="alert-heading">
                                    <i class="fas fa-clipboard-check text-primary"></i> Upload Guidelines
                                </h6>
                                <ul class="mb-0 small">
                                    <li><strong>Supported formats:</strong> PDF, PowerPoint (PPT/PPTX)</li>
                                    <li><strong>Maximum file size:</strong> 10 MB</li>
                                    <li><strong>File naming:</strong> Use descriptive names for easy identification</li>
                                    <li><strong>Content:</strong> Ensure materials are relevant to the course</li>
                                </ul>
                            </div>
                        </form>
                    </div>
                </div>
            </div>        </div>        

        <!-- Existing Materials List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">📂 Course Materials</h5>
                                <small class="text-muted">Manage uploaded files for this course</small>
                            </div>
                            <div class="text-muted small">
                                Total: <?= count($materials) ?> files
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <?php if (!empty($materials)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>File</th>
                                            <th class="text-center">Type</th>
                                            <th class="text-center">Uploaded</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($materials as $material): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <?php
                                                        // Determine file icon based on extension
                                                        $extension = strtolower(pathinfo($material['file_name'], PATHINFO_EXTENSION));
                                                        $fileIcons = [
                                                            'pdf' => ['icon' => '📄', 'color' => 'danger'],
                                                            'doc' => ['icon' => '📝', 'color' => 'primary'],
                                                            'docx' => ['icon' => '📝', 'color' => 'primary'],
                                                            'xls' => ['icon' => '📊', 'color' => 'success'],
                                                            'xlsx' => ['icon' => '📊', 'color' => 'success'],
                                                            'ppt' => ['icon' => '📊', 'color' => 'warning'],
                                                            'pptx' => ['icon' => '📊', 'color' => 'warning'],
                                                            'txt' => ['icon' => '📄', 'color' => 'secondary'],
                                                            'rtf' => ['icon' => '📄', 'color' => 'secondary'],
                                                            'jpg' => ['icon' => '🖼️', 'color' => 'info'],
                                                            'jpeg' => ['icon' => '🖼️', 'color' => 'info'],
                                                            'png' => ['icon' => '🖼️', 'color' => 'info'],
                                                            'gif' => ['icon' => '🖼️', 'color' => 'info'],
                                                            'mp4' => ['icon' => '🎥', 'color' => 'dark'],
                                                            'avi' => ['icon' => '🎥', 'color' => 'dark'],
                                                            'mov' => ['icon' => '🎥', 'color' => 'dark']
                                                        ];
                                                        $fileIcon = $fileIcons[$extension] ?? ['icon' => '📎', 'color' => 'secondary'];
                                                        ?>
                                                        <div class="bg-<?= $fileIcon['color'] ?> text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px; font-size: 1.2rem;">
                                                            <?= $fileIcon['icon'] ?>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark"><?= esc($material['file_name']) ?></div>
                                                        <small class="text-muted">
                                                            <?php if (file_exists(WRITEPATH . $material['file_path'])): ?>
                                                                Size: <?= number_format(filesize(WRITEPATH . $material['file_path']) / 1024, 1) ?> KB
                                                            <?php else: ?>
                                                                <span class="text-warning">File not found</span>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark rounded-pill">
                                                    <?= strtoupper($extension) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($material['created_at'])) ?><br>
                                                    <span class="text-muted"><?= date('g:i A', strtotime($material['created_at'])) ?></span>
                                                </small>
                                            </td>                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <!-- View Button (for PDF files) -->
                                                    <?php if ($extension === 'pdf'): ?>
                                                        <a href="<?= base_url('material/view/' . $material['id']) ?>" 
                                                           class="btn btn-outline-primary btn-sm" 
                                                           title="View Material"
                                                           target="_blank">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Download Button -->
                                                    <a href="<?= base_url('material/download/' . $material['id']) ?>" 
                                                       class="btn btn-outline-success btn-sm" 
                                                       title="Download Material">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    
                                                    <!-- Delete Button (Admin Only) -->
                                                    <?php if ($user['role'] === 'admin'): ?>
                                                        <button type="button"
                                                                class="btn btn-outline-danger btn-sm" 
                                                                onclick="confirmDelete(<?= $material['id'] ?>, '<?= esc($material['file_name'], 'js') ?>')"
                                                                title="Delete Material">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-folder-open text-muted" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="text-muted">No materials uploaded yet</h6>
                                <p class="text-muted small mb-0">Upload your first course material using the form above.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Enhanced File Upload Experience -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('material_file');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileType = document.getElementById('fileType');
    const uploadForm = document.getElementById('uploadForm');
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadProgress = document.getElementById('uploadProgress');

    // File input change handler
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Show file preview
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileType.textContent = file.type || 'Unknown';
            filePreview.style.display = 'block';
            
            // Validate file size (10MB limit)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (file.size > maxSize) {
                alert('File size exceeds 10MB limit. Please choose a smaller file.');
                fileInput.value = '';
                filePreview.style.display = 'none';
                return;
            }
            
            // Validate file extension
            const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            if (!allowedExtensions.includes(fileExtension)) {
                alert('Invalid file type. Please choose a supported file format.');
                fileInput.value = '';
                filePreview.style.display = 'none';
                return;
            }
        } else {
            filePreview.style.display = 'none';
        }
    });

    // Form submission handler
    uploadForm.addEventListener('submit', function(e) {
        const file = fileInput.files[0];
        
        if (!file) {
            e.preventDefault();
            alert('Please select a file to upload.');
            return;
        }
        
        // Show upload progress and disable button
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
        uploadBtn.disabled = true;
        uploadProgress.style.display = 'block';
        
        // Simulate progress (since we can't track real progress with standard form submission)
        let progress = 0;
        const progressBar = uploadProgress.querySelector('.progress-bar');
        const progressInterval = setInterval(function() {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90; // Don't go to 100% until form actually submits
            
            progressBar.style.width = progress + '%';
        }, 300);
        
        // Clear interval after 10 seconds (form should have submitted by then)
        setTimeout(function() {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
        }, 10000);
    });

    // Format file size helper function
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // Drag and drop functionality
    const dropZone = document.querySelector('.card-body');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
            e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('border-success', 'bg-light');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-success', 'bg-light');
    }

    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            fileInput.files = files;
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
        }
    }

    // File input preview
    document.getElementById('material_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('filePreview');
        
        if (file) {
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            
            fileName.textContent = file.name;
            fileSize.textContent = `Size: ${(file.size / 1024 / 1024).toFixed(2)} MB`;
            
            preview.classList.remove('d-none');
            
            // Validate file size
            if (file.size > 10 * 1024 * 1024) {
                alert('File size exceeds 10MB limit!');
                e.target.value = '';
                preview.classList.add('d-none');
            }
            
            // Validate file type (PDF and PPT only)
            const allowedExtensions = ['pdf', 'ppt', 'pptx'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(fileExtension)) {
                alert('Only PDF and PowerPoint (PPT/PPTX) files are allowed!');
                e.target.value = '';
                preview.classList.add('d-none');
            }
        } else {
            preview.classList.add('d-none');
        }
    });

    // Upload form loading state
    document.getElementById('uploadForm').addEventListener('submit', function() {
        const btn = document.getElementById('uploadBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Uploading...';
    });
});

// Delete confirmation function
function confirmDelete(materialId, fileName) {
    if (confirm('Are you sure you want to delete this material?\n\nFile: ' + fileName + '\n\nThis action cannot be undone and students will no longer be able to access this file!')) {
        window.location.href = '<?= base_url('material/delete/') ?>' + materialId;
    }
}

</script>

<style>
/* Custom styles for enhanced file upload experience */
.card-body.drag-over {
    border: 2px dashed #28a745 !important;
    background-color: #f8fff9 !important;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    0% { background-position: 0 0; }
    100% { background-position: 40px 0; }
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.075);
}

.btn-group .btn {
    transition: all 0.2s ease-in-out;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#filePreview {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>