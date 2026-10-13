<?php

namespace App\Controllers;

use App\Models\BloodInventoryModel;
use App\Models\BloodRequestModel;
use App\Models\DonationModel;

class BloodBank extends BaseController
{
    protected BloodInventoryModel $inventoryModel;
    protected DonationModel $donationModel;
    protected BloodRequestModel $requestModel;

    public function __construct()
    {
        $this->inventoryModel  = new BloodInventoryModel();
        $this->donationModel    = new DonationModel();
        $this->requestModel = new BloodRequestModel();
    }

    /**
     * Blood Bank Manager Dashboard View
     */
    public function dashboard()
    {
        $inventory = $this->inventoryModel->getInventoryWithDetails();
        $totals    = $this->inventoryModel->getTotalUnitsByBloodGroup();
        $requests  = $this->requestModel->getPendingRequests();
        $donations = $this->donationModel->select('donations.*, users.name as donor_name, blood_groups.name as blood_group_name')
                                         ->join('users', 'users.id = donations.donor_id', 'left')
                                         ->join('blood_groups', 'blood_groups.id = donations.blood_group_id', 'left')
                                         ->orderBy('donations.donation_date', 'DESC')
                                         ->findAll();

        $totalUnits = 0;
        foreach ($totals as $tot) {
            $totalUnits += (int) ($tot['total_units'] ?? 0);
        }

        $data = [
            'inventory'     => $inventory,
            'totals'        => $totals,
            'totalUnits'    => $totalUnits,
            'pendingReqs'   => array_slice($requests, 0, 5),
            'recentDonations' => array_slice($donations, 0, 5),
        ];

        return view('bloodbank/dashboard', $data);
    }
}
