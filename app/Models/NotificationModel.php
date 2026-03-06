<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;    protected $allowedFields    = [
        'user_id',
        'message',
        'is_read',
        'is_hidden'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';  // No updated_at field in database

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'message' => 'required|string|max_length[255]',
        'is_read' => 'permit_empty|in_list[0,1]',
        'is_hidden' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer'  => 'User ID must be an integer'
        ],
        'message' => [
            'required'   => 'Message is required',
            'string'     => 'Message must be a string',
            'max_length' => 'Message cannot exceed 255 characters'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get the count of unread notifications for a specific user
     */
    public function getUnreadCount($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->where('is_hidden', 0)
                    ->countAllResults();
    }

    /**
     * Get ALL notifications for a specific user
     */
    public function getNotificationsForUser($userId, $limit = null)
    {
        $builder = $this->where('user_id', $userId)
                       ->where('is_hidden', 0)
                       ->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->findAll();
    }

    /**
     * Get unread notifications only
     */
    public function getUnreadNotifications($userId, $limit = null)
    {
        $builder = $this->where('user_id', $userId)
                       ->where('is_read', 0)
                       ->where('is_hidden', 0)
                       ->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->findAll();
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead($notificationId)
    {
        return $this->update($notificationId, ['is_read' => 1]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->set('is_read', 1)
                    ->update();
    }

    /**
     * Hide a specific notification
     */
    public function hideNotification($notificationId)
    {
        return $this->update($notificationId, ['is_hidden' => 1]);
    }

    /**
     * Clear all notifications for a user (mark as hidden)
     */
    public function clearAllNotifications($userId)
    {
        return $this->where('user_id', $userId)
                    ->set('is_hidden', 1)
                    ->update();
    }    /**
     * Create notification for user
     * Note: type, referenceId, referenceType parameters are kept for backward compatibility but not stored
     */
    public function createNotification($userId, $message, $type = 'info', $referenceId = null, $referenceType = null)
    {
        return $this->insert([
            'user_id'   => $userId,
            'message'   => $message,
            'is_read'   => 0,
            'is_hidden' => 0
        ]);
    }    /**
     * Create notification for multiple users
     * Note: type, referenceId, referenceType parameters are kept for backward compatibility but not stored
     */
    public function createBulkNotifications($userIds, $message, $type = 'info', $referenceId = null, $referenceType = null)
    {
        $notifications = [];
        
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id'    => $userId,
                'message'    => $message,
                'is_read'    => 0,
                'is_hidden'  => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return $this->insertBatch($notifications);
    }    /**
     * Get notifications by type
     * Note: This method is kept for backward compatibility but type filtering is not supported
     */
    public function getNotificationsByType($userId, $type)
    {
        // Type is not stored in database, so just return all notifications for user
        return $this->where('user_id', $userId)
                    ->where('is_hidden', 0)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Delete old notifications (cleanup)
     */
    public function deleteOldNotifications($days = 30)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->where('created_at <', $date)
                    ->where('is_read', 1)
                    ->delete();
    }

    /**
     * Get notification statistics for a user
     */
    public function getUserStats($userId)
    {
        return [
            'total'      => $this->where('user_id', $userId)->where('is_hidden', 0)->countAllResults(),
            'unread'     => $this->getUnreadCount($userId),
            'read'       => $this->where('user_id', $userId)->where('is_read', 1)->where('is_hidden', 0)->countAllResults(),
            'by_type'    => $this->getTypeBreakdown($userId)
        ];
    }    /**
     * Get breakdown by notification type
     * Note: Type is not stored in database, returns empty array
     */
    private function getTypeBreakdown($userId)
    {
        // Type is not stored in database
        return [];
    }
}