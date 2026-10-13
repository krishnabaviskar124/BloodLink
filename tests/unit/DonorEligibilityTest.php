<?php

use App\Services\DonorEligibilityService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DonorEligibilityTest extends CIUnitTestCase
{
    protected DonorEligibilityService $eligibilityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eligibilityService = new DonorEligibilityService();
    }

    /**
     * Rule 1: Active donor + no previous donation = ELIGIBLE
     */
    public function testActiveDonorNoPreviousDonationIsEligible(): void
    {
        $donor = [
            'user_status'        => 'active',
            'blood_group_id'     => 1,
            'last_donation_date' => null,
        ];

        $result = $this->eligibilityService->evaluateEligibility($donor);

        $this->assertTrue($result['is_eligible']);
        $this->assertSame('eligible', $result['status']);
        $this->assertSame('ELIGIBLE', $result['label']);
    }

    /**
     * Rule 2: Donation exactly 90 days ago = ELIGIBLE
     */
    public function testDonationExactly90DaysAgoIsEligible(): void
    {
        $referenceDate = '2026-08-11';
        $donationDate  = date('Y-m-d', strtotime('-90 days', strtotime($referenceDate)));

        $donor = [
            'user_status'        => 'active',
            'blood_group_id'     => 1,
            'last_donation_date' => $donationDate,
        ];

        $result = $this->eligibilityService->evaluateEligibility($donor, $referenceDate);

        $this->assertTrue($result['is_eligible']);
        $this->assertSame('eligible', $result['status']);
        $this->assertSame('ELIGIBLE', $result['label']);
        $this->assertSame(90, $result['days_elapsed']);
    }

    /**
     * Rule 3: Donation less than 90 days ago = NOT ELIGIBLE
     */
    public function testDonationLessThan90DaysAgoIsNotEligible(): void
    {
        $referenceDate = '2026-08-11';
        $donationDate  = date('Y-m-d', strtotime('-45 days', strtotime($referenceDate)));

        $donor = [
            'user_status'        => 'active',
            'blood_group_id'     => 1,
            'last_donation_date' => $donationDate,
        ];

        $result = $this->eligibilityService->evaluateEligibility($donor, $referenceDate);

        $this->assertFalse($result['is_eligible']);
        $this->assertSame('ineligible', $result['status']);
        $this->assertSame('NOT ELIGIBLE', $result['label']);
        $this->assertSame(45, $result['days_elapsed']);
        $this->assertSame(45, $result['days_remaining']);
    }

    /**
     * Rule 4: Donation older than 90 days = ELIGIBLE
     */
    public function testDonationOlderThan90DaysIsEligible(): void
    {
        $referenceDate = '2026-08-11';
        $donationDate  = date('Y-m-d', strtotime('-120 days', strtotime($referenceDate)));

        $donor = [
            'user_status'        => 'active',
            'blood_group_id'     => 1,
            'last_donation_date' => $donationDate,
        ];

        $result = $this->eligibilityService->evaluateEligibility($donor, $referenceDate);

        $this->assertTrue($result['is_eligible']);
        $this->assertSame('eligible', $result['status']);
        $this->assertSame('ELIGIBLE', $result['label']);
        $this->assertSame(120, $result['days_elapsed']);
    }

    /**
     * Rule 5: Future donation date = NOT ELIGIBLE
     */
    public function testFutureDonationDateIsNotEligible(): void
    {
        $referenceDate = '2026-08-11';
        $donationDate  = '2026-08-15'; // Future date

        $donor = [
            'user_status'        => 'active',
            'blood_group_id'     => 1,
            'last_donation_date' => $donationDate,
        ];

        $result = $this->eligibilityService->evaluateEligibility($donor, $referenceDate);

        $this->assertFalse($result['is_eligible']);
        $this->assertSame('ineligible', $result['status']);
        $this->assertSame('NOT ELIGIBLE', $result['label']);
    }

    /**
     * Rule 6: Invalid/missing donation date = handled safely
     */
    public function testInvalidDonationDateHandledSafely(): void
    {
        $donor = [
            'user_status'        => 'active',
            'blood_group_id'     => 1,
            'last_donation_date' => 'invalid-date-string',
        ];

        $result = $this->eligibilityService->evaluateEligibility($donor);

        $this->assertIsArray($result);
        $this->assertTrue($result['is_eligible']);
        $this->assertSame('ELIGIBLE', $result['label']);
    }

    /**
     * Rule 7: Inactive donor = NOT ELIGIBLE
     */
    public function testInactiveDonorIsNotEligible(): void
    {
        $donor = [
            'user_status'        => 'inactive',
            'blood_group_id'     => 1,
            'last_donation_date' => null,
        ];

        $result = $this->eligibilityService->evaluateEligibility($donor);

        $this->assertFalse($result['is_eligible']);
        $this->assertSame('ineligible', $result['status']);
        $this->assertSame('NOT ELIGIBLE', $result['label']);
    }

    /**
     * Rule 8: Missing blood group = NOT ELIGIBLE
     */
    public function testMissingBloodGroupIsNotEligible(): void
    {
        $donor = [
            'user_status'        => 'active',
            'blood_group_id'     => null,
            'last_donation_date' => null,
        ];

        $result = $this->eligibilityService->evaluateEligibility($donor);

        $this->assertFalse($result['is_eligible']);
        $this->assertSame('ineligible', $result['status']);
        $this->assertSame('NOT ELIGIBLE', $result['label']);
    }
}
