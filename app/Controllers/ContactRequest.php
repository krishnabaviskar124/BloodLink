<?php

namespace App\Controllers;

use App\Models\ContactRequestModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class ContactRequest extends BaseController
{
    protected ContactRequestModel $contactRequestModel;
    protected NotificationModel $notificationModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->contactRequestModel = new ContactRequestModel();
        $this->notificationModel   = new NotificationModel();
        $this->userModel           = new UserModel();
    }

    /**
     * Store / Send new donor contact request (Authenticated users only)
     */
    public function store($donorUserId = null)
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please log in to contact donors.');
            return redirect()->to('/login');
        }

        $donorId   = (int) ($this->request->getPost('donor_id') ?: $donorUserId);
        $requestId = $this->request->getPost('request_id') ? (int) $this->request->getPost('request_id') : null;
        $message   = trim((string) $this->request->getPost('message'));

        $requesterId = (int) session()->get('user_id');

        if ($donorId <= 0) {
            session()->setFlashdata('error', 'Invalid donor selected.');
            return redirect()->back();
        }

        if ($requesterId === $donorId) {
            session()->setFlashdata('error', 'You cannot send a contact request to yourself.');
            return redirect()->back();
        }

        $donor = $this->userModel->find($donorId);
        if (! $donor) {
            session()->setFlashdata('error', 'Donor user record not found.');
            return redirect()->back();
        }

        // Duplicate Active Request Prevention
        if ($this->contactRequestModel->hasActiveRequest($requesterId, $donorId, $requestId)) {
            session()->setFlashdata('error', 'A contact request has already been sent to this donor.');
            return redirect()->back();
        }

        // Insert Contact Request in Pending State
        $this->contactRequestModel->insert([
            'requester_id' => $requesterId,
            'donor_id'     => $donorId,
            'request_id'   => $requestId,
            'status'       => 'pending',
            'message'      => $message,
        ]);

        $requesterName  = session()->get('name') ?: 'A Requester';
        $requesterPhone = session()->get('phone') ?: 'Not specified';

        // Notify Donor about incoming Contact Request
        $noteMessage = "{$requesterName} (Phone: {$requesterPhone}) has sent you a contact request regarding a blood requirement. Please review and respond in your dashboard.";
        if ($requestId) {
            $noteMessage = "{$requesterName} (Phone: {$requesterPhone}) sent you a contact request for Emergency Request #{$requestId}. Please accept or decline.";
        }

        $this->notificationModel->notifyUser(
            $donorId,
            '🩸 New Donor Contact Request',
            $noteMessage,
            'contact',
            $requesterId,
            $requestId,
            'high'
        );

        session()->setFlashdata('success', 'Contact request successfully sent to ' . esc($donor['name']) . '! Awaiting donor approval.');

        return redirect()->back();
    }

    /**
     * Accept or Decline an incoming contact request (Donor only)
     */
    public function respond($id = null)
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please log in to respond to contact requests.');
            return redirect()->to('/login');
        }

        $contactRequest = $this->contactRequestModel->find((int) $id);

        if (! $contactRequest) {
            session()->setFlashdata('error', 'Contact request record not found.');
            return redirect()->back();
        }

        $userId   = (int) session()->get('user_id');
        $userRole = strtolower((string) session()->get('role_name'));

        // Authorization check: User must be the target donor (or Admin)
        if ($contactRequest['donor_id'] != $userId && $userRole !== 'admin') {
            session()->setFlashdata('error', 'Unauthorized action: You cannot respond to another user\'s contact request.');
            return redirect()->back();
        }

        $action = strtolower(trim((string) $this->request->getPost('action')));
        if (! in_array($action, ['accept', 'decline'], true)) {
            session()->setFlashdata('error', 'Invalid contact request action.');
            return redirect()->back();
        }

        $newStatus = ($action === 'accept') ? 'accepted' : 'declined';

        $this->contactRequestModel->update($id, [
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $donorName = session()->get('name') ?: 'The Donor';

        // Notify Requester about donor response
        $this->notificationModel->notifyUser(
            (int) $contactRequest['requester_id'],
            'Contact Request ' . ucfirst($newStatus),
            'Donor ' . $donorName . ' has ' . strtoupper($newStatus) . ' your contact request.',
            'contact',
            $userId,
            $contactRequest['request_id'] ? (int) $contactRequest['request_id'] : null,
            ($newStatus === 'accepted' ? 'high' : 'normal')
        );

        session()->setFlashdata('success', 'Contact request status updated to ' . ucfirst($newStatus) . '.');

        return redirect()->back();
    }

    /**
     * Cancel an outgoing contact request (Requester only)
     */
    public function cancel($id = null)
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please log in.');
            return redirect()->to('/login');
        }

        $contactRequest = $this->contactRequestModel->find((int) $id);

        if (! $contactRequest) {
            session()->setFlashdata('error', 'Contact request record not found.');
            return redirect()->back();
        }

        $userId   = (int) session()->get('user_id');
        $userRole = strtolower((string) session()->get('role_name'));

        // Authorization check: User must be the requester (or Admin)
        if ($contactRequest['requester_id'] != $userId && $userRole !== 'admin') {
            session()->setFlashdata('error', 'Unauthorized action: You cannot cancel another user\'s contact request.');
            return redirect()->back();
        }

        $this->contactRequestModel->update($id, [
            'status'     => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', 'Contact request cancelled.');

        return redirect()->back();
    }
}
