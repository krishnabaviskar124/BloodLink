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
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'sender_id',
        'request_id',
        'title',
        'message',
        'type',
        'priority',
        'is_read',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id'    => 'required|is_natural_no_zero|is_not_unique[users.id]',
        'sender_id'  => 'permit_empty|is_natural_no_zero',
        'request_id' => 'permit_empty|is_natural_no_zero',
        'title'      => 'required|max_length[150]',
        'message'    => 'required',
        'type'       => 'permit_empty|max_length[50]',
        'priority'   => 'permit_empty|in_list[critical,high,urgent,normal,low]',
        'is_read'    => 'permit_empty|in_list[0,1]',
    ];

    /**
     * Get notifications received by a user ordered by priority (CRITICAL -> HIGH -> NORMAL -> LOW) then newest first.
     */
    public function getUserNotifications(int $userId, int $limit = 20): array
    {
        return $this->select('notifications.*, senders.name as sender_name, blood_requests.id as linked_request_id, blood_requests.hospital_name, blood_requests.units_required, blood_requests.city as request_city, blood_requests.required_date, blood_groups.name as blood_group_name')
                    ->join('users as senders', 'senders.id = notifications.sender_id', 'left')
                    ->join('blood_requests', 'blood_requests.id = notifications.request_id', 'left')
                    ->join('blood_groups', 'blood_groups.id = blood_requests.blood_group_id', 'left')
                    ->where('notifications.user_id', $userId)
                    ->orderBy("FIELD(notifications.priority, 'critical', 'high', 'urgent', 'normal', 'low')", '', false)
                    ->orderBy('notifications.created_at', 'DESC')
                    ->findAll($limit);
    }

    /**
     * Get notifications sent by a user to track read receipts.
     */
    public function getSentNotifications(int $senderId, int $limit = 20): array
    {
        return $this->select('notifications.*, recipients.name as recipient_name')
                    ->join('users as recipients', 'recipients.id = notifications.user_id', 'left')
                    ->where('notifications.sender_id', $senderId)
                    ->orderBy('notifications.created_at', 'DESC')
                    ->findAll($limit);
    }

    /**
     * Get unread notification count for a recipient user.
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    /**
     * Mark single notification as read (persists is_read and updated_at read timestamp).
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        return $this->where('id', $notificationId)
                    ->where('user_id', $userId)
                    ->set([
                        'is_read'    => 1,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ])
                    ->update();
    }

    /**
     * Mark all notifications for a user as read.
     */
    public function markAllAsRead(int $userId): bool
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->set([
                        'is_read'    => 1,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ])
                    ->update();
    }

    /**
     * Create a notification record with duplicate prevention, request_id, and priority tracking.
     */
    public function notifyUser(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        ?int $senderId = null,
        ?int $requestId = null,
        string $priority = 'normal'
    ): bool {
        // Duplicate Prevention: Check if notification already sent for same request_id to this user
        if (! empty($requestId)) {
            $existing = $this->where('user_id', $userId)
                             ->where('request_id', $requestId)
                             ->first();
            if ($existing) {
                return true; // Skip duplicate creation safely
            }
        }

        $cleanPriority = in_array(strtolower($priority), ['critical', 'high', 'urgent', 'normal', 'low'], true)
            ? strtolower($priority)
            : 'normal';

        if ($cleanPriority === 'urgent') {
            $cleanPriority = 'high';
        }

        return (bool) $this->insert([
            'user_id'    => $userId,
            'sender_id'  => $senderId,
            'request_id' => $requestId,
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'priority'   => $cleanPriority,
            'is_read'    => 0,
        ]);
    }

    /**
     * Create notifications for all users belonging to a role.
     */
    public function notifyRole(
        int $roleId,
        string $title,
        string $message,
        string $type = 'info',
        ?int $senderId = null,
        ?int $requestId = null,
        string $priority = 'normal'
    ): void {
        $userModel = new UserModel();
        $users     = $userModel->where('role_id', $roleId)->findAll();

        foreach ($users as $user) {
            $this->notifyUser($user['id'], $title, $message, $type, $senderId, $requestId, $priority);
        }
    }

    /**
     * Dispatch smart notifications to active eligible compatible donors for a blood request.
     */
    public function notifyMatchingEligibleDonors(
        array $requestData,
        int $requestId,
        int $senderId,
        string $bloodGroupName = ''
    ): int {
        $matchingService = new \App\Services\BloodMatchingService();
        $matchingDonors  = $matchingService->findMatchingDonors((int) $requestData['blood_group_id'], $requestData['city']);

        $rawUrgency = strtolower($requestData['urgency'] ?? 'normal');
        $priority   = match ($rawUrgency) {
            'critical' => 'critical',
            'urgent', 'high' => 'high',
            'low'      => 'low',
            default    => 'normal',
        };

        $headerTitle = match ($priority) {
            'critical' => '🚨 CRITICAL BLOOD MATCH ALERT',
            'high'     => '⚠️ HIGH PRIORITY MATCH ALERT',
            'low'      => '🩸 BLOOD MATCH NOTIFICATION',
            default    => '🩸 BLOOD MATCH ALERT',
        };

        $sentCount = 0;
        foreach ($matchingDonors as $donor) {
            $donorUserId = (int) ($donor['user_id'] ?? 0);
            if ($donorUserId <= 0) {
                continue;
            }

            $message = "You are a potential donor match!\n"
                     . "Blood Group: " . esc($bloodGroupName) . "\n"
                     . "City/Location: " . esc($requestData['city'] ?? '') . "\n"
                     . "Units Required: " . (int) ($requestData['units_required'] ?? 1) . "\n"
                     . "Urgency: " . strtoupper($rawUrgency) . "\n"
                     . "Date Needed: " . date('M d, Y', strtotime($requestData['required_date'] ?? 'now'));

            $created = $this->notifyUser(
                $donorUserId,
                $headerTitle,
                $message,
                'match',
                $senderId,
                $requestId,
                $priority
            );

            if ($created) {
                $sentCount++;
            }
        }

        return $sentCount;
    }
}

