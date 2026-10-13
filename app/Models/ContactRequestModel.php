<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactRequestModel extends Model
{
    protected $table            = 'contact_requests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'requester_id',
        'donor_id',
        'request_id',
        'status',
        'message',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'requester_id' => 'required|is_natural_no_zero|is_not_unique[users.id]',
        'donor_id'     => 'required|is_natural_no_zero|is_not_unique[users.id]',
        'request_id'   => 'permit_empty|is_natural_no_zero',
        'status'       => 'permit_empty|in_list[pending,accepted,declined,cancelled]',
        'message'      => 'permit_empty',
    ];

    /**
     * Check if an active contact request (pending or accepted) already exists between a requester and a donor.
     */
    public function hasActiveRequest(int $requesterId, int $donorId, ?int $requestId = null): bool
    {
        $builder = $this->where('requester_id', $requesterId)
                        ->where('donor_id', $donorId)
                        ->whereIn('status', ['pending', 'accepted']);

        if (! empty($requestId)) {
            $builder->where('request_id', $requestId);
        }

        return (bool) $builder->first();
    }

    /**
     * Check if a donor has accepted a contact request from a requester.
     */
    public function getAcceptedRequest(int $requesterId, int $donorId, ?int $requestId = null): ?array
    {
        $builder = $this->where('requester_id', $requesterId)
                        ->where('donor_id', $donorId)
                        ->where('status', 'accepted');

        if (! empty($requestId)) {
            $builder->where('request_id', $requestId);
        }

        return $builder->first();
    }

    /**
     * Get incoming contact requests for a donor with requester details.
     */
    public function getContactRequestsForDonor(int $donorUserId): array
    {
        return $this->select('contact_requests.*, requesters.name as requester_name, requesters.phone as requester_phone, requesters.email as requester_email, blood_requests.hospital_name, blood_requests.city as request_city')
                    ->join('users as requesters', 'requesters.id = contact_requests.requester_id', 'left')
                    ->join('blood_requests', 'blood_requests.id = contact_requests.request_id', 'left')
                    ->where('contact_requests.donor_id', $donorUserId)
                    ->orderBy('contact_requests.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get outgoing contact requests created by a requester.
     */
    public function getContactRequestsByRequester(int $requesterId): array
    {
        return $this->select('contact_requests.*, donors.name as donor_name, donors.phone as donor_phone, donors.email as donor_email')
                    ->join('users as donors', 'donors.id = contact_requests.donor_id', 'left')
                    ->where('contact_requests.requester_id', $requesterId)
                    ->orderBy('contact_requests.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get map of latest contact request status for a list of donor user IDs relative to a requester.
     * Return format: [donor_id => ['status' => 'pending'|'accepted'|'declined'|'cancelled', 'id' => request_id]]
     */
    public function getContactStatusMapForRequester(int $requesterId, array $donorUserIds, ?int $requestId = null): array
    {
        if (empty($donorUserIds)) {
            return [];
        }

        $builder = $this->where('requester_id', $requesterId)
                        ->whereIn('donor_id', $donorUserIds);

        if (! empty($requestId)) {
            $builder->where('request_id', $requestId);
        }

        $requests = $builder->orderBy('created_at', 'DESC')->findAll();

        $map = [];
        foreach ($requests as $req) {
            $donorId = (int) $req['donor_id'];
            if (! isset($map[$donorId])) {
                $map[$donorId] = $req;
            }
        }

        return $map;
    }
}
