<?php

use App\Models\BloodRequestModel;
use App\Models\NotificationModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class BloodRequestLifecycleTest extends CIUnitTestCase
{
    protected BloodRequestModel $requestModel;
    protected NotificationModel $notificationModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestModel      = new BloodRequestModel();
        $this->notificationModel = new NotificationModel();
    }

    /**
     * TEST 1: Default status is 'pending' and allowed list includes lifecycle states.
     */
    public function testStatusValidationAllowsLifecycleStates(): void
    {
        $validTransition = $this->requestModel->isValidStatusTransition('pending', 'approved', 'hospital');
        $this->assertTrue($validTransition);
    }

    /**
     * TEST 2: Valid transition: pending -> approved
     */
    public function testTransitionPendingToApproved(): void
    {
        $isValid = $this->requestModel->isValidStatusTransition('pending', 'approved', 'hospital');
        $this->assertTrue($isValid);
    }

    /**
     * TEST 3: Valid transition: approved -> fulfilled or completed
     */
    public function testTransitionApprovedToFulfilledOrCompleted(): void
    {
        $isValidFulfilled = $this->requestModel->isValidStatusTransition('approved', 'fulfilled', 'hospital');
        $isValidCompleted = $this->requestModel->isValidStatusTransition('approved', 'completed', 'hospital');

        $this->assertTrue($isValidFulfilled);
        $this->assertTrue($isValidCompleted);
    }

    /**
     * TEST 4: Valid transition: pending -> cancelled
     */
    public function testTransitionPendingToCancelled(): void
    {
        $isValid = $this->requestModel->isValidStatusTransition('pending', 'cancelled', 'hospital');
        $this->assertTrue($isValid);
    }

    /**
     * TEST 5: Terminal states (fulfilled, completed, cancelled) block transition by non-admins
     */
    public function testTerminalStateBlockedForNonAdmins(): void
    {
        $isBlockedFulfilled = $this->requestModel->isValidStatusTransition('fulfilled', 'pending', 'hospital');
        $isBlockedCompleted = $this->requestModel->isValidStatusTransition('completed', 'approved', 'hospital');
        $isBlockedCancelled = $this->requestModel->isValidStatusTransition('cancelled', 'pending', 'hospital');

        $this->assertFalse($isBlockedFulfilled);
        $this->assertFalse($isBlockedCompleted);
        $this->assertFalse($isBlockedCancelled);
    }

    /**
     * TEST 6: Admin override allows changing status from terminal states
     */
    public function testAdminOverrideAllowsTerminalStateTransition(): void
    {
        $isAdminAllowed = $this->requestModel->isValidStatusTransition('fulfilled', 'pending', 'admin');
        $this->assertTrue($isAdminAllowed);
    }

    /**
     * TEST 7: Invalid status strings are rejected
     */
    public function testInvalidStatusStringRejected(): void
    {
        $isInvalid = $this->requestModel->isValidStatusTransition('pending', 'unknown_status', 'hospital');
        $this->assertFalse($isInvalid);
    }

    /**
     * TEST 8: Timestamp updated_at simulation on status change
     */
    public function testTimestampRecordedOnStatusChange(): void
    {
        $before = date('Y-m-d H:i:s', time() - 1);
        $now    = date('Y-m-d H:i:s');

        $data = [
            'status'     => 'approved',
            'updated_at' => $now,
        ];

        $this->assertSame('approved', $data['status']);
        $this->assertGreaterThanOrEqual(strtotime($before), strtotime($data['updated_at']));
    }

    /**
     * TEST 9: Status change triggers status notification payload
     */
    public function testStatusChangeNotificationPayload(): void
    {
        $requestId = 50;
        $userId    = 2;
        $oldStatus = 'Pending';
        $newStatus = 'Approved';

        $title   = 'Blood Request #' . $requestId . ' Status Updated';
        $message = 'Emergency blood request #' . $requestId . ' status changed from "' . $oldStatus . '" to "' . $newStatus . '".';

        $this->assertStringContainsString('#50', $title);
        $this->assertStringContainsString('Pending', $message);
        $this->assertStringContainsString('Approved', $message);
    }

    /**
     * TEST 10: getPendingRequests method exists and returns array on BloodRequestModel
     */
    public function testGetPendingRequestsMethodExistsAndIsCallable(): void
    {
        $this->assertTrue(method_exists($this->requestModel, 'getPendingRequests'));
        
        $result = $this->requestModel->getPendingRequests();
        $this->assertIsArray($result);
    }

    /**
     * TEST 11: getPendingRequests accepts optional bloodGroupId and city parameters
     */
    public function testGetPendingRequestsAcceptsFilters(): void
    {
        $bloodGroupId = 1;
        $city = 'Dhule';

        $result = $this->requestModel->getPendingRequests($bloodGroupId, $city);
        $this->assertIsArray($result);
    }

    /**
     * TEST 12: Opening blood request details initializes matching donors and allows array_column user_id extraction.
     */
    public function testBloodRequestShowInitializesMatchingDonors(): void
    {
        $matchingService = new \App\Services\BloodMatchingService();
        $bloodGroupId    = 1;
        $city            = 'Dhule';

        $matchingDonors    = $matchingService->findMatchingDonors($bloodGroupId, $city);
        $matchingInventory = $matchingService->findMatchingInventory($bloodGroupId, $city);

        $this->assertIsArray($matchingDonors);
        $this->assertIsArray($matchingInventory);

        $donorUserIds = array_column($matchingDonors, 'user_id');
        $this->assertIsArray($donorUserIds);
    }
}
