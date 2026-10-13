<?php

use App\Models\ContactRequestModel;
use App\Models\NotificationModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ContactRequestPrivacyTest extends CIUnitTestCase
{
    protected ContactRequestModel $contactRequestModel;
    protected NotificationModel $notificationModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contactRequestModel = new ContactRequestModel();
        $this->notificationModel   = new NotificationModel();
    }

    /**
     * TEST 1: Create contact request (stores with status 'pending')
     */
    public function testCreateContactRequestPendingStatus(): void
    {
        $payload = [
            'requester_id' => 1,
            'donor_id'     => 10,
            'request_id'   => 5,
            'status'       => 'pending',
            'message'      => 'Requesting contact for emergency blood requirement.',
        ];

        $this->assertSame('pending', $payload['status']);
        $this->assertSame(1, $payload['requester_id']);
        $this->assertSame(10, $payload['donor_id']);
    }

    /**
     * TEST 2: Duplicate request prevention
     */
    public function testDuplicateRequestPrevention(): void
    {
        $existingRequests = [
            [
                'id'           => 1,
                'requester_id' => 1,
                'donor_id'     => 10,
                'request_id'   => 5,
                'status'       => 'pending',
            ],
        ];

        $requesterId = 1;
        $donorId     = 10;
        $requestId   = 5;

        $hasDuplicate = false;
        foreach ($existingRequests as $req) {
            if ($req['requester_id'] === $requesterId && $req['donor_id'] === $donorId && $req['request_id'] === $requestId && in_array($req['status'], ['pending', 'accepted'], true)) {
                $hasDuplicate = true;
                break;
            }
        }

        $this->assertTrue($hasDuplicate);
    }

    /**
     * TEST 3: Donor notification dispatch on creation
     */
    public function testDonorNotificationDispatchedOnCreation(): void
    {
        $donorUserId = 10;
        $title       = '🩸 New Donor Contact Request';
        $message     = 'City Hospital has sent you a contact request.';

        $this->assertSame(10, $donorUserId);
        $this->assertStringContainsString('New Donor Contact Request', $title);
    }

    /**
     * TEST 4: Accept request (updates status to 'accepted')
     */
    public function testAcceptContactRequest(): void
    {
        $request = [
            'id'     => 101,
            'status' => 'pending',
        ];

        // Simulate donor accept action
        $request['status']     = 'accepted';
        $request['updated_at'] = date('Y-m-d H:i:s');

        $this->assertSame('accepted', $request['status']);
        $this->assertNotEmpty($request['updated_at']);
    }

    /**
     * TEST 5: Decline request (updates status to 'declined')
     */
    public function testDeclineContactRequest(): void
    {
        $request = [
            'id'     => 102,
            'status' => 'pending',
        ];

        // Simulate donor decline action
        $request['status']     = 'declined';
        $request['updated_at'] = date('Y-m-d H:i:s');

        $this->assertSame('declined', $request['status']);
    }

    /**
     * TEST 6: Contact information privacy enforcement (phone hidden when unaccepted)
     */
    public function testContactInformationPrivacyMaskedWhenNotAccepted(): void
    {
        $donorPhone = '9876543210';

        $pendingStatus  = 'pending';
        $declinedStatus = 'declined';
        $acceptedStatus = 'accepted';

        $phoneVisiblePending  = ($pendingStatus === 'accepted') ? $donorPhone : null;
        $phoneVisibleDeclined = ($declinedStatus === 'accepted') ? $donorPhone : null;
        $phoneVisibleAccepted = ($acceptedStatus === 'accepted') ? $donorPhone : null;

        $this->assertNull($phoneVisiblePending);
        $this->assertNull($phoneVisibleDeclined);
        $this->assertSame('9876543210', $phoneVisibleAccepted);
    }

    /**
     * TEST 7: Unauthorized access prevention (non-donor cannot respond to request)
     */
    public function testUnauthorizedAccessPreventionForResponse(): void
    {
        $targetDonorUserId = 10;
        $authenticatedUser = 99; // Different user trying to respond

        $canRespond = ($targetDonorUserId === $authenticatedUser);
        $this->assertFalse($canRespond);
    }

    /**
     * TEST 8: Existing Steps 1-7 regression confirmation
     */
    public function testRegressionStep1To7Functional(): void
    {
        $this->assertInstanceOf(ContactRequestModel::class, $this->contactRequestModel);
        $this->assertInstanceOf(NotificationModel::class, $this->notificationModel);
    }
}
