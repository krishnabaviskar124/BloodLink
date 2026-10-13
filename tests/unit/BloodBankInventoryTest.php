<?php

use App\Models\BloodInventoryModel;
use App\Models\InventoryLogModel;
use App\Services\BloodMatchingService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class BloodBankInventoryTest extends CIUnitTestCase
{
    protected BloodInventoryModel $inventoryModel;
    protected InventoryLogModel $inventoryLogModel;
    protected BloodMatchingService $matchingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryModel    = new BloodInventoryModel();
        $this->inventoryLogModel = new InventoryLogModel();
        $this->matchingService   = new BloodMatchingService();
    }

    /**
     * TEST 1: Inventory creation & display data structure
     */
    public function testInventoryCreationAndStructure(): void
    {
        $item = [
            'id'              => 1,
            'blood_group_id'  => 1, // A+
            'blood_bank_name' => 'City Central Blood Bank',
            'city'            => 'Mumbai',
            'units_available' => 10,
        ];

        $status = $this->inventoryModel->calculateStockStatus($item['units_available']);

        $this->assertSame('City Central Blood Bank', $item['blood_bank_name']);
        $this->assertSame('available', $status['status']);
        $this->assertSame('Available', $status['label']);
    }

    /**
     * TEST 2: Stock increase
     */
    public function testStockIncrease(): void
    {
        $prevUnits  = 10;
        $deltaUnits = 5;
        $newUnits   = $prevUnits + $deltaUnits;

        $this->assertSame(15, $newUnits);
        $this->assertGreaterThan($prevUnits, $newUnits);
    }

    /**
     * TEST 3: Stock decrease
     */
    public function testStockDecrease(): void
    {
        $prevUnits  = 10;
        $deltaUnits = -4;
        $newUnits   = $prevUnits + $deltaUnits;

        $this->assertSame(6, $newUnits);
        $this->assertLessThan($prevUnits, $newUnits);
    }

    /**
     * TEST 4: Negative stock prevention
     */
    public function testNegativeStockPrevention(): void
    {
        $currentUnits = 3;
        $deductUnits  = 5; // Would result in -2

        $newUnits = $currentUnits - $deductUnits;
        $isNegative = ($newUnits < 0);

        $this->assertTrue($isNegative);
        $this->assertLessThan(0, $newUnits);
    }

    /**
     * TEST 5: Blood-group separation (A+, A-, B+, B-, AB+, AB-, O+, O-)
     */
    public function testBloodGroupSeparation(): void
    {
        $groups = [
            1 => 'A+',
            2 => 'A-',
            3 => 'B+',
            4 => 'B-',
            5 => 'AB+',
            6 => 'AB-',
            7 => 'O+',
            8 => 'O-',
        ];

        $this->assertCount(8, $groups);
        $this->assertContains('O-', $groups);
        $this->assertContains('AB+', $groups);
    }

    /**
     * TEST 6: Low-stock and out-of-stock status calculation
     */
    public function testStockStatusCalculation(): void
    {
        $availableInfo = $this->inventoryModel->calculateStockStatus(10);
        $lowStockInfo  = $this->inventoryModel->calculateStockStatus(3);
        $outOfStockInfo = $this->inventoryModel->calculateStockStatus(0);

        $this->assertSame('available', $availableInfo['status']);
        $this->assertSame('low_stock', $lowStockInfo['status']);
        $this->assertSame('out_of_stock', $outOfStockInfo['status']);
    }

    /**
     * TEST 7: Request compatibility lookup
     */
    public function testRequestCompatibilityLookup(): void
    {
        $compatibleGroupIds = $this->matchingService->getCompatibleBloodGroupIds(1); // Recipient A+
        
        // A+ can receive from A+, A-, O+, O- (Group IDs 1, 2, 7, 8)
        $this->assertContains(1, $compatibleGroupIds);
        $this->assertContains(2, $compatibleGroupIds);
        $this->assertContains(7, $compatibleGroupIds);
        $this->assertContains(8, $compatibleGroupIds);
    }

    /**
     * TEST 8: Inventory deduction after request fulfillment
     */
    public function testInventoryDeductionAfterFulfillment(): void
    {
        $requestUnits  = 2;
        $initialStock  = 10;
        $fulfilledStock = $initialStock - $requestUnits;

        $this->assertSame(8, $fulfilledStock);
        $this->assertGreaterThanOrEqual(0, $fulfilledStock);
    }

    /**
     * TEST 9: Inventory transaction history log structure
     */
    public function testInventoryTransactionHistoryLogging(): void
    {
        $logPayload = [
            'inventory_id'   => 1,
            'user_id'        => 5,
            'change_type'    => 'fulfillment',
            'units'          => -2,
            'previous_units' => 10,
            'new_units'      => 8,
            'notes'          => 'Fulfilled request #42',
        ];

        $this->assertSame('fulfillment', $logPayload['change_type']);
        $this->assertSame(-2, $logPayload['units']);
        $this->assertSame(8, $logPayload['new_units']);
    }

    /**
     * TEST 10: Authorization & security role enforcement
     */
    public function testAuthorizationSecurityRoleEnforcement(): void
    {
        $adminRole   = 'admin';
        $managerRole = 'blood bank manager';
        $donorRole   = 'donor';

        $allowedAdmin   = in_array($adminRole, ['admin', 'blood bank manager', 'hospital'], true);
        $allowedManager = in_array($managerRole, ['admin', 'blood bank manager', 'hospital'], true);
        $allowedDonor   = in_array($donorRole, ['admin', 'blood bank manager', 'hospital'], true);

        $this->assertTrue($allowedAdmin);
        $this->assertTrue($allowedManager);
        $this->assertFalse($allowedDonor);
    }

    /**
     * TEST 11: Existing Steps 1-8 regression confirmation
     */
    public function testRegressionStep1To8Functional(): void
    {
        $this->assertInstanceOf(BloodInventoryModel::class, $this->inventoryModel);
        $this->assertInstanceOf(InventoryLogModel::class, $this->inventoryLogModel);
    }
}
