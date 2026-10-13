<?php

use App\Models\BloodRequestModel;
use App\Models\DonorModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DonorDashboardTest extends CIUnitTestCase
{
    protected BloodRequestModel $requestModel;
    protected DonorModel $donorModel;
    protected UserModel $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestModel = new BloodRequestModel();
        $this->donorModel   = new DonorModel();
        $this->userModel    = new UserModel();
    }

    /**
     * Test BloodRequestModel::getPendingRequests method existence and return format.
     */
    public function testBloodRequestModelHasGetPendingRequests(): void
    {
        $this->assertTrue(method_exists($this->requestModel, 'getPendingRequests'));

        // Test calling with no arguments
        $allPending = $this->requestModel->getPendingRequests();
        $this->assertIsArray($allPending);

        // Test calling with blood_group_id and city arguments as passed by Donor controller
        $filteredPending = $this->requestModel->getPendingRequests(1, 'Dhule');
        $this->assertIsArray($filteredPending);
    }

    /**
     * Test Donor dashboard controller pending request matching data structure.
     */
    public function testDonorDashboardPendingRequestsFetch(): void
    {
        $donor = [
            'blood_group_id' => 1,
            'city'           => 'Dhule',
        ];

        $matchingRequests = [];
        if (! empty($donor['blood_group_id'])) {
            $matchingRequests = $this->requestModel->getPendingRequests($donor['blood_group_id'], $donor['city']);
        }

        $this->assertIsArray($matchingRequests);
    }

    /**
     * Test Hospital Find Donors returns available eligible O+ donors with empty city filter.
     */
    public function testHospitalFindDonorsReturnsAvailableEligibleDonor(): void
    {
        $matchingService = new \App\Services\BloodMatchingService();
        
        // Search for O+ (ID 7) with empty city filter
        $matchingDonors = $matchingService->findMatchingDonors(7, '');

        $this->assertIsArray($matchingDonors);
        $this->assertNotEmpty($matchingDonors, 'Hospital Find Donors should return available eligible O+ donors when city filter is empty.');

        $foundKrishna = false;
        foreach ($matchingDonors as $donor) {
            $this->assertSame('available', $donor['availability']);
            $this->assertSame('eligible', $donor['medical_eligibility']);
            if (strtolower($donor['donor_name'] ?? '') === 'krishna') {
                $foundKrishna = true;
            }
        }

        $this->assertTrue($foundKrishna, 'Donor krishna should be found in Find Donors results.');
    }

    /**
     * Test City Filtering strictly excludes donors from non-matching cities.
     */
    public function testFindDonorsCityFilterStrictlyExcludesMismatchedCities(): void
    {
        $matchingService = new \App\Services\BloodMatchingService();

        // Search for O+ in 'mumbai'
        $mumbaiDonors = $matchingService->findMatchingDonors(7, 'mumbai');
        $this->assertNotEmpty($mumbaiDonors);
        foreach ($mumbaiDonors as $d) {
            $this->assertStringContainsStringIgnoringCase('mumbai', $d['city']);
        }

        // Search for O+ in 'Dhule' (mumbai donors must NOT appear)
        $dhuleDonors = $matchingService->findMatchingDonors(7, 'Dhule');
        foreach ($dhuleDonors as $d) {
            $this->assertStringContainsStringIgnoringCase('Dhule', $d['city']);
            $this->assertStringDoesNotContainIgnoreCase('mumbai', $d['city']);
        }
    }
}
