<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use CodeIgniter\HTTP\ResponseInterface;

class Notifications extends BaseController
{
    protected $notificationModel;
    
    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        helper('text'); // For text truncation if needed
    }    /**
     * Get notifications - Returns JSON response with unread count and notification list
     * Called via AJAX to fetch current user's notifications
     * 
     * @return ResponseInterface JSON response containing unread count and notifications
     */
    public function get()
    {
        // Check if user is logged in
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - Please login'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        // Get current user ID from session
        $userID = $this->session->get('userID');
        
        // Get limit from query parameter (optional)
        $limit = $this->request->getGet('limit');
        
        // Get unread notification count
        $unreadCount = $this->notificationModel->getUnreadCount($userID);
        
        // Get ALL notifications (with optional limit) - only visible ones (not hidden)
        $notifications = $this->notificationModel->getNotificationsForUser($userID, $limit);
          // Format notifications for display
        foreach ($notifications as &$notification) {
            $notification['formatted_date'] = $this->formatNotificationDate($notification['created_at']);
            $notification['time_ago'] = $this->timeAgo($notification['created_at']);
            $notification['is_unread'] = ($notification['is_read'] == 0);
            $notification['icon'] = $this->getNotificationIcon('info'); // Default icon since type field doesn't exist
            $notification['color'] = $this->getNotificationColor('info'); // Default color since type field doesn't exist
        }
        
        // Return JSON response
        return $this->response->setJSON([
            'success' => true,
            'unread_count' => $unreadCount,
            'total_count' => count($notifications),
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Get unread notifications only
     * 
     * @return ResponseInterface JSON response with unread notifications
     */
    public function getUnread()
    {
        // Check if user is logged in
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - Please login'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        $userID = $this->session->get('userID');
        $limit = $this->request->getGet('limit');
        
        // Get unread notifications
        $notifications = $this->notificationModel->getUnreadNotifications($userID, $limit);
          // Format notifications
        foreach ($notifications as &$notification) {
            $notification['formatted_date'] = $this->formatNotificationDate($notification['created_at']);
            $notification['time_ago'] = $this->timeAgo($notification['created_at']);
            $notification['icon'] = $this->getNotificationIcon('info'); // Default icon since type field doesn't exist
            $notification['color'] = $this->getNotificationColor('info'); // Default color since type field doesn't exist
        }
        
        return $this->response->setJSON([
            'success' => true,
            'unread_count' => count($notifications),
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Get notifications by type
     * 
     * @param string $type Notification type to filter by
     * @return ResponseInterface JSON response with filtered notifications
     */
    public function getByType($type = null)
    {
        // Check if user is logged in
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - Please login'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        if (!$type) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Notification type is required'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        $userID = $this->session->get('userID');
        $notifications = $this->notificationModel->getNotificationsByType($userID, $type);
        
        // Format notifications
        foreach ($notifications as &$notification) {
            $notification['formatted_date'] = $this->formatNotificationDate($notification['created_at']);
            $notification['time_ago'] = $this->timeAgo($notification['created_at']);
            $notification['is_unread'] = ($notification['is_read'] == 0);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'type' => $type,
            'count' => count($notifications),
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Get notification statistics
     * 
     * @return ResponseInterface JSON response with user notification statistics
     */
    public function getStats()
    {
        // Check if user is logged in
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - Please login'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        $userID = $this->session->get('userID');
        $stats = $this->notificationModel->getUserStats($userID);
        
        return $this->response->setJSON([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Mark notification as read - Accepts notification ID via POST
     * Updates the notification's is_read status to 1
     * 
     * @param int $id The notification ID to mark as read
     * @return ResponseInterface JSON response indicating success or failure
     */
    public function mark_as_read($id = null)
    {
        // Check if user is logged in
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - Please login'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        // Validate notification ID
        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid notification ID'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        // Get current user ID from session
        $userID = $this->session->get('userID');
        
        // Verify the notification belongs to the current user before marking as read
        $notification = $this->notificationModel->where('id', $id)->first();
        
        if (!$notification) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Notification not found'
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        
        // Security check: Ensure the notification belongs to the logged-in user
        if ($notification['user_id'] != $userID) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - This notification does not belong to you'
            ])->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
        }
        
        // Mark notification as read
        $result = $this->notificationModel->markAsRead($id);
        
        if ($result) {
            // Get updated unread count
            $unreadCount = $this->notificationModel->getUnreadCount($userID);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Notification marked as read',
                'unread_count' => $unreadCount
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Mark all notifications as read for current user
     * 
     * @return ResponseInterface JSON response indicating success or failure
     */
    public function markAllAsRead()
    {
        // Check if user is logged in
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - Please login'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        $userID = $this->session->get('userID');
        
        // Mark all notifications as read
        $result = $this->notificationModel->markAllAsRead($userID);
        
        if ($result !== false) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'All notifications marked as read',
                'unread_count' => 0
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to mark all notifications as read'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Hide notification - Accepts notification ID via POST
     * Marks the notification as hidden without deleting from database
     * 
     * @param int $id The notification ID to hide
     * @return ResponseInterface JSON response indicating success or failure
     */
    public function hide($id = null)
    {
        // Check if user is logged in
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - Please login'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        // Validate notification ID
        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid notification ID'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        // Get current user ID from session
        $userID = $this->session->get('userID');
        
        // Verify the notification belongs to the current user before hiding
        $notification = $this->notificationModel->where('id', $id)->first();
        
        if (!$notification) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Notification not found'
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        
        // Security check: Ensure the notification belongs to the logged-in user
        if ($notification['user_id'] != $userID) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - This notification does not belong to you'
            ])->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
        }
        
        // Hide notification (don't delete)
        $result = $this->notificationModel->hideNotification($id);
        
        if ($result) {
            // Get updated unread count
            $unreadCount = $this->notificationModel->getUnreadCount($userID);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Notification dismissed',
                'unread_count' => $unreadCount
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to hide notification'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Clear all notifications for current user (mark as hidden)
     * 
     * @return ResponseInterface JSON response indicating success or failure
     */
    public function clearAll()
    {
        // Check if user is logged in
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - Please login'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        $userID = $this->session->get('userID');
        
        // Clear all notifications (mark as hidden)
        $result = $this->notificationModel->clearAllNotifications($userID);
        
        if ($result !== false) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'All notifications cleared',
                'unread_count' => 0
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to clear notifications'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Create a new notification (for testing/admin purposes)
     * 
     * @return ResponseInterface JSON response indicating success or failure
     */
    public function create()
    {
        // Check if user is logged in
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - Please login'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        // Get POST data
        $userId = $this->request->getPost('user_id');
        $message = $this->request->getPost('message');
        $type = $this->request->getPost('type') ?? 'info';
        $referenceId = $this->request->getPost('reference_id');
        $referenceType = $this->request->getPost('reference_type');
        
        // Validate required fields
        if (!$userId || !$message) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User ID and message are required'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        // Create notification
        $result = $this->notificationModel->createNotification(
            $userId,
            $message,
            $type,
            $referenceId,
            $referenceType
        );
        
        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Notification created successfully',
                'notification_id' => $result
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create notification'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    // ============================================================
    // HELPER METHODS
    // ============================================================
    
    /**
     * Format notification date for display
     * 
     * @param string $datetime DateTime string
     * @return string Formatted date string
     */
    private function formatNotificationDate($datetime)
    {
        return date('M j, Y g:i A', strtotime($datetime));
    }
    
    /**
     * Get time ago string (e.g., "5 minutes ago", "2 hours ago")
     * 
     * @param string $datetime DateTime string
     * @return string Time ago string
     */
    private function timeAgo($datetime)
    {
        $timestamp = strtotime($datetime);
        $difference = time() - $timestamp;
        
        if ($difference < 60) {
            return 'just now';
        } elseif ($difference < 3600) {
            $mins = floor($difference / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($difference < 86400) {
            $hours = floor($difference / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($difference < 604800) {
            $days = floor($difference / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($difference < 2592000) {
            $weeks = floor($difference / 604800);
            return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $timestamp);
        }
    }
    
    /**
     * Get icon class for notification type
     * 
     * @param string $type Notification type
     * @return string Font Awesome icon class
     */
    private function getNotificationIcon($type)
    {
        $icons = [
            'info' => 'fa-info-circle',
            'success' => 'fa-check-circle',
            'warning' => 'fa-exclamation-triangle',
            'error' => 'fa-times-circle',
            'assignment' => 'fa-tasks',
            'grade' => 'fa-star',
            'enrollment' => 'fa-user-plus',
            'material' => 'fa-file-alt',
            'announcement' => 'fa-bullhorn',
            'message' => 'fa-envelope'
        ];
        
        return $icons[$type] ?? 'fa-bell';
    }
    
    /**
     * Get color class for notification type
     * 
     * @param string $type Notification type
     * @return string Bootstrap color class
     */
    private function getNotificationColor($type)
    {
        $colors = [
            'info' => 'info',
            'success' => 'success',
            'warning' => 'warning',
            'error' => 'danger',
            'assignment' => 'primary',
            'grade' => 'warning',
            'enrollment' => 'success',
            'material' => 'info',
            'announcement' => 'primary',
            'message' => 'secondary'
        ];
        
        return $colors[$type] ?? 'secondary';
    }
}
