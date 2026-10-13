<?php

namespace App\Controllers;

use App\Models\BloodGroupModel;
use App\Models\UserModel;
use App\Services\AnalyticsService;

class Admin extends BaseController
{
    protected UserModel $userModel;
    protected BloodGroupModel $bloodGroupModel;
    protected AnalyticsService $analyticsService;

    public function __construct()
    {
        $this->userModel        = new UserModel();
        $this->bloodGroupModel = new BloodGroupModel();
        $this->analyticsService = new AnalyticsService();
    }

    /**
     * Professional Data-Driven Admin Analytics & Operations Dashboard
     */
    public function dashboard()
    {
        $userRole = strtolower((string) session()->get('role_name'));

        // Strict Server-Side Security & Authorization Check
        if ($userRole !== 'admin' && (int) session()->get('role_id') !== 1) {
            session()->setFlashdata('error', 'Unauthorized access: Admin privileges required.');
            return redirect()->to('/login');
        }

        // Process GET filters
        $startDate    = $this->request->getGet('start_date');
        $endDate      = $this->request->getGet('end_date');
        $bloodGroupId = $this->request->getGet('blood_group_id');
        $city         = $this->request->getGet('city');

        $bgFilterId = ! empty($bloodGroupId) ? (int) $bloodGroupId : null;
        $cleanCity  = trim((string) $city);

        // Summary Metrics
        $summary = $this->analyticsService->getSummaryMetrics($startDate, $endDate, $bgFilterId, $cleanCity);

        // System User Role Totals
        $totalUsers     = $this->userModel->countAllResults();
        $totalHospitals = $this->userModel->where('role_id', 3)->countAllResults();
        $totalManagers  = $this->userModel->where('role_id', 4)->countAllResults();

        // Detailed Analytics
        $bloodGroupDonors   = $this->analyticsService->getBloodGroupDonorAnalytics($startDate, $endDate, $cleanCity);
        $bloodGroupRequests = $this->analyticsService->getBloodGroupRequestAnalytics($startDate, $endDate, $cleanCity);
        $urgencyAnalytics   = $this->analyticsService->getUrgencyAnalytics($startDate, $endDate, $bgFilterId, $cleanCity);
        $cityDemand         = $this->analyticsService->getCityDemandAnalytics($startDate, $endDate, $bgFilterId, 10);
        $inventory          = $this->analyticsService->getInventoryAnalytics();
        $recentActivity     = $this->analyticsService->getRecentActivity(6);

        // Format Chart Data JSON Payload
        $chartBgLabels    = array_column($bloodGroupRequests, 'name');
        $chartBgData      = array_column($bloodGroupRequests, 'total');
        $chartCityLabels  = array_column($cityDemand, 'city');
        $chartCityData    = array_column($cityDemand, 'request_count');

        $data = [
            // Filters
            'selectedStartDate'    => $startDate,
            'selectedEndDate'      => $endDate,
            'selectedGroup'        => $bloodGroupId,
            'selectedCity'         => $city,
            'bloodGroups'          => $this->bloodGroupModel->orderBy('id', 'ASC')->findAll(),

            // Summary Metrics
            'totalUsers'           => $totalUsers,
            'totalHospitals'       => $totalHospitals,
            'totalManagers'        => $totalManagers,
            'totalDonors'          => $summary['totalDonors'],
            'activeDonors'         => $summary['activeDonors'],
            'availableDonors'      => $summary['availableDonors'],
            'eligibleDonors'       => $summary['eligibleDonors'],
            'totalRequests'        => $summary['totalRequests'],
            'pendingRequests'      => $summary['pendingRequests'],
            'approvedRequests'     => $summary['approvedRequests'],
            'fulfilledRequests'    => $summary['fulfilledRequests'],
            'criticalRequests'     => $summary['criticalRequests'],
            'urgentRequests'       => $summary['urgentRequests'],

            // Breakdowns & Lists
            'bloodGroupDonors'     => $bloodGroupDonors,
            'bloodGroupRequests'   => $bloodGroupRequests,
            'urgencyAnalytics'     => $urgencyAnalytics,
            'cityDemand'           => $cityDemand,
            'inventory'            => $inventory,
            'recentUsers'          => $recentActivity['recentUsers'],
            'recentRequests'       => $recentActivity['recentRequests'],
            'recentDonations'      => $recentActivity['recentDonations'],
            'recentNotifications'  => $recentActivity['recentNotifications'],

            // JSON payloads for Chart.js
            'chartBgLabelsJson'    => json_encode($chartBgLabels),
            'chartBgDataJson'      => json_encode($chartBgData),
            'chartCityLabelsJson'  => json_encode($chartCityLabels),
            'chartCityDataJson'    => json_encode($chartCityData),
            'chartStatusDataJson'  => json_encode([
                $summary['pendingRequests'],
                $summary['approvedRequests'],
                $summary['fulfilledRequests'],
            ]),
            'chartUrgencyDataJson' => json_encode([
                $urgencyAnalytics['critical'],
                $urgencyAnalytics['urgent'],
                $urgencyAnalytics['normal'],
            ]),
        ];

        return view('admin/dashboard', $data);
    }
}
