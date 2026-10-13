<?php

namespace App\Controllers;

use App\Models\BloodGroupModel;
use App\Models\BloodRequestModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use App\Services\BloodMatchingService;

class BloodRequest extends BaseController
{
    protected BloodRequestModel $requestModel;
    protected BloodGroupModel $bloodGroupModel;
    protected BloodMatchingService $matchingService;
    protected NotificationModel $notificationModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->requestModel      = new BloodRequestModel();
        $this->bloodGroupModel   = new BloodGroupModel();
        $this->matchingService   = new BloodMatchingService();
        $this->notificationModel = new NotificationModel();
        $this->userModel         = new UserModel();
    }

    /**
     * List blood requests with search, blood group, urgency, and status filters.
     */
    public function index()
    {
        $bloodGroupId = $this->request->getGet('blood_group_id');
        $city         = $this->request->getGet('city');
        $urgency      = $this->request->getGet('urgency');
        $status       = $this->request->getGet('status') ?: 'all';

        $userId   = session()->get('user_id');
        $userRole = strtolower((string) session()->get('role_name'));

        // If Hospital role, list their own requests by default unless broad search requested
        $requesterFilter = ($userRole === 'hospital') ? $userId : null;

        $requests = $this->requestModel->getFilteredRequests(
            ! empty($bloodGroupId) ? (int) $bloodGroupId : null,
            $city,
            $urgency,
            $status,
            $requesterFilter
        );

        $data = [
            'requests'       => $requests,
            'bloodGroups'    => $this->bloodGroupModel->findAll(),
            'selectedGroup'  => $bloodGroupId,
            'selectedCity'   => $city,
            'selectedUrgency'=> $urgency,
            'selectedStatus' => $status,
        ];

        return view('blood_requests/index', $data);
    }

    /**
     * Public / Smart Search Page for Blood Availability & Donors.
     */
    public function search()
    {
        $bloodGroupId = $this->request->getGet('blood_group_id');
        $city         = $this->request->getGet('city');

        $bgId = ! empty($bloodGroupId) ? (int) $bloodGroupId : null;

        $matchingDonors    = $this->matchingService->findMatchingDonors($bgId, $city);
        $matchingInventory = $this->matchingService->findMatchingInventory($bgId, $city);

        $data = [
            'bloodGroups'       => $this->bloodGroupModel->findAll(),
            'selectedGroup'     => $bloodGroupId,
            'selectedCity'      => $city,
            'matchingDonors'    => $matchingDonors,
            'matchingInventory' => $matchingInventory,
        ];

        return view('blood_requests/search', $data);
    }

    /**
     * Show form to create new emergency blood request. (Restricted from Donors)
     */
    public function create()
    {
        $userRole = strtolower((string) session()->get('role_name'));

        // Donor Role (role_id = 2) is blocked from creating hospital emergency requests
        if ($userRole === 'donor' || session()->get('role_id') == 2) {
            session()->setFlashdata('error', 'Unauthorized action: Donors cannot create hospital emergency blood requests.');
            return redirect()->to('/dashboard');
        }

        $data['bloodGroups'] = $this->bloodGroupModel->findAll();

        return view('blood_requests/create', $data);
    }

    /**
     * Process new emergency blood request creation and dispatch priority notifications.
     */
    public function store()
    {
        $userRole = strtolower((string) session()->get('role_name'));

        // Donor Role (role_id = 2) is blocked from creating hospital emergency requests
        if ($userRole === 'donor' || session()->get('role_id') == 2) {
            session()->setFlashdata('error', 'Unauthorized action: Donors cannot create hospital emergency blood requests.');
            return redirect()->to('/dashboard');
        }

        $rules = [
            'blood_group_id'   => 'required|is_natural_no_zero|is_not_unique[blood_groups.id]',
            'units_required'   => 'required|is_natural_no_zero|greater_than[0]',
            'hospital_name'    => 'required|max_length[150]',
            'city'             => 'required|max_length[100]',
            'urgency'          => 'required|in_list[normal,urgent,critical,low,medium,high]',
            'required_date'    => 'required|valid_date',
            'hospital_address' => 'permit_empty',
            'description'      => 'permit_empty|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Custom validation: Required date cannot be in the past
        $requiredDate = $this->request->getPost('required_date');
        if (strtotime($requiredDate) < strtotime(date('Y-m-d'))) {
            return redirect()->back()->withInput()->with('errors', ['required_date' => 'Required date cannot be in the past.']);
        }

        $userId = (int) session()->get('user_id');

        $requestData = [
            'requester_id'     => $userId,
            'blood_group_id'   => $this->request->getPost('blood_group_id'),
            'units_required'   => (int) $this->request->getPost('units_required'),
            'hospital_name'    => $this->request->getPost('hospital_name'),
            'hospital_address' => $this->request->getPost('hospital_address'),
            'city'             => trim($this->request->getPost('city')),
            'urgency'          => $this->request->getPost('urgency'),
            'required_date'    => $requiredDate,
            'status'           => 'pending',
            'description'      => $this->request->getPost('description'),
        ];

        $requestId  = $this->requestModel->insert($requestData);
        $bloodGroup = $this->bloodGroupModel->find($this->request->getPost('blood_group_id'));

        // Priority Mapping
        $rawUrgency = strtolower($requestData['urgency']);
        $priority   = match ($rawUrgency) {
            'critical'       => 'critical',
            'urgent', 'high' => 'high',
            'low'            => 'low',
            default          => 'normal',
        };

        $headerTitle = match ($priority) {
            'critical' => '🚨 CRITICAL BLOOD REQUEST',
            'high'     => '⚠️ HIGH PRIORITY BLOOD REQUEST',
            'low'      => '🩸 LOW PRIORITY BLOOD REQUEST',
            default    => '🩸 BLOOD REQUEST',
        };

        // Trigger Notification for Admin (Role 1) & System Managers (Role 4)
        $bgName = $bloodGroup['name'] ?? '';
        $this->notificationModel->notifyRole(1, $headerTitle, "A {$rawUrgency} {$bgName} request #{$requestId} was posted in {$requestData['city']}.", 'request', $userId, $requestId, $priority);
        $this->notificationModel->notifyRole(4, $headerTitle, "Emergency request #{$requestId} ({$bgName}) requires {$requestData['units_required']} unit(s) at {$requestData['hospital_name']}.", 'request', $userId, $requestId, $priority);

        // Trigger Smart Priority Notification for matching eligible active donors
        $this->notificationModel->notifyMatchingEligibleDonors(
            $requestData,
            $requestId,
            $userId,
            $bgName
        );


        session()->setFlashdata('success', 'Emergency blood request #' . $requestId . ' successfully created! Viewing request details below.');

        return redirect()->to('/requests/' . $requestId);
    }

    /**
     * Show single emergency blood request details.
     */
    public function show($id = null)
    {
        $request = $this->requestModel->getRequestWithDetails((int) $id);

        if (! $request) {
            session()->setFlashdata('error', 'Blood request not found.');
            return redirect()->to('/requests');
        }

        $userId            = (int) session()->get('user_id');
        $matchingDonors    = [];
        $matchingInventory = [];

        if (! empty($request['blood_group_id'])) {
            $matchingDonors    = $this->matchingService->findMatchingDonors((int) $request['blood_group_id'], $request['city'] ?? null);
            $matchingInventory = $this->matchingService->findMatchingInventory((int) $request['blood_group_id'], $request['city'] ?? null);
        }

        $donorUserIds = array_column($matchingDonors, 'user_id');

        $contactRequestModel = new \App\Models\ContactRequestModel();
        $contactMap          = $contactRequestModel->getContactStatusMapForRequester($userId, $donorUserIds, (int) $id);

        $data = [
            'req'               => $request,
            'matchingDonors'    => $matchingDonors,
            'matchingInventory' => $matchingInventory,
            'contactMap'        => $contactMap,
        ];

        return view('blood_requests/show', $data);
    }

    /**
     * Contact / Alert a donor (Authenticated Users Only)
     */
    public function contactDonor($donorUserId = null)
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please log in to contact donors.');
            return redirect()->to('/login');
        }

        $donorUserId = (int) $donorUserId;
        $donor       = $this->userModel->find($donorUserId);

        if (! $donor) {
            session()->setFlashdata('error', 'Donor user record not found.');
            return redirect()->back();
        }

        $senderId    = (int) session()->get('user_id');
        $senderName  = session()->get('name') ?: 'A BloodLink Requester';
        $senderPhone = session()->get('phone') ?: 'Not specified';

        // Trigger real notification for the donor including sender_id
        $this->notificationModel->notifyUser(
            $donorUserId,
            'Urgent Contact Request',
            $senderName . ' (Phone: ' . $senderPhone . ') is requesting to contact you regarding a blood donation.',
            'contact',
            $senderId
        );

        session()->setFlashdata('success', 'Contact details unlocked for ' . esc($donor['name']) . '! Phone: ' . esc($donor['phone']) . ' | Email: ' . esc($donor['email']));
        session()->setFlashdata('contacted_donor_id', $donorUserId);

        return redirect()->back();
    }

    /**
     * Update blood request status.
     */
    public function updateStatus($id = null)
    {
        $request = $this->requestModel->find((int) $id);

        if (! $request) {
            session()->setFlashdata('error', 'Blood request not found.');
            return redirect()->to('/requests');
        }

        $userId   = session()->get('user_id');
        $userRole = strtolower((string) session()->get('role_name'));

        // Authorization check: Requester owner, Blood Bank Manager, or Admin
        if ($request['requester_id'] != $userId && ! in_array($userRole, ['admin', 'blood bank manager'], true)) {
            session()->setFlashdata('error', 'Unauthorized action: You do not have permission to modify this request status.');
            return redirect()->to('/requests/' . $id);
        }

        $newStatus = strtolower(trim((string) ($this->request->getPost('status') ?? '')));

        if (empty($newStatus)) {
            session()->setFlashdata('error', 'Invalid status transition.');
            return redirect()->to('/requests/' . $id);
        }

        $currentStatus = strtolower((string) $request['status']);

        // Check for terminal state modification attempt
        if (in_array($currentStatus, ['fulfilled', 'completed', 'cancelled'], true) && $userRole !== 'admin') {
            session()->setFlashdata('error', 'Cannot alter a request that is already fulfilled or cancelled.');
            return redirect()->to('/requests/' . $id);
        }

        // Enforce lifecycle transition rules
        if (! $this->requestModel->isValidStatusTransition($currentStatus, $newStatus, $userRole)) {
            session()->setFlashdata('error', 'Invalid status transition.');
            return redirect()->to('/requests/' . $id);
        }

        // Save updated status and updated_at timestamp explicitly
        $now = date('Y-m-d H:i:s');
        $this->requestModel->update((int) $id, [
            'status'     => $newStatus,
            'updated_at' => $now,
        ]);

        // Inventory Deduction upon Fulfillment / Completion
        if (in_array($newStatus, ['fulfilled', 'completed'], true) && ! in_array($currentStatus, ['fulfilled', 'completed'], true)) {
            $inventoryModel = new \App\Models\BloodInventoryModel();
            $inventoryModel->deductForFulfillment(
                (int) $request['blood_group_id'],
                (string) $request['city'],
                (int) $request['units_required'],
                (int) $userId,
                (int) $id
            );
        }

        // Inventory Restoration upon Cancellation of a Fulfilled/Completed Request
        if ($newStatus === 'cancelled' && in_array($currentStatus, ['fulfilled', 'completed'], true)) {
            $inventoryModel = new \App\Models\BloodInventoryModel();
            $inventoryModel->restoreForCancellation(
                (int) $request['blood_group_id'],
                (string) $request['city'],
                (int) $request['units_required'],
                (int) $userId,
                (int) $id
            );
        }

        $formattedOld = ucfirst(str_replace('_', ' ', $currentStatus));
        $formattedNew = ucfirst(str_replace('_', ' ', $newStatus));

        // 1. Notify request owner (requester_id)
        $this->notificationModel->notifyUser(
            (int) $request['requester_id'],
            'Blood Request #' . $id . ' Status Updated',
            'Emergency blood request #' . $id . ' status changed from "' . $formattedOld . '" to "' . $formattedNew . '".',
            'status',
            (int) $userId,
            (int) $id
        );

        // 2. If updated by requester owner, also notify Admins (Role 1) and Managers (Role 4)
        if ($request['requester_id'] == $userId) {
            $this->notificationModel->notifyRole(
                1,
                'Blood Request #' . $id . ' Status Updated',
                'Hospital requester updated request #' . $id . ' status to "' . $formattedNew . '".',
                'status',
                (int) $userId,
                (int) $id
            );
            $this->notificationModel->notifyRole(
                4,
                'Blood Request #' . $id . ' Status Updated',
                'Emergency request #' . $id . ' status updated to "' . $formattedNew . '".',
                'status',
                (int) $userId,
                (int) $id
            );
        }

        session()->setFlashdata('success', 'Blood request status updated to ' . $formattedNew . '.');

        return redirect()->to('/requests/' . $id);
    }

    /**
     * Cancel an active blood request.
     */
    public function cancel($id = null)
    {
        return $this->updateStatus($id);
    }
}
