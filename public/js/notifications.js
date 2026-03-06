/**
 * Notification System - jQuery Implementation
 * 
 * This file handles all notification-related functionality including:
 * - Loading notifications via AJAX
 * - Updating badge counts
 * - Populating notification dropdown
 * - Marking notifications as read
 * 
 * Dependencies: jQuery 3.7.1+, Bootstrap 5.3.3+
 */

$(document).ready(function() {
    // Load notifications on page load
    loadNotifications();
    
    // Reload notifications when dropdown is opened
    $('#notificationsDropdown').on('click', function() {
        loadNotifications();
    });
    
    // REAL-TIME UPDATES: Auto-refresh notifications every 60 seconds
    setInterval(function() {
        loadNotifications(true); // Pass true to indicate background refresh
    }, 60000); // 60 seconds = 60000 milliseconds
    
    // Optional: Add visibility change handler to refresh when tab becomes active
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            // Tab is now visible, refresh notifications
            loadNotifications(true);
        }
    });
});

/**
 * Load notifications using jQuery AJAX
 * Uses $.get() to call /notifications endpoint
 * 
 * @param {boolean} isBackgroundRefresh - If true, this is an automatic background refresh
 */
function loadNotifications(isBackgroundRefresh = false) {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';
    
    // Store previous unread count to detect new notifications
    const previousUnreadCount = parseInt($('#notificationBadge').text()) || 0;
    
    $.get(baseUrl + 'notifications', function(data) {
        if (data.success) {
            const currentUnreadCount = data.unread_count;
            
            // Update badge count
            updateNotificationBadge(currentUnreadCount);
            
            // Populate dropdown menu with notifications
            populateNotifications(data.notifications);
            
            // If this is a background refresh and there are new notifications
            if (isBackgroundRefresh && currentUnreadCount > previousUnreadCount) {
                const newNotificationsCount = currentUnreadCount - previousUnreadCount;
                
                // Show desktop notification (if permitted)
                showDesktopNotification(newNotificationsCount, data.notifications[0]);
                
                // Play notification sound
                playNotificationSound();
                
                // Add visual pulse animation to bell icon
                animateNotificationIcon();
                
                // Show toast notification
                showToast(
                    'New Notification' + (newNotificationsCount > 1 ? 's' : ''),
                    `You have ${newNotificationsCount} new notification${newNotificationsCount > 1 ? 's' : ''}`,
                    'info'
                );
            }
        }
    }).fail(function(xhr, status, error) {
        console.error('Failed to load notifications:', error);
        
        // Only show error in dropdown if it's not a background refresh
        if (!isBackgroundRefresh) {
            $('#notificationsList').html(`
                <li class="text-center py-3">
                    <i class="fas fa-exclamation-triangle text-danger fs-3"></i>
                    <p class="mb-0 small text-danger mt-2">Failed to load notifications</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="loadNotifications()">
                        <i class="fas fa-sync-alt me-1"></i>Retry
                    </button>
                </li>
            `);
        }
    });
}

/**
 * Update notification badge count
 * If count is 0, hide badge; otherwise show it
 */
function updateNotificationBadge(count) {
    const badge = $('#notificationBadge');
    
    if (count > 0) {
        // Show badge with count
        badge.text(count > 99 ? '99+' : count);
        badge.show();
    } else {
        // Hide badge when count is 0
        badge.hide();
    }
}

/**
 * Populate dropdown menu with notification list
 * Uses Bootstrap alert classes for styling
 */
function populateNotifications(notifications) {
    const list = $('#notificationsList');
    list.empty();
    
    if (notifications.length === 0) {
        // No notifications - show empty state
        list.html(`
            <li class="px-3 py-4 text-center">
                <i class="fas fa-bell-slash text-muted fs-2 mb-2"></i>
                <p class="mb-0 text-muted">No notifications</p>
                <small class="text-muted">You're all caught up!</small>
            </li>
        `);
        return;
    }
    
    // Add each notification to the list
    notifications.forEach(function(notification, index) {
        const alertClass = notification.is_unread ? 'alert-info' : 'alert-light';
        const boldClass = notification.is_unread ? 'fw-bold' : '';
        const iconColor = notification.is_unread ? 'text-primary' : 'text-muted';
        
        const notificationHtml = `
            <li id="notification-${notification.id}" class="border-bottom">
                <div class="alert ${alertClass} mb-0 rounded-0 border-0" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle ${iconColor} me-2 mt-1"></i>
                        <div class="flex-grow-1">
                            <p class="mb-1 small ${boldClass}">
                                ${escapeHtml(notification.message)}
                            </p>
                            <small class="text-muted d-block">
                                <i class="fas fa-clock me-1"></i>
                                ${formatToPhilippineTime(notification.created_at || notification.formatted_date)}
                            </small>
                            ${notification.is_unread ? `
                                <button class="btn btn-sm btn-primary mt-2 mark-read-btn" 
                                        data-id="${notification.id}"
                                        onclick="markAsRead(${notification.id})">
                                    <i class="fas fa-check me-1"></i>
                                    Mark as Read
                                </button>
                            ` : `
                                <span class="badge bg-success mt-2">
                                    <i class="fas fa-check-circle"></i> Read
                                </span>
                            `}
                        </div>
                    </div>
                </div>
            </li>
        `;
        
        list.append(notificationHtml);
    });
}

/**
 * Mark notification as read
 * Uses $.post() to call /notifications/mark_read/{id} endpoint
 * Upon success, removes notification from list and updates badge
 */
function markAsRead(notificationId) {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const csrfHash = $('meta[name="csrf-hash"]').attr('content');
    
    // Disable the button to prevent double-clicks
    $(`button[data-id="${notificationId}"]`).prop('disabled', true).html(`
        <span class="spinner-border spinner-border-sm me-1"></span>
        Marking...
    `);
    
    $.post(
        baseUrl + 'notifications/mark_read/' + notificationId,
        JSON.stringify({ [csrfToken]: csrfHash }),
        function(data) {
            if (data.success) {
                // Remove notification from list with fade effect
                $(`#notification-${notificationId}`).fadeOut(300, function() {
                    $(this).remove();
                    
                    // Update badge count
                    updateNotificationBadge(data.unread_count);
                    
                    // If no notifications left, show empty state
                    if ($('#notificationsList li').length === 0) {
                        $('#notificationsList').html(`
                            <li class="px-3 py-4 text-center">
                                <i class="fas fa-bell-slash text-muted fs-2 mb-2"></i>
                                <p class="mb-0 text-muted">No notifications</p>
                                <small class="text-muted">You're all caught up!</small>
                            </li>
                        `);
                    }
                });
                
                // Show success toast (optional)
                showToast('Success', 'Notification marked as read', 'success');
            } else {
                // Show error message
                alert('Failed to mark notification as read: ' + data.message);
                // Re-enable button
                $(`button[data-id="${notificationId}"]`).prop('disabled', false).html(`
                    <i class="fas fa-check me-1"></i> Mark as Read
                `);
            }
        },
        'json'
    ).fail(function(xhr, status, error) {
        console.error('Error marking notification as read:', error);
        alert('An error occurred while marking the notification as read');
        // Re-enable button
        $(`button[data-id="${notificationId}"]`).prop('disabled', false).html(`
            <i class="fas fa-check me-1"></i> Mark as Read
        `);
    });
}

/**
 * Helper function to escape HTML and prevent XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Optional: Show toast notification
 * Enhanced version with actual Bootstrap toast implementation
 */
function showToast(title, message, type = 'info') {
    // Map type to Bootstrap classes
    const typeClasses = {
        'info': 'bg-info text-white',
        'success': 'bg-success text-white',
        'warning': 'bg-warning text-dark',
        'danger': 'bg-danger text-white'
    };
    
    const toastClass = typeClasses[type] || typeClasses['info'];
    
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast ${toastClass}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-bell me-2"></i>
                <strong class="me-auto">${escapeHtml(title)}</strong>
                <small class="text-muted">Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${escapeHtml(message)}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Initialize and show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    toast.show();
    
    // Remove toast from DOM after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

/**
 * Request and show desktop notification (browser notification API)
 */
function showDesktopNotification(count, latestNotification) {
    // Check if browser supports notifications
    if (!("Notification" in window)) {
        console.log('This browser does not support desktop notifications');
        return;
    }
    
    // Check notification permission
    if (Notification.permission === "granted") {
        // Permission already granted, show notification
        createDesktopNotification(count, latestNotification);
    } else if (Notification.permission !== "denied") {
        // Request permission
        Notification.requestPermission().then(function(permission) {
            if (permission === "granted") {
                createDesktopNotification(count, latestNotification);
            }
        });
    }
}

/**
 * Create and display desktop notification
 */
function createDesktopNotification(count, latestNotification) {
    const title = count === 1 
        ? 'New Notification' 
        : `${count} New Notifications`;
    
    const body = latestNotification 
        ? latestNotification.message 
        : 'You have new notifications';
    
    const notification = new Notification(title, {
        body: body,
        icon: '/favicon.ico', // Update with your app icon path
        badge: '/favicon.ico',
        tag: 'notification-' + Date.now(),
        requireInteraction: false,
        silent: false
    });
    
    // Click handler - focus window and open notifications dropdown
    notification.onclick = function() {
        window.focus();
        $('#notificationsDropdown').dropdown('show');
        notification.close();
    };
    
    // Auto-close after 10 seconds
    setTimeout(function() {
        notification.close();
    }, 10000);
}

/**
 * Play notification sound
 */
function playNotificationSound() {
    try {
        // Create audio element for notification sound
        // Using a simple beep sound encoded in base64
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiDgIG2m98OScTgwOUKXh8bZjHAU5kdXzzHkvBSF1x/DdkUELFFux6+uoVBQKRp/h8r5sIgUsgs/y2Ik5iBtqvPDjm0wMDVGl4fG2Yx0FOZPa88xxLQUgdsfx3ZJBDBRasuzsqFUUCkef4vO+bCIFLILP8tmJOogba7zw45tNDA1RpeHxt2McBTmT2vPMcS0FIHbH8d2SQQwUWrLs7KhVFApHn+LzvmwiBSyCz/LZiTqIG2u88eSbTQwNUKXh8bdlHAU5k9rzzHEtBSB2x/HdkkEMFFqy7OyoVRQLR5/i875sIgUsgc/y2Yk6iBtrvPHjnE4MDVGl4fG3ZRwFOJPa88xxLQUgdsfx3ZJCDBVbsuvqp1QUC0af4fO+bCEFLIHQ8tmJOohbaLzw45tNDA1RpeHxt2UcBTmT2vPMcC0FIHbH8d2SQgwVW7Lr6qdUFAtGn+HzvmwhBSyBzvLaiTqHW2i78OKaTgwOUKPh8bdjHAU5k9rzzHAtBSJ2x/HdkkIMFFqy7OyoVRQLR5/h875sIgUsgc7y2ok5iFtpvPDjm00MDlCj4fG3YxwFOZPa88xwLQYgd8fx3ZJCDBRasuzsqFYUC0af4fO+bCIFLIHO8tqIOohbaLvw4ptODA5QpOHxt2QcBjmS2vPMcC0FIHbH8d2RQgwUW7Ls66dVFAtHn+HzvmwhBSyB0PLaiTmIW2i78OOcTQwOUKPh8bdjHAU5k9rzznAtBSB2yPDdk0IMFFux6+qoVBQKR5/h8r5tIgUsgdDy2Yk5iFtovPDkm00MDlCk4PG4Yx0FOJPa881xLQUhd8jw3ZFBCxRbsevqp1UUC0ef4fK+bSIFLIHQ8tmJOYdbaLzw5JxMDA1QpODxuGMdBTmU2fLMcS0FIXbI8N2RQQoUW7Hr6qdVFApHn+DyvmwiBiyBz/LaiTmIWmm98OSbTQwNUKPh8bhlHAU5k9ryzHEtBSB2yPDdk0IMFFqy6+qoVRQKR5/h8r5sIgUsgdDy2ok5iFtpvPDjm00MDlCk4PG4Yx0FOZPa88xxLQUhdsjw3ZJCDBNasuvqp1UVCkaf4PK/bCMFLIHP8tuJOYhbaLvw5JxMDA5Qo+Hxt2QcBTmT2vPMciwFIXbI8N2SQgsUW7Hr6qhVFApHn+HzwGwiBSyBz/LZiTmIW2m88OSbTQwNUKTg8bhjHQU5k9rzzHEtBSF2yPDdkkILFFqy6+uoVRQLSJ/h8r9sIwYsgc/y24k5h1tou/DknE0MDVCk4PG4ZBwFOJPa881xLQYhdsjw3ZJCDBN');
        
        audio.volume = 0.3; // Set volume to 30%
        audio.play().catch(function(error) {
            console.log('Could not play notification sound:', error);
        });
    } catch (error) {
        console.log('Error playing notification sound:', error);
    }
}

/**
 * Animate notification bell icon with pulse effect
 */
function animateNotificationIcon() {
    const bellIcon = $('#notificationsDropdown i.fa-bell');
    
    if (bellIcon.length > 0) {
        // Add pulse animation class
        bellIcon.addClass('notification-pulse');
        
        // Remove animation after 2 seconds
        setTimeout(function() {
            bellIcon.removeClass('notification-pulse');
        }, 2000);
    }
}

function formatToPhilippineTime(dateString) {
    if (!dateString) return '';
    // Parse as UTC, then convert to Asia/Manila
    const date = new Date(dateString);
    // Options for formatting
    const options = {
        timeZone: 'Asia/Manila',
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    };
    return date.toLocaleString('en-PH', options);
}

// Add CSS for notification pulse animation if not already present
if (!document.getElementById('notification-animation-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-animation-styles';
    style.textContent = `
        @keyframes notificationPulse {
            0% { transform: scale(1); }
            25% { transform: scale(1.2); }
            50% { transform: scale(1); }
            75% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .notification-pulse {
            animation: notificationPulse 0.5s ease-in-out 4;
            color: #0d6efd !important;
        }
        
        #notificationBadge {
            animation: badgePulse 2s ease-in-out infinite;
        }
        
        @keyframes badgePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
    `;
    document.head.appendChild(style);
}
