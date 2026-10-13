<?php

namespace App\Controllers;

use App\Models\BloodInventoryModel;
use App\Models\BloodRequestModel;
use App\Models\UserModel;

class Home extends BaseController
{
    public function index(): string
    {
        $userModel      = new UserModel();
        $requestModel   = new BloodRequestModel();
        $inventoryModel = new BloodInventoryModel();

        // Calculate real database metrics
        $totalDonors       = $userModel->where('role_id', 2)->countAllResults();
        $fulfilledRequests = $requestModel->where('status', 'fulfilled')->countAllResults();
        $totalHospitals    = $userModel->where('role_id', 3)->countAllResults();

        $inventoryTotals = $inventoryModel->getTotalUnitsByBloodGroup();
        $totalUnits      = 0;
        foreach ($inventoryTotals as $tot) {
            $totalUnits += (int) ($tot['total_units'] ?? 0);
        }

        $data = [
            'stats' => [
                'total_donors'    => $totalDonors,
                'blood_requests'  => $fulfilledRequests,
                'units_available' => $totalUnits,
                'hospitals'       => $totalHospitals,
            ],
        ];

        return view('home', $data);
    }
}
