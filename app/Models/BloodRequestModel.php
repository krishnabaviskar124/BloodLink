<?php

namespace App\Models;

use CodeIgniter\Model;

class BloodRequestModel extends Model
{
    protected $table            = 'blood_requests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'requester_id',
        'blood_group_id',
        'units_required',
        'hospital_name',
        'hospital_address',
        'city',
        'urgency',
        'required_date',
        'status',
        'description',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'requester_id'     => 'required|is_natural_no_zero|is_not_unique[users.id]',
        'blood_group_id'   => 'required|is_natural_no_zero|is_not_unique[blood_groups.id]',
        'units_required'   => 'required|is_natural_no_zero|greater_than[0]',
        'hospital_name'    => 'required|max_length[150]',
        'hospital_address' => 'permit_empty',
        'city'             => 'required|max_length[100]',
        'urgency'          => 'permit_empty|in_list[normal,urgent,critical,low,medium,high]',
        'required_date'    => 'required|valid_date',
        'status'           => 'permit_empty|in_list[pending,matching,donors_notified,accepted,approved,fulfilled,completed,cancelled]',
        'description'      => 'permit_empty|max_length[1000]',
    ];

    protected $validationMessages = [
        'requester_id' => [
            'is_not_unique' => 'Requester user account does not exist.',
        ],
        'blood_group_id' => [
            'is_not_unique' => 'Selected blood group does not exist.',
        ],
        'units_required' => [
            'is_natural_no_zero' => 'Units required must be a valid positive number greater than 0.',
            'greater_than'       => 'Units required must be at least 1.',
        ],
    ];

    /**
     * Validate request lifecycle state transition based on current status and user role.
     */
    public function isValidStatusTransition(string $currentStatus, string $newStatus, string $userRole = 'hospital'): bool
    {
        $current = strtolower(trim($currentStatus));
        $new     = strtolower(trim($newStatus));
        $role    = strtolower(trim($userRole));

        $validStatuses = ['pending', 'matching', 'donors_notified', 'accepted', 'approved', 'fulfilled', 'completed', 'cancelled'];
        if (! in_array($new, $validStatuses, true)) {
            return false;
        }

        // Admin override can transition to any valid status
        if ($role === 'admin') {
            return true;
        }

        // Same status is valid (no-op)
        if ($current === $new) {
            return true;
        }

        // Terminal states (fulfilled, completed, cancelled) cannot be modified by non-admins
        if (in_array($current, ['fulfilled', 'completed', 'cancelled'], true)) {
            return false;
        }

        // Allowed transitions for standard roles (hospital / requester / blood bank manager)
        $allowedTransitions = [
            'pending'         => ['approved', 'matching', 'donors_notified', 'accepted', 'fulfilled', 'completed', 'cancelled'],
            'matching'        => ['approved', 'donors_notified', 'accepted', 'fulfilled', 'completed', 'cancelled'],
            'donors_notified' => ['approved', 'accepted', 'fulfilled', 'completed', 'cancelled'],
            'accepted'        => ['approved', 'fulfilled', 'completed', 'cancelled'],
            'approved'        => ['fulfilled', 'completed', 'cancelled'],
        ];

        return in_array($new, $allowedTransitions[$current] ?? [], true);
    }

    /**
     * Get single request with full requester info and blood group name.
     */
    public function getRequestWithDetails(int $id): ?array
    {
        return $this->select('blood_requests.*, users.name as requester_name, users.phone as requester_phone, users.email as requester_email, blood_groups.name as blood_group_name')
                    ->join('users', 'users.id = blood_requests.requester_id', 'left')
                    ->join('blood_groups', 'blood_groups.id = blood_requests.blood_group_id', 'left')
                    ->where('blood_requests.id', $id)
                    ->first();
    }

    /**
     * Get pending or active blood requests with optional blood group, urgency, status and city filter.
     */
    public function getFilteredRequests(?int $bloodGroupId = null, ?string $city = null, ?string $urgency = null, ?string $status = null, ?int $requesterId = null): array
    {
        $builder = $this->select('blood_requests.*, users.name as requester_name, users.phone as requester_phone, blood_groups.name as blood_group_name')
                        ->join('users', 'users.id = blood_requests.requester_id', 'left')
                        ->join('blood_groups', 'blood_groups.id = blood_requests.blood_group_id', 'left');

        if (! empty($requesterId)) {
            $builder->where('blood_requests.requester_id', $requesterId);
        }

        if (! empty($status) && $status !== 'all') {
            $builder->where('blood_requests.status', $status);
        }

        if (! empty($urgency) && $urgency !== 'all') {
            $builder->where('blood_requests.urgency', $urgency);
        }

        if (! empty($bloodGroupId)) {
            $builder->where('blood_requests.blood_group_id', $bloodGroupId);
        }

        if (! empty($city)) {
            $builder->like('blood_requests.city', trim($city));
        }

        // Order critical & urgent requests first, then newest
        return $builder->orderBy("FIELD(blood_requests.urgency, 'critical', 'urgent', 'high', 'normal', 'medium', 'low')", '', false)
                       ->orderBy('blood_requests.created_at', 'DESC')
                       ->findAll();
    }

    /**
     * Get blood requests submitted by a specific user.
     */
    public function getRequestsByRequester(int $userId): array
    {
        return $this->select('blood_requests.*, blood_groups.name as blood_group_name')
                    ->join('blood_groups', 'blood_groups.id = blood_requests.blood_group_id', 'left')
                    ->where('blood_requests.requester_id', $userId)
                    ->orderBy("FIELD(blood_requests.urgency, 'critical', 'urgent', 'high', 'normal', 'medium', 'low')", '', false)
                    ->orderBy('blood_requests.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get pending blood requests with optional blood group and city filters.
     */
    public function getPendingRequests(?int $bloodGroupId = null, ?string $city = null): array
    {
        return $this->getFilteredRequests($bloodGroupId, $city, null, 'pending');
    }
}
