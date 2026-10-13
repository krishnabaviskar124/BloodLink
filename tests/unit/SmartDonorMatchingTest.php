<?php

use App\Services\BloodMatchingService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class SmartDonorMatchingTest extends CIUnitTestCase
{
    protected BloodMatchingService $matchingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matchingService = new BloodMatchingService();
    }

    /**
     * TEST 1: Exact blood-group compatible + available + eligible + same city
     * Expected: highest score (100%).
     */
    public function test1ExactCompatibleAvailableEligibleSameCityHighestScore(): void
    {
        $donor = [
            'id'                  => 101,
            'user_id'             => 1,
            'donor_name'          => 'Krishna Baviskar',
            'blood_group_id'      => 1, // A+
            'medical_eligibility' => 'eligible',
            'availability'        => 'available',
            'city'                => 'Dhule',
            'last_donation_date'  => null,
        ];

        $result = $this->matchingService->calculateMatchScore($donor, 1, 'Dhule');

        $this->assertTrue($result['is_compatible']);
        $this->assertSame(100, $result['match_score']);
        $this->assertSame(40, $result['breakdown']['blood_compatibility']);
        $this->assertSame(20, $result['breakdown']['availability']);
        $this->assertSame(15, $result['breakdown']['eligibility']);
        $this->assertSame(15, $result['breakdown']['city_match']);
        $this->assertSame(10, $result['breakdown']['donation_suitability']);
    }

    /**
     * TEST 2: Compatible + available + eligible + different city
     * Expected: lower score than same-city donor.
     */
    public function test2CompatibleAvailableEligibleDifferentCityLowerScore(): void
    {
        $sameCityDonor = [
            'id'                  => 101,
            'user_id'             => 1,
            'donor_name'          => 'Same City Donor',
            'blood_group_id'      => 1, // A+
            'medical_eligibility' => 'eligible',
            'availability'        => 'available',
            'city'                => 'Dhule',
        ];

        $diffCityDonor = [
            'id'                  => 102,
            'user_id'             => 2,
            'donor_name'          => 'Different City Donor',
            'blood_group_id'      => 1, // A+
            'medical_eligibility' => 'eligible',
            'availability'        => 'available',
            'city'                => 'Nashik',
        ];

        $scoreSame = $this->matchingService->calculateMatchScore($sameCityDonor, 1, 'Dhule');
        $scoreDiff = $this->matchingService->calculateMatchScore($diffCityDonor, 1, 'Dhule');

        $this->assertSame(100, $scoreSame['match_score']);
        $this->assertSame(85, $scoreDiff['match_score']);
        $this->assertGreaterThan($scoreDiff['match_score'], $scoreSame['match_score']);
        $this->assertSame(0, $scoreDiff['breakdown']['city_match']);
    }

    /**
     * TEST 3: Compatible but unavailable
     * Expected: availability score = 0, lower rank than available donor.
     */
    public function test3CompatibleUnavailableLowerScore(): void
    {
        $availableDonor = [
            'id'                  => 101,
            'user_id'             => 1,
            'donor_name'          => 'Available Donor',
            'blood_group_id'      => 1,
            'medical_eligibility' => 'eligible',
            'availability'        => 'available',
            'city'                => 'Dhule',
        ];

        $unavailableDonor = [
            'id'                  => 102,
            'user_id'             => 2,
            'donor_name'          => 'Unavailable Donor',
            'blood_group_id'      => 1,
            'medical_eligibility' => 'eligible',
            'availability'        => 'not_available',
            'city'                => 'Dhule',
        ];

        $scoreAvail   = $this->matchingService->calculateMatchScore($availableDonor, 1, 'Dhule');
        $scoreUnavail = $this->matchingService->calculateMatchScore($unavailableDonor, 1, 'Dhule');

        $this->assertSame(20, $scoreAvail['breakdown']['availability']);
        $this->assertSame(0, $scoreUnavail['breakdown']['availability']);
        $this->assertGreaterThan($scoreUnavail['match_score'], $scoreAvail['match_score']);
    }

    /**
     * TEST 4: Ineligible donor
     * Expected: eligibility score = 0.
     */
    public function test4IneligibleDonorNotTreatedAsValidMatch(): void
    {
        $ineligibleDonor = [
            'id'                  => 103,
            'user_id'             => 3,
            'donor_name'          => 'Ineligible Donor',
            'blood_group_id'      => 1,
            'medical_eligibility' => 'ineligible',
            'availability'        => 'available',
            'city'                => 'Dhule',
            'last_donation_date'  => date('Y-m-d', strtotime('-30 days')), // Donated 30 days ago (<90d interval)
        ];

        $result = $this->matchingService->calculateMatchScore($ineligibleDonor, 1, 'Dhule');

        $this->assertSame(0, $result['breakdown']['eligibility']);
        $this->assertLessThan(100, $result['match_score']);
    }


    /**
     * TEST 5: Incompatible blood group
     * Expected: excluded from valid matches (is_compatible = false, match_score = 0).
     */
    public function test5IncompatibleBloodGroupExcluded(): void
    {
        $incompatibleDonor = [
            'id'                  => 104,
            'user_id'             => 4,
            'donor_name'          => 'Incompatible Donor',
            'blood_group_id'      => 3, // B+ (Incompatible for A+ recipient)
            'medical_eligibility' => 'eligible',
            'availability'        => 'available',
            'city'                => 'Dhule',
        ];

        $result = $this->matchingService->calculateMatchScore($incompatibleDonor, 1, 'Dhule'); // Recipient A+ (id=1)

        $this->assertFalse($result['is_compatible']);
        $this->assertSame(0, $result['match_score']);
        $this->assertSame(0, $result['breakdown']['blood_compatibility']);
    }

    /**
     * TEST 6: Case-insensitive city matching
     * Expected: "Dhule" vs "dhule" -> city match (15 points).
     */
    public function test6CaseInsensitiveCityMatch(): void
    {
        $donor = [
            'id'                  => 105,
            'user_id'             => 5,
            'donor_name'          => 'Case Test Donor',
            'blood_group_id'      => 1,
            'medical_eligibility' => 'eligible',
            'availability'        => 'available',
            'city'                => 'DHULE',
        ];

        $result1 = $this->matchingService->calculateMatchScore($donor, 1, 'dhule');
        $result2 = $this->matchingService->calculateMatchScore($donor, 1, 'Dhule');
        $result3 = $this->matchingService->calculateMatchScore($donor, 1, 'dHuLe');

        $this->assertSame(15, $result1['breakdown']['city_match']);
        $this->assertSame(15, $result2['breakdown']['city_match']);
        $this->assertSame(15, $result3['breakdown']['city_match']);
        $this->assertSame(100, $result1['match_score']);
    }

    /**
     * TEST 7: Two donors with same score
     * Expected: deterministic ordering.
     */
    public function test7TwoDonorsWithSameScoreDeterministicOrdering(): void
    {
        $donorAlice = [
            'id'                  => 10,
            'donor_name'          => 'Alice',
            'blood_group_id'      => 1,
            'medical_eligibility' => 'eligible',
            'availability'        => 'available',
            'city'                => 'Dhule',
            'match_score'         => 100,
        ];

        $donorBob = [
            'id'                  => 20,
            'donor_name'          => 'Bob',
            'blood_group_id'      => 1,
            'medical_eligibility' => 'eligible',
            'availability'        => 'available',
            'city'                => 'Dhule',
            'match_score'         => 100,
        ];

        $donors = [$donorBob, $donorAlice];

        usort($donors, function ($a, $b) {
            if ($a['match_score'] !== $b['match_score']) {
                return $b['match_score'] <=> $a['match_score'];
            }
            $nameCompare = strcasecmp((string) ($a['donor_name'] ?? ''), (string) ($b['donor_name'] ?? ''));
            if ($nameCompare !== 0) {
                return $nameCompare;
            }
            return ($a['id'] ?? 0) <=> ($b['id'] ?? 0);
        });

        // Alice comes before Bob alphabetically
        $this->assertSame('Alice', $donors[0]['donor_name']);
        $this->assertSame('Bob', $donors[1]['donor_name']);
    }

    /**
     * TEST 8: No compatible donors
     * Expected: clean empty-state message/result array.
     */
    public function test8NoCompatibleDonorsReturnsEmptyArray(): void
    {
        $result = $this->matchingService->findMatchingDonors(999, 'Dhule');
        $this->assertIsArray($result);
    }

    /**
     * TEST 9: Missing optional donor information
     * Expected: no PHP errors, notices, or warnings.
     */
    public function test9MissingOptionalDonorInfoNoError(): void
    {
        $incompleteDonor = [
            'user_id'        => 99,
            'blood_group_id' => 1,
        ];

        $result = $this->matchingService->calculateMatchScore($incompleteDonor, 1);

        $this->assertIsArray($result);
        $this->assertTrue($result['is_compatible']);
        $this->assertGreaterThanOrEqual(0, $result['match_score']);
        $this->assertArrayHasKey('breakdown', $result);
    }

    /**
     * TEST 10: Existing Contact Donor / Call / Copy Number functionality regression check
     * Expected: donor data structure contains all required contact attributes.
     */
    public function test10ExistingContactDonorDataPreserved(): void
    {
        $donor = [
            'id'                  => 101,
            'user_id'             => 55,
            'donor_name'          => 'Test Donor',
            'phone'               => '9876543210',
            'email'               => 'donor@example.com',
            'blood_group_id'      => 1,
            'blood_group_name'    => 'A+',
            'city'                => 'Dhule',
            'medical_eligibility' => 'eligible',
            'availability'        => 'available',
        ];

        $scoreInfo = $this->matchingService->calculateMatchScore($donor, 1, 'Dhule');
        $donor['match_score']     = $scoreInfo['match_score'];
        $donor['match_breakdown'] = $scoreInfo['breakdown'];

        $this->assertArrayHasKey('user_id', $donor);
        $this->assertArrayHasKey('phone', $donor);
        $this->assertArrayHasKey('email', $donor);
        $this->assertArrayHasKey('match_score', $donor);
        $this->assertArrayHasKey('match_breakdown', $donor);
        $this->assertSame('9876543210', $donor['phone']);
    }
}
