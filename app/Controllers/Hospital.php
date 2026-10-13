<?php

namespace App\Controllers;

use App\Models\BloodRequestModel;

class Hospital extends BaseController
{
    protected BloodRequestModel $requestModel;

    public function __construct()
    {
        $this->requestModel = new BloodRequestModel();
    }

    /**
     * Hospital Dashboard View with dynamic real-time stats
     */
    public function dashboard()
    {
        $userId   = session()->get('user_id');
        $requests = $this->requestModel->getRequestsByRequester($userId);

        $pendingCount   = 0;
        $urgentCount    = 0;
        $criticalCount  = 0;
        $fulfilledCount = 0;

        foreach ($requests as $req) {
            $st  = strtolower($req['status']);
            $urg = strtolower($req['urgency']);

            if (in_array($st, ['pending', 'matching', 'donors_notified', 'accepted', 'approved'], true)) {
                $pendingCount++;
            }
            if (in_array($st, ['fulfilled', 'completed'], true)) {
                $fulfilledCount++;
            }
            if (in_array($urg, ['urgent', 'high'], true)) {
                $urgentCount++;
            }
            if ($urg === 'critical') {
                $criticalCount++;
            }
        }

        $data = [
            'requests'       => $requests,
            'totalCount'     => count($requests),
            'pendingCount'   => $pendingCount,
            'urgentCount'    => $urgentCount,
            'criticalCount'  => $criticalCount,
            'fulfilledCount' => $fulfilledCount,
        ];

        return view('hospital/dashboard', $data);
    }
}
