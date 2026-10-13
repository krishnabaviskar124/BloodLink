<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notification extends BaseController
{
    protected NotificationModel $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Display user notification inbox and sent read receipts.
     */
    public function index()
    {
        $userId            = (int) session()->get('user_id');
        $notifications     = $this->notificationModel->getUserNotifications($userId);
        $sentNotifications = $this->notificationModel->getSentNotifications($userId);
        $unreadCount       = $this->notificationModel->getUnreadCount($userId);

        $data = [
            'notifications'     => $notifications,
            'sentNotifications' => $sentNotifications,
            'unreadCount'       => $unreadCount,
        ];

        return view('notifications/index', $data);
    }

    /**
     * Mark single notification as read.
     */
    public function markRead($id = null)
    {
        $userId = (int) session()->get('user_id');

        if (! empty($id)) {
            $this->notificationModel->markAsRead((int) $id, $userId);
        }

        return redirect()->back();
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function markAllRead()
    {
        $userId = (int) session()->get('user_id');
        $this->notificationModel->markAllAsRead($userId);

        session()->setFlashdata('success', 'All notifications marked as read.');

        return redirect()->to('/notifications');
    }
}
