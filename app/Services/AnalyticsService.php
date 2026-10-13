<?php

namespace App\Services;

use App\Models\BloodGroupModel;
use App\Models\BloodInventoryModel;
use App\Models\BloodRequestModel;
use App\Models\DonationModel;
use App\Models\DonorModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class AnalyticsService
{
    protected UserModel $userModel;
    protected DonorModel $donorModel;
    protected BloodRequestModel $requestModel;
    protected BloodInventoryModel $inventoryModel;
    protected DonationModel $donationModel;
    protected BloodGroupModel $bloodGroupModel;
    protected NotificationModel $notificationModel;

    public function __construct()
    {
        $this->userModel         = new UserModel();
        $this->donorModel        = new DonorModel();
        $this->requestModel      = new BloodRequestModel();
        $this->inventoryModel   = new BloodInventoryModel();
        $this->donationModel     = new DonationModel();
        $this->bloodGroupModel  = new BloodGroupModel();
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Get 10 Core Summary Metrics backed by server-side query aggregation.
     */
    public function getSummaryMetrics(?string $startDate = null, ?string $endDate = null, ?int $bloodGroupId = null, ?string $city = null): array
    {
        $cleanCity = trim((string) $city);

        // 1. Donors Count
        $donorQuery = $this->donorModel->select('donor_profiles.id, donor_profiles.availability, donor_profiles.medical_eligibility, users.status as user_status')
                                       ->join('users', 'users.id = donor_profiles.user_id', 'left');

        if (! empty($bloodGroupId)) {
            $donorQuery->where('donor_profiles.blood_group_id', $bloodGroupId);
        }
        if ($cleanCity !== '') {
            $donorQuery->groupStart()
                       ->like('donor_profiles.city', $cleanCity)
                       ->orLike('users.city', $cleanCity)
                       ->groupEnd();
        }
        if (! empty($startDate)) {
            $donorQuery->where('donor_profiles.created_at >=', $startDate . ' 00:00:00');
        }
        if (! empty($endDate)) {
            $donorQuery->where('donor_profiles.created_at <=', $endDate . ' 23:59:59');
        }

        $donorsList = $donorQuery->findAll();

        $totalDonors    = count($donorsList);
        $activeDonors   = 0;
        $availableDonors = 0;
        $eligibleDonors  = 0;

        foreach ($donorsList as $d) {
            if (strtolower((string) ($d['user_status'] ?? 'active')) === 'active') {
                $activeDonors++;
            }
            if (strtolower((string) ($d['availability'] ?? '')) === 'available') {
                $availableDonors++;
            }
            if (strtolower((string) ($d['medical_eligibility'] ?? '')) === 'eligible') {
                $eligibleDonors++;
            }
        }

        // 2. Requests Count
        $requestQuery = $this->requestModel->builder();

        if (! empty($bloodGroupId)) {
            $requestQuery->where('blood_requests.blood_group_id', $bloodGroupId);
        }
        if ($cleanCity !== '') {
            $requestQuery->like('blood_requests.city', $cleanCity);
        }
        if (! empty($startDate)) {
            $requestQuery->where('blood_requests.created_at >=', $startDate . ' 00:00:00');
        }
        if (! empty($endDate)) {
            $requestQuery->where('blood_requests.created_at <=', $endDate . ' 23:59:59');
        }

        $requestsList = $requestQuery->get()->getResultArray();

        $totalRequests     = count($requestsList);
        $pendingRequests   = 0;
        $approvedRequests  = 0;
        $fulfilledRequests = 0;
        $criticalRequests  = 0;
        $urgentRequests    = 0;

        foreach ($requestsList as $r) {
            $st  = strtolower((string) ($r['status'] ?? ''));
            $urg = strtolower((string) ($r['urgency'] ?? ''));

            if ($st === 'pending') {
                $pendingRequests++;
            } elseif ($st === 'approved' || $st === 'matching' || $st === 'donors_notified' || $st === 'accepted') {
                $approvedRequests++;
            } elseif ($st === 'fulfilled' || $st === 'completed') {
                $fulfilledRequests++;
            }

            if ($urg === 'critical') {
                $criticalRequests++;
            } elseif ($urg === 'urgent' || $urg === 'high') {
                $urgentRequests++;
            }
        }

        return [
            'totalDonors'       => $totalDonors,
            'activeDonors'      => $activeDonors,
            'availableDonors'   => $availableDonors,
            'eligibleDonors'    => $eligibleDonors,
            'totalRequests'     => $totalRequests,
            'pendingRequests'   => $pendingRequests,
            'approvedRequests'  => $approvedRequests,
            'fulfilledRequests' => $fulfilledRequests,
            'criticalRequests'  => $criticalRequests,
            'urgentRequests'    => $urgentRequests,
        ];
    }

    /**
     * Get blood-group-wise donor analytics (Registered, Available, Eligible).
     */
    public function getBloodGroupDonorAnalytics(?string $startDate = null, ?string $endDate = null, ?string $city = null): array
    {
        $allGroups = $this->bloodGroupModel->orderBy('id', 'ASC')->findAll();
        $cleanCity = trim((string) $city);

        $donorQuery = $this->donorModel->select('donor_profiles.blood_group_id, donor_profiles.availability, donor_profiles.medical_eligibility, users.status as user_status')
                                       ->join('users', 'users.id = donor_profiles.user_id', 'left');

        if ($cleanCity !== '') {
            $donorQuery->groupStart()
                       ->like('donor_profiles.city', $cleanCity)
                       ->orLike('users.city', $cleanCity)
                       ->groupEnd();
        }
        if (! empty($startDate)) {
            $donorQuery->where('donor_profiles.created_at >=', $startDate . ' 00:00:00');
        }
        if (! empty($endDate)) {
            $donorQuery->where('donor_profiles.created_at <=', $endDate . ' 23:59:59');
        }

        $donors = $donorQuery->findAll();

        $analytics = [];
        foreach ($allGroups as $g) {
            $gid = (int) $g['id'];
            $analytics[$gid] = [
                'id'         => $gid,
                'name'       => $g['name'],
                'registered' => 0,
                'available'  => 0,
                'eligible'   => 0,
            ];
        }

        foreach ($donors as $d) {
            $gid = (int) ($d['blood_group_id'] ?? 0);
            if (isset($analytics[$gid])) {
                $analytics[$gid]['registered']++;
                if (strtolower((string) ($d['availability'] ?? '')) === 'available') {
                    $analytics[$gid]['available']++;
                }
                if (strtolower((string) ($d['medical_eligibility'] ?? '')) === 'eligible') {
                    $analytics[$gid]['eligible']++;
                }
            }
        }

        return array_values($analytics);
    }

    /**
     * Get blood-group-wise blood request analytics (Total, Pending, Approved, Fulfilled, Cancelled).
     */
    public function getBloodGroupRequestAnalytics(?string $startDate = null, ?string $endDate = null, ?string $city = null): array
    {
        $allGroups = $this->bloodGroupModel->orderBy('id', 'ASC')->findAll();
        $cleanCity = trim((string) $city);

        $requestQuery = $this->requestModel->builder();

        if ($cleanCity !== '') {
            $requestQuery->like('blood_requests.city', $cleanCity);
        }
        if (! empty($startDate)) {
            $requestQuery->where('blood_requests.created_at >=', $startDate . ' 00:00:00');
        }
        if (! empty($endDate)) {
            $requestQuery->where('blood_requests.created_at <=', $endDate . ' 23:59:59');
        }

        $requests = $requestQuery->get()->getResultArray();

        $analytics = [];
        foreach ($allGroups as $g) {
            $gid = (int) $g['id'];
            $analytics[$gid] = [
                'id'        => $gid,
                'name'      => $g['name'],
                'total'     => 0,
                'pending'   => 0,
                'approved'  => 0,
                'fulfilled' => 0,
                'cancelled' => 0,
            ];
        }

        foreach ($requests as $r) {
            $gid = (int) ($r['blood_group_id'] ?? 0);
            $st  = strtolower((string) ($r['status'] ?? ''));

            if (isset($analytics[$gid])) {
                $analytics[$gid]['total']++;
                if ($st === 'pending') {
                    $analytics[$gid]['pending']++;
                } elseif ($st === 'approved' || $st === 'matching' || $st === 'donors_notified' || $st === 'accepted') {
                    $analytics[$gid]['approved']++;
                } elseif ($st === 'fulfilled' || $st === 'completed') {
                    $analytics[$gid]['fulfilled']++;
                } elseif ($st === 'cancelled') {
                    $analytics[$gid]['cancelled']++;
                }
            }
        }

        return array_values($analytics);
    }

    /**
     * Get Urgency Distribution Analytics (Critical, Urgent, Normal).
     */
    public function getUrgencyAnalytics(?string $startDate = null, ?string $endDate = null, ?int $bloodGroupId = null, ?string $city = null): array
    {
        $cleanCity = trim((string) $city);

        $builder = $this->requestModel->builder();

        if (! empty($bloodGroupId)) {
            $builder->where('blood_requests.blood_group_id', $bloodGroupId);
        }
        if ($cleanCity !== '') {
            $builder->like('blood_requests.city', $cleanCity);
        }
        if (! empty($startDate)) {
            $builder->where('blood_requests.created_at >=', $startDate . ' 00:00:00');
        }
        if (! empty($endDate)) {
            $builder->where('blood_requests.created_at <=', $endDate . ' 23:59:59');
        }

        $requests = $builder->get()->getResultArray();

        $critical = 0;
        $urgent   = 0;
        $normal   = 0;

        foreach ($requests as $r) {
            $urg = strtolower((string) ($r['urgency'] ?? 'normal'));
            if ($urg === 'critical') {
                $critical++;
            } elseif ($urg === 'urgent' || $urg === 'high') {
                $urgent++;
            } else {
                $normal++;
            }
        }

        return [
            'critical' => $critical,
            'urgent'   => $urgent,
            'normal'   => $normal,
            'total'    => count($requests),
        ];
    }

    /**
     * Get Top Cities / Locations by Blood Request Demand.
     */
    public function getCityDemandAnalytics(?string $startDate = null, ?string $endDate = null, ?int $bloodGroupId = null, int $limit = 10): array
    {
        $builder = $this->requestModel->select('city, COUNT(*) as request_count')
                                      ->where('city IS NOT NULL')
                                      ->where('city !=', '');

        if (! empty($bloodGroupId)) {
            $builder->where('blood_requests.blood_group_id', $bloodGroupId);
        }
        if (! empty($startDate)) {
            $builder->where('blood_requests.created_at >=', $startDate . ' 00:00:00');
        }
        if (! empty($endDate)) {
            $builder->where('blood_requests.created_at <=', $endDate . ' 23:59:59');
        }

        return $builder->groupBy('city')
                       ->orderBy('request_count', 'DESC')
                       ->findAll($limit);
    }

    /**
     * Get Integrated Blood Bank Inventory Stock Analytics & Alerts.
     */
    public function getInventoryAnalytics(): array
    {
        $totals     = $this->inventoryModel->getTotalUnitsByBloodGroup();
        $allDetails = $this->inventoryModel->getInventoryWithDetails();

        $totalUnits   = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;

        foreach ($totals as $tot) {
            $u = (int) ($tot['total_units'] ?? 0);
            $totalUnits += $u;
            if ($u === 0) {
                $outOfStockCount++;
            } elseif ($u < 5) {
                $lowStockCount++;
            }
        }

        return [
            'totals'          => $totals,
            'allDetails'      => $allDetails,
            'totalUnits'      => $totalUnits,
            'lowStockCount'   => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
        ];
    }

    /**
     * Get concise readable recent activity across donors, requests, donations, and notifications.
     */
    public function getRecentActivity(int $limit = 5): array
    {
        $recentUsers = $this->userModel->select('users.*, roles.name as role_name')
                                       ->join('roles', 'roles.id = users.role_id', 'left')
                                       ->orderBy('users.created_at', 'DESC')
                                       ->findAll($limit);

        $recentRequests = $this->requestModel->select('blood_requests.*, users.name as requester_name, blood_groups.name as blood_group_name')
                                             ->join('users', 'users.id = blood_requests.requester_id', 'left')
                                             ->join('blood_groups', 'blood_groups.id = blood_requests.blood_group_id', 'left')
                                             ->orderBy('blood_requests.created_at', 'DESC')
                                             ->findAll($limit);

        $recentDonations = $this->donationModel->select('donations.*, users.name as donor_name, blood_groups.name as blood_group_name')
                                               ->join('users', 'users.id = donations.donor_id', 'left')
                                               ->join('blood_groups', 'blood_groups.id = donations.blood_group_id', 'left')
                                               ->orderBy('donations.created_at', 'DESC')
                                               ->findAll($limit);

        $recentNotifications = $this->notificationModel->select('notifications.*, users.name as user_name')
                                                        ->join('users', 'users.id = notifications.user_id', 'left')
                                                        ->orderBy('notifications.created_at', 'DESC')
                                                        ->findAll($limit);

        return [
            'recentUsers'         => $recentUsers,
            'recentRequests'      => $recentRequests,
            'recentDonations'     => $recentDonations,
            'recentNotifications' => $recentNotifications,
        ];
    }
}
