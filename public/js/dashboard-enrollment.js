// Student Dashboard Enrollment Script
// Version: 4.3 - FULL FUNCTIONALITY

// Use DOMContentLoaded with proper error handling
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Dashboard enrollment script loaded - v4.3');
    
    // Initialize after a short delay to ensure everything is loaded
    setTimeout(initializeEnrollment, 500);
});

function initializeEnrollment() {
    // Get all enrollment buttons
    const enrollButtons = document.querySelectorAll('.enroll-btn');
    console.log('Found enrollment buttons:', enrollButtons.length);
    
    // Check if modal exists before initializing
    let enrollmentModal = null;
    const modalElement = document.getElementById('enrollmentModal');
    if (modalElement) {
        try {
            enrollmentModal = new bootstrap.Modal(modalElement);
            console.log('Modal initialized successfully');
        } catch (e) {
            console.error('Error initializing modal:', e);
        }
    } else {
        console.error('Modal element not found!');
    }
    
    const modalBody = document.getElementById('enrollmentModalBody');
    const modalTitle = document.getElementById('enrollmentModalLabel');
    
    // Track courses being enrolled to prevent duplicate requests
    const enrollmentInProgress = new Set();

    if (enrollButtons.length === 0) {
        console.error('No enrollment buttons found on the page!');
        return;
    }

    enrollButtons.forEach((button, index) => {
        console.log(`Attaching handler to button ${index}:`, button.dataset);
        button.addEventListener('click', function(e) {
            console.log('Button clicked!', e);
            e.preventDefault();
            const courseId = this.dataset.courseId;
            const courseTitle = this.dataset.courseTitle;
            const originalButton = this;
            
            // Prevent duplicate enrollment attempts
            if (enrollmentInProgress.has(courseId)) {
                console.log('⚠️ Enrollment already in progress for course:', courseId);
                return;
            }
            
            // Mark enrollment as in progress
            enrollmentInProgress.add(courseId);
            console.log('🔄 Starting enrollment for course:', courseId);

            // Disable button and show loading state
            originalButton.disabled = true;
            originalButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Enrolling...';

            // Get CSRF tokens from meta tags
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfHash = document.querySelector('meta[name="csrf-hash"]').getAttribute('content');

            // Prepare the enrollment request with CSRF protection
            const formData = new FormData();
            formData.append('course_id', courseId);
            formData.append(csrfToken, csrfHash);

            // Make AJAX request with CSRF headers
            fetch(window.ENROLL_URL || '/course/enroll', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfHash
                }
            })
            .then(response => {
                console.log('📦 Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('📦 Response data:', data);
                
                // Update CSRF token if provided in response
                if (data.csrf_hash) {
                    document.querySelector('meta[name="csrf-hash"]').setAttribute('content', data.csrf_hash);
                    console.log('🔐 CSRF token updated');
                }
                
                if (data.success) {
                    console.log('✅ Enrollment successful!');
                    
                    // Success: Show success modal and update UI
                    modalTitle.textContent = 'Enrollment Successful!';
                    modalBody.innerHTML = `
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-success">Welcome to ${data.data.course_code}!</h5>
                            <p class="mb-3"><strong>${data.data.course_title}</strong></p>
                            <p class="text-muted">You have been successfully enrolled in this course. You can now access course materials and start learning.</p>
                            
                            <div class="card border-0 bg-light mt-3 mb-3">
                                <div class="card-body py-2">
                                    <div class="row text-start">
                                        <div class="col-6 mb-2">
                                            <small class="text-muted">📅 Enrollment Date</small>
                                            <div class="fw-bold">${data.data.enrollment_date_formatted || 'N/A'}</div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <small class="text-muted">📚 Section</small>
                                            <div class="fw-bold">${data.data.section || 'N/A'}</div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <small class="text-muted">📖 Credits</small>
                                            <div class="fw-bold">${data.data.credits || 'N/A'}</div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <small class="text-muted">🎓 Term</small>
                                            <div class="fw-bold">${data.data.term || 'N/A'}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" class="btn btn-primary btn-sm" onclick="window.location.reload()">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh Page
                                </button>
                                <small class="text-muted d-block mt-2">Click refresh to see your enrolled courses</small>
                            </div>
                        </div>
                    `;
                    
                    // Update the course card to show enrolled status
                    const courseCard = originalButton.closest('.course-card');
                    if (courseCard) {
                        // Update the badge to show enrolled status
                        const badge = courseCard.querySelector('.badge');
                        if (badge) {
                            badge.className = 'badge bg-success rounded-pill small';
                            badge.textContent = 'Enrolled';
                        }
                        
                        // Replace enrollment button with enrolled status
                        originalButton.outerHTML = `
                            <div class="btn btn-success btn-sm w-100" style="pointer-events: none;">
                                <i class="fas fa-check-circle me-1"></i>
                                Successfully Enrolled!
                            </div>
                        `;
                        
                        // Add a subtle visual indicator
                        courseCard.style.border = '2px solid #198754';
                        courseCard.style.borderRadius = '0.375rem';
                    }
                    
                    // Show success modal
                    enrollmentModal.show();
                    
                } else {
                    // Error: Show error modal
                    console.log('❌ Enrollment failed:', data.error_code);
                    console.log('Error message:', data.message);
                    
                    try {
                        modalTitle.textContent = 'Enrollment Failed';
                        let errorMessage = data.message || 'An unexpected error occurred.';
                        
                        // Handle specific error types
                        if (data.error_code === 'ALREADY_ENROLLED') {
                            console.log('⚠️ Handling ALREADY_ENROLLED error');
                            
                            modalBody.innerHTML = `
                                <div class="text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-info-circle text-warning" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="text-warning">Already Enrolled</h5>
                                    <p class="text-muted">${errorMessage}</p>
                                </div>
                            `;
                            
                            enrollmentModal.show();
                            
                            // Update button to show already enrolled (don't allow retry)
                            originalButton.disabled = true;
                            originalButton.classList.remove('btn-primary');
                            originalButton.classList.add('btn-secondary');
                            originalButton.innerHTML = '<i class="fas fa-check-circle me-1"></i>Already Enrolled';
                            
                        } else if (data.error_code === 'PREREQUISITES_NOT_MET') {
                            console.log('🔒 Handling PREREQUISITES_NOT_MET error');
                            console.log('Missing prerequisites:', data.missing_prerequisites);
                            
                            // Build prerequisite list with detailed information
                            let prereqList = '';
                            if (data.missing_prerequisites && Array.isArray(data.missing_prerequisites) && data.missing_prerequisites.length > 0) {
                                prereqList = '<div class="bg-light rounded p-3 mt-3 mb-3">';
                                prereqList += '<p class="mb-2 text-start"><strong>Required Course(s):</strong></p>';
                                prereqList += '<ul class="list-unstyled text-start mb-0">';
                                
                                data.missing_prerequisites.forEach(course => {
                                    let reason = '';
                                    if (course.reason === 'not_completed') {
                                        reason = '<span class="badge bg-danger ms-2">Not Completed</span>';
                                    } else if (course.reason === 'insufficient_grade') {
                                        reason = `<span class="badge bg-warning text-dark ms-2">Grade: ${course.student_grade}% (Need: ${course.minimum_grade}%)</span>`;
                                    }
                                    
                                    prereqList += `
                                        <li class="mb-2 d-flex align-items-center">
                                            <i class="fas fa-book text-danger me-2"></i>
                                            <div>
                                                <strong>${course.course_code || 'N/A'}</strong> - ${course.title || 'Course'}
                                                ${reason}
                                                ${course.minimum_grade ? `<br><small class="text-muted">Minimum passing grade: ${course.minimum_grade}%</small>` : ''}
                                            </div>
                                        </li>
                                    `;
                                });
                                
                                prereqList += '</ul></div>';
                            } else {
                                prereqList = '<p class="text-muted">No prerequisite details available.</p>';
                            }
                            
                            modalBody.innerHTML = `
                                <div class="text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-lock text-danger" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="text-danger">⚠️ Prerequisites Not Met</h5>
                                    <p class="text-muted mb-2">You cannot enroll in <strong>${courseTitle}</strong> yet.</p>
                                    ${prereqList}
                                    <div class="alert alert-info mt-3 mb-0 text-start">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>What you need to do:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Complete the required prerequisite course(s)</li>
                                            <li>Pass with the minimum required grade</li>
                                            <li>Wait for your instructor to finalize your grades</li>
                                        </ul>
                                    </div>
                                </div>
                            `;
                            
                            console.log('🎭 Showing prerequisite modal');
                            enrollmentModal.show();
                            
                            // Update button to locked state (don't allow retry for prerequisites)
                            originalButton.disabled = true;
                            originalButton.classList.remove('btn-primary');
                            originalButton.classList.add('btn-secondary');
                            originalButton.innerHTML = '<i class="fas fa-lock me-1"></i>Prerequisites Required';
                            
                            // Update course card styling
                            const courseCard = originalButton.closest('.course-card');
                            if (courseCard) {
                                courseCard.style.opacity = '0.7';
                                const badge = courseCard.querySelector('.badge');
                                if (badge) {
                                    badge.className = 'badge bg-warning text-dark rounded-pill small';
                                    badge.textContent = 'Prerequisites Required';
                                }
                            }
                            
                        } else if (data.error_code === 'OFFERING_FULL') {
                            console.log('⚠️ Handling OFFERING_FULL error');
                            
                            modalBody.innerHTML = `
                                <div class="text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-users text-danger" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="text-danger">Course Full</h5>
                                    <p class="text-muted">${errorMessage}</p>
                                    <small class="text-muted">Please try another section or check back later.</small>
                                </div>
                            `;
                            
                            enrollmentModal.show();
                            
                            // Allow retry for course full (might refresh and try again)
                            originalButton.disabled = false;
                            originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
                            
                        } else {
                            // Generic error handler with more details if available
                            console.log('⚠️ Handling generic error:', data.error_code);
                            
                            let errorDetails = '';
                            if (data.error_code) {
                                errorDetails = `<div class="alert alert-secondary mt-3 mb-0"><small><strong>Error Code:</strong> ${data.error_code}</small></div>`;
                            }
                            
                            modalBody.innerHTML = `
                                <div class="text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="text-danger">Enrollment Error</h5>
                                    <p class="text-muted">${errorMessage}</p>
                                    ${errorDetails}
                                    <small class="text-muted d-block mt-3">Please try again later or contact support if the problem persists.</small>
                                </div>
                            `;
                            
                            enrollmentModal.show();
                            
                            // Allow retry for generic errors
                            originalButton.disabled = false;
                            originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
                        }
                        
                    } catch (error) {
                        console.error('💥 Error in error handling:', error);
                        console.error('Stack trace:', error.stack);
                        
                        // Fallback error display
                        modalTitle.textContent = 'Enrollment Failed';
                        modalBody.innerHTML = `
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-danger">Error</h5>
                                <p class="text-muted">${data.message || 'An error occurred during enrollment.'}</p>
                            </div>
                        `;
                        
                        enrollmentModal.show();
                        originalButton.disabled = false;
                        originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
                    }
                }
            })
            .catch(error => {
                console.error('💥 Network/Fetch error:', error);
                
                // Network or other error
                modalTitle.textContent = 'Connection Error';
                modalBody.innerHTML = `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-wifi text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-danger">Connection Failed</h5>
                        <p class="text-muted">Unable to process your enrollment request. Please check your internet connection and try again.</p>
                    </div>
                `;
                
                enrollmentModal.show();
                
                // Reset button state for network errors (allow retry)
                originalButton.disabled = false;
                originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
            })
            .finally(() => {
                // Always remove from in-progress set
                enrollmentInProgress.delete(courseId);
                console.log('🏁 Enrollment process completed for course:', courseId);
                
                // Safety net: ensure button is never stuck in loading state
                if (originalButton && originalButton.innerHTML && originalButton.innerHTML.includes('Enrolling')) {
                    console.log('⚠️ Safety net triggered - button still in loading state!');
                    originalButton.disabled = false;
                    originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
                }
            });
        });
    });
    console.log('✅ Enrollment handlers attached to', enrollButtons.length, 'buttons');
    
    // Load pending enrollments
    loadPendingEnrollments();
}

function loadPendingEnrollments() {
    fetch(window.PENDING_URL || '/enrollment/pending', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.pending_enrollments.length > 0) {
            displayPendingEnrollments(data.pending_enrollments);
            document.getElementById('pendingApprovalsSection').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error loading pending enrollments:', error);
    });
}

function displayPendingEnrollments(enrollments) {
    const container = document.getElementById('pendingApprovalsList');
    container.innerHTML = '';
    
    enrollments.forEach(enrollment => {
        const card = document.createElement('div');
        card.className = 'card mb-3 border-warning';
        card.innerHTML = `
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="card-title mb-1">
                            <i class="fas fa-graduation-cap me-2"></i>
                            ${enrollment.course_code} - ${enrollment.course_title}
                        </h6>
                        <p class="text-muted mb-1">
                            <small>
                                Section ${enrollment.section} • ${enrollment.term_name} • ${enrollment.academic_year}
                                <br>
                                <i class="fas fa-user me-1"></i>Enrolled by: ${enrollment.enrolled_by_name}
                            </small>
                        </p>
                        ${enrollment.notes ? `<p class="mb-0"><small><em>Notes: ${enrollment.notes}</em></small></p>` : ''}
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-success btn-sm me-2" onclick="respondToEnrollment(${enrollment.enrollment_id}, 'accept')">
                            <i class="fas fa-check me-1"></i>Accept
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="respondToEnrollment(${enrollment.enrollment_id}, 'reject')">
                            <i class="fas fa-times me-1"></i>Reject
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function respondToEnrollment(enrollmentId, action) {
    if (!confirm('Are you sure you want to ' + action + ' this enrollment?')) {
        return;
    }
    
    // Get CSRF tokens
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfHash = document.querySelector('meta[name="csrf-hash"]').getAttribute('content');
    
    // Use FormData for proper POST handling
    const formData = new FormData();
    formData.append('enrollment_id', enrollmentId);
    formData.append('action', action);
    formData.append(csrfToken, csrfHash);
    
    fetch(window.RESPOND_URL || '/enrollment/respond', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfHash
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadPendingEnrollments(); // Reload the list
            location.reload(); // Refresh to update enrolled courses
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error responding to enrollment:', error);
        showAlert('An error occurred. Please try again.', 'danger');
    });
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.getElementById('pendingApprovalsSection');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
